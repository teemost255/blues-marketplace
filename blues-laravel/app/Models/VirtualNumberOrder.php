<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualNumberOrder extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'external_order_id', 'service', 'country',
        'phone_number', 'sms_code', 'cost', 'status', 'raw_response', 'sms_received_at',
    ];

    protected $casts = [
        'sms_received_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCostAttribute(): string
    {
        return '₦' . number_format($this->cost, 2);
    }

    public function isLogsplug(): bool
    {
        return $this->provider === 'logsplug';
    }

    public function isHeroSms(): bool
    {
        return in_array($this->provider, ['herosms', 'smspool']);
    }
}
