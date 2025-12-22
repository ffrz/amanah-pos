<?php

namespace App\Models\Traits;

use App\Models\UserSetting;
use App\Models\UserNotification;

trait HasUserSettingsAndNotifications
{
    /**
     * Relasi ke UserSetting
     */
    public function settings()
    {
        return $this->hasMany(UserSetting::class, 'user_id');
    }

    /**
     * Relasi ke UserNotification
     */
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }

    /**
     * Mengambil satu nilai setting
     */
    public function getSetting($key, $default = null)
    {
        return UserSetting::value($this->id, $key, $default);
    }

    /**
     * Menyimpan/Update nilai setting
     */
    public function setSetting($key, $value)
    {
        return UserSetting::setValue($this->id, $key, $value);
    }

    /**
     * Mengambil semua settings dalam bentuk key => value array
     */
    public function getAllSettings()
    {
        return UserSetting::values($this->id);
    }

    /**
     * Kirim notifikasi internal ke user ini
     */
    public function notifyInternal($title, $message, $type = 'info', array $data = [])
    {
        return UserNotification::send($this->id, $title, $message, $type, $data);
    }

    /**
     * Hitung notifikasi yang belum dibaca
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->userNotifications()->unread()->count();
    }
}