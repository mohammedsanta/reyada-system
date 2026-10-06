<?php

// app/Http/Controllers/NotificationController.php
// Static notification controller.
// TODO (DB): Replace the static items() method with Laravel Notifications / database queries later.

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Route-safe link helper.
     *
     * If the requested route exists:
     *     return its real URL.
     *
     * If the route does not exist yet:
     *     return dashboard URL.
     *
     * This keeps the notification page working while the
     * other modules are still being developed.
     */
    private function link(string $name, ...$params): string
    {
        return Route::has($name)
            ? route($name, ...$params)
            : route('dashboard');
    }

    /**
     * Static notification data.
     *
     * TODO (DB):
     * Replace this entire method with:
     *
     *     auth()->user()->notifications
     *
     * or a custom Notification model/query.
     *
     * Every notification contains enough information for
     * the Blade page to display:
     *
     * - category
     * - title
     * - body
     * - time
     * - read/unread state
     * - priority
     * - icon
     * - color
     * - related module
     * - actor
     * - reference
     * - URL
     * - extra metadata
     */
    private function items(): Collection
    {
        return collect([

            /*
            |--------------------------------------------------------------------------
            | PAYMENTS / COLLECTIONS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 1,
                'type' => 'payment',
                'category' => 'التحصيلات',
                'title' => 'تحصيل جديد بانتظار التأكيد',
                'body' => 'إيصال سداد نقدي #4092 بمبلغ 1,500 EGP من المحصل HOOL يحتاج إلى المراجعة والتأكيد.',
                'time' => 'منذ 5 دقائق',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-money-bill-wave',
                'color' => 'brand',
                'actor' => 'HOOL',
                'reference' => '#4092',
                'module' => 'التحصيلات',
                'url' => $this->link('confirmations.index'),
            ],

            [
                'id' => 2,
                'type' => 'payment',
                'category' => 'التحصيلات',
                'title' => 'تحصيل إلكتروني جديد',
                'body' => 'تم تسجيل تحصيل بقيمة 450 EGP عن طريق محفظة إلكترونية للعميلة منى هبة صالح.',
                'time' => 'منذ 18 دقيقة',
                'read' => false,
                'priority' => 'normal',
                'icon' => 'fa-mobile-screen-button',
                'color' => 'info',
                'actor' => 'Ahmed Borlsy',
                'reference' => '#4091',
                'module' => 'التحصيلات',
                'url' => $this->link('confirmations.index'),
            ],

            [
                'id' => 3,
                'type' => 'payment',
                'category' => 'التحصيلات',
                'title' => 'تحصيل مرتفع القيمة',
                'body' => 'تم تسجيل تحصيل بقيمة 25,000 EGP ويحتاج إلى مراجعة إضافية قبل الاعتماد.',
                'time' => 'منذ 32 دقيقة',
                'read' => false,
                'priority' => 'urgent',
                'icon' => 'fa-coins',
                'color' => 'warning',
                'actor' => 'Beshoy nople',
                'reference' => '#4087',
                'module' => 'التحصيلات',
                'url' => $this->link('confirmations.index'),
            ],

            [
                'id' => 4,
                'type' => 'payment',
                'category' => 'التحصيلات',
                'title' => 'تم تأكيد تحصيل',
                'body' => 'تم اعتماد إيصال السداد #4089 بقيمة 1,500 EGP بنجاح.',
                'time' => 'منذ ساعة',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-circle-check',
                'color' => 'brand',
                'actor' => 'Admin',
                'reference' => '#4089',
                'module' => 'التحصيلات',
                'url' => $this->link('confirmations.index'),
            ],

            [
                'id' => 5,
                'type' => 'payment',
                'category' => 'التحصيلات',
                'title' => 'تحصيل مرفوض',
                'body' => 'تم رفض التحصيل #4084 بسبب عدم تطابق بيانات الإيصال مع البيانات المسجلة.',
                'time' => 'منذ ساعتين',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-circle-xmark',
                'color' => 'danger',
                'actor' => 'System',
                'reference' => '#4084',
                'module' => 'التحصيلات',
                'url' => $this->link('confirmations.index'),
            ],


            /*
            |--------------------------------------------------------------------------
            | PROMISE TO PAY
            |--------------------------------------------------------------------------
            */

            [
                'id' => 6,
                'type' => 'ptp',
                'category' => 'وعود السداد',
                'title' => 'وعد دفع مستحق اليوم',
                'body' => 'حسن زينب الهواري وعد بسداد 2,000 EGP اليوم.',
                'time' => 'منذ ساعة',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-handshake',
                'color' => 'orange',
                'actor' => 'Ahmed Borlsy',
                'reference' => 'PTP-00125',
                'module' => 'وعود السداد',
                'url' => $this->link('banks.ptp.index', 1),
            ],

            [
                'id' => 7,
                'type' => 'ptp',
                'category' => 'وعود السداد',
                'title' => 'وعد دفع متأخر',
                'body' => 'وعد السداد الخاص بالعميلة منى هبة صالح متأخر منذ يوم واحد.',
                'time' => 'منذ ساعتين',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-calendar-xmark',
                'color' => 'warning',
                'actor' => 'System',
                'reference' => 'PTP-00118',
                'module' => 'وعود السداد',
                'url' => $this->link('banks.ptp.index', 1),
            ],

            [
                'id' => 8,
                'type' => 'ptp',
                'category' => 'وعود السداد',
                'title' => 'وعد دفع مكسور',
                'body' => 'تم اكتشاف وعد دفع مكسور للعميل حسن زينب الهواري ولم يتم تسجيل تحصيل.',
                'time' => 'اليوم 08:40',
                'read' => false,
                'priority' => 'urgent',
                'icon' => 'fa-triangle-exclamation',
                'color' => 'danger',
                'actor' => 'System',
                'reference' => 'PTP-00112',
                'module' => 'وعود السداد',
                'url' => $this->link('banks.ptp.index', 1),
            ],

            [
                'id' => 9,
                'type' => 'ptp',
                'category' => 'وعود السداد',
                'title' => 'تم تحقيق وعد السداد',
                'body' => 'تم تسجيل تحصيل كامل لقيمة وعد السداد الخاص بأحمد يوسف توفيق.',
                'time' => 'أمس 16:20',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-check-double',
                'color' => 'brand',
                'actor' => 'HOOL',
                'reference' => 'PTP-00102',
                'module' => 'وعود السداد',
                'url' => $this->link('banks.ptp.index', 1),
            ],


            /*
            |--------------------------------------------------------------------------
            | COMPLAINTS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 10,
                'type' => 'complaint',
                'category' => 'الشكاوى',
                'title' => 'شكوى جديدة',
                'body' => 'تم تسجيل شكوى جديدة من العميلة منى هبة صالح وتحتاج إلى المتابعة.',
                'time' => 'منذ 3 ساعات',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-headset',
                'color' => 'pink',
                'actor' => 'System',
                'reference' => 'CMP-000102',
                'module' => 'الشكاوى',
                'url' => $this->link('banks.complaints.index', 1),
            ],

            [
                'id' => 11,
                'type' => 'complaint',
                'category' => 'الشكاوى',
                'title' => 'شكوى عاجلة',
                'body' => 'تم تصنيف الشكوى CMP-000102 كشكوى عاجلة بسبب اتصال خارج المواعيد المسموحة.',
                'time' => 'منذ 4 ساعات',
                'read' => false,
                'priority' => 'urgent',
                'icon' => 'fa-bell',
                'color' => 'danger',
                'actor' => 'Supervisor',
                'reference' => 'CMP-000102',
                'module' => 'الشكاوى',
                'url' => $this->link('banks.complaints.index', 1),
            ],

            [
                'id' => 12,
                'type' => 'complaint',
                'category' => 'الشكاوى',
                'title' => 'موعد إغلاق الشكوى يقترب',
                'body' => 'تبقى أقل من 24 ساعة على الموعد النهائي لمعالجة الشكوى CMP-000098.',
                'time' => 'اليوم 09:15',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-clock',
                'color' => 'warning',
                'actor' => 'System',
                'reference' => 'CMP-000098',
                'module' => 'الشكاوى',
                'url' => $this->link('banks.complaints.index', 1),
            ],

            [
                'id' => 13,
                'type' => 'complaint',
                'category' => 'الشكاوى',
                'title' => 'تم إغلاق شكوى',
                'body' => 'تم إغلاق الشكوى CMP-000095 بعد معالجة الطلب وتوثيق النتيجة.',
                'time' => 'أمس 17:10',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-circle-check',
                'color' => 'brand',
                'actor' => 'Supervisor',
                'reference' => 'CMP-000095',
                'module' => 'الشكاوى',
                'url' => $this->link('banks.complaints.index', 1),
            ],


            /*
            |--------------------------------------------------------------------------
            | DISTRIBUTION / ASSIGNMENTS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 14,
                'type' => 'distribution',
                'category' => 'التوزيع',
                'title' => 'تم توزيع حالات جديدة',
                'body' => 'تم توزيع 24 حالة على فريق Emirates NBD.',
                'time' => 'أمس 14:20',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-sitemap',
                'color' => 'accent',
                'actor' => 'Beshoy nople',
                'reference' => 'DIST-0024',
                'module' => 'التوزيع',
                'url' => $this->link('banks.distribution.index', 1),
            ],

            [
                'id' => 15,
                'type' => 'distribution',
                'category' => 'التوزيع',
                'title' => 'حالات بدون موظف',
                'body' => 'تم اكتشاف حالتين غير موزعتين وتحتاجان إلى تعيين موظف مسؤول.',
                'time' => 'اليوم 10:30',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-user-xmark',
                'color' => 'warning',
                'actor' => 'System',
                'reference' => 'DIST-0025',
                'module' => 'التوزيع',
                'url' => $this->link('banks.distribution.index', 1),
            ],

            [
                'id' => 16,
                'type' => 'distribution',
                'category' => 'التوزيع',
                'title' => 'إعادة توزيع حالات',
                'body' => 'تم نقل 8 حالات من الموظف أحمد إلى الموظف HOOL بسبب إعادة توزيع الفريق.',
                'time' => 'أمس 12:40',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-right-left',
                'color' => 'info',
                'actor' => 'Supervisor',
                'reference' => 'DIST-0021',
                'module' => 'التوزيع',
                'url' => $this->link('banks.distribution.index', 1),
            ],


            /*
            |--------------------------------------------------------------------------
            | VISITS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 17,
                'type' => 'visit',
                'category' => 'الزيارات',
                'title' => 'زيارة ميدانية فائتة',
                'body' => 'لم تتم زيارة العميل أحمد يوسف توفيق في الموعد المحدد.',
                'time' => 'أمس 11:05',
                'read' => true,
                'priority' => 'high',
                'icon' => 'fa-person-walking',
                'color' => 'danger',
                'actor' => 'System',
                'reference' => 'VIS-00087',
                'module' => 'الزيارات',
                'url' => $this->link('banks.visits.index', 1),
            ],

            [
                'id' => 18,
                'type' => 'visit',
                'category' => 'الزيارات',
                'title' => 'زيارة مجدولة اليوم',
                'body' => 'لديك زيارة ميدانية مجدولة اليوم للعميل فاطمة زينب السيد.',
                'time' => 'اليوم 08:00',
                'read' => false,
                'priority' => 'normal',
                'icon' => 'fa-location-dot',
                'color' => 'cyan',
                'actor' => 'System',
                'reference' => 'VIS-00092',
                'module' => 'الزيارات',
                'url' => $this->link('banks.visits.index', 1),
            ],

            [
                'id' => 19,
                'type' => 'visit',
                'category' => 'الزيارات',
                'title' => 'تم تسجيل نتيجة زيارة',
                'body' => 'تم تسجيل نتيجة الزيارة الميدانية للعميل إيمان هبة راضي.',
                'time' => 'أمس 15:45',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-clipboard-check',
                'color' => 'brand',
                'actor' => 'HOOL',
                'reference' => 'VIS-00081',
                'module' => 'الزيارات',
                'url' => $this->link('banks.visits.index', 1),
            ],


            /*
            |--------------------------------------------------------------------------
            | IMPORT / SCOPE
            |--------------------------------------------------------------------------
            */

            [
                'id' => 20,
                'type' => 'import',
                'category' => 'الاستيراد',
                'title' => 'اكتمل استيراد النطاق',
                'body' => 'تم استيراد 6 حالات بنجاح من ملف scope_september.xlsx.',
                'time' => 'قبل يومين',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-file-circle-check',
                'color' => 'cyan',
                'actor' => 'Test Account',
                'reference' => 'IMP-00045',
                'module' => 'الاستيراد',
                'url' => $this->link('banks.scope.import', 1),
            ],

            [
                'id' => 21,
                'type' => 'import',
                'category' => 'الاستيراد',
                'title' => 'استيراد يحتاج مراجعة',
                'body' => 'تم استيراد الملف scope_october.xlsx ولكن توجد 3 سجلات تحتوي على بيانات ناقصة.',
                'time' => 'اليوم 07:50',
                'read' => false,
                'priority' => 'warning',
                'icon' => 'fa-file-circle-exclamation',
                'color' => 'warning',
                'actor' => 'Test Account',
                'reference' => 'IMP-00051',
                'module' => 'الاستيراد',
                'url' => $this->link('banks.scope.import', 1),
            ],

            [
                'id' => 22,
                'type' => 'import',
                'category' => 'الاستيراد',
                'title' => 'فشل استيراد ملف',
                'body' => 'تعذر استيراد الملف scope_invalid.xlsx بسبب وجود أعمدة غير مطابقة للقالب.',
                'time' => 'أمس 18:30',
                'read' => false,
                'priority' => 'urgent',
                'icon' => 'fa-file-circle-xmark',
                'color' => 'danger',
                'actor' => 'System',
                'reference' => 'IMP-00049',
                'module' => 'الاستيراد',
                'url' => $this->link('banks.scope.import', 1),
            ],


            /*
            |--------------------------------------------------------------------------
            | DCR / REPORTS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 23,
                'type' => 'report',
                'category' => 'التقارير',
                'title' => 'تقرير DCR بانتظار الاعتماد',
                'body' => 'يوجد تقرير DCR جديد يحتاج إلى مراجعة واعتماد المشرف.',
                'time' => 'منذ ساعتين',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-file-invoice',
                'color' => 'danger',
                'actor' => 'HOOL',
                'reference' => 'DCR-00031',
                'module' => 'التقارير',
                'url' => $this->link('banks.dcr.index', 1),
            ],

            [
                'id' => 24,
                'type' => 'report',
                'category' => 'التقارير',
                'title' => 'تم اعتماد تقرير DCR',
                'body' => 'تم اعتماد تقرير DCR الخاص بـ HOOL بنجاح.',
                'time' => 'اليوم 12:10',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-file-circle-check',
                'color' => 'brand',
                'actor' => 'Beshoy nople',
                'reference' => 'DCR-00029',
                'module' => 'التقارير',
                'url' => $this->link('banks.dcr.index', 1),
            ],


            /*
            |--------------------------------------------------------------------------
            | CASES / CUSTOMERS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 25,
                'type' => 'case',
                'category' => 'الحالات',
                'title' => 'تم إنشاء حالة جديدة',
                'body' => 'تم إنشاء حالة تحصيل جديدة للعميل أحمد يوسف توفيق.',
                'time' => 'منذ 4 ساعات',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-folder-plus',
                'color' => 'info',
                'actor' => 'System',
                'reference' => 'CASE-00452',
                'module' => 'الحالات',
                'url' => $this->link('banks.index'),
            ],

            [
                'id' => 26,
                'type' => 'case',
                'category' => 'الحالات',
                'title' => 'تحديث بيانات عميل',
                'body' => 'تم تعديل بيانات العميل حسن زينب الهواري.',
                'time' => 'أمس 10:22',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-user-pen',
                'color' => 'info',
                'actor' => 'HOOL',
                'reference' => 'CASE-00441',
                'module' => 'الحالات',
                'url' => $this->link('banks.index'),
            ],

            [
                'id' => 27,
                'type' => 'case',
                'category' => 'الحالات',
                'title' => 'حالة عالية الخطورة',
                'body' => 'تم تصنيف حالة العميل فاطمة زينب السيد كحالة عالية الخطورة بسبب ارتفاع مدة التأخر.',
                'time' => 'اليوم 09:42',
                'read' => false,
                'priority' => 'urgent',
                'icon' => 'fa-fire',
                'color' => 'danger',
                'actor' => 'System',
                'reference' => 'CASE-00463',
                'module' => 'الحالات',
                'url' => $this->link('banks.index'),
            ],


            /*
            |--------------------------------------------------------------------------
            | EMPLOYEES / USERS
            |--------------------------------------------------------------------------
            */

            [
                'id' => 28,
                'type' => 'employee',
                'category' => 'الموظفون',
                'title' => 'تم إنشاء موظف جديد',
                'body' => 'تم إنشاء حساب موظف جديد وإضافته إلى فريق التحصيل.',
                'time' => 'أمس 16:30',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-user-plus',
                'color' => 'brand',
                'actor' => 'Admin',
                'reference' => 'EMP-00018',
                'module' => 'الموظفون',
                'url' => $this->link('users.create'),
            ],

            [
                'id' => 29,
                'type' => 'employee',
                'category' => 'الموظفون',
                'title' => 'تم تعديل صلاحيات موظف',
                'body' => 'تم تحديث صلاحيات أحد موظفي التحصيل بواسطة مدير النظام.',
                'time' => 'أمس 13:15',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-user-shield',
                'color' => 'warning',
                'actor' => 'Admin',
                'reference' => 'EMP-00012',
                'module' => 'الموظفون',
                'url' => $this->link('employees.index'),
            ],


            /*
            |--------------------------------------------------------------------------
            | SYSTEM / SECURITY
            |--------------------------------------------------------------------------
            */

            [
                'id' => 30,
                'type' => 'system',
                'category' => 'النظام',
                'title' => 'تسجيل دخول جديد',
                'body' => 'تم تسجيل دخول المستخدم Beshoy nople إلى النظام.',
                'time' => 'منذ 10 دقائق',
                'read' => false,
                'priority' => 'normal',
                'icon' => 'fa-right-to-bracket',
                'color' => 'info',
                'actor' => 'Beshoy nople',
                'reference' => 'LOGIN-00128',
                'module' => 'الأمان',
                'url' => $this->link('activity-logs.index'),
            ],

            [
                'id' => 31,
                'type' => 'system',
                'category' => 'النظام',
                'title' => 'محاولة دخول غير ناجحة',
                'body' => 'تم تسجيل محاولة دخول غير ناجحة للحساب Test Account.',
                'time' => 'منذ 25 دقيقة',
                'read' => false,
                'priority' => 'high',
                'icon' => 'fa-shield-halved',
                'color' => 'danger',
                'actor' => 'System',
                'reference' => 'SEC-00421',
                'module' => 'الأمان',
                'url' => $this->link('activity-logs.index'),
            ],

            [
                'id' => 32,
                'type' => 'system',
                'category' => 'النظام',
                'title' => 'تم تغيير إعدادات النظام',
                'body' => 'تم تعديل أحد إعدادات النظام بواسطة مدير النظام.',
                'time' => 'أمس 20:05',
                'read' => true,
                'priority' => 'high',
                'icon' => 'fa-gears',
                'color' => 'warning',
                'actor' => 'Admin',
                'reference' => 'SET-00018',
                'module' => 'النظام',
                'url' => $this->link('activity-logs.index'),
            ],

            [
                'id' => 33,
                'type' => 'system',
                'category' => 'النظام',
                'title' => 'اكتملت عملية النسخ الاحتياطي',
                'body' => 'تم إنشاء النسخة الاحتياطية للنظام بنجاح.',
                'time' => 'أمس 03:00',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-database',
                'color' => 'cyan',
                'actor' => 'System',
                'reference' => 'BACKUP-00071',
                'module' => 'النظام',
                'url' => $this->link('activity-logs.index'),
            ],


            /*
            |--------------------------------------------------------------------------
            | ACTIVITY
            |--------------------------------------------------------------------------
            */

            [
                'id' => 34,
                'type' => 'activity',
                'category' => 'النشاط',
                'title' => 'تم اعتماد إجراء',
                'body' => 'اعتمد Beshoy nople تقرير DCR الخاص بـ HOOL.',
                'time' => 'اليوم 12:10',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-file-circle-check',
                'color' => 'brand',
                'actor' => 'Beshoy nople',
                'reference' => 'ACT-00881',
                'module' => 'النشاط',
                'url' => $this->link('activity-logs.index'),
            ],

            [
                'id' => 35,
                'type' => 'activity',
                'category' => 'النشاط',
                'title' => 'توزيع حالات على الموظفين',
                'body' => 'قام Beshoy nople بتوزيع 24 حالة على 3 موظفين.',
                'time' => 'اليوم 10:05',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-sitemap',
                'color' => 'accent',
                'actor' => 'Beshoy nople',
                'reference' => 'ACT-00872',
                'module' => 'النشاط',
                'url' => $this->link('activity-logs.index'),
            ],

            [
                'id' => 36,
                'type' => 'activity',
                'category' => 'النشاط',
                'title' => 'تعديل بيانات',
                'body' => 'تم تعديل بيانات العميل حسن زينب الهواري.',
                'time' => 'أمس 10:22',
                'read' => true,
                'priority' => 'normal',
                'icon' => 'fa-pen-to-square',
                'color' => 'info',
                'actor' => 'HOOL',
                'reference' => 'ACT-00851',
                'module' => 'النشاط',
                'url' => $this->link('activity-logs.index'),
            ],

        ])->map(function (array $item) {
            return (object) $item;
        });
    }

    /**
     * Notification type definitions.
     *
     * Used by the Blade for category filters and statistics.
     */
    private function types(Collection $items): Collection
    {
        return $items
            ->groupBy('type')
            ->map(function (Collection $group) {
                return [
                    'type' => $group->first()->type,
                    'label' => $group->first()->category,
                    'count' => $group->count(),
                    'unread' => $group->where('read', false)->count(),
                ];
            })
            ->values();
    }

    /**
     * Notification priority definitions.
     */
    private function priorities(): array
    {
        return [
            'urgent' => [
                'label' => 'عاجل',
                'color' => 'danger',
                'icon' => 'fa-triangle-exclamation',
            ],

            'high' => [
                'label' => 'مرتفع',
                'color' => 'warning',
                'icon' => 'fa-circle-exclamation',
            ],

            'warning' => [
                'label' => 'تنبيه',
                'color' => 'warning',
                'icon' => 'fa-bell',
            ],

            'normal' => [
                'label' => 'عادي',
                'color' => 'info',
                'icon' => 'fa-circle-info',
            ],
        ];
    }

    /**
     * Build statistics for the page.
     */
    private function statistics(Collection $all): array
    {
        $unread = $all->where('read', false);

        return [
            [
                'label' => 'إجمالي الإشعارات',
                'value' => $all->count(),
                'icon' => 'fa-bell',
                'color' => 'info',
            ],

            [
                'label' => 'غير مقروءة',
                'value' => $unread->count(),
                'icon' => 'fa-envelope',
                'color' => 'brand',
            ],

            [
                'label' => 'عاجلة',
                'value' => $all->where('priority', 'urgent')->count(),
                'icon' => 'fa-triangle-exclamation',
                'color' => 'danger',
            ],

            [
                'label' => 'تحتاج متابعة',
                'value' => $all->whereIn('priority', ['urgent', 'high', 'warning'])->count(),
                'icon' => 'fa-clock',
                'color' => 'warning',
            ],
        ];
    }

    /**
     * Add useful calculated properties to every notification.
     */
    private function decorate(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $priority = $this->priorities()[$item->priority] ?? $this->priorities()['normal'];

            $item->priorityLabel = $priority['label'];
            $item->priorityColor = $priority['color'];
            $item->priorityIcon = $priority['icon'];

            $item->isUnread = !$item->read;

            return $item;
        });
    }

    /**
     * GET /notifications
     *
     * Supported query parameters:
     *
     * ?filter=all
     * ?filter=unread
     * ?type=payment
     * ?type=ptp
     * ?priority=urgent
     * ?search=customer
     */
    public function index(Request $request): View
    {
        $all = $this->decorate($this->items());

        $filter = $request->query('filter') === 'unread'
            ? 'unread'
            : 'all';

        $type = (string) $request->query('type', '');

        $priority = (string) $request->query('priority', '');

        $search = trim((string) $request->query('search', ''));

        /*
        |--------------------------------------------------------------------------
        | Apply filters
        |--------------------------------------------------------------------------
        */

        $items = $all;

        if ($filter === 'unread') {
            $items = $items->where('read', false);
        }

        if ($type !== '') {
            $items = $items->where('type', $type);
        }

        if ($priority !== '') {
            $items = $items->where('priority', $priority);
        }

        if ($search !== '') {
            $searchLower = mb_strtolower($search);

            $items = $items->filter(function ($item) use ($searchLower) {
                $haystack = mb_strtolower(
                    implode(' ', [
                        $item->title,
                        $item->body,
                        $item->category,
                        $item->actor,
                        $item->reference,
                        $item->module,
                    ])
                );

                return str_contains($haystack, $searchLower);
            });
        }

        $items = $items->values();

        return view('notifications.index', [
            /*
            |--------------------------------------------------------------------------
            | Main data
            |--------------------------------------------------------------------------
            */

            'items' => $items,

            /*
            |--------------------------------------------------------------------------
            | Original filter contract
            |--------------------------------------------------------------------------
            */

            'filter' => $filter,

            /*
            |--------------------------------------------------------------------------
            | Counts
            |--------------------------------------------------------------------------
            */

            'unread' => $all->where('read', false)->count(),

            'total' => $all->count(),

            'urgent' => $all
                ->where('priority', 'urgent')
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Advanced page data
            |--------------------------------------------------------------------------
            */

            'stats' => $this->statistics($all),

            'types' => $this->types($all),

            'priorities' => $this->priorities(),

            'selectedType' => $type,

            'selectedPriority' => $priority,

            'search' => $search,

            /*
            |--------------------------------------------------------------------------
            | Navigation
            |--------------------------------------------------------------------------
            */

            'links' => [
                'dashboard' => $this->link('dashboard'),
                'activity' => $this->link('activity-logs.index'),
                'confirmations' => $this->link('confirmations.index'),
                'complaints' => $this->link('banks.complaints.index', 1),
                'ptp' => $this->link('banks.ptp.index', 1),
            ],
        ]);
    }

    /**
     * POST /notifications/read-all
     *
     * Static mode:
     * We cannot permanently modify the notifications because
     * they currently live inside the controller.
     *
     * TODO (DB):
     *
     *     auth()->user()
     *         ->unreadNotifications
     *         ->markAsRead();
     */
    public function readAll(): RedirectResponse
    {
        return back()->with(
            'success',
            'تم تحديد كل الإشعارات كمقروءة. عند ربط النظام بقاعدة البيانات سيتم حفظ الحالة فعلياً.'
        );
    }

    /**
     * POST /notifications/{notification}/read
     *
     * In static mode this simply opens the notification's
     * related page.
     *
     * TODO (DB):
     * Mark the notification as read before redirecting.
     */
    public function read(int $notification): RedirectResponse
    {
        $item = $this->items()
            ->firstWhere('id', $notification);

        abort_unless($item, 404);

        /*
        |--------------------------------------------------------------------------
        | TODO (DB)
        |--------------------------------------------------------------------------
        |
        | Example later:
        |
        | $notification->markAsRead();
        |
        */

        return redirect($item->url);
    }
}