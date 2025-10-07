<?php

namespace Aifnet\Fal\Models;

use App\Models\Error;
use Aifnet\Fal\Helpers\FalRequestHelper;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class FalRequest extends Model
{
    protected $table = 'fal_requests';

    const TYPE_NULL  = 0;
    const TYPE_AUDIO = 10;

    const TYPE_MAP = [
        self::TYPE_NULL  => 'unknown',
        self::TYPE_AUDIO => 'audio',
    ];

    const STATUS_IN_QUEUE   = 0;
    const STATUS_PROCESSING = 10;
    const STATUS_COMPLETED  = 20;
    const STATUS_FAILED     = 30;

    protected $fillable = [
        'request_id',
        'user_id',
        'endpoint_id',
        'data_id',
        'error_id',
        'type',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public const FAL_STATUS_MAP = [
        'IN_QUEUE'      => self::STATUS_IN_QUEUE,
        'PROCESSING'    => self::STATUS_PROCESSING,
        'COMPLETED'     => self::STATUS_COMPLETED,
        'OK'            => self::STATUS_COMPLETED,
        'FAILED'        => self::STATUS_FAILED,
        'ERROR'         => self::STATUS_FAILED,
    ];

    public function endpoint()
    {
        return $this->belongsTo(FalEndpoint::class, 'endpoint_id');
    }

    public function data()
    {
        return $this->belongsTo(FalData::class, 'data_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function error()
    {
        return $this->hasOne(Error::class, 'id', 'error_id');
    }

    public function getTypeNameAttribute()
    {
        return self::TYPE_MAP[$this->type] ?? 'unknown';
    }

    public function isInQueue()
    {
        return $this->status == self::STATUS_IN_QUEUE;
    }

    public function isProcessing()
    {
        return $this->status == self::STATUS_PROCESSING;
    }

    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function isFailed()
    {
        return $this->status == self::STATUS_FAILED;
    }

    public function getFileExtension()
    {
        $outputUrl = $this->data->output[$this->typeName]['url'] ?? null;

        if (empty($outputUrl)) {
            return null;
        }

        return pathinfo($outputUrl, PATHINFO_EXTENSION);
    }

    public function fail($errorMessage)
    {
        $this->status = self::STATUS_FAILED;
        $this->updated_at = now();
        $this->completed_at = now();
        $this->error_id = Error::logGetId(
            $message = $errorMessage,
            $source  = Error::SOURCE_FAL
        );

        return $this->save();
    }

    public static function findByRequestId($requestId, $with = [])
    {
        return self::with($with)->where('request_id', $requestId)->first();
    }

    public static function submit(
        $endpointName,
        $input,
        $config = [],
        $type = self::TYPE_NULL
    ) {
        $responseJson = FalRequestHelper::sendRequest(
            FalRequestHelper::buildEndpointUrl($endpointName),
            $input,
            $method = 'post',
        );

        $requestId = $responseJson['request_id'] ?? null;
        $status = $responseJson['status'] ?? 'IN_QUEUE';

        if (empty($requestId)) {
            throw new \Exception('Could not send request to Fal AI.');
        }

        $endpoint = FalEndpoint::firstOrCreate(['name' => $endpointName]);

        $data = FalData::create(['input' => ['falParameters' => $input, 'config' => $config]]);

        return self::create([
            'request_id' => $requestId,
            'user_id' => $config['user_id'] ?? null,
            'endpoint_id' => $endpoint->id,
            'data_id' => $data->id,
            'type' => $type,
            'status' => self::FAL_STATUS_MAP[$status] ?? self::STATUS_IN_QUEUE,
        ]);
    }
}
