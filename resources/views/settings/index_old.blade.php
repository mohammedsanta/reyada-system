{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إعدادات النظام')

@php
    // name => [title, description]
    $toggles = [
        'notify_payment'        => ['إشعار عند تسجيل تحصيل جديد', 'يصل للمشرفين لمراجعته وتأكيده'],
        'notify_complaint'      => ['إشعار عند تسجيل شكوى جديدة', 'يصل للمسؤول عن الشكاوى'],
        'notify_broken_promise' => ['إشعار عند كسر وعد دفع', 'يصل للموظف ومشرفه'],
        'daily_digest'          => ['ملخص يومي بالبريد الإلكتروني', 'تقرير مختصر كل صباح للمدير'],
    ];
@endphp

@section('content')

    <x-page-header title="إعدادات النظام" subtitle="بيانات الشركة وقواعد التحصيل والأمان والإشعارات" icon="fa-gear" />

    <x-flash />

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            {{-- Company --}}
            <x-section-card title="بيانات الشركة" icon="fa-building" color="info">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div class="md:col-span-2"><x-form-field name="company_name" label="اسم الشركة" :value="$s['company_name']" /></div>
                    <x-form-field name="company_phone" label="الهاتف" :value="$s['company_phone']" dir="ltr" />
                    <x-form-field name="company_email" label="البريد الإلكتروني" type="email" :value="$s['company_email']" dir="ltr" />
                    <div class="md:col-span-2"><x-form-field name="company_address" label="العنوان" :value="$s['company_address']" /></div>
                </div>
            </x-section-card>

            {{-- Collection rules --}}
            <x-section-card title="قواعد التحصيل" icon="fa-sliders" color="warning">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <x-select-field name="default_per_page" label="عدد الصفوف الافتراضي في الجداول" :options="$perPage" :value="$s['default_per_page']" />
                    <x-form-field name="max_cases_per_employee" label="أقصى عدد حالات لكل موظف" type="number" min="1" max="500" :value="$s['max_cases_per_employee']" />
                    <x-form-field name="ptp_default_days" label="موعد الوعد الافتراضي (بعد كم يوم)" type="number" min="0" max="30" :value="$s['ptp_default_days']" />
                    <x-form-field name="ptp_overdue_alert_days" label="تنبيه الوعد المتأخر بعد (أيام)" type="number" min="0" max="30" :value="$s['ptp_overdue_alert_days']" />
                </div>
                <p class="mt-3 text-[11px] leading-5 text-dim">أقصى عدد حالات يُستخدم في صفحة توزيع الحالات كسعة لكل موظف.</p>
            </x-section-card>

            {{-- Security --}}
            <x-section-card title="الأمان" icon="fa-shield-halved" color="danger">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <x-form-field name="login_max_attempts" label="محاولات الدخول الخاطئة قبل الحظر" type="number" min="3" max="10" :value="$s['login_max_attempts']" />
                    <x-form-field name="session_minutes" label="مدة الجلسة بدون نشاط (دقيقة)" type="number" min="15" max="1440" :value="$s['session_minutes']" />
                </div>
            </x-section-card>

            {{-- Notifications --}}
            <x-section-card title="الإشعارات" icon="fa-bell" color="brand">
                <div class="space-y-2">
                    @foreach($toggles as $name => [$label, $hint])
                        <label class="check-row">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1" class="accent-brand" @checked(old($name, $s[$name]))>
                            <span>
                                <b class="text-fg">{{ $label }}</b>
                                <span class="block text-[11px] text-dim">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </x-section-card>

        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ الإعدادات</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>

@endsection