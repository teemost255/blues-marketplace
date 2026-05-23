<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SupportTicket extends Model
{
    protected $fillable = ['user_id', 'subject', 'message', 'admin_reply', 'status', 'priority'];
    public function user() { return $this->belongsTo(User::class); }
}
