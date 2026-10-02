<?php

// app/Http/Controllers/ConfirmationController.php  (static data)
// "تأكيد التحصيلات": a supervisor approves or rejects the receipts collectors recorded.

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ConfirmationController extends Controller
{
    // TODO (DB): Payment::with(['debtCase.client', 'collector'])
    private function payments(): Collection
    {
        // id, receipt, client, case code, bank, collector, amount, method, paid_at, status
        $rows = [
            [1, '#4092', 'حسن زينب الهواري', '1000002', 'Emirates NBD', 'HOOL',         1500, 'cash',        'اليوم 10:20',  'pending'],
            [2, '#4091', 'منى هبة صالح',     '1000003', 'Emirates NBD', 'Ahmed Borlsy', 450,  'e_wallet',    'اليوم 09:45',  'pending'],
            [3, '#4090', 'إيمان هبة راضي',   '1000004', 'Emirates NBD', 'Ahmed Borlsy', 2200, 'bank_transfer', 'أمس 16:10',  'pending'],
            [4, '#4089', 'أحمد يوسف توفيق',  '1000005', 'Emirates NBD', 'Ahmed Borlsy', 1500, 'cash',        'أمس 12:30',    'confirmed'],
            [5, '#4088', 'فاطمة زينب السيد', '1000000', 'Emirates NBD', 'HOOL',         400,  'cash',        'قبل يومين',    'confirmed'],
            [6, '#4087', 'مصطفى خالد حسن',   '1000001', 'Emirates NBD', 'HOOL',         900,  'e_wallet',    'قبل يومين',    'rejected'],
        ];

        return collect($rows)->map(fn (array $r) => (object) array_combine(
            ['id', 'receipt', 'client', 'case_code', 'bank', 'collector', 'amount', 'method', 'paid_at', 'status'], $r
        ));
    }

    // GET /confirmations?status=pending|confirmed|rejected
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'confirmed', 'rejected'], true) ? $request->query('status') : 'pending';
        $all    = $this->payments();

        return view('confirmations.index', [
            'status'   => $status,
            'payments' => $all->where('status', $status)->values(),
            'counts'   => ['pending' => $all->where('status', 'pending')->count(), 'confirmed' => $all->where('status', 'confirmed')->count(), 'rejected' => $all->where('status', 'rejected')->count()],
            'pendingAmount' => $all->where('status', 'pending')->sum('amount'),
            'confirmedAmount' => $all->where('status', 'confirmed')->sum('amount'),
            'methods'  => ['cash' => 'نقدي', 'e_wallet' => 'محفظة إلكترونية', 'bank_transfer' => 'تحويل بنكي'],
        ]);
    }

    // POST /confirmations/{payment}/approve
    public function approve(int $payment): RedirectResponse
    {
        $this->payments()->firstWhere('id', $payment) ?? abort(404);
        // TODO (DB): status = confirmed, confirmed_by, confirmed_at, then debt_cases.collected_amount += amount
        return back()->with('success', 'تم تأكيد التحصيل (بيانات تجريبية، لم يتم الحفظ).');
    }

    // POST /confirmations/{payment}/reject
    public function reject(Request $request, int $payment): RedirectResponse
    {
        $this->payments()->firstWhere('id', $payment) ?? abort(404);
        $request->validate(['reason' => ['required', 'string', 'max:255']]);
        // TODO (DB): status = rejected, rejection_reason
        return back()->with('success', 'تم رفض التحصيل (بيانات تجريبية، لم يتم الحفظ).');
    }
}