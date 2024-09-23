<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('blocked_user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_blocks');
    }

};
