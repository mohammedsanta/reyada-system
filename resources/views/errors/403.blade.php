{{-- 403: not allowed. Our own messages (abort(403, 'لا يمكن ...')) are shown; Laravel's English default is replaced. --}}
@extends('errors.layout')

@php $custom = trim($exception->getMessage()); @endphp

@section('code', '403')
@section('tone', 'danger')
@section('icon', 'fa-ban')
@section('title', 'غير مصرّح لك')
@section('message', preg_match('/\p{Arabic}/u', $custom) ? $custom : 'ليس لديك صلاحية للوصول إلى هذه الصفحة. إذا كنت تحتاجها تواصل مع المدير.')