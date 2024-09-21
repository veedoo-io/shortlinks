<?php declare(strict_types=1);

namespace App\Service\Url;

use App\Helpers\UTM;
use App\Models\Url\Link;
use App\Models\Url\LinkCreator;

class LinkService
{
    /**
     * @param string $url
     * @param string|null $author_name
     * @return Link
     */
    public function firstOrCreateLink(string $url, ?string $author_name): Link
    {
        return Link::query()->firstOrCreate(
            ['original_url' => $url],
            [
                'key_url' => $keyUrl = Link::createKeyUrl(),
                'short_url' => UTM::add(url("/$keyUrl"), $keyUrl),
                'author_name' => $author_name,
                'counter_creators' => 0
            ]
        );
    }

    /**
     * @param Link $link
     * @param string|null $author_name
     * @return LinkCreator|null
     */
    public function firstOrCreateLinkCreator(Link $link, ?string $author_name): ?LinkCreator
    {
        if (!$link->wasRecentlyCreated && $link->author_name !== $author_name) {
            $linkCreator = LinkCreator::query()->firstOrCreate([
                'link_id' => $link->id,
                'author_name' => $author_name
            ]);

            // якщо один і той самий автор пробує створити декілька разів link то ми це не зараховуємо
            if ($linkCreator->wasRecentlyCreated) {
                $link->increment('counter_creators');
            }

            return  $linkCreator;
        }

        return null;
    }
}
