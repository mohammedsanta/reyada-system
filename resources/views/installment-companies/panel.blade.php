{{-- resources/views/installment-companies/panel.blade.php : control panel of one company (tiles are "soon" until their pages exist) --}}
@extends('layouts.app')

@section('title', $company->name . ' - لوحة التحكم')

@section('content')

    <header class="card mb-8 flex flex-wrap items-center justify-center gap-4 py-8 sm:relative">
        <a href="{{ route('installment-companies.index') }}" class="btn btn-secondary btn-sm sm:absolute sm:right-6 sm:top-6">
            <i class="fa-solid fa-arrow-right text-xs"></i> رجوع
        </a>
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-info/15 text-info"><i class="fa-solid fa-building text-xl"></i></span>
            <div>
                <h1 class="text-2xl font-bold text-fg">{{ $company->name }}</h1>
                <p class="mt-1 text-xs text-muted">لوحة تحكم شركة التقسيط</p>
            </div>
        </div>
    </header>

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="عدد الحالات" :value="$company->cases_count" unit="حالة" />
        <x-stat-card label="إجمالي المديونية" :value="number_format($company->total_debt)" unit="EGP" />
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        {{-- TODO: pass :href once the company pages exist --}}
        <x-hub-tile title="عرض النطاق"   description="استعراض عملاء الشركة وبياناتهم"  icon="fa-eye"            color="brand" />
        <x-hub-tile title="استيراد النطاق" description="رفع ملفات Excel وتحديث البيانات" icon="fa-file-arrow-up"  color="cyan" />
        <x-hub-tile title="توزيع الحالات" description="توزيع الحالات على الموظفين"        icon="fa-sitemap"        color="accent" />
    </section>

@endsection