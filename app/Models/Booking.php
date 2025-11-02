<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'status',
        'quantity',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket() : BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function payment() : HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
