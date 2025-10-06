<?php

namespace App\Lib\Fal\Models;

use Illuminate\Database\Eloquent\Model;

class FalData extends Model
{
    protected $table = 'fal_data';

    protected $fillable = [
        'input',
        'output'
    ];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
    ];

    public function request()
    {
        return $this->hasOne(FalRequest::class, 'data_id', 'id');
    }
}
