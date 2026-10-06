<?php

// app/Http/Controllers/NotificationController.php  (static data)

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /** Link to a page only if its route exists; otherwise go to the dashboard. */
    private function link(string $name, ...$params): string
    {
        return Route::has($name) ? route($name, ...$params) : route('dashboard');
    }

    // TODO (DB): auth()->user()->notifications
    private function items(): Collection
    {
        return collect([
            ['id' => 1, 'type' => 'payment',      'title' => 'تحصيل جديد بانتظار التأكيد',  'body' => 'إيصال سداد نقدي #4092 بمبلغ 1,500 EGP من المحصل HOOL',   'time' => 'منذ 5 دقائق',  'read' => false, 'url' => $this->link('confirmations.index')],
            ['id' => 2, 'type' => 'ptp',          'title' => 'وعد دفع مستحق اليوم',          'body' => 'حسن زينب الهواري وعد بسداد 2,000 EGP اليوم',             'time' => 'منذ ساعة',      'read' => false, 'url' => $this->link('banks.ptp.index', 1)],
            ['id' => 3, 'type' => 'complaint',    'title' => 'شكوى جديدة',                   'body' => 'تم تسجيل شكوى جديدة من العميل منى هبة صالح',              'time' => 'منذ 3 ساعات',   'read' => false, 'url' => $this->link('banks.complaints.index', 1)],
            ['id' => 4, 'type' => 'distribution', 'title' => 'تم توزيع حالات جديدة',         'body' => 'تم توزيع 24 حالة على فريق Emirates NBD',                  'time' => 'أمس 14:20',     'read' => true,  'url' => $this->link('banks.distribution.index', 1)],
            ['id' => 5, 'type' => 'visit',        'title' => 'زيارة ميدانية فائتة',          'body' => 'لم تتم زيارة العميل أحمد يوسف توفيق في الموعد المحدد',    'time' => 'أمس 11:05',     'read' => true,  'url' => $this->link('banks.visits.index', 1)],
            ['id' => 6, 'type' => 'system',       'title' => 'اكتمل استيراد النطاق',         'body' => 'تم استيراد 6 حالات بنجاح من ملف scope_september.xlsx',     'time' => 'قبل يومين',    'read' => true,  'url' => $this->link('banks.scope.import', 1)],
        ])->map(fn (array $item) => (object) $item);
    }

    // GET /notifications?filter=unread
    public function index(Request $request): View
    {
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';
        $all    = $this->items();

        return view('notifications.index', [
            'items'  => $filter === 'unread' ? $all->where('read', false)->values() : $all,
            'filter' => $filter,
            'unread' => $all->where('read', false)->count(),
        ]);
    }

    // POST /notifications/read-all
    public function readAll(): RedirectResponse
    {
        // TODO (DB): auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'تم تحديد كل الإشعارات كمقروءة (بيانات تجريبية).');
    }

    // POST /notifications/{notification}/read : mark as read, then open the related page
    public function read(int $notification): RedirectResponse
    {
        $item = $this->items()->firstWhere('id', $notification) ?? abort(404);

        return redirect($item->url);
    }
}