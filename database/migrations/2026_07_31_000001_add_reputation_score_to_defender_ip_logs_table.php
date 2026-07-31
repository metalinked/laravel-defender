<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('defender_ip_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('reputation_score')->nullable()->after('country_code');
        });
    }

    public function down() {
        Schema::table('defender_ip_logs', function (Blueprint $table) {
            $table->dropColumn('reputation_score');
        });
    }
};
