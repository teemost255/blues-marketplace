<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransferPayment extends Model
{
    protected $fillable = [
        'user_id', 'type', 'listing_id', 'purchase_id',
        'amount', 'reference', 'status', 'admin_note', 'confirmed_at',
    ];

    protected $casts = ['confirmed_at' => 'datetime'];

    public function user()    { return $this->belongsTo(User::class); }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function purchase(){ return $this->belongsTo(Purchase::class); }
}
