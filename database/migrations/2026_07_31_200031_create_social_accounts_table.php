<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('social_accounts');
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('platform'); // instagram, facebook, tiktok
            $table->string('provider')->nullable();
            $table->string('account_id')->nullable()->index();
            $table->string('account_name')->nullable();
            $table->string('username')->nullable();
            $table->string('provider_id')->nullable();
            $table->text('access_token')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_connected')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
