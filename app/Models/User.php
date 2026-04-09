<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $role
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'middle_initial',
        'email',
        'password',
        'provider',
        'provider_id',
        'provider_token',
        'avatar',
        'email_verified_at',
        'last_login_at',
        'last_seen_at',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user registered via OAuth
     */
    public function isOAuthUser(): bool
    {
        return !empty($this->provider);
    }

    /**
     * Get user's display avatar
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?d=mp&s=200';
    }

    /**
     * User orders (optional, helpful)
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * User custom orders
     */
    public function customOrders()
    {
        return $this->hasMany(CustomOrder::class);
    }

    /**
     * User cart items
     */
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * User notifications
     */
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * User wishlists
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * User addresses
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Unread notifications
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    /**
     * Get unread notification count
     */
    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Generate and save OTP code
     */
    public function generateOtp(): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Prefer hashed OTP, but gracefully fallback if an old schema still has otp_code length 6.
        $this->otp_code = \Illuminate\Support\Facades\Hash::make($otp);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->otp_attempts = 0;

        try {
            $this->save();
        } catch (\Illuminate\Database\QueryException $e) {
            $isOtpLengthIssue = str_contains(strtolower($e->getMessage()), 'otp_code')
                && (str_contains(strtolower($e->getMessage()), 'data too long')
                    || str_contains(strtolower($e->getMessage()), 'right truncated'));

            if (!$isOtpLengthIssue) {
                throw $e;
            }

            $this->otp_code = $otp;
            $this->save();
        }

        return $otp;
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $otp): bool
    {
        // Check if OTP exists and hasn't expired
        if (!$this->otp_code || !$this->otp_expires_at || $this->otp_expires_at->isPast()) {
            return false;
        }

        // Check attempt limit (max 3 attempts)
        if ($this->otp_attempts >= 3) {
            return false;
        }

        // Increment attempts
        $this->otp_attempts += 1;
        $this->save();

        $storedOtp = (string) $this->otp_code;
        $looksHashed = str_starts_with($storedOtp, '$2y$') || str_starts_with($storedOtp, '$2a$') || str_starts_with($storedOtp, '$2b$');

        // Check OTP using hash comparison for hashed values, or constant-time plain compare for legacy 6-char storage.
        $otpMatches = $looksHashed
            ? \Illuminate\Support\Facades\Hash::check($otp, $storedOtp)
            : hash_equals($storedOtp, $otp);

        if ($otpMatches) {
            // Clear OTP and verify email
            $this->otp_code = null;
            $this->otp_expires_at = null;
            $this->otp_attempts = 0;
            $this->email_verified_at = now();
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Check if OTP is expired
     */
    public function isOtpExpired(): bool
    {
        return !$this->otp_expires_at || $this->otp_expires_at->isPast();
    }

    /**
     * Check if OTP attempts exceeded
     */
    public function isOtpAttemptsExceeded(): bool
    {
        return $this->otp_attempts >= 3;
    }
}
