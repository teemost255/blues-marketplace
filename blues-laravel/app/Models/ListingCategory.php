<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active'];
    public function listings() { return $this->hasMany(Listing::class, 'category', 'slug'); }
}
