<?php declare(strict_types=1);

namespace App\Http\Controllers\Url;

use App\Http\Controllers\Controller;
use App\Http\Requests\Url\UrlCreateRequest;
use App\Models\Url\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    /**
     * @param string $key_url
     * @return RedirectResponse
     */
    public function index(string $key_url): RedirectResponse
    {
        $link = Link::query()->where('key_url', $key_url)->first();

        if (empty($link)) {
            abort(404, 'Not Found Link');
        }

        return redirect($link->original_url, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @param UrlCreateRequest $request
     * @return JsonResponse
     */
    public function create(UrlCreateRequest $request): JsonResponse
    {
        $link = Link::query()->updateOrCreate(
            ['original_url' => $request->url],
            [
                'key_url' => $keyUrl = Link::createKeyUrl(),
                'short_url' => url("/api/$keyUrl"),
                'author_name' => $request->author_name
            ]
        );

        return $this->sendResponse([
            'short_url' => $link->short_url,
            'original_url' => $link->original_url,
            'key_url' => $link->key_url,
            'author_name' => $link->author_name,
        ]);
    }
}
