{{-- resources/views/employees/partials/scripts.blade.php --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search');
    const filterForm = document.getElementById('employeeFilters');
    const refreshButton = document.getElementById('refreshEmployees');
    const focusSearchButton = document.getElementById('focusEmployeeSearch');
    const scrollToTop = document.getElementById('scrollToTop');
    const applyButton = document.getElementById('applyFilters');

    // Ctrl + K: focus search.
    // Ctrl + Enter: apply filters.
    // Escape: clear search when search has focus.
    document.addEventListener('keydown', function (event) {
        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k'
        ) {
            event.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key === 'Enter' &&
            filterForm
        ) {
            event.preventDefault();
            filterForm.requestSubmit();
        }

        if (
            event.key === 'Escape' &&
            document.activeElement === searchInput &&
            searchInput?.value !== ''
        ) {
            searchInput.value = '';
        }
    });

    // Focus search from the empty state.
    focusSearchButton?.addEventListener('click', function () {
        if (!searchInput) return;

        searchInput.focus();
        searchInput.select();

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Refresh icon animation.
    refreshButton?.addEventListener('click', function () {
        refreshButton.querySelector('i')?.classList.add('fa-spin');
    });

    // Prevent repeated submissions while applying filters.
    filterForm?.addEventListener('submit', function () {
        if (!applyButton) return;

        applyButton.disabled = true;
        applyButton.classList.add('opacity-70', 'cursor-not-allowed');
        applyButton.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري التحليل...';
    });

    // Scroll to page top.
    scrollToTop?.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>
@endpush