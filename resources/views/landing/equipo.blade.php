@extends('landing.layouts.landing')
@section('title', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
@section('meta_description', 'Cras tincidunt vel nisl porta hendrerit. Nunc sit amet dolor a odio condimentum maximus. Etiam sollicitudin vulputate mi, in luctus leo auctor vitae.')

@section('content')
@include('landing.menu.headerX')
 

<!-- Banner estatico --> 
<section class="relative w-full h-64 md:h-96 bg-cover bg-center flex items-center justify-center pt-20">

    <div class="absolute inset-0">

        <!-- Imagen para móviles y tablets -->
        <img src="{{ asset('images/equipo/EquipoBanner.jpg') }}" alt="Equipo de profesionales (Vista móvil/tablet)"
            class="w-full h-full object-cover object-top block lg:hidden" loading="lazy">

        <!-- Imagen para escritorio -->
        <img src="{{ asset('images/equipo/EquipoBannerM.jpg') }}" alt="Equipo de profesionales (Vista escritorio)"
            class="w-full h-full object-cover object-center hidden lg:block" loading="lazy">

        <!-- Overlay negro semi-transparente -->
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-white text-3xl md:text-5xl font-bold mb-2">
            Especialistas en Medicos
        </h1>
        <h2 class="text-center text-white font-semibold text-lg">
            Conoce a nuestro equipo
        </h2>
    </div>

</section>

<div class="max-w-7xl mx-auto px-6 py-16">


    <!-- Sección Médicos -->
    <h2 class="text-3xl font-[Poppins] mb-4 text-center">
        Especialistas Medicos
    </h2>
    <p class="text-center lg:text-[18px] text-[16px] mb-12">
        Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/D1.png') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP.<br>Aenean Feugiat</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>

        </div>


        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/D3.png') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP. <br>Curabitur Vitae  </h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>


        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
               <img src="{{ asset('images/equipo/D2.jpg') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP. <br>Nullam a feugiat</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>


        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/D6.jpg') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP. <br>Cras malesuada eros</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>


        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
               <img src="{{ asset('images/equipo/D4.jpg') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP. <br>Quisque ut</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
               <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>


        </div>


        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">

            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
               <img src="{{ asset('images/equipo/D5.jpg') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP. <br>Vivamus maximus</h3>
            </div>

            <div class="px-4 py-4 flex-grow">
               <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">

            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/D3.png') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ESP. <br>Vestibulum quis</h3>
            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">

            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/D2.jpg') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>

            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Esp <br>Donec luctus</h3>
            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Sed eleifend tempus felis et porta. Aenean feugiat cursus interdum. 
                    Praesent auctor lobortis turpis quis ornare. Nullam rhoncus lacus ligula, 
                    non vehicula mauris semper quis. Suspendisse maximus neque luctus, iaculis justo id, lobortis eros. 
                    Maecenas finibus non tortor fringilla feugiat. Etiam sodales massa lectus.
                </p>
            </div>
        </div>

    </div>

    <!-- Sección Enfermeros -->

    <h2 class="text-3xl font-[Poppins] mt-16 mb-4 text-center">Enfermería especializada </h2>
    <p class="text-center lg:text-[18px] text-[16px] mb-12">
        El equipo de enfermería acompaña cada tratamiento para que el proceso sea más cómodo, seguro y cuidado en cada etapa.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E1.png') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>Paola Vidal</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E4.png') }}" alt="DR"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>ANA BENITEZ</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
               <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E3.png') }}" alt="Dra Amairani Romero"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>LILIANA GAMA</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E2.png') }}" alt="Dra Amairani Romero"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">ENF. <br>ALAN NAVARRETE</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E5.jpg') }}" alt="Dra Amairani Romero"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>Erika López</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E2.png') }}" alt="Dra Amairani Romero"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>Gabriela Pérez</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E4.png') }}" alt="Dra Amairani Romero"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>Mario Flores</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
                <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

        <div
            class="bg-white shadow-lg rounded-xl overflow-hidden text-center flex flex-col max-w-sm w-full mx-auto h-full min-h-[550px]">


            <div class="relative w-full aspect-[3/4] overflow-hidden bg-gray-100">
                <img src="{{ asset('images/equipo/E1.png') }}" alt="Dra Amairani Romero"
                    class="absolute inset-0 w-full h-full object-cover object-top" loading="lazy">
            </div>
            <div class="bg-teal-700 p-3 text-white">
                <h3 class="text-xl font-[Cinzel] uppercase">Enf. <br>Mariana</h3>

            </div>

            <div class="px-4 py-4 flex-grow">
               <p class="text-sm font-[Poppins] text-gray-600">
                    Duis eleifend arcu eu sem bibendum, rutrum accumsan nisi congue. 
                    Quisque tempus sed turpis vitae sagittis.
                </p>
            </div>
        </div>

    </div>

    <br>
