{{-- 503: maintenance mode (php artisan down). Does not depend on the database or on any route. --}}
@extends('errors.layout')

@section('code', '503')
@section('tone', 'info')
@section('icon', 'fa-screwdriver-wrench')
@section('title', 'النظام تحت الصيانة')
@section('message', 'نقوم بتحديث النظام الآن لنقدم لك خدمة أفضل. سنعود خلال دقائق، شكراً لصبرك.')

@section('actions')
    <button type="button" onclick="location.reload()" class="btn btn-primary"><i class="fa-solid fa-rotate-right text-xs"></i> تحديث الصفحة</button>
@endsection