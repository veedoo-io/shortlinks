<?php declare(strict_types=1);

namespace App\Models\Url;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string short_url
 * @property string key_url
 * @property string original_url
 */
class Link extends Model
{
    use HasFactory;

    protected $table = 'links';

    protected $fillable = [
        'key_url',
        'original_url',
        'short_url',
    ];

    /**
     * @return string
     */
    public static function createKeyUrl(): string
    {
        $urlKey = Str::random((int)env('FAKE_URL_LENGTH', 8));

        $link = self::query()->where('key_url', $urlKey)->first();

        if (!empty($link)) {
            return self::createKeyUrl();
        }

        return $urlKey;
    }
}
