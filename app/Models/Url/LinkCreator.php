<?php declare(strict_types=1);

namespace App\Models\Url;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int link_id
 * @property string author_name
 * @property Carbon|string|null created_at
 */
class LinkCreator extends Model
{
    use HasFactory;

    protected $table = 'link_creators';

    protected $fillable = [
        'link_id',
        'author_name',
    ];

    protected function serializeDate($date){
        return $date->format('Y-m-d H:i:s');
    }

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