</div>



<section class="bg-white-100 py-12">
    <div class="grid lg:grid-cols-2 gap-8 mb-20">


        <div class="relative w-full max-w-sm mx-auto rounded-xl overflow-hidden shadow-2xl">

            <div class="relative w-full aspect-[9/16] lg:h-[600px] lg:aspect-auto">
                <video class="absolute top-0 left-0 w-full h-full object-cover" autoplay loop muted playsinline
                    preload="none">
                    <source src="{{ asset('images/equipo/equipo.mp4') }}" type="video/mp4">
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>

        </div>

        <div class="bg-gray-100 p-8 rounded-xl shadow-lg flex flex-col justify-start items-start">
            <p class="uppercase text-sm font-semibold text-gray-500 mb-2"></p>
            <h2 class="text-3xl font-bold text-verdeOscuro mb-4">
                Ventajas de atenderte con especialistas
            </h2>
            <p class="text-gray-500 text-lg mb-6">
                Elegir un equipo médico especializado te permite:
            </p>
            <ul class="list-disc pl-5 ml-0 text-gray-500 text-lg gap-[20px] mb-8">
                <li class="ml-4">
                    Vivamus consequat, sapien id suscipit molesti.
                </li>
                <li class="ml-4">
                   Sed ac elit mi. Sed eget mattis nibh, sit amet tempor sem.
                </li>
                <li class="ml-4">
                    Vivamus vehicula est lorem, mollis lobortis orci interdum non.
                </li>
                <li class="ml-4">
                   Nunc cursus, nisi vitae aliquet commodo, magna lectus ultricies velit, ut laoreet ligula elit sed nunc.
                </li>
            </ul>
            <h3 class="font-semibold lg:text-[22px] text-[18px]">
                Da el siguiente paso con un diagnóstico médico.
            </h3>
            <p class="text-gray-500 text-lg my-4">
                Agenda tu valoración y recibe una recomendación clara, personalizada y basada en criterios médicos.
            </p>
            <a href="/#contacto"
                class="inline-block w-auto bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                ¡Contáctanos ahora!
            </a>
        </div>
    </div>
</section>

<section class="p-20">
    <div class="container mx-auto">
        <h2 class="text-3xl font-[Poppins] mt-16 mb-4 text-center">
            Conoce nuestras clínicas especializadas
        </h2>
        <div class="flex lg:flex-row flex-col justify-center items-center gap-[20px] mb-8">
            <h3 class="font-semibold lg:text-[22px] text-[18px]">
                Clínica 
            </h3>
            <h3 class="font-semibold lg:text-[22px] text-[18px]">
                Clínica 
            </h3>
        </div>
        <div class="flex flex-row justify-center">
            <a href="clinicas/queretaro "
                class="inline-block w-auto bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                Ver clínicas
            </a>
        </div>
    </div>
</section>

<hr class="my-12 border-t border-gray-300">
@include('landing.sections.footer')
@endsection