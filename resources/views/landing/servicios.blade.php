@extends('landing.layouts.landing')
@section('title', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
@section('meta_description', 'Cras tincidunt vel nisl porta hendrerit. Nunc sit amet dolor a odio condimentum maximus. Etiam sollicitudin vulputate mi, in luctus leo auctor vitae.')

@section('content')
@include('landing.menu.headerX') 
 
<!-- Banner estático -->
<section class="relative w-full h-96 md:h-[32rem] bg-cover bg-center  pt-20"
    style="background-image: url('{{ asset('images/servicios/BannerS.jpg') }}');">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 flex flex-col items-center justify-center h-full gap-[10px]">
        <h1 class="text-white text-3xl md:text-5xl font-bold text-center">
            Tratamientos
        </h1>
        <h2 class="text-center text-white lg:text-[34px] text-[24px] font-semibold">
            La ciencia de recuperar tu mejor versión
        </h2>
        <p class="text-center text-white lg:text-[18px] text-[16px]">
           Curabitur iaculis elementum lacus ut auctor. Aliquam congue auctor fermentum
        </p>
        <div>
            <a href="/#contacto"
                class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                ¡Agenda tu Cita!
            </a>
        </div>
    </div>
</section>

<section id="servicios" class="py-16 px-6 bg-white" x-data="{ open: 'servicio1' }">
    <h2 class="text-center font-semibold mb-4 lg:text-[34px] text-[24px]">
        Tratamientos: opciones médicas personalizadas
    </h2>
    <p class="text-center mb-12 lg:text-[18px] text-[16px]">
        Cada <strong>tratamiento</strong> Curabitur iaculis elementum lacus ut auctor. Aliquam congue auctor fermentum
    </p>
    <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 items-start">
        <div class="flex justify-center w-full">
            <div class="relative w-full max-w-sm mx-auto rounded-xl overflow-hidden shadow-2xl">
                <div class="relative w-full aspect-[9/16] lg:h-[600px] lg:aspect-auto">
                    <video class="absolute top-0 left-0 w-full h-full object-cover" autoplay loop muted playsinline
                        preload="none">
                        <source src="{{ asset('images/servicios/proced.mp4') }}" type="video/mp4">
                        Tu navegador no soporta la reproducción de video.
                    </video>
                </div>
            </div>
        </div>

        <div id="MicroCB" x-data="{ activeTab: 'tab1' }">
            <h3 class="text-3xl md:text-4xl font-[Poppins] text-verdeOscuro mb-6">
                Proin a velit sem. Nullam rutrum tincidunt ex
            </h3>

            <p class="text-verdeOscuro/80 text-lg mb-8">
                et egestas augue tempus elementum. Aenean facilisis,
                ipsum eu ornare venenatis, orci tortor vehicula sem, id rhoncus elit eros vitae elit
                <br /><br />
                <strong>
                    Nulla pellentesque euismod sapien:
                </strong>
            </p>

            <div class="space-y-4">
                <div class="space-y-2">

                    <button @click="activeTab = 'tab1'"
                        class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                        :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab === 'tab1', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab !== 'tab1' }">
                        ● vel ultrices erat tincidunt ut.
                    </button>

                    <button @click="activeTab = 'tab2'"
                        class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                        :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab === 'tab2', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab !== 'tab2' }">
                        ● Maecenas sed metus nisl.
                    </button>

                    <button @click="activeTab = 'tab3'"
                        class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                        :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab === 'tab3', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab !== 'tab3' }">
                        ● Nulla quis sapien iaculis
                    </button>

                    <button @click="activeTab = 'tab4'"
                        class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                        :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab === 'tab4', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab !== 'tab3' }">
                        ● viverra at id dolor.
                    </button>
                </div>

                <div class="mt-6 p-6 border rounded-lg bg-gray-50 shadow-inner">

                    <div x-show="activeTab === 'tab1'" x-transition:enter.duration.500ms>
                        <h4 class="text-xl font-bold text-verdeOscuro mb-2">Pellentesque in aliquet erat.</h4>
                        <p class="text-gray-700 mb-4">
                           Morbi venenatis arcu id facilisis mollis <strong>In scelerisque</strong> vehicula tellus. Proin augue nibh, egestas sed ex ac, faucibus semper sem.
                        </p>
                        <a href="/#contacto"
                            class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                            Me interesa
                        </a>
                    </div>

                    <div x-show="activeTab === 'tab2'" x-transition:enter.duration.500ms>
                        <h4 class="text-xl font-bold text-verdeOscuro mb-2">Suspendisse placerat dui vitae enim sagittis.</h4>
                        <p class="text-gray-700">ut pulvinar nisl interdum. Aliquam feugiat ultricies justo et ullamcorper. 
                            Etiam nec tellus consequat, accumsan velit eu, ullamcorper ex. </p>
                    </div>

                    <div x-show="activeTab === 'tab3'" x-transition:enter.duration.500ms>
                        <h4 class="text-xl font-bold text-verdeOscuro mb-2">Phasellus mollis sollicitudin ipsum eget convallis.</h4>
                        <p class="text-gray-700">Morbi venenatis arcu id facilisis mollis. In scelerisque vehicula tellus. 
                            Proin augue nibh, egestas sed ex ac, faucibus semper sem. 
                            Suspendisse fermentum dui in accumsan lacinia. 
                            Etiam accumsan pharetra mattis. Duis sodales nulla in tellus ultrices varius.</p>
                    </div>

                    <div x-show="activeTab === 'tab4'" x-transition:enter.duration.500ms>
                        <h4 class="text-xl font-bold text-verdeOscuro mb-2">Aliquam erat volutpat.</h4>
                        <p class="text-gray-700">Nam congue consequat nunc, sed suscipit justo tincidunt sollicitudin. 
                            Pellentesque egestas pulvinar metus eu rhoncus. Praesent ut dui id velit semper faucibus in ac erat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br><br><br>
    <hr class="my-12 border-t border-gray-300">
    <br><br><br>

    <div class="max-w-7xl mx-auto">

        <div class="text-center max-w-4xl mx-auto mb-16">
            <h2 class="text-4xl md:text-5xl font-[Poppins] text-verdeOscuro mb-4">
                Nulla facilisi. Aenean et pretium justo.
            </h2>
            <p class="text-lg text-gray-700">
                Maecenas et libero lectus. <strong>Donec pulvinar felis a lectus tristique sollicitudin.</strong> Mauris arcu massa, scelerisque sit amet varius sit amet, auctor eu tortor.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">

            <article
                class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-teal-700 hover:shadow-xl transition duration-300">
                <div class="flex items-center mb-4">

                    <h3 class="font-bold text-xl text-verdeOscuro">
                        Donec viverra nec nunc id tempor.
                    </h3>
                </div>
                <ul class="text-gray-600 list-disc pl-5 space-y-2">
                    <li>Cras cursus quis purus in semper.</li>
                    <li>In vel condimentum ex.</li>
                    <li>Nam nec rutrum ante. </li>
                </ul>
            </article>

            <article
                class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-teal-700 hover:shadow-xl transition duration-300">
                <div class="flex items-center mb-4">

                    <h3 class="font-bold text-xl text-verdeOscuro">
                        Nam nec rutrum ante. 
                    </h3>
                </div>
                <p class="text-gray-600">
                    Nullam lectus augue, convallis sit amet nisi a, porttitor fringilla mauris. Nullam efficitur quam vitae arcu dictum, tincidunt lobortis orci tincidunt.
                </p>
            </article>


            <article
                class="bg-white p-6 rounded-xl lg:col-span-2 shadow-lg border-t-4 border-teal-700 hover:shadow-xl transition duration-300">
                <div class="flex items-center mb-4">

                    <h3 class="font-bold text-xl text-verdeOscuro">
                       Morbi convallis convallis nibh sit amet lobortis.
                    </h3>
                </div>
                <p class="text-gray-600">
                   Vivamus ornare, <strong>quam</strong> in vestibulum lacinia, elit felis porta erat, vitae vehicula nibh sapien eget lectus:
                <ul class="text-gray-600 list-disc pl-5 space-y-2">
                     <li>Cras cursus quis purus in semper.</li>
                    <li>In vel condimentum ex.</li>
                    <li>Nam nec rutrum ante. </li>
                </ul>
                <p class="text-gray-600 mt-2 mb-8">
                   Etiam id sagittis turpis. 
                </p>
                <div>
                    <a href="/tecnologias"
                        class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                        Ver Tecnologías
                    </a>
                </div>
            </article>
        </div>

        <div class="mt-16 text-center">
            <h3 class="text-3xl font-bold text-verdeOscuro mb-6"></h3>
            <img src="{{ asset('images/servicios/SERV1.gif') }}" alt="Instalaciones modernas"
                class="mx-auto rounded-xl shadow-2xl max-w-5xl w-full h-auto object-cover" loading="lazy">
        </div>

    </div>

    <br><br><br>
    <hr class="my-12 border-t border-gray-300">
    <br><br><br>

    <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 items-start">

        <div class="**lg:order-first**" x-data="{ activeTab2: 'tabA' }">
            <h2 class="text-3xl md:text-4xl font-[Poppins] text-verdeOscuro mb-6">
                 Suspendisse ornare, orci eu tempor ullamcorper, ante nunc posuere sapien
            </h2>
            <p class="text-verdeOscuro/80 text-lg mb-8">
               sed sollicitudin risus orci vitae elit. Sed lacinia, risus non pharetra feugiat, eros lectus tristique lectus, ullamcorper egestas nisi lorem nec ante. 

            <div class="space-y-2">

                <button @click="activeTab2 = 'tabA'"
                    class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                    :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab2 === 'tabA', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab2 !== 'tabA' }">
                    ●  Phasellus varius
                </button>

                <button @click="activeTab2 = 'tabB'"
                    class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                    :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab2 === 'tabB', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab2 !== 'tabB' }">
                    ● arcu sit amet tincidunt
                </button>

                <button @click="activeTab2 = 'tabC'"
                    class="w-full text-left p-4 font-semibold rounded-lg transition duration-200 border-l-4"
                    :class="{ 'bg-teal-50 border-teal-700 text-verdeOscuro shadow-md': activeTab2 === 'tabC', 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50': activeTab2 !== 'tabC' }">
                    ● ultrices id ultrices nec
                </button>

            </div>

            <div class="mt-6 p-6 border rounded-lg bg-gray-50 shadow-inner">
                <div x-show="activeTab2 === 'tabA'" x-transition:enter.duration.500ms>
                    <h4 class="text-xl font-bold text-verdeOscuro mb-2">Medicina Regenerativa</h4>
                    <!-- <p class="text-gray-700">•Terapia androgenética.</p> -->
                    <p class="text-gray-700">•Condimentum.</p>
                    <p class="text-gray-700">•Praesent eleifend fermentum.</p>
                </div>

                <div x-show="activeTab2 === 'tabB'" x-transition:enter.duration.500ms>
                    <h4 class="text-xl font-bold text-verdeOscuro mb-2">Aliquam sit</h4>
                    <p class="text-gray-700">•Suspendisse.</p>
                    <p class="text-gray-700">•Aliquam.</p>
                    <p class="text-gray-700">•Curabitur.</p>
                </div>

                <div x-show="activeTab2 === 'tabC'" x-transition:enter.duration.500ms>
                    <h4 class="text-xl font-bold text-verdeOscuro mb-2">Phasellus eleifend</h4>
                    <p class="text-gray-700">•Libero quis efficitur efficitur.</p>
                    <P class="text-gray-700">•Lacus odio imperdiet leo.</P>
                    <P class="text-gray-700">•Vitae accumsan quam mauris.</P>
                </div>
            </div>
        </div>

        <div class="relative w-full max-w-md mx-auto rounded-xl overflow-hidden shadow-2xl">
            <!-- Móvil: 9/16, Desktop: altura fija 600px -->
            <div class="relative w-full aspect-[9/16] lg:h-[600px]">

                <!-- Carrusel Swiper -->
                <div class="swiper mySwiper w-full h-full">
                    <div class="swiper-wrapper">

                        <!-- Slides -->
                        <div class="swiper-slide">
                            <img src="{{ asset('images/servicios/S1.jpg') }}" alt="S4"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>

                        <div class="swiper-slide">
                            <img src="{{ asset('images/servicios/S2.jpg') }}" alt="S1"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>

                        <div class="swiper-slide">
                            <img src="{{ asset('images/servicios/S3.png') }}" alt="S2"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>

                        <div class="swiper-slide">
                            <img src="{{ asset('images/servicios/S4.jpg') }}" alt="S3"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>

                    </div>

                    <!-- Paginación -->
                    <div class="swiper-pagination !bottom-3"></div>

                    <!-- Flechas -->
                    <div class="swiper-button-prev !text-[#1C6C73] !w-10 !h-10"></div>
                    <div class="swiper-button-next !text-[#1C6C73] !w-10 !h-10"></div>
                </div>
            </div>
        </div>

    </div>




    </div>
