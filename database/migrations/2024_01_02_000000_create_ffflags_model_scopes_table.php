<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ffflags_model_scopes', function (Blueprint $table) {
            $table->id();
            $table->string('feature_slug');
            $table->string('scope_type');
            $table->string('condition');
            $table->json('value');
            $table->timestamps();

            $table->unique(['feature_slug', 'scope_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ffflags_model_scopes');
    }
};
