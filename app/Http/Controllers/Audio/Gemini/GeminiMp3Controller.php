<?php

namespace App\Http\Controllers\Audio\Gemini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiMp3Controller extends Controller
{
    public function index()
    {
        return "HELLO";
    }
    /**
     * @throws AuthenticationException
     */
    public function convertToMp3(ConvertToMp3Request $request)
    {
        try {
            $token = env('BEARER_TOKEN');

            if ($request->header('Authorization') !== "Bearer $token") {
                throw new AuthenticationException();
            }

            $fileGeminiPcm = $request->file('file');
            $fileGeminiPcm->storeAs('audio/gemini', $fileGeminiPcm->getClientOriginalName(), ['disk' => 'public']);

            $name = explode('.', $fileGeminiPcm->getClientOriginalName());
            $disk = Storage::disk('public');
            $nameMp3 =  "$name[0].mp3";

            $realPathPcm = $disk->path("audio/gemini/{$fileGeminiPcm->getClientOriginalName()}");
            $realPathMp3 = $disk->path($pathMp3 = "audio/gemini/{$nameMp3}");

            \exec("ffmpeg -f s16le -ar 24000 -ac 1 -i {$realPathPcm} {$realPathMp3}");

            if ($disk->exists($pathMp3)) {
                $disk->delete($realPathPcm);
                return response()->download($realPathMp3, $nameMp3)->deleteFileAfterSend(true);
            }

            $disk->delete($realPathPcm);
            return response()->json(['message' => 'Not Found File'], 404);


        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), [
                'fileName' => $request->file('file')->getClientOriginalName(),
                'ErrorOutput' => $exception->getFile()
            ]);

            return response()->json(['message' => 'Not Convert to mp3', 'error' => $exception->getMessage()], 404);
        }
    }
}
