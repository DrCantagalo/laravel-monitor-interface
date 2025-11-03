@extends('layouts.master')
@section('title', 'Michel Cantagalo - fullstack dev / web project manager')
@section('description', 'Michel Cantagalo - fullstack dev / web project manager')
@section('meta-image', Vite::asset('resources/images/OG.png'))
@section('main')
    <main>
        INDEX
    </main>
    <script>window.lang = @json(session('lang'));</script>
@endsection