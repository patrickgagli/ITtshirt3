<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton site-wide settings row (always id = 1).
 *
 * @property string      $site_name
 * @property string|null $tagline
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image_url
 * @property string|null $social_facebook
 * @property string|null $social_instagram
 * @property string|null $social_twitter
 * @property string|null $social_tiktok
 * @property string|null $twitter_handle
 */
class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'tagline',
        'meta_description',
        'meta_keywords',
        'og_image_url',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'social_tiktok',
        'twitter_handle',
    ];

    /**
     * Return the single settings record, creating it with defaults if missing.
     */
    public static function instance(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['site_name' => 'ITtshirt3']
        );
    }
}
