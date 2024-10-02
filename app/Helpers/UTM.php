<?php declare(strict_types=1);

namespace App\Helpers;

use App\Service\Url\UrlParse;

class UTM
{
    /**
     * @param string $url
     * @param string $keyUrl
     * @return string
     */
    public static function add(string $url): string
    {
        $url = UrlParse::fromString($url);

        return $url->createUrl()->withUTM()->getUrlToString();
    }
}
