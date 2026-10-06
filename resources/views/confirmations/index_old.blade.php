{{-- resources/views/confirmations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'تأكيد التحصيلات')

@section('content')

    <x-page-header title="تأكيد التحصيلات" subtitle="مراجعة إيصالات السداد واعتمادها" icon="fa-circle-check" />

    <x-flash />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-metric-card label="بانتظار التأكيد" :value="$counts['pending']" icon="fa-hourglass-half" color="warning" />
        <x-metric-card label="مبلغ بانتظار التأكيد" :value="number_format($pendingAmount)" unit="EGP" icon="fa-money-bill-wave" color="orange" />
        <x-metric-card label="مبلغ تم تأكيده" :value="number_format($confirmedAmount)" unit="EGP" icon="fa-circle-check" color="brand" />
    </section>

    <div class="segmented mb-6">
        @foreach(['pending' => 'بانتظار التأكيد', 'confirmed' => 'مؤكدة', 'rejected' => 'مرفوضة'] as $key => $label)
            <a href="{{ route('confirmations.index', ['status' => $key]) }}" class="segmented-item {{ $status === $key ? 'segmented-item-active' : '' }}">
                {{ $label }} <span class="text-[10px] opacity-70">({{ $counts[$key] }})</span>
            </a>
        @endforeach
    </div>

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>رقم الإيصال</th><th>العميل</th><th>البنك</th><th>المحصل</th><th>المبلغ</th><th>طريقة الدفع</th><th>وقت السداد</th><th>الحالة</th><th>الإجراءات</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><span class="badge badge-info font-mono">{{ $payment->receipt }}</span></td>
                        <td class="font-semibold text-fg">{{ $payment->client }} <span class="text-[11px] text-dim">({{ $payment->case_code }})</span></td>
                        <td>{{ $payment->bank }}</td>
                        <td>{{ $payment->collector }}</td>
                        <td class="font-bold text-brand">EGP {{ number_format($payment->amount) }}</td>
                        <td>{{ $methods[$payment->method] }}</td>
                        <td>{{ $payment->paid_at }}</td>
                        <td><x-status-badge type="payment" :status="$payment->status" /></td>
                        <td>
                            @if($payment->status === 'pending')
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('confirmations.approve', $payment->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-check text-xs"></i> تأكيد</button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm" data-reject="{{ $payment->id }}" data-receipt="{{ $payment->receipt }}">
                                        <i class="fa-solid fa-xmark text-xs"></i> رفض
                                    </button>
                                </div>
                            @else
                                <span class="text-dim">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state text="لا توجد تحصيلات في هذه الحالة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- One reject dialog reused for every row --}}
    <x-modal id="rejectModal" title="رفض التحصيل" width="30rem">
        <form id="rejectForm" method="POST" action="#" class="space-y-4">
            @csrf
            <input type="hidden" name="_reject_id" id="reject-id" value="">
            <p class="text-sm text-muted">سيتم رفض الإيصال <b id="reject-receipt" class="text-fg"></b>. اكتب السبب:</p>
            <x-textarea-field name="reason" label="سبب الرفض" rows="3" required />
            <div class="flex gap-2">
                <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                <button type="button" class="btn btn-secondary" data-close-modal>إلغاء</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const rejectUrl = @json(route('confirmations.reject', '__ID__'));

        function openReject(id) {
            const button = document.querySelector(`[data-reject="${id}"]`);
            if (!button) return;

            document.getElementById('rejectForm').action = rejectUrl.replace('__ID__', id);
            document.getElementById('reject-id').value = id;
            document.getElementById('reject-receipt').textContent = button.dataset.receipt;
            document.getElementById('rejectModal').showModal();
        }

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-reject]');
            if (button) openReject(button.dataset.reject);
        });

        // validation failed (reason missing): open the dialog again so the error is visible
        @if($errors->has('reason') && old('_reject_id'))
            openReject(@json(old('_reject_id')));
        @endif
    </script>
@endpush