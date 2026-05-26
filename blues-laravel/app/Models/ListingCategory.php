<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'image_path', 'is_active'];

    public function getImageAttribute(): ?string
    {
        if (!$this->image_path) return null;
        if (str_starts_with($this->image_path, 'http')) return $this->image_path;
        return asset($this->image_path);
    }
    public function listings() { return $this->hasMany(Listing::class, 'category', 'slug'); }
}
