<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualNumberOrder extends Model
{
    protected $fillable = [
        'user_id', 'activation_id', 'phone_number',
        'service', 'service_name', 'country', 'country_name',
        'cost', 'sms_code', 'status', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'cost'       => 'decimal:2',
        'user_id'    => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'waiting']);
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'waiting'   => 'bg-yellow-900/50 text-yellow-400 border-yellow-700/50',
            'received'  => 'bg-blue-900/50 text-blue-400 border-blue-700/50',
            'completed' => 'bg-green-900/50 text-green-400 border-green-700/50',
            'cancelled' => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
            'expired'   => 'bg-red-900/50 text-red-400 border-red-700/50',
            default     => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
        };
    }
}
