<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Response;

/**
 * Generates sitemap.xml for the public frontend from the CMS's own page list —
 * since `alihsanislamicsch` is a pure client-rendered SPA (no server runtime), it
 * can't produce this itself, so the CMS does it and the frontend's hosting can
 * either point search engines here directly or proxy/redirect its own
 * /sitemap.xml to this endpoint.
 */
class SitemapController extends Controller
{
    /**
     * CMS slug => public frontend path. A slug not listed here (e.g. an internal
     * QA page, or a legacy redirect-only URL like "akademik-tk") is left out of
     * the sitemap on purpose — only real, canonical, indexable pages belong in it.
     *
     * @var array<string, string>
     */
    private const SLUG_PATHS = [
        'home' => '/',
        'about' => '/about',
        'sekolah-unit' => '/sekolah-unit',
        'unit-tk' => '/sekolah-unit/tk',
        'unit-sd' => '/sekolah-unit/sd',
        'unit-kober' => '/sekolah-unit/kober',
        // Older slug/route names, kept here so the sitemap stays correct on any
        // environment that hasn't been migrated to the unit-* slugs above yet.
        'visi' => '/visi',
        'visi-tk' => '/visiTk',
        'visi-kober' => '/visiKober',
        'penerimaan' => '/ppdb',
        'gallery' => '/galeri',
        'kontak' => '/kontak',
        'guru' => '/guru',
        'news' => '/news',
    ];

    public function index(): Response
    {
        $domain = rtrim(Setting::current()->seo_canonical_domain ?: config('app.frontend_url'), '/');

        $pages = Page::query()
            ->where('is_published', true)
            ->whereIn('slug', array_keys(self::SLUG_PATHS))
            ->get(['slug', 'updated_at']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($pages as $page) {
            $path = self::SLUG_PATHS[$page->slug];

            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($domain.$path).'</loc>'."\n";
            $xml .= '    <lastmod>'.$page->updated_at->toAtomString().'</lastmod>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
