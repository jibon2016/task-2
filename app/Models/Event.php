<?php

namespace App\Models;

use App\Traits\CommonQueryScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Event extends Model
{
    use HasFactory, CommonQueryScopes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    final public function get_events(Request $request)
    {
        $query = self::query();
        if ($request->input('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->input('date')) {
            $query->whereDate('date', $request->input('date'));
        }
        if ($request->input('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }
        return $query->orderBy('id', 'desc')->paginate($request->input('per_page') ?? 10)->withQueryString();
    }
}
