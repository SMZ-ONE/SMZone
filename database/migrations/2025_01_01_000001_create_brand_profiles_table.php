<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('SMZ Natural');
            $table->string('tone_default')->default('professional'); // professional|friendly|playful|luxury
            $table->string('lang_default')->default('de'); // de|en
            $table->text('brand_voice')->nullable(); // örn: "Wir sind natürlich, ehrlich, ohne Greenwashing..."
            $table->text('brand_story')->nullable(); // Marka hikayesi - AI her caption'da arka planda bilir ama uydurmaz
            $table->json('forbidden_words')->nullable(); // ["Wien", "billig", "chemisch"] - AI bunları asla kullanmaz
            $table->json('preferred_hashtags')->nullable(); // ["#smznatural", "#veganbeauty"] - her zaman ekle
            $table->json('brand_keywords')->nullable(); // ["Argan", "Rose", "Aloe", "Vegan", "Bio"] - marka DNA
            $table->text('extra_instructions')->nullable(); // Ekstra prompt talimatı
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_profiles');
    }
};
