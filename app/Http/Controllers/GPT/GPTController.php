<?php declare(strict_types=1);

namespace App\Http\Controllers\GPT;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenAI;


class GPTController
{
    public function index()
    {
        return view('gpt.index');
    }

    public function create(Request $request)
    {
        $apiKey = (string)env('API_KEY_OPENAI');
        $client = OpenAI::client($apiKey);

        $result = $client->audio()->speech([
            'model' => 'tts-1',
            'input' => $request->textAudio ?? 'hello, world!',
            'voice' => $request->voice ?? 'alloy',
        ]);

        $nameRandom = Str::random();
        $time = Carbon::now()->toDateTimeString();

        Storage::disk('public')->put("audio/{$time}-{$request->voice}-tts-1-{$nameRandom}.mp3", $result);

        return redirect()->back();
    }
}
