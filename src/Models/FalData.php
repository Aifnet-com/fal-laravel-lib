<?php

namespace Aifnet\Fal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    public function getInputValue($key, $default = null)
    {
        return Arr::get($this->input, $key, $default);
    }

    public function setInputValue($key, $value)
    {
        $input = $this->getAttribute('input') ?? [];
        Arr::set($input, $key, $value);
        $this->setAttribute('input', $input);

        return $this;
    }

    public function removeInputValue($key)
    {
        $input = $this->getAttribute('input') ?? [];
        Arr::forget($input, $key);
        $this->setAttribute('input', $input);

        return $this;
    }

    public function getOutputValue($key, $default = null)
    {
        return Arr::get($this->output, $key, $default);
    }

    public function setOutputValue($key, $value)
    {
        $output = $this->getAttribute('output') ?? [];
        Arr::set($output, $key, $value);
        $this->setAttribute('output', $output);

        return $this;
    }

    public function removeOutputValue($key)
    {
        $output = $this->getAttribute('output') ?? [];
        Arr::forget($output, $key);
        $this->setAttribute('output', $output);

        return $this;
    }
}
