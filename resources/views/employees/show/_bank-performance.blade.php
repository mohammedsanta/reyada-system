<x-panel
    title="الأداء حسب البنك"
    icon="fa-building-columns"
>

    <div class="mb-5 grid grid-cols-2 gap-3">

        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">

            <p class="text-[11px] text-muted">
                عدد البنوك
            </p>

            <p class="mt-1 text-lg font-bold text-fg">
                {{ number_format($report['banks']['count']) }}
            </p>

        </div>


        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">

            <p class="text-[11px] text-muted">
                إجمالي التحصيل
            </p>

            <p class="mt-1 text-lg font-bold text-brand">

                {{ number_format($report['performance']['total_collected']) }}

                <span class="text-[10px] font-normal text-muted">
                    EGP
                </span>

            </p>

        </div>

    </div>


    @if($report['banks']['highest'])

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand/10 bg-brand/5 px-4 py-3">

            <div class="flex items-center gap-3">

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-trophy"></i>
                </span>

                <div>

                    <p class="text-[10px] text-muted">
                        أعلى بنك من حيث التحصيل
                    </p>

                    <p class="mt-0.5 text-sm font-bold text-fg">
                        {{ $report['banks']['highest']->name }}
                    </p>

                </div>

            </div>

            <div class="text-left">

                <p class="text-sm font-bold text-brand">
                    EGP
                    {{ number_format($report['banks']['highest']->collected ?? 0) }}
                </p>

            </div>

        </div>

    @endif


    <div class="table-wrap">

        <table class="data-table whitespace-nowrap">

            <thead>

                <tr>
                    <th>البنك</th>
                    <th>الحالات</th>
                    <th>التحصيل</th>
                    <th>محقق</th>
                    <th>مكسور</th>
                    <th class="w-40">الكفاءة</th>
                </tr>

            </thead>

            <tbody>

                @forelse($report['banks']['items'] as $bank)

                    @php
                        $bankEfficiency = max(
                            0,
                            min(100, (float) ($bank->efficiency ?? 0))
                        );

                        $bankTone = match (true) {
                            $bankEfficiency >= 90 => 'brand',
                            $bankEfficiency >= 70 => 'warning',
                            default => 'danger',
                        };

                        $toneText = [
                            'brand' => 'text-brand',
                            'warning' => 'text-warning',
                            'danger' => 'text-danger',
                        ];

                        $toneBar = [
                            'brand' => 'bg-brand',
                            'warning' => 'bg-warning',
                            'danger' => 'bg-danger',
                        ];

                        $bankCollected = (float) ($bank->collected ?? 0);

                        $collectionShare = $report['performance']['total_collected'] > 0
                            ? round(
                                ($bankCollected / $report['performance']['total_collected']) * 100
                            )
                            : 0;
                    @endphp

                    <tr>

                        <td>

                            <div class="flex items-center gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa-solid fa-building-columns text-xs"></i>
                                </span>

                                <div class="min-w-0">

                                    <div class="font-semibold text-fg">
                                        {{ $bank->name }}
                                    </div>

                                    <div class="mt-0.5 text-[10px] text-dim">
                                        حصة التحصيل {{ $collectionShare }}%
                                    </div>

                                </div>

                            </div>

                        </td>


                        <td>
                            <span class="font-semibold">
                                {{ number_format($bank->cases ?? 0) }}
                            </span>
                        </td>


                        <td>

                            <div class="font-semibold text-brand">
                                EGP {{ number_format($bankCollected) }}
                            </div>

                            <div class="mt-1 h-1 w-20 overflow-hidden rounded-full bg-white/[0.06]">

                                <div
                                    class="h-full rounded-full bg-brand"
                                    style="width: {{ min(100, $collectionShare) }}%"
                                ></div>

                            </div>

                        </td>


                        <td>
                            <span class="font-semibold text-fg">
                                {{ number_format($bank->kept ?? 0) }}
                            </span>
                        </td>


                        <td>

                            <span class="{{ ($bank->broken ?? 0) > 0
                                ? 'font-semibold text-danger'
                                : 'text-muted' }}">

                                {{ number_format($bank->broken ?? 0) }}

                            </span>

                        </td>


                        <td>

                            <div class="mb-1 flex items-center justify-between gap-2">

                                <span class="text-[11px] font-bold {{ $toneText[$bankTone] }}">
                                    {{ $bankEfficiency }}%
                                </span>

                                <span class="text-[10px] text-dim">

                                    {{ $bankTone === 'brand'
                                        ? 'قوي'
                                        : ($bankTone === 'warning'
                                            ? 'متوسط'
                                            : 'يحتاج متابعة') }}

                                </span>

                            </div>

                            <div class="progress">

                                <div
                                    class="h-full rounded-full {{ $toneBar[$bankTone] }}"
                                    style="width: {{ $bankEfficiency }}%"
                                ></div>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6">

                            <x-empty-state text="لا توجد بنوك مسندة لهذا الموظف" />

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-panel>