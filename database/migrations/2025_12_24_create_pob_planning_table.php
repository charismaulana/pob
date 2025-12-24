<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pob_planning', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // References employee in Ramesa
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('location');
            $table->enum('status', ['planned', 'confirmed', 'cancelled'])->default('planned');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['employee_id', 'start_date', 'end_date']);
            $table->index('location');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pob_planning');
    }
};
