    @extends('landing.layouts.landing')
    @section('title', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
@section('meta_description', 'Cras tincidunt vel nisl porta hendrerit. Nunc sit amet dolor a odio condimentum maximus. Etiam sollicitudin vulputate mi, in luctus leo auctor vitae.')

    @section('content')
    @include('landing.menu.headerX')
    <!-- Tailwind ya lo tendrás -->
    <!-- Banner estático -->
    <section class="relative w-full h-64 md:h-96 bg-cover bg-center pt-20"
        style="background-image: url('{{ asset('images/clinicas/QBanner.jpg') }}');">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 flex flex-col items-center justify-center h-full">
            <h1 class="text-white text-3xl md:text-5xl font-bold text-center">
                Clínica
            </h1>
            <h2 class="text-center text-white font-semibold mb-4 lg:text-[34px] text-[24px]">
                Atención médica especializada
            </h2>
        </div>
    </section>
    <section class="lg:p-20 py-20 px-8">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-center font-semibold mb-4 lg:text-[34px] text-[24px]">
                Diagnóstico y tratamiento en un entorno médico especializado
            </h2>
            <p class="text-center lg:text-[18px] text-[16px] mb-12">
                Nuestra <strong>clínica </strong> ofrece atención médica en un ambiente tranquilo, moderno y diseñado para la comodidad del paciente. Aquí realizamos diagnósticos, tratamientos médicos y seguimiento personalizado con altos estándares clínicos.
            </p>
            <div class="flex flex-row justify-center">
                <a href="#contacto"
                    class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                    Quiero agendar
                </a>
            </div>
        </div>
    </section>
    <div class="pt-15">
        <!-- Sección: Ubicación -->
        <section class="py-16 px-6 bg-white">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6">
                Ubicación de nuestra clínica semper condimentum
            </h2>
            <p class="text-verdeOscuro/80 mb-6">
                ¡Visítanos en nuestra sede ubicada en neque bibendum!
            </p>
                <div class="w-full h-96 rounded-2xl overflow-hidden shadow-lg">
                    <iframe class="w-full h-full"
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1003.2464572344413!2d-99.27067124325332!3d19.358241719697485!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1ses-419!2smx!4v1780943676561!5m2!1ses-419!2smx" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </section>

        <!-- Sección: Conoce nuestras instalaciones -->
        <section class="py-16 px-6 bg-gray-50">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6">
                Instalaciones enfocadas en atención médica capilar
            </h2>
            <p class="text-verdeOscuro/80 mb-8">
                Nuestra clínica cuenta con espacios diseñados para brindar una experiencia médica ordenada, privada y segura. Cada área está pensada para realizar diagnósticos precisos y acompañar al paciente durante todo su proceso.
                <br /><br />
                En este espacio encontrarás:
            </p>
            <ul class="list-disc inline-block text-left max-w-2xl mx-auto text-verdeOscuro/80 mb-8">
                <li class="mb-2">
                   In maximus ex nec velit euismod vulputate.
                </li>
                <li class="mb-2">
                    Suspendisse massa odio, sagittis pellentesque aliquet id, hendrerit eget mauris.
                </li>
                <li class="mb-2">
                    Curabitur nec velit ut velit ornare mattis.
                </li>
            </ul>

                <!-- Carrusel Swiper -->
                <div class="swiper instalacionesQRO">
                    <div class="swiper-wrapper">
                        <!-- Slide 1 -->
                        <div class="swiper-slide">
                            <img src="{{ asset('images/clinicas/Q1.jpg') }}" alt="Instalaciones Querétaro 1"
                                class="rounded-xl w-full h-[350px] md:h-[650px] object-cover" loading="lazy" />
                        </div>

                        <!-- Slide 2 -->
                        <div class="swiper-slide">
                            <img src="{{ asset('images/clinicas/Q2.jpg') }}" alt="Instalaciones Querétaro 2"
                                class="rounded-xl w-full h-[350px] md:h-[650px] object-cover" loading="lazy" />
                        </div>

                        <!-- Slide 3 -->
                        <div class="swiper-slide">
                            <img src="{{ asset('images/clinicas/Q3.jpg') }}" alt="Instalaciones Querétaro 3"
                                class="rounded-xl w-full h-[350px] md:h-[650px] object-cover" loading="lazy" />
                        </div>

                        <!-- Slide 4 -->
                        <div class="swiper-slide">
                            <img src="{{ asset('images/clinicas/Q4.jpg') }}" alt="Instalaciones Querétaro 4"
                                class="rounded-xl w-full h-[350px] md:h-[650px] object-cover" loading="lazy" />
                        </div>

                    </div>

                    <!-- Paginación -->
                    <div class="swiper-pagination mt-6"></div>

                    <!-- Controles de navegación -->
                    <div class="swiper-button-prev text-verdeOscuro"></div>
                    <div class="swiper-button-next text-verdeOscuro"></div>
                </div>
            </div>
        </section>

        <!-- SwiperJS -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new Swiper('.instalacionesQRO', {
                    loop: true,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    effect: 'slide',
                    speed: 700,
                });
            });
        </script>

        <style>
            .swiper-button-next,
            .swiper-button-prev {
                color: #1C6C73;
                transition: transform 0.2s;
            }

            .swiper-button-next:hover,
            .swiper-button-prev:hover {
                transform: scale(1.2);
            }

            .swiper-pagination-bullet {
                background: #1C6C73 !important;
                opacity: 0.6;
            }

            .swiper-pagination-bullet-active {
                background: #1C6C73 !important;
                opacity: 1;
            }
        </style>


        <!-- Sección: Recorrido a Querétaro -->
        <section class="py-16 px-6 bg-white">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6">
                Conoce la clínica antes de tu visita
            </h2>
            <p class="text-verdeOscuro/80 mb-4">
                Te invitamos a recorrer nuestra clínica y conocer el espacio donde realizamos diagnósticos y tratamientos.
            </p>

                <!-- Contenedor del video responsive -->
                <div class="w-full rounded-2xl shadow-lg overflow-hidden">
                    <div class="relative pb-[56.25%] h-0"> <!-- Mantiene relación 16:9 -->
                        <video class="absolute top-0 left-0 w-full h-full object-contain md:object-cover" controls
                            poster="preview.jpg" preload="none">
                            <source src="{{ asset('images/recorrido/Recorrido2.mp4') }}" type="video/webm">
                            Tu navegador no soporta la reproducción de video.
                        </video>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección: Apartado legal -->
        <section class="py-16 px-6 bg-gray-50">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6">Apartado legal</h2>
                <p class="text-verdeOscuro/80">
                Clínica cumple con todas las regulaciones sanitarias vigentes, asegurando la privacidad y seguridad de tus datos y tratamientos. Todos los procedimientos son realizados por personal certificado y bajo estrictos estándares médicos.
            </p>
            </div>
        </section>
    </div>
    @include('landing.forms.contacto')

    <section class="lg:p-20 py-20 px-8">
        <div class="container mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6 text-center">
                Conoce quién te atiende y cómo podemos ayudarte
            </h2>
            <p class="text-verdeOscuro/80 mb-4 text-center">
                En nuestra clínica capilar en Querétaro, cada tratamiento está respaldado por un equipo médico especializado y opciones diseñadas según tu diagnóstico y tipo de alopecia.
            </p>
            <div class="flex lg:flex-row flex-col justify-center items-center gap-[20px] my-10">
                <div class="p-[20px] flex flex-col justify-between rounded-[20px] bg-teal-50 lg:w-1/2 w-full">
                    <h3 class="text-center font-semibold lg:text-[22px] text-[18px] mb-4">
                        Conoce a nuestros especialistas
                    </h3>
                    <div class="flex flex-row justify-center items-center">
                        <a href="/equipo "
                            class="inline-block w-auto bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                            Ver Equipo
                        </a>
                    </div>
                </div>
                <div class="p-[20px] flex flex-col justify-between rounded-[20px] bg-teal-50 lg:w-1/2 w-full">
                    <h3 class="text-center font-semibold lg:text-[22px] text-[18px] mb-4">
                        Explora nuestros tratamientos
                    </h3>
                    <div class="flex flex-row justify-center items-center">
                        <a href="/servicios "
                            class="inline-block w-auto bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                            Ver Servicios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('landing.sections.footer')

    @endsection