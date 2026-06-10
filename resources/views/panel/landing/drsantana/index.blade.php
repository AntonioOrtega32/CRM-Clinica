@extends('panel.layouts.panel')

@section('title', 'Panel - Blog Dr. Santana')

@section('content')

    
    @include('panel.landing.drsantana.certificacionesIndex')
    
    @include('panel.landing.drsantana.blogIndex')
        @include('panel.landing.drsantana.claculadoraDR')
    @include('panel.landing.drsantana.galeriaIndex')

@endsection
