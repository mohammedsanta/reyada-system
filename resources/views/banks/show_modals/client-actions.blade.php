{{-- resources/views/banks/show_modals/client-actions.blade.php --}}

<x-modal id="clientActionsModal" title="إجراءات العميل" width="38rem">

    <div class="space-y-4">

        <div class="rounded-2xl border border-line bg-surface p-5">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-bolt"></i>
                </span>

                <div>

                    <h3 class="font-bold text-fg">
                        إجراءات سريعة
                    </h3>

                    <p id="actions-client-name" class="mt-1 text-xs text-muted">
                        -
                    </p>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">

            <button
                type="button"
                data-client-action="copy-code"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-info/40 hover:bg-white/[0.03]"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">
                    <i class="fa-solid fa-copy"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        نسخ كود العميل
                    </b>

                    <small class="text-[11px] text-dim">
                        Copy Client Code
                    </small>
                </span>

            </button>


            <button
                type="button"
                data-client-action="copy-national-id"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-info/40 hover:bg-white/[0.03]"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">
                    <i class="fa-solid fa-id-card"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        نسخ National ID
                    </b>

                    <small class="text-[11px] text-dim">
                        Copy National ID
                    </small>
                </span>

            </button>


            <button
                type="button"
                data-client-action="copy-phone"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-brand/40 hover:bg-white/[0.03]"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">
                    <i class="fa-solid fa-phone"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        نسخ الهاتف
                    </b>

                    <small class="text-[11px] text-dim">
                        Primary Phone
                    </small>
                </span>

            </button>


            <button
                type="button"
                data-client-action="copy-phone2"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-brand/40 hover:bg-white/[0.03]"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">
                    <i class="fa-solid fa-phone-volume"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        نسخ الهاتف الثاني
                    </b>

                    <small class="text-[11px] text-dim">
                        Secondary Phone
                    </small>
                </span>

            </button>


            <button
                type="button"
                data-client-action="copy-address"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-warning/40 hover:bg-white/[0.03]"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-warning/15 text-warning">
                    <i class="fa-solid fa-location-dot"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        نسخ العنوان
                    </b>

                    <small class="text-[11px] text-dim">
                        Copy Address
                    </small>
                </span>

            </button>


            <button
                type="button"
                data-client-action="export"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-brand/40 hover:bg-white/[0.03]"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">
                    <i class="fa-solid fa-file-export"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        تصدير بيانات العميل
                    </b>

                    <small class="text-[11px] text-dim">
                        CSV
                    </small>
                </span>

            </button>


            <button
                type="button"
                data-client-action="print"
                class="flex items-center gap-3 rounded-xl border border-line bg-surface px-4 py-3 text-right transition hover:border-info/40 hover:bg-white/[0.03] sm:col-span-2"
            >

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">
                    <i class="fa-solid fa-print"></i>
                </span>

                <span>
                    <b class="block text-sm text-fg">
                        طباعة ملف العميل
                    </b>

                    <small class="text-[11px] text-dim">
                        Print Client File
                    </small>
                </span>

            </button>

        </div>


        <div class="flex justify-end">

            <button
                type="button"
                class="btn btn-secondary"
                data-close-modal
            >
                إغلاق
            </button>

        </div>

    </div>

</x-modal>


