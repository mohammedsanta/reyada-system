<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EmployeePerformanceController extends Controller
{
    /**
     * الموظفين التجريبيين.
     */
    private function employees(): Collection
    {
        return collect([

            /*
            |--------------------------------------------------------------------------
            | BANK MISR
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85001',
                'name' => 'Ehab Fathy',
                'bank' => 'بنك مصر',
                'cases' => 22,
                'ptp' => 18,
                'amount' => 73500,
                'efficiency' => 98,
                'color' => 'brand',
            ],

            [
                'code' => '85002',
                'name' => 'Ahmed Hassan',
                'bank' => 'بنك مصر',
                'cases' => 20,
                'ptp' => 16,
                'amount' => 68400,
                'efficiency' => 96,
                'color' => 'brand',
            ],

            [
                'code' => '85003',
                'name' => 'Mohamed Adel',
                'bank' => 'بنك مصر',
                'cases' => 19,
                'ptp' => 15,
                'amount' => 61200,
                'efficiency' => 94,
                'color' => 'brand',
            ],

            [
                'code' => '85004',
                'name' => 'Mostafa Samir',
                'bank' => 'بنك مصر',
                'cases' => 18,
                'ptp' => 14,
                'amount' => 57900,
                'efficiency' => 91,
                'color' => 'brand',
            ],

            [
                'code' => '85005',
                'name' => 'Karim Nabil',
                'bank' => 'بنك مصر',
                'cases' => 17,
                'ptp' => 13,
                'amount' => 52800,
                'efficiency' => 88,
                'color' => 'brand',
            ],

            [
                'code' => '85006',
                'name' => 'Omar Khaled',
                'bank' => 'بنك مصر',
                'cases' => 15,
                'ptp' => 11,
                'amount' => 46500,
                'efficiency' => 84,
                'color' => 'brand',
            ],

            [
                'code' => '85007',
                'name' => 'Youssef Tarek',
                'bank' => 'بنك مصر',
                'cases' => 13,
                'ptp' => 9,
                'amount' => 38900,
                'efficiency' => 79,
                'color' => 'warning',
            ],

            [
                'code' => '85008',
                'name' => 'Mahmoud Ashraf',
                'bank' => 'بنك مصر',
                'cases' => 11,
                'ptp' => 8,
                'amount' => 31400,
                'efficiency' => 72,
                'color' => 'warning',
            ],

            [
                'code' => '85009',
                'name' => 'Amr Gamal',
                'bank' => 'بنك مصر',
                'cases' => 7,
                'ptp' => 4,
                'amount' => 18600,
                'efficiency' => 56,
                'color' => 'warning',
            ],

            [
                'code' => '85010',
                'name' => 'Sherif Wael',
                'bank' => 'بنك مصر',
                'cases' => 2,
                'ptp' => 1,
                'amount' => 4200,
                'efficiency' => 22,
                'color' => 'danger',
            ],

            /*
            |--------------------------------------------------------------------------
            | NATIONAL BANK OF EGYPT
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85101',
                'name' => 'Mahmoud Hany',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 24,
                'ptp' => 20,
                'amount' => 81200,
                'efficiency' => 99,
                'color' => 'brand',
            ],

            [
                'code' => '85102',
                'name' => 'Ahmed Samir',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 21,
                'ptp' => 17,
                'amount' => 70400,
                'efficiency' => 97,
                'color' => 'brand',
            ],

            [
                'code' => '85103',
                'name' => 'Islam Mostafa',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 20,
                'ptp' => 15,
                'amount' => 64100,
                'efficiency' => 93,
                'color' => 'brand',
            ],

            [
                'code' => '85104',
                'name' => 'Hassan Ali',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 18,
                'ptp' => 14,
                'amount' => 57200,
                'efficiency' => 89,
                'color' => 'brand',
            ],

            [
                'code' => '85105',
                'name' => 'Mohamed Reda',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 16,
                'ptp' => 12,
                'amount' => 49600,
                'efficiency' => 86,
                'color' => 'brand',
            ],

            [
                'code' => '85106',
                'name' => 'Walid Atef',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 14,
                'ptp' => 10,
                'amount' => 40700,
                'efficiency' => 78,
                'color' => 'warning',
            ],

            [
                'code' => '85107',
                'name' => 'Tamer Ibrahim',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 12,
                'ptp' => 8,
                'amount' => 33800,
                'efficiency' => 73,
                'color' => 'warning',
            ],

            [
                'code' => '85108',
                'name' => 'Khaled Mahmoud',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 8,
                'ptp' => 5,
                'amount' => 21400,
                'efficiency' => 61,
                'color' => 'warning',
            ],

            [
                'code' => '85109',
                'name' => 'Mina George',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 5,
                'ptp' => 2,
                'amount' => 8900,
                'efficiency' => 41,
                'color' => 'warning',
            ],

            [
                'code' => '85110',
                'name' => 'Peter Nabil',
                'bank' => 'البنك الأهلي المصري',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | BANQUE DU CAIRE
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85201',
                'name' => 'Ahmed Borlsy',
                'bank' => 'بنك القاهرة',
                'cases' => 19,
                'ptp' => 15,
                'amount' => 61200,
                'efficiency' => 95,
                'color' => 'brand',
            ],

            [
                'code' => '85202',
                'name' => 'Beshoy Nople',
                'bank' => 'بنك القاهرة',
                'cases' => 13,
                'ptp' => 9,
                'amount' => 32600,
                'efficiency' => 79,
                'color' => 'warning',
            ],

            [
                'code' => '85203',
                'name' => 'George Sameh',
                'bank' => 'بنك القاهرة',
                'cases' => 16,
                'ptp' => 12,
                'amount' => 45100,
                'efficiency' => 87,
                'color' => 'brand',
            ],

            [
                'code' => '85204',
                'name' => 'Emad Fouad',
                'bank' => 'بنك القاهرة',
                'cases' => 10,
                'ptp' => 6,
                'amount' => 25800,
                'efficiency' => 68,
                'color' => 'warning',
            ],

            [
                'code' => '85205',
                'name' => 'Maged Hossam',
                'bank' => 'بنك القاهرة',
                'cases' => 8,
                'ptp' => 5,
                'amount' => 19700,
                'efficiency' => 58,
                'color' => 'warning',
            ],

            [
                'code' => '85206',
                'name' => 'Fady Magdy',
                'bank' => 'بنك القاهرة',
                'cases' => 4,
                'ptp' => 2,
                'amount' => 8300,
                'efficiency' => 38,
                'color' => 'danger',
            ],

            [
                'code' => '85207',
                'name' => 'Nader Ashraf',
                'bank' => 'بنك القاهرة',
                'cases' => 3,
                'ptp' => 1,
                'amount' => 5200,
                'efficiency' => 26,
                'color' => 'danger',
            ],

            [
                'code' => '85208',
                'name' => 'Karim Atef',
                'bank' => 'بنك القاهرة',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | EMIRATES NBD
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85392',
                'name' => 'Ahmed Borlsy',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 19,
                'ptp' => 15,
                'amount' => 61200,
                'efficiency' => 95,
                'color' => 'brand',
            ],

            [
                'code' => '85577',
                'name' => 'Beshoy Nople',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 13,
                'ptp' => 9,
                'amount' => 32600,
                'efficiency' => 79,
                'color' => 'warning',
            ],

            [
                'code' => '85515',
                'name' => 'HOOL',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 11,
                'ptp' => 6,
                'amount' => 24800,
                'efficiency' => 71,
                'color' => 'orange',
            ],

            [
                'code' => '85394',
                'name' => 'Michael Samir',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 17,
                'ptp' => 13,
                'amount' => 53800,
                'efficiency' => 90,
                'color' => 'brand',
            ],

            [
                'code' => '85395',
                'name' => 'Andrew Nabil',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 12,
                'ptp' => 8,
                'amount' => 29400,
                'efficiency' => 76,
                'color' => 'warning',
            ],

            [
                'code' => '85396',
                'name' => 'Mark George',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 9,
                'ptp' => 5,
                'amount' => 22100,
                'efficiency' => 64,
                'color' => 'warning',
            ],

            [
                'code' => '85397',
                'name' => 'Adel Tarek',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 6,
                'ptp' => 3,
                'amount' => 13700,
                'efficiency' => 49,
                'color' => 'warning',
            ],

            [
                'code' => '85398',
                'name' => 'Sameh Wael',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 4,
                'ptp' => 2,
                'amount' => 9400,
                'efficiency' => 33,
                'color' => 'danger',
            ],

            [
                'code' => '85399',
                'name' => 'Fares Hany',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 2,
                'ptp' => 0,
                'amount' => 2100,
                'efficiency' => 18,
                'color' => 'danger',
            ],

            [
                'code' => '85400',
                'name' => 'Yasser Magdy',
                'bank' => 'بنك الإمارات دبي الوطني',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | CIB
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85401',
                'name' => 'Omar Hassan',
                'bank' => 'CIB',
                'cases' => 21,
                'ptp' => 17,
                'amount' => 69800,
                'efficiency' => 97,
                'color' => 'brand',
            ],

            [
                'code' => '85402',
                'name' => 'Karim Essam',
                'bank' => 'CIB',
                'cases' => 18,
                'ptp' => 14,
                'amount' => 57600,
                'efficiency' => 92,
                'color' => 'brand',
            ],

            [
                'code' => '85403',
                'name' => 'Sherif Ahmed',
                'bank' => 'CIB',
                'cases' => 15,
                'ptp' => 11,
                'amount' => 43800,
                'efficiency' => 83,
                'color' => 'brand',
            ],

            [
                'code' => '85404',
                'name' => 'Youssef Hany',
                'bank' => 'CIB',
                'cases' => 12,
                'ptp' => 8,
                'amount' => 32100,
                'efficiency' => 74,
                'color' => 'warning',
            ],

            [
                'code' => '85405',
                'name' => 'Tarek Nabil',
                'bank' => 'CIB',
                'cases' => 8,
                'ptp' => 4,
                'amount' => 17600,
                'efficiency' => 55,
                'color' => 'warning',
            ],

            [
                'code' => '85406',
                'name' => 'Mina Sameh',
                'bank' => 'CIB',
                'cases' => 6,
                'ptp' => 2,
                'amount' => 11300,
                'efficiency' => 44,
                'color' => 'warning',
            ],

            [
                'code' => '85407',
                'name' => 'Hany Fawzy',
                'bank' => 'CIB',
                'cases' => 3,
                'ptp' => 1,
                'amount' => 5800,
                'efficiency' => 29,
                'color' => 'danger',
            ],

            [
                'code' => '85408',
                'name' => 'George Adel',
                'bank' => 'CIB',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | B.TECH
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85411',
                'name' => 'Mohamed Gamal',
                'bank' => 'B.TECH',
                'cases' => 20,
                'ptp' => 16,
                'amount' => 62500,
                'efficiency' => 94,
                'color' => 'brand',
            ],

            [
                'code' => '85412',
                'name' => 'Ahmed Khaled',
                'bank' => 'B.TECH',
                'cases' => 17,
                'ptp' => 13,
                'amount' => 48900,
                'efficiency' => 86,
                'color' => 'brand',
            ],

            [
                'code' => '85413',
                'name' => 'Mostafa Hany',
                'bank' => 'B.TECH',
                'cases' => 14,
                'ptp' => 10,
                'amount' => 35700,
                'efficiency' => 78,
                'color' => 'warning',
            ],

            [
                'code' => '85414',
                'name' => 'Mahmoud Adel',
                'bank' => 'B.TECH',
                'cases' => 10,
                'ptp' => 6,
                'amount' => 24900,
                'efficiency' => 69,
                'color' => 'warning',
            ],

            [
                'code' => '85415',
                'name' => 'Maged Samir',
                'bank' => 'B.TECH',
                'cases' => 8,
                'ptp' => 4,
                'amount' => 16800,
                'efficiency' => 57,
                'color' => 'warning',
            ],

            [
                'code' => '85416',
                'name' => 'Hossam Wael',
                'bank' => 'B.TECH',
                'cases' => 5,
                'ptp' => 2,
                'amount' => 9100,
                'efficiency' => 36,
                'color' => 'danger',
            ],

            [
                'code' => '85417',
                'name' => 'Fady Nabil',
                'bank' => 'B.TECH',
                'cases' => 2,
                'ptp' => 1,
                'amount' => 3900,
                'efficiency' => 24,
                'color' => 'danger',
            ],

            [
                'code' => '85418',
                'name' => 'Peter Hany',
                'bank' => 'B.TECH',
                'cases' => 1,
                'ptp' => 0,
                'amount' => 900,
                'efficiency' => 12,
                'color' => 'danger',
            ],

            [
                'code' => '85419',
                'name' => 'John Magdy',
                'bank' => 'B.TECH',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | CREDIT AGRICOLE
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85421',
                'name' => 'Nader Hassan',
                'bank' => 'Credit Agricole',
                'cases' => 18,
                'ptp' => 14,
                'amount' => 55200,
                'efficiency' => 93,
                'color' => 'brand',
            ],

            [
                'code' => '85422',
                'name' => 'Adham Tarek',
                'bank' => 'Credit Agricole',
                'cases' => 15,
                'ptp' => 11,
                'amount' => 42700,
                'efficiency' => 85,
                'color' => 'brand',
            ],

            [
                'code' => '85423',
                'name' => 'Hany Ashraf',
                'bank' => 'Credit Agricole',
                'cases' => 11,
                'ptp' => 7,
                'amount' => 28600,
                'efficiency' => 71,
                'color' => 'warning',
            ],

            [
                'code' => '85424',
                'name' => 'Mina Adel',
                'bank' => 'Credit Agricole',
                'cases' => 7,
                'ptp' => 4,
                'amount' => 14900,
                'efficiency' => 52,
                'color' => 'warning',
            ],

            [
                'code' => '85425',
                'name' => 'George Nabil',
                'bank' => 'Credit Agricole',
                'cases' => 3,
                'ptp' => 1,
                'amount' => 4600,
                'efficiency' => 27,
                'color' => 'danger',
            ],

            [
                'code' => '85426',
                'name' => 'Fares Sameh',
                'bank' => 'Credit Agricole',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | QNB
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85431',
                'name' => 'Ahmed Fathy',
                'bank' => 'QNB',
                'cases' => 23,
                'ptp' => 19,
                'amount' => 76400,
                'efficiency' => 99,
                'color' => 'brand',
            ],

            [
                'code' => '85432',
                'name' => 'Mohamed Hany',
                'bank' => 'QNB',
                'cases' => 19,
                'ptp' => 15,
                'amount' => 58400,
                'efficiency' => 91,
                'color' => 'brand',
            ],

            [
                'code' => '85433',
                'name' => 'Karim Fawzy',
                'bank' => 'QNB',
                'cases' => 13,
                'ptp' => 9,
                'amount' => 34200,
                'efficiency' => 77,
                'color' => 'warning',
            ],

            [
                'code' => '85434',
                'name' => 'Walid Nabil',
                'bank' => 'QNB',
                'cases' => 9,
                'ptp' => 5,
                'amount' => 20100,
                'efficiency' => 63,
                'color' => 'warning',
            ],

            [
                'code' => '85435',
                'name' => 'Tamer Adel',
                'bank' => 'QNB',
                'cases' => 4,
                'ptp' => 2,
                'amount' => 7800,
                'efficiency' => 34,
                'color' => 'danger',
            ],

            [
                'code' => '85436',
                'name' => 'Bassem George',
                'bank' => 'QNB',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

            /*
            |--------------------------------------------------------------------------
            | FAISAL ISLAMIC
            |--------------------------------------------------------------------------
            */

            [
                'code' => '85441',
                'name' => 'Hassan Mahmoud',
                'bank' => 'بنك فيصل الإسلامي',
                'cases' => 17,
                'ptp' => 13,
                'amount' => 49600,
                'efficiency' => 90,
                'color' => 'brand',
            ],

            [
                'code' => '85442',
                'name' => 'Omar Nabil',
                'bank' => 'بنك فيصل الإسلامي',
                'cases' => 14,
                'ptp' => 10,
                'amount' => 38200,
                'efficiency' => 82,
                'color' => 'brand',
            ],

            [
                'code' => '85443',
                'name' => 'Ahmed Tarek',
                'bank' => 'بنك فيصل الإسلامي',
                'cases' => 10,
                'ptp' => 6,
                'amount' => 23500,
                'efficiency' => 70,
                'color' => 'warning',
            ],

            [
                'code' => '85444',
                'name' => 'Mahmoud Fathy',
                'bank' => 'بنك فيصل الإسلامي',
                'cases' => 7,
                'ptp' => 4,
                'amount' => 15100,
                'efficiency' => 59,
                'color' => 'warning',
            ],

            [
                'code' => '85445',
                'name' => 'Mina Fawzy',
                'bank' => 'بنك فيصل الإسلامي',
                'cases' => 3,
                'ptp' => 1,
                'amount' => 5100,
                'efficiency' => 31,
                'color' => 'danger',
            ],

            [
                'code' => '85446',
                'name' => 'Peter Adel',
                'bank' => 'بنك فيصل الإسلامي',
                'cases' => 0,
                'ptp' => 0,
                'amount' => 0,
                'efficiency' => 0,
                'color' => 'brand',
            ],

        ]);
    }

    /**
     * الحروف الأولى من اسم الموظف.
     */
    private function initials(string $name): string
    {
        return collect(explode(' ', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('');
    }

    /**
     * لون الأداء.
     */
    private function tone(float $efficiency): string
    {
        return match (true) {
            $efficiency >= 90 => 'excellent',
            $efficiency >= 70 => 'good',
            $efficiency >= 40 => 'average',
            $efficiency > 0   => 'low',
            default           => 'inactive',
        };
    }

    /**
     * مستوى الأداء باللغة العربية.
     */
    private function performanceLevel(float $efficiency): string
    {
        return match (true) {
            $efficiency >= 90 => 'ممتاز جداً',
            $efficiency >= 80 => 'ممتاز',
            $efficiency >= 70 => 'جيد جداً',
            $efficiency >= 60 => 'جيد',
            $efficiency >= 40 => 'متوسط',
            $efficiency > 0   => 'يحتاج متابعة',
            default           => 'بدون نشاط',
        };
    }

    /**
     * حالة الأداء.
     */
    private function performanceStatus(float $efficiency): string
    {
        return match (true) {
            $efficiency >= 70 => 'excellent',
            $efficiency >= 40 => 'average',
            $efficiency > 0   => 'low',
            default           => 'inactive',
        };
    }

    /**
     * صفحة أداء الموظفين.
     */
    public function index(Request $request): View
    {
        $allEmployees = $this->employees();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            'institution' => $request->string('institution')->toString(),
            'search'      => $request->string('search')->toString(),
            'month'       => $request->string('month')->toString(),
            'year'        => $request->string('year')->toString(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Institutions
        |--------------------------------------------------------------------------
        */

        $institutions = $allEmployees
            ->pluck('bank')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Apply filters
        |--------------------------------------------------------------------------
        */

        $employees = $allEmployees
            ->filter(function (array $employee) use ($filters) {

                if (
                    $filters['institution'] !== '' &&
                    ($employee['bank'] ?? '') !== $filters['institution']
                ) {
                    return false;
                }

                if ($filters['search'] !== '') {
                    $search = mb_strtolower($filters['search']);

                    $name = mb_strtolower($employee['name'] ?? '');
                    $code = mb_strtolower($employee['code'] ?? '');
                    $bank = mb_strtolower($employee['bank'] ?? '');

                    if (
                        !str_contains($name, $search) &&
                        !str_contains($code, $search) &&
                        !str_contains($bank, $search)
                    ) {
                        return false;
                    }
                }

                return true;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Prepare employee presentation data
        |--------------------------------------------------------------------------
        */

        $employees = $employees
            ->map(function (array $employee) {

                $efficiency = (float) ($employee['efficiency'] ?? 0);

                $employee['initials'] = $employee['initials']
                    ?? $this->initials($employee['name'] ?? '');

                $employee['tone'] = $employee['tone']
                    ?? $this->tone($efficiency);

                $employee['performance_level'] =
                    $this->performanceLevel($efficiency);

                $employee['performance_status'] =
                    $this->performanceStatus($efficiency);

                return $employee;
            })
            ->sortByDesc('efficiency')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Ranking
        |--------------------------------------------------------------------------
        */

        $employees = $employees
            ->values()
            ->map(function (array $employee, int $index) {
                $employee['rank'] = $index + 1;

                return $employee;
            });

        /*
        |--------------------------------------------------------------------------
        | Global count
        |--------------------------------------------------------------------------
        */

        $totalCount = $allEmployees->count();

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalAmount = (float) $employees->sum(
    fn (array $employee) => (float) ($employee['amount'] ?? 0)
);

$totalCases = (int) $employees->sum(
    fn (array $employee) => (int) ($employee['cases'] ?? 0)
);

$totalPtp = (int) $employees->sum(
    fn (array $employee) => (int) ($employee['ptp'] ?? 0)
);

$averageEfficiency = $employees->count()
    ? round(
        $employees->avg(
            fn (array $employee) =>
                (float) ($employee['efficiency'] ?? 0)
        ),
        1
    )
    : 0;

$highestEfficiencyEmployee = $employees
    ->sortByDesc(
        fn (array $employee) =>
            (float) ($employee['efficiency'] ?? 0)
    )
    ->first();

$highestAmountEmployee = $employees
    ->sortByDesc(
        fn (array $employee) =>
            (float) ($employee['amount'] ?? 0)
    )
    ->first();

$summary = [
    'displayedCount' => $employees->count(),
    'grandTotalCount' => $totalCount,

    'totalAmount' => $totalAmount,
    'totalCases' => $totalCases,
    'totalPtp' => $totalPtp,

    'averageEfficiency' => $averageEfficiency,

    'highestEfficiencyEmployee' => $highestEfficiencyEmployee,
    'highestAmountEmployee' => $highestAmountEmployee,
];

/*
|--------------------------------------------------------------------------
| Metric cards
|--------------------------------------------------------------------------
*/

$stats = [
    [
        'label' => 'الموظفون',
        'value' => $employees->count(),
        'unit' => 'موظف',
        'icon' => 'fa-users',
        'color' => 'info',
    ],
    [
        'label' => 'إجمالي المبالغ',
        'value' => number_format($totalAmount),
        'unit' => 'ج.م',
        'icon' => 'fa-money-bill-wave',
        'color' => 'brand',
    ],
    [
        'label' => 'إجمالي الحالات',
        'value' => $totalCases,
        'unit' => 'حالة',
        'icon' => 'fa-folder-open',
        'color' => 'warning',
    ],
    [
        'label' => 'إجمالي PTP',
        'value' => $totalPtp,
        'unit' => 'وعد سداد',
        'icon' => 'fa-handshake',
        'color' => 'accent',
    ],
    [
        'label' => 'متوسط الكفاءة',
        'value' => number_format($averageEfficiency, 1),
        'unit' => '%',
        'icon' => 'fa-gauge-high',
        'color' => 'info',
    ],
];


        /*
        |--------------------------------------------------------------------------
        | Filter state
        |--------------------------------------------------------------------------
        */

        $months = $this->months();

        $monthName = $filters['month'] !== ''
            ? ($months[$filters['month']] ?? '')
            : '';

        $hasFilters =
            $filters['institution'] !== '' ||
            $filters['search'] !== '' ||
            $filters['month'] !== '' ||
            $filters['year'] !== '';

        $filterState = [
            'institution' => $filters['institution'],
            'search' => $filters['search'],
            'month' => $filters['month'],
            'year' => $filters['year'],

            'monthName' => $monthName,
            'hasFilters' => $hasFilters,
        ];

        /*
        |--------------------------------------------------------------------------
        | Years
        |--------------------------------------------------------------------------
        */

        $years = range(
            now()->year - 2,
            now()->year
        );

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view('employees.index', [
            'employees' => $employees,
            'stats' => $stats,

            'summary' => $summary,
            'filterState' => $filterState,

            'totalCount' => $totalCount,

            'filters' => $filters,
            'institutions' => $institutions,
            'months' => $months,
            'years' => $years,
        ]);
    }

    /**
     * إحصائيات الصفحة.
     */
    // private function stats(Collection $employees): array
    // {
    //     $count = $employees->count();

    //     $totalAmount = (float) $employees->sum(
    //         fn (array $employee) =>
    //             (float) ($employee['amount'] ?? 0)
    //     );

    //     $totalCases = (int) $employees->sum(
    //         fn (array $employee) =>
    //             (int) ($employee['cases'] ?? 0)
    //     );

    //     $totalPtp = (int) $employees->sum(
    //         fn (array $employee) =>
    //             (int) ($employee['ptp'] ?? 0)
    //     );

    //     $averageEfficiency = $count
    //         ? round(
    //             $employees->avg(
    //                 fn (array $employee) =>
    //                     (float) ($employee['efficiency'] ?? 0)
    //             ),
    //             1
    //         )
    //         : 0;

    //     return [
    //         'employees' => $count,
    //         'amount' => $totalAmount,
    //         'cases' => $totalCases,
    //         'ptp' => $totalPtp,
    //         'efficiency' => $averageEfficiency,
    //     ];
    // }

    /**
     * الشهور العربية.
     */
    private function months(): array
    {
        return [
            1 => 'يناير',
            2 => 'فبراير',
            3 => 'مارس',
            4 => 'أبريل',
            5 => 'مايو',
            6 => 'يونيو',
            7 => 'يوليو',
            8 => 'أغسطس',
            9 => 'سبتمبر',
            10 => 'أكتوبر',
            11 => 'نوفمبر',
            12 => 'ديسمبر',
        ];
    }
}