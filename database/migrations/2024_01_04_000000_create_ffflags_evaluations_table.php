<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ffflags_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('feature_slug')->index();
            $table->string('scope_type')->nullable();
            $table->string('scope_id')->nullable();
            $table->boolean('result');
            $table->string('call_file')->nullable();
            $table->integer('call_line')->nullable();
            $table->json('conditions_snapshot')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ffflags_evaluations');
    }
};
