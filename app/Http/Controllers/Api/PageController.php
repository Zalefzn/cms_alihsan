<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * List every published page (slug + title only — call show() for
     * the full content of one page). Pass ?lang=en for English title
     * and description; defaults to Indonesian.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->locale($request);

        $pages = Page::query()
            ->where('is_published', true)
            ->orderBy('title')
            ->get(['slug', 'title', 'title_en', 'meta_description', 'meta_description_en'])
            ->map(fn (Page $page) => [
                'slug' => $page->slug,
                'title' => $page->localizedTitle($locale),
                'meta_description' => $page->localizedMetaDescription($locale),
            ]);

        return response()->json(['data' => $pages]);
    }

    /**
     * A single published page with its visible blocks, in order, with
     * any uploaded media resolved to public URLs. Pass ?lang=en for
     * English content (falls back to Indonesian for any field that
     * hasn't been translated yet); defaults to Indonesian.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $this->locale($request);

        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $blocks = $page->blocks()
            ->where('is_visible', true)
            ->orderBy('order')
            ->get()
            ->map(fn ($block) => [
                'id' => $block->id,
                'type' => $block->type,
                'order' => $block->order,
                'data' => $block->resolvedData($locale),
            ]);

        return response()->json([
            'data' => [
                'slug' => $page->slug,
                'title' => $page->localizedTitle($locale),
                'meta_description' => $page->localizedMetaDescription($locale),
                'blocks' => $blocks,
            ],
        ]);
    }

    private function locale(Request $request): string
    {
        return $request->query('lang') === 'en' ? 'en' : 'id';
    }
}
