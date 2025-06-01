<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('ip_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('route')->nullable();
            $table->string('method', 10);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_suspicious')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ip_logs');
    }
};