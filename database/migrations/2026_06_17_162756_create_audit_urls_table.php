<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->onDelete('cascade');
            $table->text('url');
            $table->integer('http_code')->nullable();
            $table->text('redirect_final_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_urls');
    }
};