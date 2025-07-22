<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            DB::statement('ALTER TABLE cotio_instancias ALTER COLUMN cotio_estado DROP NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            DB::statement('ALTER TABLE cotio_instancias ALTER COLUMN cotio_estado SET NOT NULL');
        });
    }
};
