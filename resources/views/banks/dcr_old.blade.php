{{-- resources/views/banks/dcr.blade.php --}}
@extends('layouts.app')

@section('title', 'التقرير اليومي DCR - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="التقرير اليومي DCR" subtitle="متابعة عمل الموظفين يوم بيوم" />

    <x-flash />

    {{-- DATE NAVIGATION --}}
    <div class="card mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('banks.dcr.index', ['bank' => $bank->id, 'date' => $prev]) }}" class="btn btn-secondary btn-sm" title="اليوم السابق"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>

            <form method="GET" action="{{ route('banks.dcr.index', $bank->id) }}">
                <label for="date" class="sr-only">التاريخ</label>
                <input id="date" name="date" type="date" value="{{ $date->toDateString() }}" class="form-input py-1.5" onchange="this.form.submit()">
            </form>

            <a href="{{ route('banks.dcr.index', ['bank' => $bank->id, 'date' => $next]) }}" class="btn btn-secondary btn-sm" title="اليوم التالي"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>

            @unless($isToday)
                <a href="{{ route('banks.dcr.index', $bank->id) }}" class="btn btn-outline-info btn-sm">اليوم</a>
            @endunless
        </div>

        <p class="text-sm font-bold text-fg">{{ $dayName }} · {{ $date->format('d/m/Y') }}</p>
    </div>

    {{-- TOTALS --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="المكالمات" :value="$totals['calls']" icon="fa-phone" color="info" />
        <x-metric-card label="الزيارات" :value="$totals['visits']" icon="fa-person-walking" color="cyan" />
        <x-metric-card label="الوعود المسجلة" :value="$totals['promises']" icon="fa-handshake" color="warning" />
        <x-metric-card label="المحصل اليوم" :value="number_format($totals['collected'])" unit="EGP" icon="fa-money-bill-wave" color="brand" />
    </section>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr>
                    <th>الموظف</th><th>الحالات</th><th>مكالمات</th><th>زيارات</th><th>وعود</th>
                    <th>قيمة الوعود</th><th>المحصل</th><th>الحالة</th><th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$row->user->name" />
                                <div>
                                    <div class="font-semibold text-fg">{{ $row->user->name }}</div>
                                    <div class="text-[11px] text-dim">{{ $row->user->role }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $row->cases }}</td>
                        <td>{{ $row->calls }}</td>
                        <td>{{ $row->visits }}</td>
                        <td>{{ $row->promises }}</td>
                        <td>EGP {{ number_format($row->promised) }}</td>
                        <td class="font-semibold text-brand">EGP {{ number_format($row->collected) }}</td>
                        <td><x-status-badge type="dcr" :status="$row->status" /></td>
                        <td>
                            <form method="POST" action="{{ route('banks.dcr.update', [$bank->id, $row->user->id]) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                                @if($row->status === 'draft')
                                    <button type="submit" name="status" value="submitted" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-paper-plane text-xs"></i> إرسال للاعتماد</button>
                                @elseif($row->status === 'submitted')
                                    <button type="submit" name="status" value="approved" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-check text-xs"></i> اعتماد</button>
                                    <button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark text-xs"></i> رفض</button>
                                @elseif($row->status === 'rejected')
                                    <button type="submit" name="status" value="submitted" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-right text-xs"></i> إعادة الإرسال</button>
                                @else
                                    <span class="text-xs text-dim"><i class="fa-solid fa-lock ml-1"></i> معتمد</span>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="fa-calendar-xmark" text="لا توجد تقارير لهذا التاريخ" /></td></tr>
                @endforelse
            </tbody>

            @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-white/[0.03] font-bold text-fg">
                        <td class="px-4 py-3">الإجمالي</td>
                        <td class="px-4 py-3">{{ $totals['cases'] }}</td>
                        <td class="px-4 py-3">{{ $totals['calls'] }}</td>
                        <td class="px-4 py-3">{{ $totals['visits'] }}</td>
                        <td class="px-4 py-3">{{ $totals['promises'] }}</td>
                        <td class="px-4 py-3">EGP {{ number_format($totals['promised']) }}</td>
                        <td class="px-4 py-3 text-brand">EGP {{ number_format($totals['collected']) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

@endsection