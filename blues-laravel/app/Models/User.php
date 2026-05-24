<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'status', 'email_notifications', 'referred_by'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'email_notifications' => 'boolean',
        ];
    }

    public function profile()       { return $this->hasOne(Profile::class); }
    public function wallet()        { return $this->hasOne(Wallet::class); }
    public function purchases()     { return $this->hasMany(Purchase::class); }
    public function notifications() { return $this->hasMany(Notification::class); }
    public function wishlists()     { return $this->hasMany(Wishlist::class); }
    public function tickets()       { return $this->hasMany(SupportTicket::class); }
    public function transactions()  { return $this->hasMany(WalletTransaction::class); }
    public function referrer()      { return $this->belongsTo(User::class, 'referred_by'); }
    public function referrals()     { return $this->hasMany(User::class, 'referred_by'); }

    public function getWalletBalance(): float {
        return $this->wallet?->balance ?? 0.00;
    }

    public function isBanned(): bool    { return $this->status === 'banned'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
    public function isActive(): bool    { return $this->status === 'active'; }
}
