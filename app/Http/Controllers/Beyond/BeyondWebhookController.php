<?php declare(strict_types=1);

namespace App\Http\Controllers\Beyond;

use App\Http\Controllers\Controller;
use App\Service\Beyond\BeyondService;
use GuzzleHttp\Client;

class BeyondWebhookController extends Controller
{
    private BeyondService $beyondService;

    public function __construct(BeyondService $beyondService)
    {

        $this->beyondService = $beyondService;
    }

    public function create()
    {
        return $this->sendResponse();
    }
}
