<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            // recipient_hash wird bei Massenausgaben nicht gesetzt.
            // Der Unique-Index bleibt bestehen — MySQL/SQLite behandeln NULL
            // als unterschiedliche Werte, sodass mehrere NULLs erlaubt sind.
            $table->string('recipient_hash', 64)->nullable()->change();

            $table->text('comment')->nullable()->after('recipient_hash');
        });
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('comment');
            $table->string('recipient_hash', 64)->nullable(false)->change();
        });
    }
};
