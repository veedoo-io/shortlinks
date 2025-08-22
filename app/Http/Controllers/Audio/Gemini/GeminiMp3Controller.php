<?php

namespace App\Http\Controllers\Audio\Gemini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;

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
        $nameMp3 =  "{$fileName}.mp3";
        $disk = Storage::disk('local');

        try {
            $fileGeminiPcm->storeAs('audio/gemini', $namePcm, ['disk' => 'local']);

            $realPathPcm = $disk->path($pathPcm = "audio/gemini/{$namePcm}");
            $realPathMp3 = $disk->path($pathMp3 = "audio/gemini/{$nameMp3}");

            \exec("ffmpeg -f s16le -ar 24000 -ac 1 -i {$realPathPcm} {$realPathMp3}");

            if ($disk->exists($pathMp3)) {
                $resultSave = Storage::disk('s3')->put($request->path, $disk->get($pathMp3), ['visibility' => Visibility::PUBLIC]);
                Storage::disk('s3')->setVisibility($request->path, Visibility::PUBLIC);

                $disk->delete($pathPcm);
                $disk->delete($pathMp3);

                if ($resultSave) {
                    return response()->json(['message' => 'Convert to mp3', 'status' => true], 200);
                }

                return response()->json(['message' => 'Not Save to mp3', 'status' => false], 404);
            }

            $disk->delete($pathPcm);
            $disk->delete($pathMp3);

            return response()->json(['message' => 'Not Found File', 'status' => false], 404);


        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), [
                'fileName' => $request->file('file')->getClientOriginalName(),
                'ErrorOutput' => $exception->getFile()
            ]);

            $disk->delete("audio/gemini/{$namePcm}");
            $disk->delete("audio/gemini/{$nameMp3}");

            return response()->json(['message' => $exception->getMessage(), 'status' => false], 404);
        }
    }
}
