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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_chat_id')->constrained()->onDelete('restrict');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('message_type',['text','image','file'])->default('text');
            $table->string('message');
            $table->boolean('is_edited')->default(false);
            $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
