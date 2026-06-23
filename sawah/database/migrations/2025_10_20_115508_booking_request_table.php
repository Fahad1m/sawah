<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // صاحب الطلب (العميل)

            $table->enum('type', ['cancel', 'modify']);

            $table->string('reason_code')->nullable(); // schedule_change, health_issue, ... إلخ
            $table->text('note')->nullable();

            $table->date('new_start_date')->nullable();
            $table->unsignedInteger('new_people_count')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_reason')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['booking_id', 'type', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('booking_requests');
    }
};
