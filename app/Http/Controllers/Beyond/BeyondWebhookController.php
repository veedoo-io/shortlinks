<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use App\Http\Controllers\Controller;
use App\Service\Beyond\BeyondService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BeyondWebhookController extends Controller
{
    private BeyondService $beyondService;

    public function __construct(BeyondService $beyondService)
    {
        $this->beyondService = $beyondService;
    }

    /**
     * @throws AuthenticationException
     */
    public function create(Request $request, int $projectId): JsonResponse
    {
        Log::channel('webhook-beyond')->info('Webhook Created', [
            'external_id' => $request->input('external_id'),
            'action_type'  => $request->input('action_type'),
            'projectId' => $projectId
        ]);

        $token = env('BEARER_TOKEN');

        if ($request->header('Authorization') !== "Bearer $token") {
            throw new AuthenticationException();
        }

        $this->beyondService->downloadAudioWithWebhook($projectId, $request->all());

        return $this->sendResponse();
    }
}
