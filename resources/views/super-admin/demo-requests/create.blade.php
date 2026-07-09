@extends('super-admin.layout')
@section('title', 'Create Demo Request')
@section('subtitle', 'Add a sales lead for later public demo-form integration.')
@section('content')<form method="POST" action="{{ route('super-admin.demo-requests.store') }}">@csrf @include('super-admin.demo-requests._form', ['button' => 'Create request'])</form>@endsection
