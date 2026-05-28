<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'price', 'stock',
        'is_active', 'image_path', 'image_url', 'featured', 'login_details',
    ];

    public function purchases()   { return $this->hasMany(Purchase::class); }
    public function credentials() { return $this->hasMany(ListingCredential::class)->orderBy('sort_order')->orderBy('id'); }
    public function availableCredentials() { return $this->credentials()->where('is_used', false); }

    /** Keep stock column in sync with available credentials count. */
    public function syncStock(): void
    {
        $count = $this->availableCredentials()->count();
        $this->updateQuietly(['stock' => $count]);
    }

    /** Returns true if this listing uses the new per-credential system. */
    public function usesCredentialSystem(): bool
    {
        return $this->credentials()->exists();
    }

    public function getImageAttribute(): ?string
    {
        if ($this->image_path) {
            $path = ltrim($this->image_path, '/');
            return '/' . $path;
        }
        if ($this->image_url) return $this->image_url;
        return null;
    }
}
