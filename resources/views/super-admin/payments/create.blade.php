@extends('super-admin.layout')
@section('title', 'Create Payment')
@section('subtitle', 'Record a manual payment without gateway automation.')
@section('content')
<form method="POST" action="{{ route('super-admin.payments.store') }}" enctype="multipart/form-data">@csrf @include('super-admin.payments._form', ['button' => 'Create payment'])</form>
@endsection
