<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'image_path', 'is_active'];

    public function getImageAttribute(): ?string
    {
        if ($this->image_path) return asset('storage/' . $this->image_path);
        return null;
    }
    public function listings() { return $this->hasMany(Listing::class, 'category', 'slug'); }
}
