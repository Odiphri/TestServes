@extends('super-admin.layout')
@section('title', 'Create Support Ticket')
@section('subtitle', 'Add a manual support ticket for a school or owner.')
@section('content')<form method="POST" action="{{ route('super-admin.support-tickets.store') }}">@csrf @include('super-admin.support-tickets._form', ['button' => 'Create ticket'])</form>@endsection
