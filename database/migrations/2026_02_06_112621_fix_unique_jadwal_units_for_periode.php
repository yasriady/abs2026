<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /**
         * 1️⃣ DROP INDEX periode jika sudah ada
         */
        $periodeIndexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'jadwal_units')
            ->where('index_name', 'jadwal_units_periode_idx')
            ->exists();

        if ($periodeIndexExists) {
            DB::statement('DROP INDEX jadwal_units_periode_idx ON jadwal_units');
        }

        /**
         * 2️⃣ DROP UNIQUE (unit_id, hari) jika ada
         */
        $uniqueExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'jadwal_units')
            ->where('index_name', 'jadwal_units_unit_id_hari_unique')
            ->exists();

        if ($uniqueExists) {
            DB::statement('ALTER TABLE jadwal_units DROP INDEX jadwal_units_unit_id_hari_unique');
        }

        /**
         * 3️⃣ DROP FOREIGN KEY jika ada
         */
        $foreignKey = DB::table('information_schema.key_column_usage')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'jadwal_units')
            ->where('column_name', 'unit_id')
            ->whereNotNull('referenced_table_name')
            ->first();

        if ($foreignKey) {
            DB::statement("ALTER TABLE jadwal_units DROP FOREIGN KEY {$foreignKey->constraint_name}");
        }

        /**
         * 4️⃣ TAMBAHKAN FK + INDEX PERIODE
         */
        Schema::table('jadwal_units', function (Blueprint $table) {

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();

            $table->index(
                ['unit_id', 'hari', 'start_date', 'end_date'],
                'jadwal_units_periode_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_units', function (Blueprint $table) {

            $table->dropIndex('jadwal_units_periode_idx');

            $table->dropForeign(['unit_id']);

            $table->unique(['unit_id', 'hari']);

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();
        });
    }
};
