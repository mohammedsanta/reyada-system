{{-- 419: the CSRF token expired (the page stayed open too long) --}}
@extends('errors.layout')

@section('code', '419')
@section('tone', 'warning')
@section('icon', 'fa-hourglass-end')
@section('title', 'انتهت صلاحية الصفحة')
@section('message', 'بقيت الصفحة مفتوحة لفترة طويلة. أعد تحميلها ثم حاول مرة أخرى، ولن تفقد بيانات حسابك.')

@section('actions')
    <button type="button" onclick="location.reload()" class="btn btn-primary"><i class="fa-solid fa-rotate-right text-xs"></i> إعادة تحميل الصفحة</button>
    <a href="{{ url('/') }}" class="btn btn-secondary"><i class="fa-solid fa-house text-xs"></i> الصفحة الرئيسية</a>
@endsection