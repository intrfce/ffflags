<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ffflags_feature_cache', function (Blueprint $table) {
            $table->id();
            $table->string('feature_class');
            $table->string('scope_type')->nullable();
            $table->string('scope_id')->nullable();
            $table->boolean('result');
            $table->timestamps();

            $table->unique(['feature_class', 'scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ffflags_feature_cache');
    }
};
