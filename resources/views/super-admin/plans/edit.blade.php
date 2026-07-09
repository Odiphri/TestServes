@extends('super-admin.layout')

@section('title', 'Edit Plan')
@section('subtitle', 'Update pricing, trial length, included app features, and availability.')

@section('content')
@include('super-admin.plans._ai_draft')

<form action="{{ route('super-admin.subscription-plans.update', $plan) }}" method="POST">
    @csrf
    @method('PUT')
    @include('super-admin.plans._form', ['button' => 'Save plan'])
</form>
@endsection
