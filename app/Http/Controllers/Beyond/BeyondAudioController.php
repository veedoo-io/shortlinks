<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use App\Http\Controllers\Controller;
use App\Service\Beyond\BeyondService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeyondAudioController extends Controller
{
    private BeyondService $beyondService;

    public function __construct(BeyondService $beyondService)
    {
        $this->beyondService = $beyondService;
    }

    public function downloadAudioByProject(Request $request)
    {
        set_time_limit(300);
        $projectId = (int)$request->input('projectId');

        $this->beyondService->downloadAudioByProject($projectId);
    }

    /**
     * @throws AuthenticationException
     */
    public function syncAudioByPostId(Request $request, int $projectId, int $postId): JsonResponse
    {
        set_time_limit(300);
        $token = env('BEARER_TOKEN');

        if ($request->header('Authorization') !== "Bearer $token") {
            throw new AuthenticationException();
        }

        $success = $this->beyondService->syncAudioByPostId($projectId, $postId);

        return $this->sendResponse([
            'success' => $success,
        ]);
    }

    /**
     * @return View
     */
    public function index(): View
    {
        $lists = $this->beyondService->getListProject();

        return view('beyond.index', compact('lists'));
    }
}
