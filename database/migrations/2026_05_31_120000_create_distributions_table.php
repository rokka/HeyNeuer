<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')
                  ->constrained('computers')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('distributed_at');
            $table->string('md5_hash', 32)->nullable(); // wird in Folge-Migration ersetzt
            $table->timestamps();

            $table->index('distributed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
