<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('reporter_name', 120)->nullable()->after('reporter_email');
            $table->string('reporter_phone', 30)->nullable()->after('reporter_name');
        });
    }

    public function down(): void
    {
        Schema::table('reports', fn (Blueprint $table) => $table->dropColumn(['reporter_name', 'reporter_phone']));
    }
};
