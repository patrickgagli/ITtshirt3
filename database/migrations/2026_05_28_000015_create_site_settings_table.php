<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // SEO
            $table->string('site_name', 100)->default('ITtshirt3');
            $table->string('tagline', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_image_url', 500)->nullable();

            // Social media links
            $table->string('social_facebook', 500)->nullable();
            $table->string('social_instagram', 500)->nullable();
            $table->string('social_twitter', 500)->nullable();
            $table->string('social_tiktok', 500)->nullable();
            $table->string('twitter_handle', 100)->nullable();

            $table->timestamps();
        });

        // Seed the single canonical row so the app always has a settings record.
        DB::table('site_settings')->insert([
            'id'               => 1,
            'site_name'        => 'ITtshirt3',
            'tagline'          => 'La boutique de t-shirts tendance',
            'meta_description' => 'Découvrez notre collection de t-shirts pour hommes, femmes, garçons et filles.',
            'meta_keywords'    => 't-shirt, boutique, mode, vêtements',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
