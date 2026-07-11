@extends('super-admin.layout')
@section('title', 'Edit Payment')
@section('subtitle', 'Update payment details and notes.')
@section('content')
<form method="POST" action="{{ route('super-admin.payments.update', $payment) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('super-admin.payments._form', ['button' => 'Save payment'])</form>
@endsection
