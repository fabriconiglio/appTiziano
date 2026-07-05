<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarSync extends Model
{
    protected $table = 'google_calendar_sync';

    protected $fillable = [
        'calendar_id',
        'sync_token',
        'ultima_sync_en',
    ];

    protected $casts = [
        'ultima_sync_en' => 'datetime',
    ];
}
