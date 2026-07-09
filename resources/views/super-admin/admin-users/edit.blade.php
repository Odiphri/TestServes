@extends('super-admin.layout')
@section('title', 'Edit Admin User')
@section('subtitle', 'Update role, profile, status, or password.')
@section('content')<form method="POST" action="{{ route('super-admin.admin-users.update', $admin) }}">@csrf @method('PUT') @include('super-admin.admin-users._form', ['button' => 'Save admin'])</form>@endsection
