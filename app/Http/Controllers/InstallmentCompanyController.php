<?php

// app/Http/Controllers/InstallmentCompanyController.php  (static data)

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentCompanyController extends Controller
{
    private function find(int $id): object
    {
        return StaticData::installmentCompanies()->firstWhere('id', $id) ?? abort(404);
    }

    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'code' => ['required', 'string', 'max:20'], 'is_active' => ['nullable', 'boolean']];
    }

    public function index(): View
    {
        return view('installment-companies.index', ['companies' => StaticData::installmentCompanies()]);
    }

    public function create(): View
    {
        return view('installment-companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules());
        // TODO (DB): InstallmentCompany::create($validated);
        return redirect()->route('installment-companies.index')->with('success', 'تمت إضافة الشركة (بيانات تجريبية، لم يتم الحفظ).');
    }

    // GET /installment-companies/{company}/panel
    public function panel(int $company): View
    {
        return view('installment-companies.panel', ['company' => $this->find($company)]);
    }

    public function edit(int $company): View
    {
        return view('installment-companies.edit', ['company' => $this->find($company)]);
    }

    public function update(Request $request, int $company): RedirectResponse
    {
        $this->find($company);
        $request->validate($this->rules());
        // TODO (DB): $company->update($validated);
        return redirect()->route('installment-companies.index')->with('success', 'تم تعديل الشركة (بيانات تجريبية، لم يتم الحفظ).');
    }

    public function destroy(int $company): RedirectResponse
    {
        $this->find($company);
        // TODO (DB): $company->delete();
        return redirect()->route('installment-companies.index')->with('success', 'تم حذف الشركة (بيانات تجريبية، لم يتم الحذف).');
    }
}