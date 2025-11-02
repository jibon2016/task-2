<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

trait CommonQueryScopes
{
    /**
     * Scope: filter by date range on a column (defaults to created_at).
     *
     * Usage:
     *   Model::filterByDate('2025-01-01', '2025-01-31')->get();
     *   Model::filterByDate(null, '2025-01-31', 'event_date')->get();
     *
     * @param Builder $query
     * @param string|null $from  Date string parseable by Carbon
     * @param string|null $to    Date string parseable by Carbon
     * @param string $column
     * @return Builder
     */
    public function scopeFilterByDate(Builder $query, ?string $from = null, ?string $to = null, string $column = 'created_at'): Builder
    {
        if ($from) {
            try {
                $fromDt = Carbon::parse($from)->startOfDay();
                $query->where($column, '>=', $fromDt);
            } catch (\Exception $e) {
                // ignore invalid date and continue
            }
        }

        if ($to) {
            try {
                $toDt = Carbon::parse($to)->endOfDay();
                $query->where($column, '<=', $toDt);
            } catch (\Exception $e) {
                // ignore invalid date and continue
            }
        }

        return $query;
    }

    /**
     * Scope: search by title (or custom column) using LIKE.
     *
     * Usage:
     *   Model::searchByTitle('concert')->get();
     *   Model::searchByTitle('john', 'organizer_name')->get();
     *
     * @param Builder $query
     * @param string|null $term
     * @param string $column
     * @return Builder
     */
    public function scopeSearchByTitle(Builder $query, ?string $term = null, string $column = 'title'): Builder
    {
        $term = trim((string) ($term ?? ''));

        if ($term === '') {
            return $query;
        }

        return $query->where($column, 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%');
    }
}
