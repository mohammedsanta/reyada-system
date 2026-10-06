<x-panel
    title="آخر الوعود"
    icon="fa-handshake"
>

    <div class="mb-5 grid grid-cols-3 gap-3">

        <div class="rounded-xl border border-brand/10 bg-brand/5 p-3">

            <p class="text-[10px] text-muted">
                محقق
            </p>

            <p class="mt-1 text-lg font-bold text-brand">
                {{ number_format($report['promises']['kept']) }}
            </p>

        </div>


        <div class="rounded-xl border border-warning/10 bg-warning/5 p-3">

            <p class="text-[10px] text-muted">
                نشط
            </p>

            <p class="mt-1 text-lg font-bold text-warning">
                {{ number_format($report['promises']['active']) }}
            </p>

        </div>


        <div class="rounded-xl border border-danger/10 bg-danger/5 p-3">

            <p class="text-[10px] text-muted">
                مكسور
            </p>

            <p class="mt-1 text-lg font-bold text-danger">
                {{ number_format($report['promises']['broken']) }}
            </p>

        </div>

    </div>


    <div class="table-wrap">

        <table class="data-table whitespace-nowrap">

            <thead>

                <tr>
                    <th>العميل</th>
                    <th>المبلغ</th>
                    <th>الموعد</th>
                    <th>الحالة</th>
                </tr>

            </thead>

            <tbody>

                @forelse($report['recent'] as $recent)

                    @php
                        $client = $recent[0] ?? '-';
                        $amount = $recent[1] ?? 0;
                        $when = $recent[2] ?? '-';
                        $status = $recent[3] ?? 'active';
                    @endphp

                    <tr>

                        <td>

                            <div class="flex items-center gap-2">

                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-info/10 text-info">

                                    <i class="fa-solid fa-user text-[10px]"></i>

                                </span>

                                <span class="font-semibold text-fg">
                                    {{ $client }}
                                </span>

                            </div>

                        </td>


                        <td>

                            <span class="font-semibold text-brand">
                                EGP {{ number_format((float) $amount) }}
                            </span>

                        </td>


                        <td>

                            <span class="text-xs text-muted">

                                <i class="fa-regular fa-calendar ml-1 text-info"></i>

                                {{ $when }}

                            </span>

                        </td>


                        <td>

                            <x-status-badge
                                type="ptp"
                                :status="$status"
                            />

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4">

                            <x-empty-state text="لا توجد وعود" />

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-panel>