<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Listing, ListingCategory, ListingCredential};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::withCount(['credentials as total_credentials' => fn($q) => $q,
                                     'credentials as available_credentials' => fn($q) => $q->where('is_used', false)]);
        if ($request->search)   $query->where('title', 'like', "%{$request->search}%");
        if ($request->category) $query->where('category', $request->category);
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', (bool)$request->status);
        }
        $listings   = $query->latest()->paginate(20)->withQueryString();
        $categories = ListingCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.listings', compact('listings', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'image'    => 'nullable|image|max:4096',
        ]);

        $data = $request->only('title', 'description', 'category', 'price', 'login_details');
        $data['is_active'] = $request->boolean('is_active');
        $data['featured']  = $request->boolean('featured');
        $data['stock']     = 0;

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $listing = Listing::create($data);
        return redirect()->route('admin.listings.edit', $listing)->with('success', 'Listing created — now add credentials below.');
    }

    public function edit(Listing $listing)
    {
        $listing->load('credentials');
        $categories = ListingCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.listings-edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Listing $listing)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'image'    => 'nullable|image|max:4096',
        ]);

        $data = $request->only('title', 'description', 'category', 'price', 'login_details');
        $data['is_active'] = $request->boolean('is_active');
        $data['featured']  = $request->boolean('featured');

        if ($request->hasFile('image')) {
            // Remove old image file
            if ($listing->image_path && file_exists(public_path($listing->image_path))) {
                @unlink(public_path($listing->image_path));
            }
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $listing->update($data);
        $listing->syncStock();
        return redirect()->route('admin.listings.edit', $listing)->with('success', 'Listing updated.');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return back()->with('success', 'Listing deleted.');
    }

    // ── Credential management ──────────────────────────────────────────────────

    public function addCredential(Request $request, Listing $listing)
    {
        $request->validate(['details' => 'required|string|max:5000']);

        $maxOrder = $listing->credentials()->max('sort_order') ?? 0;
        $listing->credentials()->create([
            'details'    => trim($request->details),
            'sort_order' => $maxOrder + 1,
        ]);
        $listing->syncStock();

        return back()->with('success', 'Credential added. Stock is now ' . $listing->fresh()->stock . '.');
    }

    public function removeCredential(Listing $listing, ListingCredential $credential)
    {
        abort_if($credential->listing_id !== $listing->id, 403);
        if ($credential->is_used) {
            return back()->with('error', 'Cannot remove a credential that has already been delivered to a buyer.');
        }
        $credential->delete();
        $listing->syncStock();
        return back()->with('success', 'Credential removed.');
    }

    private function storeImage($file): string
    {
        $dir = public_path('uploads/listings');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return 'uploads/listings/' . $filename;
    }
}
