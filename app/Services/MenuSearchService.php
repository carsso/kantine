<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Information;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Read-only menu queries shared by the public API and the MCP server.
 */
class MenuSearchService
{
    public const MAX_MENU_DAYS = 31;

    public const MAX_SEARCH_RESULTS = 200;

    public const DEFAULT_SEARCH_RESULTS = 50;

    public const MAX_EVENTS = 50;

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function dateRangeRules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function searchRules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', Rule::in(array_keys(Dish::getTagsDefinitions()))],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_SEARCH_RESULTS],
            ...self::dateRangeRules(),
        ];
    }

    /**
     * Menus of every day in the range that has dishes or information, start and end included.
     *
     * @return array<int, array{date: string, weekday: string, event_name: ?string, information: ?string, dishes: array<int, array<string, mixed>>}>
     */
    public function menus(Tenant $tenant, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate ??= today()->format('Y-m-d');
        $endDate ??= $startDate;
        $this->ensureValidRange($startDate, $endDate, self::MAX_MENU_DAYS);

        $dishesByDate = Dish::where('tenant_id', $tenant->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('name')
            ->with('category.parent')
            ->get()
            ->sortBy([
                fn (Dish $a, Dish $b) => ($a->category?->parent?->sort_order ?? 0) <=> ($b->category?->parent?->sort_order ?? 0),
                fn (Dish $a, Dish $b) => ($a->category?->sort_order ?? 0) <=> ($b->category?->sort_order ?? 0),
                fn (Dish $a, Dish $b) => $a->id <=> $b->id,
            ])
            ->groupBy('date');

        $informationByDate = $this->informationQuery($tenant)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        $dates = $dishesByDate->keys()->merge($informationByDate->keys())->unique()->sort()->values();

        return $dates->map(fn (string $date) => [
            ...$this->formatDay($date, $informationByDate->get($date)),
            'dishes' => $dishesByDate->get($date, collect())->map(fn (Dish $dish) => $this->formatDish($dish, false))->values()->all(),
        ])->all();
    }

    /**
     * Dishes whose name contains every word of the query, optionally filtered by tags and dates.
     *
     * @param  array{query?: ?string, tags?: ?array<int, string>, start_date?: ?string, end_date?: ?string, order?: ?string, limit?: ?int}  $filters
     * @return array{total: int, results: array<int, array<string, mixed>>}
     */
    public function searchDishes(Tenant $tenant, array $filters): array
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        if ($startDate && $endDate) {
            $this->ensureValidRange($startDate, $endDate);
        }

        $query = Dish::where('tenant_id', $tenant->id)->whereNotNull('name');

        $words = preg_split('/\s+/', trim($filters['query'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            $query->where('name', 'like', '%'.addcslashes($word, '%_\\').'%');
        }
        foreach ($filters['tags'] ?? [] as $tag) {
            $query->where('tags', 'like', '%"'.$tag.'"%');
        }
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $total = $query->count();
        $order = $filters['order'] ?? 'asc';

        $dishes = $query->with('category.parent')
            ->orderBy('date', $order)
            ->orderBy('id')
            ->limit($filters['limit'] ?? self::DEFAULT_SEARCH_RESULTS)
            ->get();

        return [
            'total' => $total,
            'results' => $dishes->map(fn (Dish $dish) => $this->formatDish($dish))->all(),
        ];
    }

    /**
     * Days carrying an event or an information message, from today by default.
     *
     * @return array<int, array{date: string, weekday: string, event_name: ?string, information: ?string}>
     */
    public function events(Tenant $tenant, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate ??= today()->format('Y-m-d');
        if ($endDate) {
            $this->ensureValidRange($startDate, $endDate);
        }

        return $this->informationQuery($tenant)
            ->where('date', '>=', $startDate)
            ->when($endDate, fn ($query) => $query->where('date', '<=', $endDate))
            ->orderBy('date')
            ->limit(self::MAX_EVENTS)
            ->get()
            ->map(fn (Information $information) => $this->formatDay($information->date, $information))
            ->all();
    }

    /**
     * @throws ValidationException
     */
    protected function ensureValidRange(string $startDate, string $endDate, ?int $maxDays = null): void
    {
        if ($endDate < $startDate) {
            throw ValidationException::withMessages([
                'end_date' => 'The end_date must be on or after the start_date.',
            ]);
        }

        if ($maxDays && Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) >= $maxDays) {
            throw ValidationException::withMessages([
                'end_date' => "The date range cannot exceed {$maxDays} days.",
            ]);
        }
    }

    /**
     * @return Builder<Information>
     */
    protected function informationQuery(Tenant $tenant): Builder
    {
        return Information::where('tenant_id', $tenant->id)
            ->where(function ($query) {
                $query->where(fn ($query) => $query->whereNotNull('event_name')->where('event_name', '!=', ''))
                    ->orWhere(fn ($query) => $query->whereNotNull('information')->where('information', '!=', ''));
            });
    }

    /**
     * @return array{date: string, weekday: string, event_name: ?string, information: ?string}
     */
    protected function formatDay(string $date, ?Information $information): array
    {
        return [
            'date' => $date,
            'weekday' => Carbon::parse($date)->dayName,
            'event_name' => $information?->event_name ?: null,
            'information' => $information?->information ?: null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatDish(Dish $dish, bool $withDate = true): array
    {
        return [
            ...($withDate ? ['date' => $dish->date, 'weekday' => Carbon::parse($dish->date)->dayName] : []),
            'name' => $dish->name,
            'tags' => $dish->tags ?? [],
            'type' => $dish->category?->parent?->type,
            'category' => $dish->category?->parent?->name,
            'sub_category' => $dish->category?->name,
        ];
    }
}
