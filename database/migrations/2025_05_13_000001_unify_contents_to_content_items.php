<?php
// database/migrations/2025_05_13_000001_unify_contents_to_content_items.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. content_items'e eksik kolonları ekle (ileriye dönük)
        Schema::table('content_items', function (Blueprint $table) {
            if (!Schema::hasColumn('content_items', 'social_account_id')) {
                $table->foreignId('social_account_id')->nullable()->after('product_id')->constrained('social_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('content_items', 'media')) {
                $table->json('media')->nullable()->after('body');
            }
        });

        // 2. contents tablosundaki veriyi content_items'a taşı (caption -> body)
        if (Schema::hasTable('contents')) {
            $contents = DB::table('contents')->get();
            foreach ($contents as $c) {
                // aynı başlıkta var mı kontrol et, yoksa ekle
                $exists = DB::table('content_items')->where('title', $c->title)->where('created_at', $c->created_at)->exists();
                if (!$exists) {
                    DB::table('content_items')->insert([
                        'product_id' => $c->product_id,
                        'social_account_id' => $c->social_account_id,
                        'title' => $c->title ?? 'Migrated #'.$c->id,
                        'body' => $c->body ?? $c->caption ?? $c->title ?? 'Migrated content - '.$c->id,
                        'media' => $c->media ?? '[]',
                        'platform' => $c->platform,
                        'status' => $c->status,
                        'scheduled_at' => $c->scheduled_at,
                        'published_at' => $c->published_at,
                        'user_id' => 1, // legacy veriye default user
                        'created_at' => $c->created_at,
                        'updated_at' => $c->updated_at,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('content_items', function (Blueprint $table) {
            $table->dropForeign(['social_account_id']);
            $table->dropColumn(['social_account_id', 'media']);
        });
    }
};