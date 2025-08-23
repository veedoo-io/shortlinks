<?php

namespace App\Http\Controllers\Audio\Gemini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;
use Symfony\Component\HttpFoundation\Response;

class GeminiMp3Controller extends Controller
{
    /**
     * @throws AuthenticationException
     */
    public function convertToMp3(ConvertToMp3Request $request)
    {
        $token = env('BEARER_TOKEN');

        if ($request->header('Authorization') !== "Bearer $token") {
            throw new AuthenticationException();
        }

        $referrer = $request->headers->get('referer') ?? 'preprod.veedoo.dev';

        if (str_contains($referrer, 'preprod.veedoo.dev')) {
            $diskSpace = Storage::disk('s3-preprod');
        } else {
            $diskSpace = Storage::disk('s3');
        }


        $fileGeminiPcm = $request->file('file');
        $fileName = $fileGeminiPcm->getFilename();

        $namePcm = "{$fileName}.pcm";
        $nameMp3 = "{$fileName}.mp3";
        $disk = Storage::disk('local');
        $pathPcm = "audio/gemini/{$namePcm}";
        $pathMp3 = "audio/gemini/{$nameMp3}";

        try {
            $fileGeminiPcm->storeAs('audio/gemini', $namePcm, ['disk' => 'local']);

            $realPathPcm = escapeshellarg($disk->path($pathPcm));
            $realPathMp3 = escapeshellarg($disk->path($pathMp3));

            $resultCommand = \exec(sprintf("ffmpeg -f s16le -ar 24000 -ac 1 -i %s %s", $realPathPcm, $realPathMp3), $output, $returnVar);

            // is Not Successful command
            if ($returnVar !== 0) {
                $this->cleanup($disk, $pathPcm, $pathMp3);

                Log::error('ffmpeg failed', [
                    'exit_code' => $returnVar,
                    'error_output' => $output,
                    'command' => $resultCommand,
                ]);

                return $this->sendError(json_encode($output), Response::HTTP_NOT_FOUND);
            }

            if (!$disk->exists($pathMp3)) {
                $this->cleanup($disk, $pathPcm, $pathMp3);
                return $this->sendError('MP3 file not generated', Response::HTTP_NOT_FOUND);
            }

            $diskSpace->delete($request->path);
            $stream = $disk->readStream($pathMp3);
            $resultSave = $diskSpace->put($request->path, $stream, ['visibility' => Visibility::PUBLIC]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $this->cleanup($disk, $pathPcm, $pathMp3);

            if (!$resultSave) {
                return $this->sendError('Failed to save MP3 file', Response::HTTP_NOT_FOUND);
            }

            $diskSpace->setVisibility($request->path, Visibility::PUBLIC);
            return $this->sendResponse([], 'Converted to MP3 successfully');

        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), [
                'fileName' => $request->file('file')->getClientOriginalName(),
                'ErrorOutput' => $exception->getFile()
            ]);

            $this->cleanup($disk, $pathPcm, $pathMp3);

            return $this->sendError($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    private function cleanup($disk, $pathPcm, $pathMp3): void
    {
        $disk->delete($pathPcm);
        $disk->delete($pathMp3);
    }
}