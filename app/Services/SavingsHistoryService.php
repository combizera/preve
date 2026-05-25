<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class SavingsHistoryService
{
    public function __construct(private SavingsBucketBalanceService $balances) {}

    /**
     * Monthly balance snapshots for every month of $year, across all of the
     * user's savings buckets.
     *
     * @return list<array{month: string, balance: int, deposits: int, withdrawals: int}>
     */
    public function monthlyBalances(User $user, int $year): array
    {
        $buckets = $user
            ->savingsBuckets()
            ->get(['id', 'current_amount', 'created_at']);

        $allTransactions = $user
            ->transactions()
            ->whereNotNull('savings_bucket_id')
            ->get(['savings_bucket_id', 'amount', 'type', 'transaction_date']);

        $signedSumPerBucket = [];
        foreach ($allTransactions as $tx) {
            $delta = $this->balances->bucketDelta((int) $tx->amount, $tx->type);
            $signedSumPerBucket[$tx->savings_bucket_id]
                = ($signedSumPerBucket[$tx->savings_bucket_id] ?? 0) + $delta;
        }

        $events = [];
        foreach ($buckets as $bucket) {
            $initial = (int) $bucket->current_amount - ($signedSumPerBucket[$bucket->id] ?? 0);
            if ($initial !== 0) {
                $events[] = [
                    'date'  => CarbonImmutable::instance($bucket->created_at),
                    'delta' => $initial,
                ];
            }
        }

        foreach ($allTransactions as $tx) {
            $events[] = [
                'date'  => CarbonImmutable::instance($tx->transaction_date),
                'delta' => $this->balances->bucketDelta((int) $tx->amount, $tx->type),
            ];
        }

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = CarbonImmutable::create($year, $m, 1)->startOfMonth();
            $monthEnd = $monthStart->endOfMonth();

            $balance = 0;
            $deposits = 0;
            $withdrawals = 0;

            foreach ($events as $event) {
                if ($event['date']->lte($monthEnd)) {
                    $balance += $event['delta'];
                }

                if ($event['date']->betweenIncluded($monthStart, $monthEnd)) {
                    if ($event['delta'] > 0) {
                        $deposits += $event['delta'];
                    } else {
                        $withdrawals += -$event['delta'];
                    }
                }
            }

            $result[] = [
                'month'       => sprintf('%04d-%02d', $year, $m),
                'balance'     => $balance,
                'deposits'    => $deposits,
                'withdrawals' => $withdrawals,
            ];
        }

        return $result;
    }

    /**
     * Years for which the user has any savings activity (bucket creation or
     * bucket-affecting transaction). Always includes the current year so the
     * selector is never empty.
     *
     * @return list<int>
     */
    public function availableYears(User $user): array
    {
        $earliestBucket = $user->savingsBuckets()->min('created_at');
        $earliestTx = $user
            ->transactions()
            ->whereNotNull('savings_bucket_id')
            ->min('transaction_date');

        $now = CarbonImmutable::now();
        $earliest = $now->year;

        foreach ([$earliestBucket, $earliestTx] as $candidate) {
            if ($candidate === null) {
                continue;
            }

            $year = CarbonImmutable::parse($candidate)->year;
            $earliest = min($earliest, $year);
        }

        return range($now->year, $earliest);
    }
}
