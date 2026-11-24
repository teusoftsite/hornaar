<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nests', function (Blueprint $table) {
            // eigen = eigen terrein (inwoner regelt een bestrijder via ons), openbaar = gemeente
            $table->string('plek', 10)->default('openbaar')->after('location_type');
        });
    }

    public function down(): void
    {
        Schema::table('nests', fn (Blueprint $table) => $table->dropColumn('plek'));
    }
};
