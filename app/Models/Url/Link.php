<?php declare(strict_types=1);

namespace App\Models\Url;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int id
 * @property string short_url
 * @property string key_url
 * @property string original_url
 * @property string|null author_name
 * @property int counter_creators
 * @property Carbon|string|null created_at
 */
class Link extends Model
{
    use HasFactory;

    protected $table = 'links';

    protected $fillable = [
        'key_url',
        'original_url',
        'short_url',
        'author_name',
        'counter_creators',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
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

    /**
     * @return array
     */
    public function toArrayResponse(): array
    {
        return [
            'short_url' => $this->short_url,
            'original_url' => $this->original_url,
            'key_url' => $this->key_url,
            'author_name' => $this->author_name,
            'counter_creators' => $this->counter_creators,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
