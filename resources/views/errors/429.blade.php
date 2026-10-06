{{-- 429: too many requests --}}
@extends('errors.layout')

@php $wait = (int) ($exception->getHeaders()['Retry-After'] ?? 0); @endphp

@section('code', '429')
@section('tone', 'warning')
@section('icon', 'fa-gauge-high')
@section('title', 'طلبات كثيرة جداً')
@section('message', 'أرسلت عدداً كبيراً من الطلبات في وقت قصير. ' . ($wait > 0 ? "انتظر {$wait} ثانية ثم حاول مرة أخرى." : 'انتظر قليلاً ثم حاول مرة أخرى.'))