<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use App\Service\Beyond\BeyondService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BeyondAudioController
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
     * @return View
     */
    public function index(): View
    {
        $lists = $this->beyondService->getListProject();

        return view('beyond.index', compact('lists'));
    }
}
