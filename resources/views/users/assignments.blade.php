{{-- resources/views/users/assignments.blade.php --}}
@extends('layouts.app')

@section('title', 'البنوك والشركات - ' . $user->name)

@php
    $selectedBanks     = array_map('intval', old('banks', $user->bank_ids));
    $selectedCompanies = array_map('intval', old('companies', $user->company_ids));
@endphp

@section('content')

    <x-page-header title="البنوك والشركات" :subtitle="$user->name . ' · ' . $user->role" icon="fa-building">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4 flex items-center gap-4">
        <x-avatar :name="$user->name" size="h-12 w-12" />
        <div>
            <p class="text-sm font-bold text-fg">{{ $user->name }}</p>
            <p class="mt-1 text-xs text-muted">الموظف لا يرى إلا البنوك والشركات المحددة هنا.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('users.assignments.update', $user->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            {{-- BANKS --}}
            <x-section-card title="البنوك" icon="fa-building-columns" color="info">
                <div class="mb-3 flex items-center justify-between">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-muted">
                        <input type="checkbox" class="accent-brand" data-check-all=".bank-check"> تحديد الكل
                    </label>
                    <span class="badge badge-outline-info"><span data-count-for=".bank-check">0</span> / {{ $banks->count() }}</span>
                </div>

                <div class="space-y-2">
                    @foreach($banks as $bank)
                        <label class="check-row">
                            <input type="checkbox" name="banks[]" value="{{ $bank->id }}" class="bank-check accent-brand" @checked(in_array($bank->id, $selectedBanks, true))>
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-black"><i class="fa-solid fa-building-columns text-xs"></i></span>
                            <span class="flex-1">
                                <b class="text-fg">{{ $bank->name }}</b>
                                <span class="block text-[11px] text-dim">{{ $bank->code }}</span>
                            </span>
                            @unless($bank->is_active) <span class="badge badge-danger">متوقف</span> @endunless
                        </label>
                    @endforeach
                </div>
                @error('banks.*') <p class="form-error">{{ $message }}</p> @enderror
            </x-section-card>

            {{-- INSTALLMENT COMPANIES --}}
            <x-section-card title="شركات التقسيط" icon="fa-building" color="warning">
                <div class="mb-3 flex items-center justify-between">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-muted">
                        <input type="checkbox" class="accent-brand" data-check-all=".company-check"> تحديد الكل
                    </label>
                    <span class="badge badge-warning"><span data-count-for=".company-check">0</span> / {{ $companies->count() }}</span>
                </div>

                <div class="space-y-2">
                    @foreach($companies as $company)
                        <label class="check-row">
                            <input type="checkbox" name="companies[]" value="{{ $company->id }}" class="company-check accent-brand" @checked(in_array($company->id, $selectedCompanies, true))>
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning/15 text-warning"><i class="fa-solid fa-building text-xs"></i></span>
                            <span class="flex-1">
                                <b class="text-fg">{{ $company->name }}</b>
                                <span class="block text-[11px] text-dim">{{ $company->code }}</span>
                            </span>
                            @unless($company->is_active) <span class="badge badge-danger">متوقفة</span> @endunless
                        </label>
                    @endforeach
                </div>
                @error('companies.*') <p class="form-error">{{ $message }}</p> @enderror
            </x-section-card>

        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        // "3 / 5" counters next to each list
        function updateCounts() {
            document.querySelectorAll('[data-count-for]').forEach(counter => {
                counter.textContent = document.querySelectorAll(`${counter.dataset.countFor}:checked`).length;
            });
        }

        document.addEventListener('change', () => setTimeout(updateCounts, 0));   // wait for "select all" to finish
        updateCounts();
    </script>
@endpush