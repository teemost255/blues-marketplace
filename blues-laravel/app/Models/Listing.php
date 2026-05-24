<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = ['title', 'description', 'category', 'price', 'stock', 'is_active', 'image_path', 'image_url', 'featured', 'login_details'];
    public function purchases() { return $this->hasMany(Purchase::class); }

    public function getImageAttribute(): ?string
    {
        if ($this->image_path) return asset('storage/' . $this->image_path);
        if ($this->image_url)  return $this->image_url;
        return null;
    }
}
