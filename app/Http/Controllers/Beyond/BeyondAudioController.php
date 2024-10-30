<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BeyondAudioController
{
    private Client $client;

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

    private function findProject($projectId)
    {
        $lists = $this->getListProject();

        return collect($lists)->where('id', $projectId)->first();
    }

    /**
     * @throws GuzzleException
     */
    public function downloadAudioByProject(Request $request)
    {
        $projectId = $request->input('projectId');
        $project = $this->findProject($projectId);
        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        for($i=0; $i<500; $i+=100) {
            $response = $this->loadContents($projectId, $i);

            $content = json_decode($response->getBody()->getContents(), true);

            foreach ($content as $item) {
                if (!isset($item['audio'][1])){
                    continue;
                }

                $urlAudio = $item['audio'][1]['url'];

                $responseAudio = $this->client->get($urlAudio)->getBody()->getContents();

                $result = Storage::disk('public')->put("beyond/{$project['id']}/{$item['source_id']}/{$item['id']}.mp3", $responseAudio);

                $result = json_encode($result);
                echo "audio: {$item['id']}|Post: {$item['source_id']}|Success: $result <br>";
            }
        }
    }

    private function loadContents($projectId, $offset)
    {
        return $this->client->request(
            'GET',
            "https://api.beyondwords.io/v1/projects/{$projectId}/content", [
            'form_params' => [
                'pagination' => [
                    'limit' => 50,
                    'offset' => $offset,
                ]
            ]
        ]);
    }
}
