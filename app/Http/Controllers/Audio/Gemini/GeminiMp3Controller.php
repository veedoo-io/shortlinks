<?php

namespace App\Http\Controllers\Audio\Gemini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;
use App\Service\Audio\Gemini\GeminiMp3Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GeminiMp3Controller extends Controller
{
    public function __construct(private GeminiMp3Service $geminiMp3Service)
    {

    }

    /**
     * @param ConvertToMp3Request $request
     *
     * @return JsonResponse
     */
    public function convertToMp3(ConvertToMp3Request $request): JsonResponse
    {
        $fileGeminiPcm = $request->file('file');

        $this->geminiMp3Service->selectDiskSpace($request->headers->get('referer') ?? 'preprod');

        $this->geminiMp3Service->generateFileName($fileGeminiPcm->getFilename());

        try {
            $this->geminiMp3Service->storeAsAudio($fileGeminiPcm);

            $this->geminiMp3Service->execConvertCommand();

            $this->geminiMp3Service->existsConvertedFile();

            $this->geminiMp3Service->storeFileOnSpace($request->path);

            $this->geminiMp3Service->cleanup();

            return $this->sendResponse([], 'Converted to MP3 successfully');

        } catch (\Throwable $exception) {
            Log::error('Gemini MP3 conversion failed', [
                'exception' => $exception,
                'client_filename' => $request->file('file')->getClientOriginalName(),
                'target_path' => $request->path,
            ]);

            $this->geminiMp3Service->cleanup();

            return $this->sendError($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}