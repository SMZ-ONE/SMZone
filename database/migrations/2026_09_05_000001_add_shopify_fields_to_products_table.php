<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Shopify senkronu için: hangi yerel satırın hangi Shopify ürünü olduğunu
            // eşleştirmek amacıyla (upsert anahtarı). Shopify'ın GraphQL gid'i
            // ("gid://shopify/Product/123...") aynen saklanıyor - tekrar Shopify'a
            // sorgu atmak gerekirse (örn. ShopifyService::find()) doğrudan kullanılabilsin diye.
            $table->string('shopify_id')->nullable()->unique()->after('id');

            // Shopify'daki productType alanı - AiContentService'in prompt'unda zaten
            // "Kategorie" olarak kullanılıyordu ama önceden hiç doldurulmuyordu.
            $table->string('category')->nullable()->after('description');

            // Online mağaza linki - draft'ları düzenlerken orijinal ürüne hızlı erişim için.
            $table->string('shopify_url')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shopify_id', 'category', 'shopify_url']);
        });
    }
};
