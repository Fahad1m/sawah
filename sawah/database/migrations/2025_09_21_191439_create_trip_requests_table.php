<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('trip_requests', function (Blueprint $t) {
            $t->id();
            $t->string('user_name');          
            $t->string('user_email')->nullable();
            $t->string('destination');
            $t->date('date');
            $t->unsignedInteger('people')->default(1);
            $t->unsignedInteger('budget')->default(0);
            $t->enum('status',['pending','approved','rejected'])->default('pending');
            $t->text('details')->nullable();
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('trip_requests'); }
};
