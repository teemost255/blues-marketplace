<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model
{
    protected $fillable = ['user_id', 'listing_id', 'amount', 'status', 'delivery_data'];
    public function user()    { return $this->belongsTo(User::class); }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function review()  { return $this->hasOne(\App\Models\ListingReview::class, 'purchase_id'); }
}
