<?php declare(strict_types=1);

namespace App\Service\Beyond;

use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BeyondService
{
    private int $number = 1;
    private Filesystem $disk;
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'X-Api-Key' => env('SPEETCHKIT_TOKEN'),
                'accept' => 'application/json',
            ]
        ]);

        $this->disk = Storage::disk('spaces');
    }

    /**
     * @return array
     */
    public function getListProject(): array
    {
        $response = $this->client->get('https://api.beyondwords.io/v1/projects');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $projectId
     * @return array
     */
    public function findProjectOrFail(int $projectId): array
    {
        $lists = $this->getListProject();

        $project = collect($lists)->where('id', $projectId);

        if ($project->isEmpty()) {
            throw new NotFoundHttpException('Project not found');
        }

        return $project->first();
    }

    public function loadAllFiles(string $path)
    {
        return collect($this->disk->allFiles($path));
    }

    /**
     * @param int $projectId
     * @return void
     */
    public function downloadAudioByProject(int $projectId)
    {
        $project = $this->findProjectOrFail($projectId);

        $listFiles = $this->loadAllFiles("beyond/{$projectId}");

        $contents = $this->loadAllContent($projectId);

        echo "<h2>{$project['name']}</h2>";
        echo "<h2>Total in Storage: {$listFiles->count()}</h2>";
        echo "<h2>Total Contens: {$contents->count()}</h2>";

        $contents->chunk(10)->each(function($chunk) use ($project, $listFiles) {
            foreach ($chunk as $item) {
                $path = "beyond/{$project['id']}/{$item['source_id']}.mp3";
                $info = "{$this->number}) audio: {$item['id']} |Post: {$item['source_id']} |Path: {$path} |Success:";
                $this->number++;

                if (!isset($item['audio'][1])) {
                    echo "$info FALSE |Info: Missing record audio<br>";
                    continue;
                }

                if (in_array($path, $listFiles->toArray())) {
                    echo "$info true |Info: already in storage <br>";
                    continue;
                }

                $result = json_encode($this->loadAndStoreByUrl($item['audio'][1]['url'], $path));

                echo "$info {$result} <br>";
            }
            usleep(200000);//0.2 second
        });
    }

    /**
     * @param string $url
     * @param string $path
     * @return bool
     */
    public function loadAndStoreByUrl(string $url, string $path): bool
    {
        $responseAudio = $this->client->get($url)->getBody()->getContents();

        return $this->disk->put($path, $responseAudio);
    }

    /**
     * @param int $projectId
     * @return Collection
     */
    public function loadAllContent(int $projectId): Collection
    {
        $offset = 0;
        $contents = collect();
        do {
            $loadContents = $this->loadContents($projectId, $offset, $limit = 50);

            $contents = $contents->merge($loadContents);

            $offset += $limit;
        } while (count($loadContents) !== 0);

        return $contents;
    }

    /**
     * @param int $projectId
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function loadContents(int $projectId, int $offset, int $limit): array
    {
        $response = $this->client->get(
            "https://api.beyondwords.io/v1/projects/{$projectId}/content", [
            'form_params' => [
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
