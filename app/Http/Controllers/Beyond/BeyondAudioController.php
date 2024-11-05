<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BeyondAudioController
{
    private Client $client;

    private int $number = 1;
    private string $disk = 'spaces';

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'X-Api-Key' => env('SPEETCHKIT_TOKEN'),
                'accept' => 'application/json',
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function index()
    {
        $lists = $this->getListProject();

        return view('beyond.index', compact('lists'));
    }

    private function getListProject()
    {
        $response = $this->client->get('https://api.beyondwords.io/v1/projects');

        $lists = json_decode($response->getBody()->getContents(), true);

        return $lists;
    }

    private function findProject(int $projectId)
    {
        $lists = $this->getListProject();

        return collect($lists)->where('id', $projectId)->first();
    }

    /**
     * @throws GuzzleException
     */
    public function downloadAudioByProject(Request $request)
    {
        set_time_limit(300);
        $projectId = (int)$request->input('projectId');
        $project = $this->findProject($projectId);
        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        $listFiles = Storage::disk($this->disk)->allFiles("beyond/{$project['id']}");

        $contents = $this->loadAllContent($projectId);

        $totalStorage = count($listFiles);
        $totalContents = count($contents);

        echo "<h2>{$project['name']}</h2>";
        echo "<h2>Total in Storage: {$totalStorage}</h2>";
        echo "<h2>Total Contens: {$totalContents}</h2>";

        $contents->chunk(10)->each(function($chunk) use ($project, $listFiles) {
            foreach ($chunk as $item) {
                $path = "beyond/{$project['id']}/{$item['source_id']}.mp3";

                if (!isset($item['audio'][1])) {
                    echo "{$this->number}) audio: {$item['id']} |Post: {$item['source_id']} |Path: {$path} |Success: FALSE |Info: Missing record audio<br>";
                    $this->number++;
                    continue;
                }

                if (in_array($path, $listFiles)) {
                    echo "{$this->number}) audio: {$item['id']} |Post: {$item['source_id']} |Path: {$path} |Success: true |Info: already in storage <br>";
                    $this->number++;
                    continue;
                }

                $result = json_encode($this->loadAndStoreByUrl($item['audio'][1]['url'], $path));

                echo "{$this->number}) audio: {$item['id']} |Post: {$item['source_id']} |Path: {$path} |Success: {$result} <br>";
                $this->number++;
            }
            usleep(200000);//0.2 second
        });
    }

    /**
     * @param string $url
     * @param string $path
     * @return bool
     * @throws GuzzleException
     */
    private function loadAndStoreByUrl(string $url, string $path): bool
    {
        $responseAudio = $this->client->get($url)->getBody()->getContents();

        return Storage::disk($this->disk)->put($path, $responseAudio);
    }

    private function loadAllContent(int $projectId): Collection
    {
        $offset = 0;
        $contents = collect();
        do {
            $loadContents = $this->loadContents($projectId, $offset);

            $contents = $contents->merge($loadContents);

            $offset += 100;
        } while (count($loadContents) !== 0);

        return $contents;
    }

    private function loadContents(int $projectId, int $offset, int $limit = 50): array
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