@push('scripts')
<script>
(function () {

    let activeClient = null;


    window.openClientActions = function (client) {

        if (!client) {
            return;
        }

        activeClient = client;

        const name =
            document.getElementById('actions-client-name');

        if (name) {
            name.textContent =
                (client.name || '-') +
                ' • ' +
                (client.code || '-');
        }

        const modal =
            document.getElementById('clientActionsModal');

        if (modal) {
            modal.showModal();
        }

    };


    async function copyText(text) {

        if (!text) {

            alert('لا توجد بيانات متاحة للنسخ.');

            return;

        }


        try {

            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {

                await navigator.clipboard.writeText(
                    String(text)
                );

            } else {

                const textarea =
                    document.createElement('textarea');

                textarea.value = String(text);

                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';

                document.body.appendChild(textarea);

                textarea.focus();
                textarea.select();

                document.execCommand('copy');

                textarea.remove();

            }

            alert('تم النسخ بنجاح.');

        } catch (error) {

            alert('تعذر نسخ البيانات.');

        }

    }


    function csvEscape(value) {

        const text =
            value === null ||
            value === undefined
                ? ''
                : String(value);

        return '"' +
            text.replace(/"/g, '""') +
            '"';

    }


    function exportClient(client) {

        const fields = [

            ['كود العميل', client.code],
            ['اسم العميل', client.name],
            ['National ID', client.national_id],
            ['الهاتف', client.phone],
            ['الهاتف الثاني', client.phone2],
            ['البريد الإلكتروني', client.email],
            ['المحافظة', client.governorate],
            ['العنوان', client.address],
            ['نوع القرض', client.loan_type],
            ['الحالة', client.status],
            ['إجمالي المديونية', client.total_debt],
            ['المبلغ المتأخر', client.overdue_amount],
            ['قيمة القسط', client.installment_value],
            ['Bucket', client.bucket],
            ['DPD', client.dpd],
            ['غرامة التأخر', client.late_fee],
            ['جهة العمل', client.employer],
            ['الوظيفة', client.job_title],
            ['هاتف العمل', client.work_phone],
            ['عنوان العمل', client.work_address],
            ['الموظف', client.employee],

        ];


        const rows = fields.map(function (row) {

            return row
                .map(csvEscape)
                .join(',');

        });


        const csv =
            '\uFEFF' +
            'البيان,القيمة\n' +
            rows.join('\n');


        const blob =
            new Blob(
                [csv],
                {
                    type: 'text/csv;charset=utf-8;'
                }
            );


        const url =
            URL.createObjectURL(blob);


        const link =
            document.createElement('a');

        link.href = url;

        link.download =
            'client-' +
            (client.code || client.id || 'data') +
            '.csv';

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);

    }


    function printClient(client) {

        const printWindow =
            window.open(
                '',
                '_blank',
                'width=1000,height=800'
            );


        if (!printWindow) {

            alert('المتصفح منع نافذة الطباعة.');

            return;

        }


        const money = function (value) {

            if (
                value === null ||
                value === undefined ||
                value === ''
            ) {
                return '-';
            }

            return Number(value).toLocaleString(
                'en-US'
            );

        };


        printWindow.document.write(`
            <!doctype html>

            <html dir="rtl" lang="ar">

            <head>

                <meta charset="utf-8">

                <title>
                    ملف العميل - ${client.name || ''}
                </title>

                <style>

                    body {
                        font-family: Arial, sans-serif;
                        margin: 40px;
                        color: #111;
                        background: #fff;
                    }

                    h1 {
                        margin-bottom: 5px;
                    }

                    .muted {
                        color: #666;
                        font-size: 13px;
                    }

                    .grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-top: 25px;
                    }

                    .section {
                        border: 1px solid #ddd;
                        border-radius: 12px;
                        padding: 18px;
                    }

                    .section h2 {
                        margin-top: 0;
                        font-size: 17px;
                    }

                    .row {
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 8px 0;
                        border-bottom: 1px solid #eee;
                    }

                    .row:last-child {
                        border-bottom: 0;
                    }

                    .label {
                        color: #666;
                    }

                    .value {
                        font-weight: bold;
                    }

                    @media print {

                        body {
                            margin: 15px;
                        }

                    }

                </style>

            </head>

            <body>

                <h1>${client.name || '-'}</h1>

                <div class="muted">
                    كود العميل:
                    ${client.code || '-'}
                    |
                    National ID:
                    ${client.national_id || '-'}
                </div>


                <div class="grid">

                    <div class="section">

                        <h2>البيانات الشخصية</h2>

                        <div class="row">
                            <span class="label">الهاتف</span>
                            <span class="value">${client.phone || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">الهاتف الثاني</span>
                            <span class="value">${client.phone2 || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">المحافظة</span>
                            <span class="value">${client.governorate || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">العنوان</span>
                            <span class="value">${client.address || '-'}</span>
                        </div>

                    </div>


                    <div class="section">

                        <h2>المديونية</h2>

                        <div class="row">
                            <span class="label">إجمالي المديونية</span>
                            <span class="value">${money(client.total_debt)} EGP</span>
                        </div>

                        <div class="row">
                            <span class="label">المتأخر</span>
                            <span class="value">${money(client.overdue_amount)} EGP</span>
                        </div>

                        <div class="row">
                            <span class="label">قيمة القسط</span>
                            <span class="value">${money(client.installment_value)} EGP</span>
                        </div>

                        <div class="row">
                            <span class="label">DPD</span>
                            <span class="value">${client.dpd || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">غرامة التأخر</span>
                            <span class="value">${money(client.late_fee)} EGP</span>
                        </div>

                    </div>


                    <div class="section">

                        <h2>بيانات العمل</h2>

                        <div class="row">
                            <span class="label">جهة العمل</span>
                            <span class="value">${client.employer || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">الوظيفة</span>
                            <span class="value">${client.job_title || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">هاتف العمل</span>
                            <span class="value">${client.work_phone || '-'}</span>
                        </div>

                        <div class="row">
                            <span class="label">عنوان العمل</span>
                            <span class="value">${client.work_address || '-'}</span>
                        </div>

                    </div>

                </div>

                <script>

                    window.onload = function () {
                        window.print();
                    };

                <\/script>

            </body>

            </html>
        `);


        printWindow.document.close();

    }


    document.addEventListener('click', function (event) {

        const button =
            event.target.closest(
                '[data-client-action]'
            );

        if (!button || !activeClient) {
            return;
        }


        const action =
            button.dataset.clientAction;


        if (action === 'copy-code') {

            copyText(activeClient.code);

        }


        if (action === 'copy-national-id') {

            copyText(activeClient.national_id);

        }


        if (action === 'copy-phone') {

            copyText(activeClient.phone);

        }


        if (action === 'copy-phone2') {

            copyText(activeClient.phone2);

        }


        if (action === 'copy-address') {

            copyText(activeClient.address);

        }


        if (action === 'export') {

            exportClient(activeClient);

        }


        if (action === 'print') {

            printClient(activeClient);

        }

    });

})();
</script>
@endpush