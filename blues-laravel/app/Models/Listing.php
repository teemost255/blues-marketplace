<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Listing extends Model
{
    protected $fillable = ['title', 'description', 'category', 'price', 'stock', 'is_active', 'image_url'];
    public function purchases() { return $this->hasMany(Purchase::class); }
}
