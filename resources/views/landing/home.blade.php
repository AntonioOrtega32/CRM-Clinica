@extends('landing.layouts.landing')

@section('title', 'Clínica Capilar Elite | Especialistas en Injerto Capilar')
@section('meta_description', 'Clínica capilar especializada en injerto capilar, barba y terapias regenerativas. Diagnóstico médico avanzado en CDMX y Querétaro. ¡Agenda ya!')

@section('content')
<!-- Menú -->
@include('landing.menu.header')

<!-- Hero -->
@include('landing.sections.hero')
@include('landing.sections.carruselcer')
@include('landing.sections.conocenos')
@include('landing.sections.calculadora')

<!-- SECCIÓN DE TESTIMONIOS -->
@include('landing.sections.testimonios')


<!-- Formulario de contacto -->
@include('landing.forms.contacto')
<section class="py-16 px-6 bg-gray-50">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6">Apartado legal</h2>
        <p class="text-verdeOscuro/80">
           Lorem ipsum dolor sit amet consectetur adipiscing elit. 
           Quisque faucibus ex sapien vitae pellentesque sem placerat. 
           In id cursus mi pretium tellus duis convallis. 
           Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. 
           Iaculis massa nisl malesuada lacinia integer nunc posuere. 
           Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.
            <br>
            <br>

             <!--
        <p>
           
    <a href="{{ asset('terminos-condiciones-msi-cdmx.pdf') }}" target="_blank">
        Términos y condiciones.
    </a>
</p>

<p>
    <a href="{{ asset('aviso-privacidad-2025.pdf') }}" target="_blank">
        Aviso de privacidad.
    </a>
</p>
-->


        </p>
    </div>
</section>
@include('landing.sections.footer')
@endsection