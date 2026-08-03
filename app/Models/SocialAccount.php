<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    protected $fillable = ['platform','username','access_token','avatar','is_connected'];
    protected $casts = ['is_connected'=>'boolean'];
    public function contents(): HasMany { return $this->hasMany(Content::class); }
}