</section>

<section class="lg:p-20 py-20 px-8">
    <div class="container mx-auto">
        <h2 class="text-center lg:text-[34px] text-[24px] font-semibold">
            Clínicas especializadas
        </h2>
        <p class="text-center lg:text-[18px] text-[16px]">Curabitur ultricies dui ut elit gravida, ac bibendum libero vulputate. Proin ultrices aliquet magna, nec euismod leo mollis consequat. Nuestros tratamientos se realizan en clínicas capilares equipadas con tecnología médica avanzada y personal certificado, garantizando seguridad, privacidad y altos estándares clínicos.
        </p>
        <div class="flex lg:flex-row flex-col justify-center items-center gap-[20px] my-10">
            <div class="p-[20px] rounded-[20px] bg-teal-50 lg:w-1/2 w-full">
                <h3 class="text-center font-semibold lg:text-[22px] text-[18px]">
                    Clínica en CDMX
                </h3>
                <p class="text-center lg:text-[18px] text-[16px] mt-2">
                    Atención médica especializada.
                </p>
            </div>
            <div class="p-[20px] rounded-[20px] bg-teal-50 lg:w-1/2 w-full">
                <h3 class="text-center font-semibold lg:text-[22px] text-[18px]">
                    Clínica Capilar
                </h3>
                <p class="text-center lg:text-[18px] text-[16px] mt-2">
                    Instalaciones diseñadas para diagnóstico, tratamiento y seguimiento médico personalizado para pacientes del Bajío y otras regiones.
                </p>
            </div>
        </div>
        <div class="flex flex-row justify-center">
            <a href="clinicas/queretaro "
                class="inline-block w-auto bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                Ver clínicas
            </a>
        </div>

        <div class="mt-12">
            <h2 class="text-center lg:text-[34px] text-[24px] font-semibold">
                Un tratamiento diseñado para ti
            </h2>
            <div class="flex flex-row justify-center mt-12">
                <a href="/#contacto"
                    class="inline-block w-auto bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                     ¡Contáctanos! 
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDesktop = window.innerWidth >= 1024;

        new Swiper(".mySwiper", {
            loop: true,
            speed: 800,
            effect: isDesktop ? "fade" : "slide",
            fadeEffect: {
                crossFade: true
            },
            slidesPerView: 1,
            spaceBetween: isDesktop ? 0 : 10,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            autoplay: {
                delay: 3500, 
                disableOnInteraction: false,
            },
        });
    });
</script>


@include('landing.sections.footer')
@endsection