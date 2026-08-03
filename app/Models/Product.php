<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name','sku','description','price','image','tags','is_active'];
    protected $casts = ['tags'=>'array','is_active'=>'boolean','price'=>'decimal:2'];
    public function contents(): HasMany { return $this->hasMany(Content::class); }
}
