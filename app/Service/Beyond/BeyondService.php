<?php declare(strict_types=1);

namespace App\Service\Beyond;

use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

    public function loadAllFiles(string $path): Collection
    {
        return collect($this->disk->allFiles($path));
    }

    /**
     * @param int $projectId
     * @return void
     */
    public function downloadAudioByProject(int $projectId): void
    {
        $project = $this->findProjectOrFail($projectId);

        $listFiles = $this->loadAllFiles("beyond/{$projectId}");

        $contents = $this->loadAllContent($projectId);

        echo "<h2>{$project['name']}</h2>";
        echo "<h2>Total in Storage: {$listFiles->count()}</h2>";
        echo "<h2>Total Contens: {$contents->count()}</h2>";

        $contents->chunk(10)->each(function($chunk) use ($project, $listFiles) {
            foreach ($chunk as $content) {
                $path = $this->pathAudio($project['id'], $content['source_id']);
                $info = "{$this->number}) audio: {$content['id']} |Post: {$content['source_id']} |Path: {$path} |Success:";
                $this->number++;

                if (!isset($content['audio'][1])) {
                    echo "$info FALSE |Info: Missing record audio<br>";
                    continue;
                }

                if (in_array($path, $listFiles->toArray())) {
                    echo  "$info true |Info: already in storage <br>";
                    continue;
                }

                $result = json_encode($this->loadAndStoreByUrl($content['audio'][1]['url'], $path));

                echo "$info {$result} <br>";
            }
            usleep(200000);//0.2 second
        });
    }

    public function syncAudioByPostId(int $projectId, int $postId): bool
    {
        $project = $this->findProjectOrFail($projectId);

        $listFiles = $this->loadAllFiles("beyond/{$projectId}");

        $content = $this->loadContentByPostOrFail($projectId, $postId);

        if ($postId === (int)$content['source_id']) {
            $path = $this->pathAudio($project['id'], $content['source_id']);
            $info = [
                'audio' => $content['id'],
                'projectId' => $projectId,
                'postId'  => $postId,
            ];

            if (!isset($content['audio'][1])) {
                Log::channel('sync-beyond')->critical('Sync Error', [
                    ...$info,
                    'info' => 'Missing record audio',
                ]);
                return false;
            }

            if (in_array($path, $listFiles->toArray())) {
                Log::channel('sync-beyond')->critical('Sync Error', [
                    ...$info,
                    'info' => 'already in storage',
                ]);
                return true;
            }



            $result = $this->loadAndStoreByUrl($content['audio'][1]['url'], $path);

            if ($result) {
                Log::channel('sync-beyond')->info('Sync Success', [
                    ...$info,
                    'info' => 'Success',
                ]);
            } else {
                Log::channel('sync-beyond')->critical('Sync Failure', [
                    ...$info,
                    'info' => 'Failure',
                ]);
            }

            return $result;
        }

        return false;
    }

    private function pathAudio(int $projectId, $external_id): string
    {
        return "beyond/{$projectId}/{$external_id}.mp3";
    }

    public function downloadAudioWithWebhook(int $projectId, array $content): void
    {
        if ($content['action_type'] !== 'audio.updated' && $content['action_type'] !== 'audio.error') {
            Log::channel('webhook-beyond-error')->critical("Media not downloaded: action_type={$content['action_type']}");
            throw new NotFoundHttpException("Media not downloaded: action_type={$content['action_type']}");
        }

        $project = $this->findProjectOrFail($projectId);

        $path = $this->pathAudio($project['id'], $content['external_id']);
        $info = "{$this->number}) projectId:{$project['id']} | audio: {$content['id']} |Post: {$content['external_id']} |Path: {$path} |Success:";

        $audio = collect($content['media'])->where('content_type', 'mp3');

        if ($audio->isEmpty()) {
            $info .= " FALSE";
            Log::channel('webhook-beyond-error')->critical("Media not found: $info");
            throw new NotFoundHttpException('Media not found');
        }

        $result = json_encode($this->loadAndStoreByUrl($audio->first()['url'], $path));

        Log::channel('webhook-beyond')->info($info . $result);
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

    public function loadContentByPostOrFail(int $projectId, int $postId)
    {
        $response = $this->client->get(
            "https://api.beyondwords.io/v1/projects/{$projectId}/content", [
            'form_params' => [
                'filter' => [
                    'source_id' => $postId,
                ],
            ]
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        if (!isset($response[0])) {
            throw new NotFoundHttpException('Content not found');
        }

        return $response[0];
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
