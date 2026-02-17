<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\WoohooCategorySyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::with('parent')->orderBy('parent_id')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function show(Request $request, Category $category): View
    {
        $category->load(['parent', 'children' => fn ($q) => $q->withCount('products')]);
        $category->loadCount('products');

        $products = $category->products()
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.show', compact('category', 'products'));
    }

    public function sync(WoohooCategorySyncService $sync): RedirectResponse
    {
        $result = $sync->sync(false);

        if ($result['success']) {
            return redirect()->route('admin.categories.index')
                ->with('success', "Synced {$result['synced']} categories from Woohoo.");
        }

        return redirect()->route('admin.categories.index')
            ->with('error', $result['message'] ?? 'Sync failed.');
    }

    public function create(): View
    {
        $parents = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.categories.form', ['category' => null, 'parents' => $parents]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'color_code' => 'nullable|string|max:50',
            'offer_description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::where('id', '!=', $category->id)->orderBy('name')->get(['id', 'name']);

        return view('admin.categories.form', ['category' => $category, 'parents' => $parents]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'color_code' => 'nullable|string|max:50',
            'offer_description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['parent_id'] = $validated['parent_id'] ?? null;

        if (isset($validated['parent_id']) && (int) $validated['parent_id'] === $category->id) {
            $validated['parent_id'] = $category->parent_id;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }
}
