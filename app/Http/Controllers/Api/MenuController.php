<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * The full navbar menu tree: visible top-level items, each with
     * their visible children, in display order. Pass ?lang=en for
     * English labels; defaults to Indonesian.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('lang') === 'en' ? 'en' : 'id';

        $items = MenuItem::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->with(['children' => fn ($query) => $query->where('is_visible', true)])
            ->orderBy('order')
            ->get()
            ->map(fn (MenuItem $item) => $this->transform($item, $locale));

        return response()->json(['data' => $items]);
    }

    private function transform(MenuItem $item, string $locale): array
    {
        return [
            'label' => $item->localizedLabel($locale),
            'url' => $item->url,
            'open_in_new_tab' => $item->open_in_new_tab,
            'children' => $item->children->map(fn (MenuItem $child) => $this->transform($child, $locale)),
        ];
    }
}
