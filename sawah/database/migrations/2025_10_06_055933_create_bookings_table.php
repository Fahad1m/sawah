<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('bookings', function (Blueprint $t) {
      $t->id();
      $t->foreignId('trip_id')->constrained()->cascadeOnDelete();
      $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
      $t->string('customer_name');
      $t->string('customer_email')->nullable();
      $t->date('start_date');
      $t->unsignedInteger('people_count')->default(1);
      $t->decimal('amount', 10, 2)->default(0);
      $t->enum('status', ['pending','confirmed','cancelled'])->default('pending');
      $t->string('payment_ref')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('bookings'); }
};
