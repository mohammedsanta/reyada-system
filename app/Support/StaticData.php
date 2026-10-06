<?php

namespace App\Support;

use Illuminate\Support\Collection;

class StaticData
{
    /*
    |--------------------------------------------------------------------------
    | User Array Indexes
    |--------------------------------------------------------------------------
    |
    | rawUsers() currently uses indexed arrays.
    | These constants prevent magic numbers from being scattered everywhere.
    |
    */

    private const USER_ID = 0;
    private const USER_CODE = 1;
    private const USER_NAME = 2;
    private const USER_PHONE = 3;
    private const USER_EMAIL = 4;
    private const USER_ROLE_ID = 5;
    private const USER_STATUS = 6;
    private const USER_SUPERVISOR_ID = 7;
    private const USER_BANK_IDS = 8;
    private const USER_COMPANY_IDS = 9;
    private const USER_IS_SYSTEM = 10;
    private const USER_OVERRIDES = 11;
    private const USER_BANK = 12;
    private const USER_CASES = 13;
    private const USER_PTP = 14;
    private const USER_AMOUNT = 15;
    private const USER_EFFICIENCY = 16;
    private const USER_COLOR = 17;

    private const ROLE_LABELS = [
        1 => 'Owner',
        2 => 'Super Visor',
        3 => 'Call Center',
    ];

    /**
     * Command: Return all permission groups and their available actions.
     */
    public static function permissionGroups(): array
    {
        return [
            'users' => [
                'label' => 'المستخدمون',
                'actions' => [
                    'view' => 'عرض',
                    'create' => 'إضافة',
                    'edit' => 'تعديل',
                    'delete' => 'حذف',
                ],
            ],

            'roles' => [
                'label' => 'الرتب والصلاحيات',
                'actions' => [
                    'view' => 'عرض',
                    'manage' => 'إدارة',
                ],
            ],

            'banks' => [
                'label' => 'البنوك',
                'actions' => [
                    'view' => 'عرض',
                    'manage' => 'إدارة',
                    'import' => 'استيراد النطاق',
                ],
            ],

            'clients' => [
                'label' => 'العملاء',
                'actions' => [
                    'view' => 'عرض',
                    'edit' => 'تعديل',
                    'assign' => 'توزيع الحالات',
                ],
            ],

            'ptp' => [
                'label' => 'الوعود (PTP)',
                'actions' => [
                    'view' => 'عرض',
                    'manage' => 'إدارة',
                ],
            ],

            'payments' => [
                'label' => 'التحصيلات',
                'actions' => [
                    'view' => 'عرض',
                    'confirm' => 'تأكيد',
                ],
            ],

            'complaints' => [
                'label' => 'الشكاوى',
                'actions' => [
                    'view' => 'عرض',
                    'manage' => 'إدارة',
                ],
            ],

            'visits' => [
                'label' => 'الزيارات',
                'actions' => [
                    'view' => 'عرض',
                    'manage' => 'إدارة',
                ],
            ],

            'dcr' => [
                'label' => 'تقارير DCR',
                'actions' => [
                    'view' => 'عرض',
                    'approve' => 'اعتماد',
                ],
            ],

            'reports' => [
                'label' => 'التقارير',
                'actions' => [
                    'view' => 'عرض',
                    'export' => 'تصدير',
                ],
            ],

            'activity' => [
                'label' => 'سجل النشاط',
                'actions' => [
                    'view' => 'عرض',
                ],
            ],
        ];
    }

    /**
     * Command: Build the complete permission collection with generated IDs.
     */
    public static function permissions(): Collection
    {
        $id = 0;
        $permissions = [];

        foreach (self::permissionGroups() as $group => $config) {
            foreach ($config['actions'] as $action => $label) {
                $permissions[] = (object) [
                    'id' => ++$id,
                    'name' => "{$group}.{$action}",
                    'label' => $label,
                    'group' => $group,
                    'group_label' => $config['label'],
                ];
            }
        }

        return collect($permissions);
    }

