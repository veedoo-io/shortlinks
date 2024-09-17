<?php declare(strict_types=1);

namespace App\Http\Controllers\Url;

use App\Http\Controllers\Controller;
use App\Http\Requests\Url\UrlCreateRequest;
use App\Models\Url\Link;
use App\Service\Url\LinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    private LinkService $linkService;

    /**
     * @param LinkService $linkService
     */
    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

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
        $link = $this->linkService->firstOrCreateLink($request->url, $request->author_name);

        $this->linkService->firstOrCreateLinkCreator($link, $request->author_name ?? $request->ip());

        return $this->sendResponse($link->toArrayResponse());
    }
}
