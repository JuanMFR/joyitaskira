<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Los banners pasan a ser las noticias del carrusel de inicio
        // (título, descripción, imagen y link opcional).
        Schema::rename('banners', 'news');

        Schema::table('news', function (Blueprint $table) {
            $table->renameColumn('subtitle', 'description');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });

        // El mini-blog de novedades se reemplaza por las noticias del carrusel.
        Schema::dropIfExists('posts');
    }

    public function down(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->renameColumn('description', 'subtitle');
        });

        Schema::rename('news', 'banners');
    }
};
