<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reading_preferences',
        'notification_preferences',
        'display_preferences',
        'privacy_preferences',
        'language',
        'timezone',
        'email_notifications',
        'push_notifications',
    ];

    protected $casts = [
        'reading_preferences' => 'array',
        'notification_preferences' => 'array',
        'display_preferences' => 'array',
        'privacy_preferences' => 'array',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
    ];

    /**
     * Get the user that owns the preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get reading preference value.
     */
    public function getReadingPreference(string $key, $default = null)
    {
        return $this->reading_preferences[$key] ?? $default;
    }

    /**
     * Set reading preference value.
     */
    public function setReadingPreference(string $key, $value): void
    {
        $preferences = $this->reading_preferences ?? [];
        $preferences[$key] = $value;
        $this->update(['reading_preferences' => $preferences]);
    }

    /**
     * Get notification preference value.
     */
    public function getNotificationPreference(string $key, $default = null)
    {
        return $this->notification_preferences[$key] ?? $default;
    }

    /**
     * Set notification preference value.
     */
    public function setNotificationPreference(string $key, $value): void
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$key] = $value;
        $this->update(['notification_preferences' => $preferences]);
    }

    /**
     * Get display preference value.
     */
    public function getDisplayPreference(string $key, $default = null)
    {
        return $this->display_preferences[$key] ?? $default;
    }

    /**
     * Set display preference value.
     */
    public function setDisplayPreference(string $key, $value): void
    {
        $preferences = $this->display_preferences ?? [];
        $preferences[$key] = $value;
        $this->update(['display_preferences' => $preferences]);
    }

    /**
     * Get privacy preference value.
     */
    public function getPrivacyPreference(string $key, $default = null)
    {
        return $this->privacy_preferences[$key] ?? $default;
    }

    /**
     * Set privacy preference value.
     */
    public function setPrivacyPreference(string $key, $value): void
    {
        $preferences = $this->privacy_preferences ?? [];
        $preferences[$key] = $value;
        $this->update(['privacy_preferences' => $preferences]);
    }

    /**
     * Check if user has email notifications enabled.
     */
    public function hasEmailNotifications(): bool
    {
        return $this->email_notifications;
    }

    /**
     * Check if user has push notifications enabled.
     */
    public function hasPushNotifications(): bool
    {
        return $this->push_notifications;
    }

    /**
     * Get default reading preferences.
     */
    public static function getDefaultReadingPreferences(): array
    {
        return [
            'font_size' => 'medium',
            'font_family' => 'serif',
            'theme' => 'light',
            'line_height' => 1.5,
            'margin' => 'normal',
            'auto_scroll' => false,
            'reading_speed' => 'normal',
        ];
    }

    /**
     * Get default notification preferences.
     */
    public static function getDefaultNotificationPreferences(): array
    {
        return [
            'new_releases' => true,
            'price_drops' => true,
            'reading_reminders' => true,
            'achievements' => true,
            'social_updates' => false,
        ];
    }

    /**
     * Get default display preferences.
     */
    public static function getDefaultDisplayPreferences(): array
    {
        return [
            'compact_mode' => false,
            'show_cover_images' => true,
            'show_ratings' => true,
            'show_progress' => true,
            'grid_view' => false,
        ];
    }

    /**
     * Get default privacy preferences.
     */
    public static function getDefaultPrivacyPreferences(): array
    {
        return [
            'profile_visibility' => 'public',
            'reading_activity' => 'friends',
            'library_visibility' => 'private',
            'reviews_visibility' => 'public',
        ];
    }
}
