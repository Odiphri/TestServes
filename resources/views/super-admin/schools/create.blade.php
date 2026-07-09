@extends('super-admin.layout')

@section('title', 'Create School')
@section('subtitle', 'Manually add a school, owner, plan, subscription dates, and branding settings.')

@section('content')
<form action="{{ route('super-admin.schools.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('super-admin.schools._form', ['button' => 'Create school'])
</form>
@endsection
