<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'ONGOING', 'COMPLETED', 'CANCELLED'])->default('DRAFT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};