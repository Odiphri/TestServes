@extends('super-admin.layout')
@section('title', 'Edit Demo Request')
@section('subtitle', 'Update status, assignment, and notes.')
@section('content')<form method="POST" action="{{ route('super-admin.demo-requests.update', $demoRequest) }}">@csrf @method('PUT') @include('super-admin.demo-requests._form', ['button' => 'Save request'])</form>@endsection
