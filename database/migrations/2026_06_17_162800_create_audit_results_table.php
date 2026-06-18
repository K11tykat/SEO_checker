<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_url_id')->constrained()->onDelete('cascade');
            
            $table->boolean('h1_is_valid')->default(false);
            $table->text('h1_error_reason')->nullable();
            
            $table->boolean('title_is_valid')->default(false);
            $table->text('title_error_reason')->nullable();
            $table->integer('title_length')->nullable();
            
            $table->boolean('description_is_valid')->default(false);
            $table->text('description_error_reason')->nullable();
            $table->integer('description_length')->nullable();
            
            $table->boolean('headings_valid')->default(false);
            
            $table->integer('external_links_count')->default(0);
            $table->integer('external_links_nofollow')->default(0);
            $table->integer('external_links_dofollow')->default(0);
            
            $table->boolean('og_marker')->default(false);
            
            $table->boolean('schema_marker')->default(false);
            $table->json('schema_formats')->nullable();
            
            $table->boolean('robots_marker')->default(false);
            $table->boolean('sitemap_marker')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_results');
    }
};