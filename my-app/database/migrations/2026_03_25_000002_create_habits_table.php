<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emoji')->default('⭐');
            $table->string('color')->default('#1e3a2f');
            $table->string('time_of_day')->default('morning');
            $table->text('why')->nullable();
            $table->text('bundle')->nullable();
            $table->text('two_min_version')->nullable();
            $table->text('stack')->nullable();
            $table->string('duration')->nullable();
            $table->text('reward')->nullable();
            $table->string('difficulty')->default('medium');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};
