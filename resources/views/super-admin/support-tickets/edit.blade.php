@extends('super-admin.layout')
@section('title', 'Edit Support Ticket')
@section('subtitle', 'Change assignment, priority, status, or internal notes.')
@section('content')<form method="POST" action="{{ route('super-admin.support-tickets.update', $supportTicket) }}">@csrf @method('PUT') @include('super-admin.support-tickets._form', ['button' => 'Save ticket'])</form>@endsection
