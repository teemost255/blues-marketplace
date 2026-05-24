<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingReview extends Model
{
    protected $fillable = ['user_id', 'listing_id', 'purchase_id', 'rating', 'comment'];

    public function user()    { return $this->belongsTo(User::class); }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function purchase(){ return $this->belongsTo(Purchase::class); }
}
