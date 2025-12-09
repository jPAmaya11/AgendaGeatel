<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'location',
        'start_at',
        'end_at',
        'all_day',

        'reminder_at',
        'reminder_channel',

        'reminder_type',
        'reminder_interval',
        'reminder_unit',  
        'reminder_start_at', 
        'reminder_frequency',

        'reminder_sent_at',
        'reminder_status',

        'google_event_id',
        'status',
    ];

    protected $casts = [
        'start_at'          => 'datetime',
        'end_at'            => 'datetime',
        'reminder_at'       => 'datetime',
        'reminder_start_at' => 'datetime',
        'reminder_sent_at'  => 'datetime',
        'all_day'           => 'boolean',
    ];

    /**
     * Usuario dueño del evento.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