    /**
     * Command: Return the raw employee records used by the demo application.
     *
     * IMPORTANT:
     * Keep your existing 74 records here.
     */
    private static function rawUsers(): array
    {
        return [

            // ------------------------------------------------------------------
            // EXISTING USERS
            // ------------------------------------------------------------------

            [
                1,
                '922885',
                'Test Account',
                '0127673938',
                'test@collex.com',
                1,
                'active',
                null,
                [],
                [],
                true,
                [],
                null,
                0,
                0,
                0,
                0,
                'brand',
            ],

            [
                2,
                '85577',
                'Beshoy nople',
                '01256987456',
                'ggglgogo@example.com',
                2,
                'active',
                1,
                [1],
                [],
                false,
                [],
                'بنك الإمارات دبي الوطني',
                13,
                9,
                32600,
                79,
                'warning',
            ],

            [
                3,
                '85515',
                'HOOL',
                '01236987445',
                'joniur@managhfgger.com',
                3,
                'active',
                2,
                [1],
                [],
                false,
                [
                    'reports.view' => true,
                ],
                'بنك الإمارات دبي الوطني',
                11,
                6,
                24800,
                71,
                'orange',
            ],

            [
                4,
                '85392',
                'Ahmed Borlsy',
                '01270008000',
                'berolsy@collex.com',
                3,
                'active',
                2,
                [1],
                [],
                false,
                [],
                'بنك الإمارات دبي الوطني',
                19,
                15,
                61200,
                95,
                'brand',
            ],

            // ------------------------------------------------------------------
            // IMPORTANT:
            // Paste the rest of your existing records here unchanged.
            //
            // Your records already have this exact structure:
            //
            // [
            //     id,
            //     code,
            //     name,
            //     phone,
            //     email,
            //     role_id,
            //     status,
            //     supervisor_id,
            //     bank_ids,
            //     company_ids,
            //     is_system,
            //     overrides,
            //     bank,
            //     cases,
            //     ptp,
            //     amount,
            //     efficiency,
            //     color,
            // ],
            // ------------------------------------------------------------------

            // BANK OF MISR
            [5, '85001', 'Ehab Fathy', '01000000001', 'ehab.fathy@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 22, 18, 73500, 98, 'brand'],
            [6, '85002', 'Ahmed Hassan', '01000000002', 'ahmed.hassan@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 20, 16, 68400, 96, 'brand'],
            [7, '85003', 'Mohamed Adel', '01000000003', 'mohamed.adel@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 19, 15, 61200, 94, 'brand'],
            [8, '85004', 'Mostafa Samir', '01000000004', 'mostafa.samir@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 18, 14, 57900, 91, 'brand'],
            [9, '85005', 'Karim Nabil', '01000000005', 'karim.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 17, 13, 52800, 88, 'brand'],
            [10, '85006', 'Omar Khaled', '01000000006', 'omar.khaled@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 15, 11, 46500, 84, 'brand'],
            [11, '85007', 'Youssef Tarek', '01000000007', 'youssef.tarek@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 13, 9, 38900, 79, 'warning'],
            [12, '85008', 'Mahmoud Ashraf', '01000000008', 'mahmoud.ashraf@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 11, 8, 31400, 72, 'warning'],
            [13, '85009', 'Amr Gamal', '01000000009', 'amr.gamal@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 7, 4, 18600, 56, 'warning'],
            [14, '85010', 'Sherif Wael', '01000000010', 'sherif.wael@example.com', 3, 'active', 2, [1], [], false, [], 'بنك مصر', 2, 1, 4200, 22, 'danger'],

            // NATIONAL BANK OF EGYPT
            [15, '85101', 'Mahmoud Hany', '01000000011', 'mahmoud.hany@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 24, 20, 81200, 99, 'brand'],
            [16, '85102', 'Ahmed Samir', '01000000012', 'ahmed.samir@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 21, 17, 70400, 97, 'brand'],
            [17, '85103', 'Islam Mostafa', '01000000013', 'islam.mostafa@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 20, 15, 64100, 93, 'brand'],
            [18, '85104', 'Hassan Ali', '01000000014', 'hassan.ali@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 18, 14, 57200, 89, 'brand'],
            [19, '85105', 'Mohamed Reda', '01000000015', 'mohamed.reda@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 16, 12, 49600, 86, 'brand'],
            [20, '85106', 'Walid Atef', '01000000016', 'walid.atef@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 14, 10, 40700, 78, 'warning'],
            [21, '85107', 'Tamer Ibrahim', '01000000017', 'tamer.ibrahim@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 12, 8, 33800, 73, 'warning'],
            [22, '85108', 'Khaled Mahmoud', '01000000018', 'khaled.mahmoud@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 8, 5, 21400, 61, 'warning'],
            [23, '85109', 'Mina George', '01000000019', 'mina.george@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 5, 2, 8900, 41, 'warning'],
            [24, '85110', 'Peter Nabil', '01000000020', 'peter.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'البنك الأهلي المصري', 0, 0, 0, 0, 'brand'],

            // BANQUE DU CAIRE
            [25, '85201', 'Ahmed Borlsy', '01000000021', 'ahmed.borlsy@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 19, 15, 61200, 95, 'brand'],
            [26, '85202', 'Beshoy Nople', '01000000022', 'beshoy.nople@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 13, 9, 32600, 79, 'warning'],
            [27, '85203', 'George Sameh', '01000000023', 'george.sameh@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 16, 12, 45100, 87, 'brand'],
            [28, '85204', 'Emad Fouad', '01000000024', 'emad.fouad@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 10, 6, 25800, 68, 'warning'],
            [29, '85205', 'Maged Hossam', '01000000025', 'maged.hossam@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 8, 5, 19700, 58, 'warning'],
            [30, '85206', 'Fady Magdy', '01000000026', 'fady.magdy@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 4, 2, 8300, 38, 'danger'],
            [31, '85207', 'Nader Ashraf', '01000000027', 'nader.ashraf@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 3, 1, 5200, 26, 'danger'],
            [32, '85208', 'Karim Atef', '01000000028', 'karim.atef@example.com', 3, 'active', 2, [1], [], false, [], 'بنك القاهرة', 0, 0, 0, 0, 'brand'],

            // EMIRATES NBD
            [33, '85394', 'Michael Samir', '01000000029', 'michael.samir@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 17, 13, 53800, 90, 'brand'],
            [34, '85395', 'Andrew Nabil', '01000000030', 'andrew.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 12, 8, 29400, 76, 'warning'],
            [35, '85396', 'Mark George', '01000000031', 'mark.george@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 9, 5, 22100, 64, 'warning'],
            [36, '85397', 'Adel Tarek', '01000000032', 'adel.tarek@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 6, 3, 13700, 49, 'warning'],
            [37, '85398', 'Sameh Wael', '01000000033', 'sameh.wael@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 4, 2, 9400, 33, 'danger'],
            [38, '85399', 'Fares Hany', '01000000034', 'fares.hany@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 2, 0, 2100, 18, 'danger'],
            [39, '85400', 'Yasser Magdy', '01000000035', 'yasser.magdy@example.com', 3, 'active', 2, [1], [], false, [], 'بنك الإمارات دبي الوطني', 0, 0, 0, 0, 'brand'],

            // CIB
            [40, '85401', 'Omar Hassan', '01000000036', 'omar.hassan@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 21, 17, 69800, 97, 'brand'],
            [41, '85402', 'Karim Essam', '01000000037', 'karim.essam@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 18, 14, 57600, 92, 'brand'],
            [42, '85403', 'Sherif Ahmed', '01000000038', 'sherif.ahmed@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 15, 11, 43800, 83, 'brand'],
            [43, '85404', 'Youssef Hany', '01000000039', 'youssef.hany@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 12, 8, 32100, 74, 'warning'],
            [44, '85405', 'Tarek Nabil', '01000000040', 'tarek.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 8, 4, 17600, 55, 'warning'],
            [45, '85406', 'Mina Sameh', '01000000041', 'mina.sameh@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 6, 2, 11300, 44, 'warning'],
            [46, '85407', 'Hany Fawzy', '01000000042', 'hany.fawzy@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 3, 1, 5800, 29, 'danger'],
            [47, '85408', 'George Adel', '01000000043', 'george.adel@example.com', 3, 'active', 2, [1], [], false, [], 'CIB', 0, 0, 0, 0, 'brand'],

            // B.TECH
            [48, '85411', 'Mohamed Gamal', '01000000044', 'mohamed.gamal@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 20, 16, 62500, 94, 'brand'],
            [49, '85412', 'Ahmed Khaled', '01000000045', 'ahmed.khaled@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 17, 13, 48900, 86, 'brand'],
            [50, '85413', 'Mostafa Hany', '01000000046', 'mostafa.hany@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 14, 10, 35700, 78, 'warning'],
            [51, '85414', 'Mahmoud Adel', '01000000047', 'mahmoud.adel@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 10, 6, 24900, 69, 'warning'],
            [52, '85415', 'Maged Samir', '01000000048', 'maged.samir@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 8, 4, 16800, 57, 'warning'],
            [53, '85416', 'Hossam Wael', '01000000049', 'hossam.wael@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 5, 2, 9100, 36, 'danger'],
            [54, '85417', 'Fady Nabil', '01000000050', 'fady.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 2, 1, 3900, 24, 'danger'],
            [55, '85418', 'Peter Hany', '01000000051', 'peter.hany@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 1, 0, 900, 12, 'danger'],
            [56, '85419', 'John Magdy', '01000000052', 'john.magdy@example.com', 3, 'active', 2, [1], [], false, [], 'B.TECH', 0, 0, 0, 0, 'brand'],

            // CREDIT AGRICOLE
            [57, '85421', 'Nader Hassan', '01000000053', 'nader.hassan@example.com', 3, 'active', 2, [1], [], false, [], 'Credit Agricole', 18, 14, 55200, 93, 'brand'],
            [58, '85422', 'Adham Tarek', '01000000054', 'adham.tarek@example.com', 3, 'active', 2, [1], [], false, [], 'Credit Agricole', 15, 11, 42700, 85, 'brand'],
            [59, '85423', 'Hany Ashraf', '01000000055', 'hany.ashraf@example.com', 3, 'active', 2, [1], [], false, [], 'Credit Agricole', 11, 7, 28600, 71, 'warning'],
            [60, '85424', 'Mina Adel', '01000000056', 'mina.adel@example.com', 3, 'active', 2, [1], [], false, [], 'Credit Agricole', 7, 4, 14900, 52, 'warning'],
            [61, '85425', 'George Nabil', '01000000057', 'george.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'Credit Agricole', 3, 1, 4600, 27, 'danger'],
            [62, '85426', 'Fares Sameh', '01000000058', 'fares.sameh@example.com', 3, 'active', 2, [1], [], false, [], 'Credit Agricole', 0, 0, 0, 0, 'brand'],

            // QNB
            [63, '85431', 'Ahmed Fathy', '01000000059', 'ahmed.fathy@example.com', 3, 'active', 2, [1], [], false, [], 'QNB', 23, 19, 76400, 99, 'brand'],
            [64, '85432', 'Mohamed Hany', '01000000060', 'mohamed.hany@example.com', 3, 'active', 2, [1], [], false, [], 'QNB', 19, 15, 58400, 91, 'brand'],
            [65, '85433', 'Karim Fawzy', '01000000061', 'karim.fawzy@example.com', 3, 'active', 2, [1], [], false, [], 'QNB', 13, 9, 34200, 77, 'warning'],
            [66, '85434', 'Walid Nabil', '01000000062', 'walid.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'QNB', 9, 5, 20100, 63, 'warning'],
            [67, '85435', 'Tamer Adel', '01000000063', 'tamer.adel@example.com', 3, 'active', 2, [1], [], false, [], 'QNB', 4, 2, 7800, 34, 'danger'],
            [68, '85436', 'Bassem George', '01000000064', 'bassem.george@example.com', 3, 'active', 2, [1], [], false, [], 'QNB', 0, 0, 0, 0, 'brand'],

            // FAISAL ISLAMIC
            [69, '85441', 'Hassan Mahmoud', '01000000065', 'hassan.mahmoud@example.com', 3, 'active', 2, [1], [], false, [], 'بنك فيصل الإسلامي', 17, 13, 49600, 90, 'brand'],
            [70, '85442', 'Omar Nabil', '01000000066', 'omar.nabil@example.com', 3, 'active', 2, [1], [], false, [], 'بنك فيصل الإسلامي', 14, 10, 38200, 82, 'brand'],
            [71, '85443', 'Ahmed Tarek', '01000000067', 'ahmed.tarek@example.com', 3, 'active', 2, [1], [], false, [], 'بنك فيصل الإسلامي', 10, 6, 23500, 70, 'warning'],
            [72, '85444', 'Mahmoud Fathy', '01000000068', 'mahmoud.fathy@example.com', 3, 'active', 2, [1], [], false, [], 'بنك فيصل الإسلامي', 7, 4, 15100, 59, 'warning'],
            [73, '85445', 'Mina Fawzy', '01000000069', 'mina.fawzy@example.com', 3, 'active', 2, [1], [], false, [], 'بنك فيصل الإسلامي', 3, 1, 5100, 31, 'danger'],
            [74, '85446', 'Peter Adel', '01000000070', 'peter.adel@example.com', 3, 'active', 2, [1], [], false, [], 'بنك فيصل الإسلامي', 0, 0, 0, 0, 'brand'],
        ];
    }

    /**
     * Command: Return all users with their related display data.
     */
    public static function users(): Collection
    {
        $rawUsers = collect(self::rawUsers());

        $banks = StaticBanks::all()
            ->pluck('name', 'id');

        return $rawUsers->map(
            fn (array $user) => self::transformUser(
                $user,
                $rawUsers,
                $banks
            )
        );
    }

    /**
     * Command: Convert one raw user record into the application user object.
     */
    private static function transformUser(
        array $user,
        Collection $allUsers,
        Collection $banks
    ): object {
        $roleId = (int) ($user[self::USER_ROLE_ID] ?? 0);
        $isSystem = (bool) ($user[self::USER_IS_SYSTEM] ?? false);

        return (object) [
            'id' => $user[self::USER_ID] ?? null,
            'code' => $user[self::USER_CODE] ?? null,
            'name' => $user[self::USER_NAME] ?? null,
            'phone' => $user[self::USER_PHONE] ?? null,
            'email' => $user[self::USER_EMAIL] ?? null,

            'role_id' => $roleId,

            'role' => self::ROLE_LABELS[$roleId]
                ?? 'غير محدد',

            'status' => $user[self::USER_STATUS] ?? null,

            'supervisor_id' =>
                $user[self::USER_SUPERVISOR_ID] ?? null,

            'supervisor' => self::resolveSupervisor(
                $user,
                $allUsers
            ),

            'bank_ids' =>
                $user[self::USER_BANK_IDS] ?? [],

            'company_ids' =>
                $user[self::USER_COMPANY_IDS] ?? [],

            'banks' => self::resolveBanks(
                $user[self::USER_BANK_IDS] ?? [],
                $banks
            ),

            /*
            |--------------------------------------------------------------------------
            | Performance Data
            |--------------------------------------------------------------------------
            |
            | This is the part that was missing from the old implementation.
            |
            */

            'bank' =>
                $user[self::USER_BANK] ?? null,

            'cases' =>
                $user[self::USER_CASES] ?? 0,

            'ptp' =>
                $user[self::USER_PTP] ?? 0,

            'amount' =>
                $user[self::USER_AMOUNT] ?? 0,

            'efficiency' =>
                $user[self::USER_EFFICIENCY] ?? 0,

            'color' =>
                $user[self::USER_COLOR] ?? 'danger',

            'is_system' => $isSystem,

            'overrides' =>
                $user[self::USER_OVERRIDES] ?? [],

            'last_login' => $isSystem
                ? 'اليوم 09:12'
                : 'أمس 18:40',

            'actions' => self::resolveUserActions(
                $isSystem,
                $roleId
            ),
        ];
    }

    /**
     * Command: Resolve the supervisor information for a user.
     */
    private static function resolveSupervisor(
        array $user,
        Collection $allUsers
    ): ?array {
        $supervisorId = $user[self::USER_SUPERVISOR_ID] ?? null;

        if ($supervisorId === null) {
            return null;
        }

        $supervisor = $allUsers->first(
            fn (array $candidate) =>
                (int) ($candidate[self::USER_ID] ?? 0)
                === (int) $supervisorId
        );

        if (!$supervisor) {
            return null;
        }

        $isOwner = (int) ($supervisor[self::USER_ROLE_ID] ?? 0) === 1;

        return [
            'name' => $isOwner
                ? 'المالك'
                : ($supervisor[self::USER_NAME] ?? 'غير محدد'),

            'is_owner' => $isOwner,
        ];
    }

    /**
     * Command: Resolve bank IDs into bank names.
     */
    private static function resolveBanks(
        array $bankIds,
        Collection $banks
    ): array {
        return collect($bankIds)
            ->map(fn ($bankId) => $banks->get($bankId))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Command: Resolve the action buttons available for a user.
     */
    private static function resolveUserActions(
        bool $isSystem,
        int $roleId
    ): array {
        if ($isSystem) {
            return [];
        }

        if ($roleId === 2) {
            return [
                'profile',
                'team',
                'banks',
                'permissions',
                'edit',
            ];
        }

        return [
            'profile',
            'banks',
            'permissions',
            'edit',
        ];
    }

    /**
     * Command: Return all configured roles with permission and user counts.
     */
    public static function roles(): Collection
    {
        $permissions = self::permissions();

        $permissionIds = static function (array $names) use ($permissions): array {
            return $permissions
                ->whereIn('name', $names)
                ->pluck('id')
                ->values()
                ->all();
        };

        $roles = [
            [
                'id' => 1,
                'name' => 'owner',
                'label' => 'Owner',
                'description' => 'صلاحيات كاملة على النظام',
                'is_system' => true,
                'level' => 100,
                'permission_ids' => $permissions
                    ->pluck('id')
                    ->all(),
            ],

            [
                'id' => 2,
                'name' => 'super_visor',
                'label' => 'Super Visor',
                'description' => 'يشرف على فريق التحصيل ويوزع الحالات',
                'is_system' => false,
                'level' => 50,
                'permission_ids' => $permissions
                    ->reject(
                        fn ($permission) => in_array(
                            $permission->name,
                            [
                                'users.delete',
                                'roles.manage',
                                'banks.manage',
                            ],
                            true
                        )
                    )
                    ->pluck('id')
                    ->values()
                    ->all(),
            ],

            [
                'id' => 3,
                'name' => 'call_center',
                'label' => 'Call Center',
                'description' => 'يتابع العملاء ويسجل الوعود',
                'is_system' => false,
                'level' => 10,
                'permission_ids' => $permissionIds([
                    'clients.view',
                    'clients.edit',
                    'ptp.view',
                    'ptp.manage',
                    'complaints.view',
                    'visits.view',
                    'dcr.view',
                ]),
            ],
        ];

        $usersByRole = self::users()
            ->groupBy('role_id')
            ->map
            ->count();

        return collect($roles)->map(
            function (array $role) use ($usersByRole) {
                $role['users_count'] =
                    $usersByRole->get($role['id'], 0);

                $role['permissions_count'] =
                    count($role['permission_ids']);

                return (object) $role;
            }
        );
    }

    /**
     * Command: Find a single user by numeric ID.
     */
    public static function user(int $id): object
    {
        return self::users()
            ->firstWhere('id', $id)
            ?? abort(404);
    }

    /**
     * Command: Return every non-system employee.
     */
    public static function employees(): Collection
    {
        return self::users()
            ->reject(fn ($user) => $user->is_system)
            ->values();
    }

    /**
     * Command: Return all installment companies.
     */
    public static function installmentCompanies(): Collection
    {
        return collect([
            [
                'id' => 1,
                'name' => 'فاليو',
                'code' => 'VALU',
                'is_active' => true,
                'cases_count' => 120,
                'total_debt' => 840000,
            ],
            [
                'id' => 2,
                'name' => 'سهولة',
                'code' => 'SOHL',
                'is_active' => true,
                'cases_count' => 64,
                'total_debt' => 410000,
            ],
            [
                'id' => 3,
                'name' => 'كونتكت',
                'code' => 'CNTC',
                'is_active' => false,
                'cases_count' => 0,
                'total_debt' => 0,
            ],
        ])->map(
            fn (array $company) => (object) $company
        );
    }

    /**
     * Command: Return all sample clients.
     */
    public static function clients(): Collection
    {
        return collect([
            [
                'id' => 1000001,
                'code' => '1000001',
                'name' => 'مصطفى خالد حسن',
                'phone' => '01080672586',
                'address' => '86 شارع عباس العقاد - فيصل - أسيوط',
            ],
            [
                'id' => 1000002,
                'code' => '1000002',
                'name' => 'حسن زينب الهواري',
                'phone' => '01299928200',
                'address' => '82 شارع الهرم - الدقي - سوهاج',
            ],
            [
                'id' => 1000003,
                'code' => '1000003',
                'name' => 'منى هبة صالح',
                'phone' => '01097389540',
                'address' => '58 شارع رمسيس - المعادي - القاهرة',
            ],
            [
                'id' => 1000004,
                'code' => '1000004',
                'name' => 'إيمان هبة راضي',
                'phone' => '01174820823',
                'address' => '123 شارع البطل أحمد عبدالعزيز - شبرا - الإسماعيلية',
            ],
            [
                'id' => 1000005,
                'code' => '1000005',
                'name' => 'أحمد يوسف توفيق',
                'phone' => '01096103142',
                'address' => '147 شارع الهرم - المهندسين - الدقهلية',
            ],
            [
                'id' => 1000000,
                'code' => '1000000',
                'name' => 'فاطمة زينب السيد',
                'phone' => '01055111338',
                'address' => '117 شارع التسعين - المعادي - المنيا',
            ],
        ])->map(
            fn (array $client) => (object) $client
        );
    }
}
