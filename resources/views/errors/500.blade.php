{{-- 500: server error. No technical details are shown to the user (they stay in storage/logs/laravel.log). --}}
@extends('errors.layout')

@section('code', '500')
@section('tone', 'danger')
@section('icon', 'fa-triangle-exclamation')
@section('title', 'حدث خطأ غير متوقع')
@section('message', 'حدث خطأ من جانبنا. تم تسجيل المشكلة ونعمل على إصلاحها. حاول مرة أخرى بعد قليل.')

@section('actions')
    <button type="button" onclick="location.reload()" class="btn btn-primary"><i class="fa-solid fa-rotate-right text-xs"></i> إعادة المحاولة</button>
    <a href="{{ url('/') }}" class="btn btn-secondary"><i class="fa-solid fa-house text-xs"></i> الصفحة الرئيسية</a>
@endsection