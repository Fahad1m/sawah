<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();

            // التقييم 1 إلى 5
            $table->unsignedTinyInteger('rating');

            // نص التوصية
            $table->text('text')->nullable();

            $table->timestamps();

            // لتحسين الاستعلامات
            $table->index(['user_id', 'trip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
