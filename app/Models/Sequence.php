<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    protected $table = 'sequences';

    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name', 'value'];

    protected $casts = [
        'value' => 'integer',
    ];
}
