<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'status', 'email_notifications', 'referred_by',
        'referral_deposited', 'referral_purchased', 'referral_bonus_paid',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'email_notifications'  => 'boolean',
            'referral_deposited'   => 'boolean',
            'referral_purchased'   => 'boolean',
            'referral_bonus_paid'  => 'boolean',
            'last_login_at'        => 'datetime',
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

    public function referralQualificationStatus(): string
    {
        if ($this->referral_bonus_paid)  return 'qualified';
        if (!$this->referred_by)         return 'none';
        if ($this->referral_deposited && $this->referral_purchased) return 'qualified';
        if ($this->referral_deposited)   return 'needs_purchase';
        if ($this->referral_purchased)   return 'needs_deposit';
        return 'pending';
    }
}
