<?php

// app/Http/Controllers/UserTeamController.php  (static data)
// "فريق العمل": the employees who report to one supervisor, with their numbers.

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\View\View;

class UserTeamController extends Controller
{
    // GET /users/{user}/team
    public function __invoke(int $user): View
    {
        $supervisor = StaticData::user($user);

        // TODO (DB): real numbers from performance_snapshots / debt_cases / promises_to_pay / payments
        $performance = [
            // user id => cases, promises kept, collected, efficiency %
            3 => ['cases' => 3, 'kept' => 1, 'collected' => 4200,  'efficiency' => 31],
            4 => ['cases' => 3, 'kept' => 2, 'collected' => 15400, 'efficiency' => 68],
        ];

        $members = StaticData::users()
            ->where('supervisor_id', $supervisor->id)
            ->values()
            ->map(function ($member) use ($performance) {
                $stats = $performance[$member->id] ?? ['cases' => 0, 'kept' => 0, 'collected' => 0, 'efficiency' => 0];

                foreach ($stats as $key => $value) {
                    $member->{$key} = $value;
                }

                // green from 70%, yellow from 40%, red below
                $member->tone = $stats['efficiency'] >= 70 ? 'brand' : ($stats['efficiency'] >= 40 ? 'warning' : 'danger');

                return $member;
            });

        return view('users.team', [
            'supervisor' => $supervisor,
            'members'    => $members,
            'stats'      => [
                ['label' => 'أعضاء الفريق',    'value' => $members->count(),                         'unit' => null,   'icon' => 'fa-users',            'color' => 'info'],
                ['label' => 'إجمالي الحالات',  'value' => $members->sum('cases'),                    'unit' => null,   'icon' => 'fa-briefcase',        'color' => 'accent'],
                ['label' => 'إجمالي التحصيل',  'value' => number_format($members->sum('collected')), 'unit' => 'EGP',  'icon' => 'fa-money-bill-wave',  'color' => 'brand'],
                ['label' => 'متوسط الكفاءة',   'value' => number_format((float) $members->avg('efficiency'), 1) . '%', 'unit' => null, 'icon' => 'fa-bolt', 'color' => 'cyan'],
            ],
        ]);
    }
}