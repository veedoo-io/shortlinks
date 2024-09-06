<?php declare(strict_types=1);

namespace App\Http\Controllers\Url;

use App\Http\Controllers\Controller;
use App\Http\Requests\Url\UrlCreateRequest;
use App\Models\Url\Link;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    public function index(string $key_url)
    {
        $link = Link::query()->where('key_url', $key_url)->first();

        //TODO перевірка на існування ключа

        return redirect($link->original_url, Response::HTTP_MOVED_PERMANENTLY);
    }

    public function create(UrlCreateRequest $request): JsonResponse
    {
        $keyUrl = Link::createKeyUrl();
        $short_url = url("/$keyUrl");

        Link::query()->create([
            'key_url' => $keyUrl,
            'original_url' => $request->url,
            'short_url' => $short_url,
        ]);

        return $this->sendResponse([
            'short_url' => $short_url,
            'original_url' => $request->url,
            'key_url' => $keyUrl,
        ]);
    }
}
