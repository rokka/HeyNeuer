<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dummy-Daten sind in MD5-Format (32 Zeichen) und random — können weg,
        // bevor wir den Spaltentyp ändern und einen Unique-Constraint setzen.
        DB::table('distributions')->delete();

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('md5_hash');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->string('recipient_hash', 64)->unique()->after('distributed_at');
        });
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropUnique(['recipient_hash']);
            $table->dropColumn('recipient_hash');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->string('md5_hash', 32)->nullable()->after('distributed_at');
        });
    }
};
