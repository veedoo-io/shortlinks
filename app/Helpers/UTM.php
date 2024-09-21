<?php declare(strict_types=1);

namespace App\Helpers;

class UTM
{
    /**
     * @param string $url
     * @param string $keyUrl
     * @return string
     */
    public static function add(string $url, string $keyUrl): string
    {
        $utmParams = config('utm.params');
        $utmParams['utm_content'] = $keyUrl;

        $urlParamsUtm =  (bool)config('utm.include') ? '?' . http_build_query($utmParams) : '';

        return $url . $urlParamsUtm;
    }
}
