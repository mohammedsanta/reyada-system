{{-- resources/views/settings/lookup.blade.php
     ONE view for the simple "list + add / edit / delete" settings pages (loan types, governorates).
     The controller sends: title, subtitle, icon, items, search, hasEnglish, hasActive, usedLabel,
     storeUrl, updateUrl, destroyUrl (with __ID__) and singular. --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

    <x-page-header :title="$title" :subtitle="$subtitle" :icon="$icon">
        <x-slot:actions>
            <button type="button" id="addBtn" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus text-xs"></i> إضافة {{ $singular }}</button>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    @error('delete') <div class="alert alert-danger mb-6">{{ $message }}</div> @enderror

    @if($hasEnglish)
        <form method="GET" action="{{ url()->current() }}" class="card filter-bar">
            <div class="min-w-[240px] flex-1">
                <label for="search" class="sr-only">بحث</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="ابحث بالاسم..." class="form-input pl-9">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass text-xs"></i> بحث</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
        </form>
    @endif

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    @if($hasEnglish) <th>بالإنجليزية</th> @endif
                    <th>{{ $usedLabel }}</th>
                    @if($hasActive) <th>الحالة</th> @endif
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td class="font-semibold text-fg">{{ $item->name }}</td>
                        @if($hasEnglish) <td dir="ltr" class="text-right">{{ $item->name_en }}</td> @endif
                        <td>{{ $item->used ?: '-' }}</td>
                        @if($hasActive)
                            <td><span class="badge {{ $item->is_active ? 'badge-success' : 'badge-danger' }}">{{ $item->is_active ? 'نشط' : 'متوقف' }}</span></td>
                        @endif
                        <td>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm"
                                        data-edit data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                        data-name-en="{{ $item->name_en ?? '' }}" data-active="{{ ($item->is_active ?? true) ? 1 : 0 }}">
                                    تعديل
                                </button>

                                <form method="POST" action="{{ str_replace('__ID__', $item->id, $destroyUrl) }}" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state text="لا توجد بيانات" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ONE dialog for adding AND editing (the script switches its action / title) --}}
    <x-modal id="lookupModal" :title="'إضافة ' . $singular" width="30rem">
        <form id="lookupForm" method="POST" action="{{ $storeUrl }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" value="PUT" id="lookup-method" disabled>
            <input type="hidden" name="_mode" id="lookup-mode" value="add">
            <input type="hidden" name="_id" id="lookup-id" value="">

            <x-form-field name="name" label="الاسم" />
            @if($hasEnglish) <x-form-field name="name_en" label="الاسم بالإنجليزية" dir="ltr" /> @endif

            @if($hasActive)
                <label class="check-row">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="accent-brand" checked>
                    نشط (يظهر في القوائم)
                </label>
            @endif

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <button type="button" class="btn btn-secondary" data-close-modal>إلغاء</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const storeUrl  = @json($storeUrl);
        const updateUrl = @json($updateUrl);
        const singular  = @json($singular);

        const dialog = document.getElementById('lookupModal');
        const form   = document.getElementById('lookupForm');
        const method = document.getElementById('lookup-method');
        const nameEn = document.getElementById('name_en');
        const active = form.querySelector('input[type="checkbox"][name="is_active"]');

        // add mode or edit mode (edit sends PUT to the item's url)
        function setMode(mode, id) {
            form.action  = mode === 'edit' ? updateUrl.replace('__ID__', id) : storeUrl;
            method.disabled = mode !== 'edit';
            document.getElementById('lookup-mode').value = mode;
            document.getElementById('lookup-id').value   = id ?? '';
            dialog.querySelector('h3').textContent = (mode === 'edit' ? 'تعديل ' : 'إضافة ') + singular;
        }

        document.getElementById('addBtn').addEventListener('click', () => {
            setMode('add');
            document.getElementById('name').value = '';
            if (nameEn) nameEn.value = '';
            if (active) active.checked = true;
            dialog.showModal();
        });

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-edit]');
            if (!button) return;

            setMode('edit', button.dataset.id);
            document.getElementById('name').value = button.dataset.name;
            if (nameEn) nameEn.value = button.dataset.nameEn;
            if (active) active.checked = button.dataset.active === '1';
            dialog.showModal();
        });

        // validation failed: open the dialog again (the fields already hold what the user typed)
        @if($errors->any() && old('_mode'))
            setMode(@json(old('_mode')), @json(old('_id')));
            dialog.showModal();
        @endif
    </script>
@endpush