{{-- Admin Master Layout --}}
{{-- This file redirects to the main admin layout for compatibility --}}
@extends('layouts.admin')

@section('title', $title ?? 'Admin Dashboard')

@section('content')
@yield('content')
@endsection

@push('styles')
@stack('styles')
@endpush

@push('scripts')
@stack('scripts')
@endpush