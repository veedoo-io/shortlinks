<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class Controller
{
    /**
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function sendResponse(array $data = [], string $message = 'ok'): JsonResponse
    {
        return Response::json(ResponseUtil::makeResponse($message, $data));
    }

    /**
     * @param string $message
     * @param array $errors
     * @param int $code
     * @return JsonResponse
     */
    public function sendError(string $message, int $code, array $errors = []): JsonResponse
    {
        return Response::json(ResponseUtil::makeError($message, $errors), $code);
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function sendNotFound(string $message = null): JsonResponse
    {
        return $this->sendError(
            $message ?? 'Resource not found',
            SymfonyResponse::HTTP_NOT_FOUND
        );
    }
}
