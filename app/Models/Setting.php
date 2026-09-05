<?php

namespace App\Models;

use App\Support\MediaResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings table (site identity, navbar top bar, footer) editable
 * from the CMS via App\Filament\Pages\SiteSettings and exposed publicly via
 * SettingController — lets the frontend's navbar/footer/logo follow whatever
 * the admin sets, while shipping with defaults matching the site's original
 * hardcoded copy so a fresh install looks identical until someone edits it.
 */
class Setting extends Model
{
    protected $fillable = [
        'logo',
        'site_name',
        'site_name_en',
        'site_tagline',
        'site_tagline_en',
        'topbar_message',
        'topbar_message_en',
        'topbar_phone',
        'topbar_email',
        'footer_description',
        'footer_description_en',
        'footer_copyright',
        'footer_copyright_en',
        'social_website',
        'social_facebook',
        'social_twitter',
        'seo_default_meta_description',
        'seo_default_meta_description_en',
        'seo_default_og_image',
        'seo_keywords',
        'seo_keywords_en',
        'seo_google_site_verification',
        'seo_canonical_domain',
        'seo_twitter_handle',
        'seo_robots_index',
    ];

    protected $casts = [
        'seo_robots_index' => 'boolean',
    ];

    /**
     * The one settings row, creating it with the site's original hardcoded
     * copy as defaults the first time it's ever accessed.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'site_name' => 'Al-Ihsan Islamic School',
            'site_name_en' => 'Al-Ihsan Islamic School',
            'site_tagline' => 'National & Singapore - Based Curriculum Integrated With Islamic Values',
            'site_tagline_en' => 'National & Singapore - Based Curriculum Integrated With Islamic Values',
            'topbar_message' => 'Selamat datang di SD Al-Ihsan Islamic School',
            'topbar_message_en' => 'Welcome to SD Al-Ihsan Islamic School',
            'topbar_phone' => '+62 813-2097-5696',
            'topbar_email' => 'administrasi@alihsanislamicsch.co.id',
            'footer_description' => 'Mencetak generasi islami yang cerdas, berakhlak mulia, dan siap menghadapi tantangan global.',
            'footer_description_en' => 'Molding an Islamic generation that is intelligent, noble, and ready to face global challenges.',
            'footer_copyright' => '© {year} Al-Ihsan Islamic School — Hak cipta dilindungi.',
            'footer_copyright_en' => '© {year} Al-Ihsan Islamic School — All rights reserved.',
            'seo_default_meta_description' => 'Al-Ihsan Islamic School — sekolah unggulan dengan Kurikulum Nasional & Singapura yang dipadukan dengan nilai-nilai Islam.',
            'seo_default_meta_description_en' => 'Al-Ihsan Islamic School — a leading school combining the National & Singapore Curriculum with Islamic values.',
            'seo_robots_index' => true,
        ]);
    }

    /**
     * Localized, media-resolved shape for the public API — every `_en`
     * field swapped in for English (falling back to the Indonesian value
     * when empty) and the logo turned into a public URL.
     */
    public function resolvedData(string $locale = 'id'): array
    {
        $pick = fn (string $field) => ($locale === 'en' && filled($this->{"{$field}_en"}))
            ? $this->{"{$field}_en"}
            : $this->{$field};

        return [
            'logo' => MediaResolver::resolveValue($this->logo),
            'site_name' => $pick('site_name'),
            'site_tagline' => $pick('site_tagline'),
            'topbar_message' => $pick('topbar_message'),
            'topbar_phone' => $this->topbar_phone,
            'topbar_email' => $this->topbar_email,
            'footer_description' => $pick('footer_description'),
            'footer_copyright' => $pick('footer_copyright'),
            'social_website' => $this->social_website,
            'social_facebook' => $this->social_facebook,
            'social_twitter' => $this->social_twitter,
            'seo' => [
                'default_meta_description' => $pick('seo_default_meta_description'),
                'default_og_image' => MediaResolver::resolveValue($this->seo_default_og_image),
                'keywords' => ($locale === 'en' && filled($this->seo_keywords_en)) ? $this->seo_keywords_en : $this->seo_keywords,
                'google_site_verification' => $this->seo_google_site_verification,
                'canonical_domain' => $this->seo_canonical_domain,
                'twitter_handle' => $this->seo_twitter_handle,
                'robots_index' => $this->seo_robots_index,
            ],
        ];
    }
}
