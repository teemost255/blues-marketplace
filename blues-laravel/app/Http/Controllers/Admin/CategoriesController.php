<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = ListingCategory::withCount('listings')->orderBy('name')->paginate(20);
        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:listing_categories,name',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:100',
        ]);

        ListingCategory::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'icon'        => $request->icon,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Category \"{$request->name}\" created.");
    }

    public function update(Request $request, ListingCategory $category)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:listing_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:100',
        ]);

        $category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'icon'        => $request->icon,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Category updated.");
    }

    public function destroy(ListingCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
