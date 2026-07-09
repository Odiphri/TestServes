@extends('super-admin.layout')

@section('title', 'School Settings')
@section('subtitle', 'Update school details, owner contacts, subscription status, and portal branding.')

@section('content')
<form action="{{ route('super-admin.schools.update', $school) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('super-admin.schools._form', ['button' => 'Save settings'])
</form>
@endsection
