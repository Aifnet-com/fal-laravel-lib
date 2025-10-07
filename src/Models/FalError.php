<?php

namespace Aifnet\Fal\Models;

use Illuminate\Database\Eloquent\Model;

class FalError extends Model
{
    protected $table = 'fal_errors';

    const UPDATED_AT = null;

    protected $fillable = [
        'message',
        'md5'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public static function getByMessage($message)
    {
        return static::query()->firstWhere('md5', md5($message));
    }

    public static function log($message)
    {
        return static::firstOrCreate(
            ['md5' => md5($message)],
            ['message' => $message]
        );
    }

    public static function logGetId($message)
    {
        $error = static::log($message);
        return $error ? $error->id : null;
    }
}
