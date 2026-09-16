<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('help_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('target_audience', ['penyewa', 'pemilik', 'umum'])->default('penyewa');
            $table->foreignId('parent_id')->nullable()->constrained('help_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_category_id')->constrained('help_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary_answer')->nullable();
            $table->boolean('has_platform_tabs')->default(false);
            $table->longText('content_desktop')->nullable();
            $table->longText('content_mobile')->nullable();
            $table->longText('additional_notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('help_article_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_article_id')->constrained('help_articles')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_helpful');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_article_feedbacks');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('help_categories');
    }
};
