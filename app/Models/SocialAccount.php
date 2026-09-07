<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    use HasFactory;
    protected $fillable = ['platform','username','provider_id','access_token','avatar','is_connected','last_synced_at'];
    protected $casts = ['is_connected'=>'boolean','last_synced_at'=>'datetime'];

    // Content::class -> ContentItem::class: 'contents' tablosu 'content_items'e birleştirildi.
    // Metod adı geriye dönük uyumluluk için 'contents' olarak bırakıldı (başka yerde ->contents()
    // çağıran kod olabilir); foreign key varsayılanı (social_account_id) zaten iki tarafta da uyuşuyor.
    public function contents(): HasMany { return $this->hasMany(ContentItem::class); }

    public function isConnected(): bool { return $this->is_connected; }
}
