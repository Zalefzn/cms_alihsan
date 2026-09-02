<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * The full navbar menu tree: visible top-level items, each with
     * their visible children, in display order.
     */
    public function index(): JsonResponse
    {
        $items = MenuItem::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->with(['children' => fn ($query) => $query->where('is_visible', true)])
            ->orderBy('order')
            ->get()
            ->map(fn (MenuItem $item) => $this->transform($item));

        return response()->json(['data' => $items]);
    }

    private function transform(MenuItem $item): array
    {
        return [
            'label' => $item->label,
            'url' => $item->url,
            'open_in_new_tab' => $item->open_in_new_tab,
            'children' => $item->children->map(fn (MenuItem $child) => $this->transform($child)),
        ];
    }
}
