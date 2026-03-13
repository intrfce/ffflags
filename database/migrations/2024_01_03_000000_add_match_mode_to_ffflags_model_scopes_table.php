<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ffflags_model_scopes', function (Blueprint $table) {
            $table->string('match_mode')->default('any')->after('scope_type');
        });
    }

    public function down(): void
    {
        Schema::table('ffflags_model_scopes', function (Blueprint $table) {
            $table->dropColumn('match_mode');
        });
    }
};
