@extends('super-admin.layout')

@section('title', 'Edit Payment Dispute')
@section('subtitle', 'Update investigation status, assignment, and finance notes.')

@section('content')
<form method="POST" action="{{ route('super-admin.payment-disputes.update', $paymentDispute) }}">
    @csrf
    @method('PUT')
    @include('super-admin.payment-disputes._form', ['button' => 'Save dispute'])
</form>
@endsection
