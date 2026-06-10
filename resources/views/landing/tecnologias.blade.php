@extends('landing.layouts.landing')
@section('title', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
@section('meta_description', 'Cras tincidunt vel nisl porta hendrerit. Nunc sit amet dolor a odio condimentum maximus. Etiam sollicitudin vulputate mi, in luctus leo auctor vitae.')

@section('content')
@include('landing.menu.headerX')
 
<!-- Banner estático -->
<section class="relative w-full h-[30rem] md:h-[32rem] bg-cover bg-center pt-32"
    style="background-image: url('{{ asset('images/tecnologias/BannerT.jpg') }}');">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 flex flex-col items-center justify-center h-full container mx-auto gap-[10px]">
        <h1 class="text-white text-2xl md:text-4xl font-bold text-center">
            Tecnología de vanguardia
        </h1>
        <h2 class="text-center text-white lg:text-[34px] text-[24px] font-semibold">
            Diagnóstico preciso y tratamientos personalizados
        </h2>
        <p class="text-center text-white lg:text-[18px] text-[16px]">
            En <strong>Clínica Lumia </strong> utilizamos <strong>tecnología avanzada</strong> para analizar tu caso con exactitud, definir el tratamiento adecuado y optimizar cada procedimiento de forma segura, médica y personalizada.
        </p>
        <div>
            <a href="/#contacto"
                class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                Me interesa agendar
            </a>
        </div>
    </div>
</section>

<!-- Sección de tecnologías -->
<section class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-center font-semibold mb-4 lg:text-[34px] text-[24px]">
        Tecnología aplicada al diagnóstico y tratamiento
    </h2>
    <div class="max-w-7xl mx-auto py-16 px-6 space-y-24">

        <!-- BLOQUE DE TECNOLOGÍA -->
        @php
        $tecnologias = [
        [
        'titulo' => 'In condimentum',
        'subtitulo' => 'felis at gravida ultrices,',
        'video' => 'T1.mp4',
        'puntos' => [
        'nisl arcu pulvinar lacus, a suscipit odio odio vitae urna.',

        'Fusce pulvinar lorem vel ante ultrices.',
        ],
        'ordenVideo' => 'lg:order-1',
        'ordenTexto' => 'lg:order-2',
        ],
        [
        'titulo' => 'Fusce',
        'subtitulo' => 'Praesent convallis lacus id lectus molestie ornare.',
        'video' => 'T2.mp4',
        'puntos' => [
        'Donec dapibus.',
        'Mauris vel venenatis pulvinar, odio odio commodo urna.',
        'Cuenta con respaldo de dermatólogos a nivel internacional.',
        ],
        'ordenVideo' => 'lg:order-2',
        'ordenTexto' => 'lg:order-1',
        ],
        [
        'titulo' => 'Vivamus luctus',
        'subtitulo' => 'Vestibulum id faucibus magna.',
        'video' => 'T3.mp4',
        'puntos' => [
        'Ut sodales vel ligula vitae placerat.',
        'Suspendisse sagittis finibus lectus',
        'Quis imperdiet lacus.',
        'Etiam dapibus posuere mauris sit amet volutpat.',
        ],
        'ordenVideo' => 'lg:order-1',
        'ordenTexto' => 'lg:order-2',
        ],
        [
        'titulo' => 'Vivamus',
        'subtitulo' => 'Vulputate lacus quis mi feugiat mattis.',
        'video' => 'T4.mp4',
        'puntos' => [
        'Ut sodales vel ligula vitae placerat.',
        'Suspendisse sagittis finibus lectus',
        'Quis imperdiet lacus.',
        'Etiam dapibus posuere mauris sit amet volutpat.',
        ],
        'ordenVideo' => 'lg:order-2',
        'ordenTexto' => 'lg:order-1',
        ],
        [
        'titulo' => 'Morbi',
        'subtitulo' => 'Etiam dapibus posuere mauris sit amet volutpat.',
        'video' => 'T5.mp4',
        'puntos' => [
        'Ut sodales vel ligula vitae placerat.',
        'Suspendisse sagittis finibus lectus',
        'Quis imperdiet lacus.',
        'Etiam dapibus posuere mauris sit amet volutpat.',
        ],
        'ordenVideo' => 'lg:order-1',
        'ordenTexto' => 'lg:order-2',
        ],
        [
        'titulo' => 'Volutpat',
        'subtitulo' => 'Sed eleifend tempus felis et porta.',
        'video' => 'T6.mp4',
        'puntos' => [
        'Ut sodales vel ligula vitae placerat.',
        'Suspendisse sagittis finibus lectus',
        'Quis imperdiet lacus.',
        'Etiam dapibus posuere mauris sit amet volutpat.',
        ],
        'ordenVideo' => 'lg:order-2',
        'ordenTexto' => 'lg:order-1',
        ],
        ];
        @endphp

        @foreach ($tecnologias as $tec)
        <div
            class="flex flex-col {{ $tec['ordenVideo'] === 'lg:order-1' ? 'lg:flex-row' : 'lg:flex-row-reverse' }} gap-8 items-center">
            <!-- Título (solo visible arriba en móvil/tablet) -->
            <h3 class="text-3xl font-[Poppins] text-verdeOscuro mb-4 block lg:hidden text-center">
                {{ $tec['titulo'] }}
            </h3>

            <!-- Video -->
            <div
                class="{{ $tec['ordenVideo'] }} relative w-full max-w-sm mx-auto rounded-xl overflow-hidden shadow-2xl">
                <div class="relative w-full aspect-[9/16] lg:h-[600px] lg:aspect-auto">
                    @if (Str::endsWith($tec['video'], '.mp4'))
                    <video class="absolute top-0 left-0 w-full h-full object-cover" autoplay loop muted
                        playsinline preload="none">
                        <source src="{{ asset('images/tecnologias/' . $tec['video']) }}" type="video/mp4">
                        Tu navegador no soporta la reproducción de video.
                    </video>
                    @else
                    <img src="{{ asset('images/tecnologias/' . $tec['video']) }}" alt="{{ $tec['titulo'] }}"
                        class="absolute inset-0 w-full h-full object-cover object-bottom" loading="lazy">
                    @endif
                </div>
            </div>


            <!-- Texto -->
            <div
                class="{{ $tec['ordenTexto'] }} bg-gray-100 p-8 rounded-xl shadow-lg flex flex-col justify-center text-center lg:text-left">
                <p class="uppercase text-sm font-[Cinzel] text-gray-500 mb-2">{{ $tec['subtitulo'] }}</p>

                <!-- Título (solo visible en escritorio) -->
                <h3 class="text-3xl font-[Poppins] text-verdeOscuro mb-4 hidden lg:block">
                    {{ $tec['titulo'] }}
                </h3>

                @foreach ($tec['puntos'] as $p)
                <p class="text-gray-700 text-lg mb-4">• {{ $p }}</p>
                @endforeach
            </div>
        </div>
        @endforeach

        <h2 class="text-2xl font-[Poppins] text-center text-verdeOscuro mt-24">

        </h2>

    </div>
</section>

<section class="lg:p-20 lg:pt-8">
    <div class="container mx-auto">
        <h2 class="text-center font-semibold mb-4 lg:text-[34px] text-[24px]">
            Tecnología capilar aplicada a nuestros servicios
        </h2>
        <p class="text-center lg:text-[18px] text-[16px]">
            La tecnología que utilizamos está integrada a cada uno de nuestros tratamientos médicos, permitiendo diagnósticos más certeros y procedimientos personalizados según las necesidades de cada paciente.
        </p>
        <div class="mt-8 flex flex-row justify-center">
            <a href="/#servicios"
                class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                Conoce nuestros servicios
            </a>
        </div>
    </div>
</section>
<section class="lg:p-20 lg:pt-8">
    <div class="container mx-auto">
        <h2 class="text-center font-semibold mb-4 lg:text-[34px] text-[24px]">
            El equipo médico detrás de nuestra tecnología capilar
        </h2>
        <p class="text-center lg:text-[18px] text-[16px]">
            La tecnología por sí sola no garantiza resultados. En Clínica Lumia, cada herramienta es utilizada por un equipo médico especializado, capacitado para interpretar los datos, diseñar el tratamiento adecuado y ejecutar cada procedimiento con precisión y ética médica.
        </p>
        <div class="mt-8 flex flex-row justify-center">
            <a href="/equipo"
                class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                Conoce nuestros especialistas
            </a>
        </div>
    </div>
</section>


@include('landing.sections.footer')
@endsection