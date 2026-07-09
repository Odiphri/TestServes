@extends('super-admin.layout')

@section('title', 'Open Payment Dispute')
@section('subtitle', 'Create a finance investigation case for a payment issue.')

@section('content')
<form method="POST" action="{{ route('super-admin.payment-disputes.store') }}">
    @csrf
    @include('super-admin.payment-disputes._form', ['button' => 'Open dispute'])
</form>
@endsection
