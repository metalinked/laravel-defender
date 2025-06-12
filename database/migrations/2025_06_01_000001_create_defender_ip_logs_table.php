<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('defender_ip_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('route')->nullable();
            $table->string('method', 10);
            $table->string('user_agent', 512)->nullable();
            $table->string('referer', 512)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('headers_hash', 128)->nullable();
            $table->boolean('is_suspicious')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('defender_ip_logs');
    }
};
