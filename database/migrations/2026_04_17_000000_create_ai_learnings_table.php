<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_learnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('product_name')->nullable();
            $table->string('product_url')->nullable();
            $table->text('original_prompt')->nullable();
            $table->text('original_content');
            $table->text('edited_content')->nullable();
            $table->string('platform')->default('instagram');
            $table->string('tone')->default('premium');
            $table->string('lang')->default('de');
            $table->string('feedback_type')->default('edit'); // edit, like, dislike
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_learnings');
    }
};
