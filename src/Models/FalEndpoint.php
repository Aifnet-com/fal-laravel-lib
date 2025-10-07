<?php

namespace Aifnet\Fal\Models;

use Illuminate\Database\Eloquent\Model;

class FalEndpoint extends Model
{
    public $timestamps = false;

    protected $table = 'fal_endpoints';

    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function requests()
    {
        return $this->hasMany(FalRequest::class, 'endpoint_id');
    }
}
