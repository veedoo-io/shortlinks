<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use App\Http\Controllers\Controller;
use App\Service\Beyond\BeyondService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BeyondWebhookController extends Controller
{
    private BeyondService $beyondService;

    public function __construct(BeyondService $beyondService)
    {
        $this->beyondService = $beyondService;
    }

    public function create(Request $request, int $projectId)
    {
        Log::alert($request, [
            'header' => $request->header('Authorization'),
            'path'  => $request->path(),
            'projectId' => $projectId
        ]);

        return $this->sendResponse();
    }
}
