<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('device_class')->index();
            $table->string('model');
            $table->boolean('has_webcam')->default(false);
            $table->boolean('has_wifi')->default(false);
            $table->string('status')->index();
            $table->text('comment')->nullable();
            $table->string('cpu_model')->nullable();
            $table->unsignedInteger('ram_gb')->nullable();
            $table->string('disk_type');
            $table->unsignedInteger('disk_gb')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};
