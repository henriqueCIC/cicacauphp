<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legislations', function (Blueprint $table) {
            $table->date('original_publication_date')->nullable();
            $table->string('source')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('legislations', function (Blueprint $table) {
            $table->dropColumn(['original_publication_date', 'source']);
        });
    }
};
