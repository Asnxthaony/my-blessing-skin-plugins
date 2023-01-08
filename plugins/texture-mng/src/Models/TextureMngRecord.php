<?php

namespace TextureMng\Models;

use Illuminate\Database\Eloquent\Model;
use Lorisleiva\LaravelSearchString\Concerns\SearchString;

class TextureMngRecord extends Model
{
    use SearchString;

    protected $table = 'texture_mng_record';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'texture_id', 'operator', 'reason',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'texture_id' => 'integer',
        'operator' => 'integer',
    ];

    protected $searchStringColumns = [
        'user_id',
        'reason' => ['searchable' => true],
        'operator' => ['searchable' => true],
        'created_at' => ['date' => true],
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
