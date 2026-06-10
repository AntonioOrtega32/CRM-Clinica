@extends('panel.layouts.panel')

@section('title', 'Resultados de la Landing')


@section('content')
    @include('panel.landing.contenido.calculadora')
    @include('panel.landing.contenido.encabezado')
    @include('panel.landing.contenido.casosExito') 
@endsection
 