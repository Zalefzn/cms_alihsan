<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * List every published page (slug + title only — call show() for
     * the full content of one page).
     */
    public function index(): JsonResponse
    {
        $pages = Page::query()
            ->where('is_published', true)
            ->orderBy('title')
            ->get(['slug', 'title', 'meta_description']);

        return response()->json(['data' => $pages]);
    }

    /**
     * A single published page with its visible blocks, in order,
     * with any uploaded media resolved to public URLs.
     */
    public function show(string $slug): JsonResponse
    {
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
                'data' => $block->resolvedData(),
            ]);

        return response()->json([
            'data' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'blocks' => $blocks,
            ],
        ]);
    }
}
