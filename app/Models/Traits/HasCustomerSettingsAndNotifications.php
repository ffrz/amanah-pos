<?php

namespace App\Models\Traits;

use App\Models\CustomerSetting;
use App\Models\CustomerNotification;

trait HasCustomerSettingsAndNotifications
{
    /**
     * Relasi ke CustomerSetting
     */
    public function settings()
    {
        return $this->hasMany(CustomerSetting::class, 'customer_id');
    }

    /**
     * Relasi ke CustomerNotification
     */
    public function customerNotifications()
    {
        return $this->hasMany(CustomerNotification::class, 'customer_id');
    }

    /**
     * Mengambil satu nilai setting
     */
    public function getSetting($key, $default = null)
    {
        return CustomerSetting::value($this->id, $key, $default);
    }

    /**
     * Menyimpan/Update nilai setting
     */
    public function setSetting($key, $value)
    {
        return CustomerSetting::setValue($this->id, $key, $value);
    }

    /**
     * Mengambil semua settings dalam bentuk key => value array
     */
    public function getAllSettings()
    {
        return CustomerSetting::values($this->id);
    }

    /**
     * Kirim notifikasi internal ke customer ini
     */
    public function notifyInternal($title, $message, $type = 'info', array $data = [])
    {
        return CustomerNotification::send($this->id, $title, $message, $type, $data);
    }

    /**
     * Hitung notifikasi yang belum dibaca
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->customerNotifications()->unread()->count();
    }
}