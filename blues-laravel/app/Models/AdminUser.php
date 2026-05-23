<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Model
{
    protected $table = 'admins_users';
    protected $fillable = ['email', 'password_hash', 'display_name', 'avatar_url', 'is_active', 'last_login'];
    protected $hidden = ['password_hash'];

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', strtolower(trim($email)))->where('is_active', true)->first();
    }
}
