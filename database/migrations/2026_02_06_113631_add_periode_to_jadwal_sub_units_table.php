<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /**
         * 1️⃣ Tambahkan kolom periode (jika belum ada)
         */
        Schema::table('jadwal_sub_units', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_sub_units', 'start_date')) {
                $table->date('start_date')->nullable()->after('hari');
            }
            if (!Schema::hasColumn('jadwal_sub_units', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        /**
         * 2️⃣ DROP FOREIGN KEY sub_unit_id (jika ada)
         */
        $foreignKey = DB::table('information_schema.key_column_usage')
            ->select('CONSTRAINT_NAME')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'jadwal_sub_units')
            ->where('column_name', 'sub_unit_id')
            ->whereNotNull('referenced_table_name')
            ->first();

        if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
            DB::statement(
                "ALTER TABLE jadwal_sub_units DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}"
            );
        }

        /**
         * 3️⃣ DROP UNIQUE (sub_unit_id, hari) jika ada
         */
        $uniqueExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'jadwal_sub_units')
            ->where('index_name', 'jadwal_sub_units_sub_unit_id_hari_unique')
            ->exists();

        if ($uniqueExists) {
            DB::statement(
                'ALTER TABLE jadwal_sub_units DROP INDEX jadwal_sub_units_sub_unit_id_hari_unique'
            );
        }

        /**
         * 4️⃣ Tambahkan FK + INDEX periode
         */
        Schema::table('jadwal_sub_units', function (Blueprint $table) {

            $table->foreign('sub_unit_id')
                ->references('id')
                ->on('sub_units')
                ->cascadeOnDelete();

            $table->index(
                ['sub_unit_id', 'hari', 'start_date', 'end_date'],
                'jadwal_sub_units_periode_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_sub_units', function (Blueprint $table) {

            $table->dropIndex('jadwal_sub_units_periode_idx');

            $table->dropForeign(['sub_unit_id']);

            $table->unique(['sub_unit_id', 'hari']);

            $table->foreign('sub_unit_id')
                ->references('id')
                ->on('sub_units')
                ->cascadeOnDelete();

            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
