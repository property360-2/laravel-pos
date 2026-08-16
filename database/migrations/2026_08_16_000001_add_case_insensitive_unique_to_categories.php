<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
        });

        if ($driver === 'sqlite') {
            DB::statement(
                'CREATE UNIQUE INDEX categories_name_case_insensitive_unique '
                .'ON categories (lower(name)) WHERE deleted_at IS NULL'
            );
        } else {
            DB::statement(
                'CREATE UNIQUE INDEX categories_name_case_insensitive_unique '
                .'ON categories (lower(name))'
            );
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX categories_name_case_insensitive_unique');

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
