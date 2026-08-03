<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    protected $fillable = ['title','body','product_id','social_account_id','image','status','scheduled_at','platforms'];
    protected $casts = ['platforms'=>'array','scheduled_at'=>'datetime'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function socialAccount(): BelongsTo { return $this->belongsTo(SocialAccount::class); }
}
