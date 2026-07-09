@extends('super-admin.layout')
@section('title', 'Create Admin User')
@section('subtitle', 'Add an internal platform staff account.')
@section('content')<form method="POST" action="{{ route('super-admin.admin-users.store') }}">@csrf @include('super-admin.admin-users._form', ['button' => 'Create admin'])</form>@endsection
