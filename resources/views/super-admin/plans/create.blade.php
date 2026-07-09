@extends('super-admin.layout')

@section('title', 'Create Plan')
@section('subtitle', 'Define pricing, trial length, included app features, and status.')

@section('content')
@include('super-admin.plans._ai_draft')

<form action="{{ route('super-admin.subscription-plans.store') }}" method="POST">
    @csrf
    @include('super-admin.plans._form', ['button' => 'Create plan'])
</form>
@endsection
