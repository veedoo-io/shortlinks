<?php

namespace App\Http\Controllers\Audio\Gemini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;
use Symfony\Component\Process\Process;
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

        $fileGeminiPcm = $request->file('file');
        $fileName = $fileGeminiPcm->getFilename();

        $namePcm = "{$fileName}.pcm";
        $nameMp3 = "{$fileName}.mp3";
        $disk = Storage::disk('local');
        $pathPcm = "audio/gemini/{$namePcm}";
        $pathMp3 = "audio/gemini/{$nameMp3}";

        try {
            $fileGeminiPcm->storeAs('audio/gemini', $namePcm, ['disk' => 'local']);

            $realPathPcm = $disk->path($pathPcm);
            $realPathMp3 = $disk->path($pathMp3);

            $process = new Process([
                'ffmpeg', '-f', 's16le', '-ar', '24000', '-ac', '1', '-i', $realPathPcm, '-y', $realPathMp3
            ]);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->cleanup($disk, $pathPcm, $pathMp3);

                Log::error('ffmpeg failed', [
                    'exit_code' => $process->getExitCode(),
                    'error_output' => $process->getErrorOutput(),
                ]);

                return $this->sendError($process->getErrorOutput(), Response::HTTP_NOT_FOUND);
            }

            if (!$disk->exists($pathMp3)) {
                $this->cleanup($disk, $pathPcm, $pathMp3);
                return $this->sendError('MP3 file not generated', Response::HTTP_NOT_FOUND);
            }

            $stream = $disk->readStream($pathMp3);
            $resultSave = Storage::disk('s3')->put($request->path, $stream, ['visibility' => Visibility::PUBLIC]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $this->cleanup($disk, $pathPcm, $pathMp3);

            if (!$resultSave) {
                return $this->sendError('Failed to save MP3 file', Response::HTTP_NOT_FOUND);
            }

            Storage::disk('s3')->setVisibility($request->path, Visibility::PUBLIC);
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