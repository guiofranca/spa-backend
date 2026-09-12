<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Expense\ExpenseHistoryRequest;
use App\Http\Resources\Api\v1\ExpenseHistoryResource;
use App\Models\Bill;
use App\Models\Category;
use Illuminate\Support\Collection;

class ExpenseHistoryController extends Controller
{
    public function index(ExpenseHistoryRequest $request): ExpenseHistoryResource
    {
        $activeGroupId = $request->user()->active_group_id;
        $selectedCategories = collect($request->validated('categories', []))->map(fn($id) => (int)$id);
        $from = $request->validated('from');
        $to = $request->validated('to');
        $today = now()->toDateString();

        $bills = Bill::query()
            ->where('group_id', $activeGroupId)
            ->with(['category:id,name,icon', 'settle:id,name,date,settled,group_id'])
            ->where(function ($query) use ($activeGroupId) {
                $query->whereNull('settle_id')
                    ->orWhereHas('settle', fn($settle) => $settle->where('group_id', $activeGroupId));
            })
            ->when($from || $to, function ($query) use ($activeGroupId, $from, $to, $today) {
                $fromDate = $from ?? '0000-01-01';
                $toDate = $to ?? '9999-12-31';

                $query->where(function ($periodQuery) use ($activeGroupId, $fromDate, $toDate, $today) {
                    $periodQuery->whereHas('settle', fn($settle) => $settle
                        ->where('group_id', $activeGroupId)
                        ->whereBetween('date', [$fromDate, $toDate]));

                    if ($fromDate <= $today && $today <= $toDate) {
                        $periodQuery->orWhereNull('settle_id');
                    }
                });
            })
            ->get();

        $entries = [];
        $categoryMeta = [];

        $bills
            ->groupBy(fn(Bill $bill) => $bill->settle_id ?? 'pending')
            ->each(function ($groupBills, $key) use (&$entries, &$categoryMeta, $today) {
                $first = $groupBills->first();

                $entry = [
                    'settle_id' => null,
                    'name' => 'Em aberto',
                    'date' => $today,
                    'settled' => false,
                ];

                if ($key !== 'pending') {
                    $entry['settle_id'] = (int)$key;
                    $entry['name'] = $first->settle->name ?? '';
                    $entry['date'] = $first->settle->date->format('Y-m-d');
                    $entry['settled'] = (bool)$first->settle->settled;
                }

                $categorySums = [];

                $groupBills->each(function (Bill $bill) use (&$categorySums, &$categoryMeta) {
                    $categoryKey = $bill->category_id === null ? 'none' : (string)$bill->category_id;
                    $categorySums[$categoryKey] = ($categorySums[$categoryKey] ?? 0) + $bill->getAttributes()['value'];

                    if (! isset($categoryMeta[$categoryKey])) {
                        $categoryMeta[$categoryKey] = [
                            'id' => $bill->category_id,
                            'name' => $bill->category?->name ?? 'Sem categoria',
                            'icon' => $bill->category?->icon,
                        ];
                    }
                });

                $entry['total'] = $this->money($groupBills->sum(fn(Bill $bill) => $bill->getAttributes()['value']));
                $entry['category_sums'] = $categorySums;

                $entries[] = $entry;
            });

        usort($entries, fn($a, $b) => $a['date'] <=> $b['date']);

        $bars = array_map(fn($entry) => [
            'settle_id' => $entry['settle_id'],
            'name' => $entry['name'],
            'date' => $entry['date'],
            'settled' => $entry['settled'],
            'total' => $entry['total'],
        ], $entries);

        $series = $this->buildSeries($entries, $categoryMeta, $selectedCategories);

        return new ExpenseHistoryResource((object)[
            'filters' => [
                'categories' => $selectedCategories->values()->all(),
                'from' => $from,
                'to' => $to,
            ],
            'bars' => $bars,
            'series' => $series,
        ]);
    }

    private function buildSeries(array $entries, array $categoryMeta, Collection $selectedCategories): array
    {
        if ($selectedCategories->isNotEmpty()) {
            return $this->buildSelectedSeries($entries, $categoryMeta, $selectedCategories);
        }

        $series = [];

        $keys = collect($categoryMeta)
            ->keys()
            ->filter(fn($key) => $key !== 'none')
            ->sortBy(fn($key) => $categoryMeta[$key]['name'])
            ->values();

        if (isset($categoryMeta['none'])) {
            $keys->push('none');
        }

        foreach ($keys as $key) {
            $series[] = [
                'category_id' => $categoryMeta[$key]['id'],
                'name' => $categoryMeta[$key]['name'],
                'icon' => $categoryMeta[$key]['icon'],
                'is_aggregate' => false,
                'values' => array_map(
                    fn($entry) => $this->money($entry['category_sums'][$key] ?? null),
                    $entries,
                ),
            ];
        }

        return $series;
    }

    private function buildSelectedSeries(array $entries, array $categoryMeta, Collection $selectedCategories): array
    {
        $series = [];

        foreach ($selectedCategories as $categoryId) {
            $category = $categoryMeta[(string)$categoryId] ?? Category::find($categoryId)?->only('id', 'name', 'icon');

            $series[] = [
                'category_id' => $categoryId,
                'name' => $category['name'] ?? null,
                'icon' => $category['icon'] ?? null,
                'is_aggregate' => false,
                'values' => array_map(
                    fn($entry) => $this->money($entry['category_sums'][(string)$categoryId] ?? null),
                    $entries,
                ),
            ];
        }

        $selected = $selectedCategories->all();

        $series[] = [
            'category_id' => null,
            'name' => 'Outros',
            'icon' => null,
            'is_aggregate' => true,
            'values' => array_map(function ($entry) use ($selected) {
                $other = collect($entry['category_sums'])
                    ->reject(fn($cents, $key) => in_array((int)$key, $selected, true))
                    ->sum();

                return $this->money($other);
            }, $entries),
        ];

        return $series;
    }

    private function money(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        return sprintf('%.2f', $cents / 100);
    }
}