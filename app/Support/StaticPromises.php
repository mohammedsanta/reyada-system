<?php

// app/Support/StaticPromises.php
// Sample PTP (promise to pay) data shared by the PTP Hub and the promise details page.
// TODO (DB): delete and query the promises_to_pay table.

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StaticPromises
{
    /** Only bank #1 has sample promises. */
    public static function forBank(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        // [client code, client name, promised amount, paid so far, due in N days, status, employee]
        $rows = [
            ['1000001', 'مصطفى خالد حسن',  1500, 0,    2,   'active',  'HOOL'],
            ['1000002', 'حسن زينب الهواري', 2000, 0,    0,   'active',  'HOOL'],
            ['1000003', 'منى هبة صالح',     1500, 0,    -1,  'active',  'Ahmed Borlsy'],
            ['1000004', 'إيمان هبة راضي',   1500, 0,    5,   'review',  'Ahmed Borlsy'],
            ['1000005', 'أحمد يوسف توفيق',  1500, 1500, -3,  'kept',    'Ahmed Borlsy'],
            ['1000000', 'فاطمة زينب السيد', 1000, 400,  -6,  'partial', 'HOOL'],
            ['1000002', 'حسن زينب الهواري', 3000, 0,    -10, 'broken',  'HOOL'],
        ];

        $today = Carbon::today();

        return collect($rows)->map(function (array $row, int $index) use ($today, $bankId) {
            [$code, $name, $amount, $paid, $days, $status, $employee] = $row;

            $date = $today->copy()->addDays($days);
            [$dueLabel, $dueTone] = self::due($status, $days);

            return (object) [
                'id' => $index + 1, 'bank_id' => $bankId,
                'client_code' => $code, 'client_name' => $name,
                'amount' => $amount, 'paid' => $paid,
                'paid_percent' => $amount > 0 ? (int) round($paid / $amount * 100) : 0,
                'promise_date' => $date->toDateString(),
                'promise_date_label' => $date->format('d/m/Y'),
                'status' => $status, 'employee' => $employee,
                'due_label' => $dueLabel, 'due_tone' => $dueTone,
                'notes' => 'العميل أكد السداد عبر الهاتف وطلب مهلة قصيرة.',
                'created_at_label' => $today->copy()->subDays(abs($days) + 3)->format('d/m/Y'),
            ];
        });
    }

    public static function find(int $bankId, int $id): object
    {
        return self::forBank($bankId)->firstWhere('id', $id) ?? abort(404);
    }

    /** Text under each card: "بعد 3 يوم", "متأخر 2 يوم", ... */
    public static function due(string $status, int $days): array
    {
        return match (true) {
            $status === 'kept'    => ['تم السداد', 'brand'],
            $status === 'partial' => ['سُدد جزئياً', 'info'],
            $days < 0             => ['متأخر ' . abs($days) . ' يوم', 'danger'],
            $days === 0           => ['اليوم', 'warning'],
            default               => ['بعد ' . $days . ' يوم', 'muted'],
        };
    }
}