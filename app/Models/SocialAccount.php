<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    use HasFactory;
    protected $fillable = ['platform','username','provider_id','access_token','avatar','is_connected','last_synced_at'];
    protected $casts = [
        'is_connected' => 'boolean',
        'last_synced_at' => 'datetime',
        // Setting.value ile aynı prensip: token düz metin kalmasın.
        'access_token' => 'encrypted',
    ];

    public function contents(): HasMany { return $this->hasMany(ContentItem::class); }

    public function isConnected(): bool { return $this->is_connected; }
}
