<?php declare(strict_types=1);

namespace App\Http\Controllers\GPT;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenAI;


class GPTController
{

    public function index(Request $request)
    {
        if (!$this->checkAuth($request)) {
            return redirect()->route('gpt.auth.index');
        }

        return view('gpt.index');
    }

    public function create(Request $request)
    {
        set_time_limit(120);

        if (!$this->checkAuth($request)) {
            return redirect()->route('gpt.auth.index');
        }

        $textAudio = $request->textAudio;

        $result = '';
        $textExplode = explode('|||||', Str::wordWrap($textAudio, 4000, '|||||'));

        foreach ($textExplode as $text) {
            $apiKey = (string)env('API_KEY_OPENAI');
            $client = OpenAI::client($apiKey);

            $result .= $client->audio()->speech([
                'model' => 'tts-1',
                'input' => $text ?? 'hello, world!',
                'voice' => $request->voice ?? 'alloy',
            ]);
        }

        $nameRandom = Str::random();
        $time = Carbon::now()->toDateTimeString();

        Storage::disk('public')->put("audio/{$time}-{$request->voice}-tts-1-{$nameRandom}.mp3", $result);

        return redirect()->back();
    }

    private function checkAuth(Request $request): bool
    {
        return (bool)$request->session()->get('authenticated', false) !== false;
    }

    public function auth(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|max:255',
        ]);

        if (env('PASSWORD_GPT_PAGE') === $validated['password']) {
            Session::put('authenticated', true);

            return redirect()->route('gpt.index');
        }

        return redirect()->route('gpt.auth.index')->withErrors([
            'password' => 'Wrong Password',
        ]);
    }
}
