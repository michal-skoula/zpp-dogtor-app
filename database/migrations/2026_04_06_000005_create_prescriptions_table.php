<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users');
            $table->foreignId('patient_id')->constrained('users');
            $table->foreignId('drug_id')->constrained('drugs');
            $table->decimal('dose_amount', 8, 2);
            $table->string('dose_unit', 20);
            $table->unsignedTinyInteger('frequency_value');
            $table->string('frequency_unit', 20);
            $table->unsignedTinyInteger('times_per_dose')->default(1);
            $table->text('instructions')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
