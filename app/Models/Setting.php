<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'description'];

    protected $casts = [
        'value' => 'encrypted',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->first()?->value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
    }
}
