{{-- <x-status-badge type="payment" :status="$p->status" />  : one place for every status label + color --}}
@props(['type', 'status'])

@php
    $maps = [
        'payment'   => ['pending' => ['قيد المراجعة', 'badge-warning'], 'confirmed' => ['مؤكد', 'badge-success'], 'rejected' => ['مرفوض', 'badge-danger']],
        'complaint' => ['open' => ['مفتوحة', 'badge-warning'], 'in_review' => ['قيد المراجعة', 'badge-info'], 'resolved' => ['تم الحل', 'badge-success'], 'rejected' => ['مرفوضة', 'badge-danger'], 'closed' => ['مغلقة', 'badge-neutral']],
        'priority'  => ['low' => ['منخفضة', 'badge-neutral'], 'medium' => ['متوسطة', 'badge-info'], 'high' => ['عالية', 'badge-warning'], 'urgent' => ['عاجلة', 'badge-danger']],
        'visit'     => ['scheduled' => ['مجدولة', 'badge-info'], 'completed' => ['تمت', 'badge-success'], 'missed' => ['فائتة', 'badge-danger'], 'cancelled' => ['ملغاة', 'badge-neutral']],
        'dcr'       => ['draft' => ['مسودة', 'badge-neutral'], 'submitted' => ['مُرسل', 'badge-warning'], 'approved' => ['معتمد', 'badge-success'], 'rejected' => ['مرفوض', 'badge-danger']],
        'ptp'       => ['active' => ['نشط', 'badge-warning'], 'review' => ['قيد المراجعة', 'badge-info'], 'kept' => ['محقق كلياً', 'badge-success'], 'partial' => ['محقق جزئياً', 'badge-info'], 'broken' => ['مكسور', 'badge-danger']],
        'user'      => ['active' => ['نشط', 'badge-success'], 'inactive' => ['متوقف', 'badge-danger'], 'suspended' => ['موقوف', 'badge-warning']],
        'export'    => ['pending' => ['قيد التجهيز', 'badge-warning'], 'completed' => ['جاهز', 'badge-success'], 'failed' => ['فشل', 'badge-danger']],
        'import'    => ['pending' => ['في الانتظار', 'badge-neutral'], 'processing' => ['قيد المعالجة', 'badge-warning'], 'completed' => ['مكتمل', 'badge-success'], 'failed' => ['فشل', 'badge-danger']],
    ];

    [$label, $class] = $maps[$type][$status] ?? [$status, 'badge-neutral'];
@endphp

<span class="badge {{ $class }}">{{ $label }}</span>