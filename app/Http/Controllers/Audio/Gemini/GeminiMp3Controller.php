<?php

namespace App\Http\Controllers\Audio\Gemini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audio\Gemini\ConvertToMp3Request;

use FFMpeg\Format\Audio\Mp3;
use Illuminate\Support\Facades\Log;
use ProtoneMedia\LaravelFFMpeg\Exporters\EncodingException;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class GeminiMp3Controller extends Controller
{
    public function convertToMp3(ConvertToMp3Request $request)
    {
        try {
            $fileGeminiPcm = $request->file('file');

            $file = FFMpeg::open($fileGeminiPcm)
                ->export()
                ->inFormat(new Mp3())
                ->getAudioStream()->all();
            dd($file);

            return response()->stream(function() use ($file) {
                fpassthru($file);
            }, 200, [
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => 'attachment; filename="converted.mp3"',
            ]);

        } catch (EncodingException $exception) {
            Log::error($exception->getMessage(), [
                'fileName' => $request->file('file')->getClientOriginalName(),
                'ErrorOutput' => $exception->getErrorOutput()
            ]);
            $command = $exception->getCommand();
            $errorLog = $exception->getErrorOutput();
        }
    }
}
