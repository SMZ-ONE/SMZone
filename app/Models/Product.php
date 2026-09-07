<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // NOT: user_id, slug, short_description, ingredients, benefits kasıtlı olarak
    // KALDIRILDI - bunlar gerçek 'products' tablosunda hiç var olmayan kolonlardı
    // (model migration'dan önce yazılmış olmalı). Biri bu alanları fillable üzerinden
    // yazmaya çalışsaydı, tıpkı brand_profiles.website_url'de yaşadığımız
    // "no such column" hatasını verirdi. Gerçekten gerekirse ileride ayrı bir
    // migration + fillable eklentisiyle geri getirilebilir.
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'price',
        'image',
        'tags',
        'is_active',
        'stock',
        'shopify_id',
        'shopify_url',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];
}
