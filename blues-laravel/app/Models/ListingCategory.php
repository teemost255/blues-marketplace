<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingCategory extends Model
{
    protected $fillable = ['name'];
    public function listings() { return $this->hasMany(Listing::class, 'category', 'name'); }
}
