<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reflections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_profile_id');
            $table->date('week_of');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_profile_id', 'week_of']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reflections');
    }
};
