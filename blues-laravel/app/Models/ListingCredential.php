<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingCredential extends Model
{
    protected $fillable = ['listing_id', 'details', 'is_used', 'used_at', 'purchase_id', 'sort_order'];

    protected $casts = ['is_used' => 'boolean', 'used_at' => 'datetime'];

    public function listing() { return $this->belongsTo(Listing::class); }
}
