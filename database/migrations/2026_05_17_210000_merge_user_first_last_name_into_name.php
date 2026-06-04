<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'first_name')) {
            // Fresh installs already have `name` only — nothing to do.
            return;
        }

        if (! Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }

        DB::statement("UPDATE users SET name = TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) WHERE name IS NULL");
        DB::statement("UPDATE users SET name = NULL WHERE name = ''");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'first_name')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        $users = DB::table('users')->whereNotNull('name')->get(['id', 'name']);
        foreach ($users as $u) {
            $parts = preg_split('/\s+/', trim($u->name), 2);
            DB::table('users')->where('id', $u->id)->update([
                'first_name' => $parts[0] ?? null,
                'last_name'  => $parts[1] ?? null,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
