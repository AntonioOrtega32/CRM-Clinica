<section id="heroSection"
    class="pt-28 relative min-h-[100dvh] flex flex-col items-center justify-center text-beigeClaro overflow-hidden bg-black transition-all duration-300">

    <div class="absolute w-full h-full overflow-hidden">
        @php
        $horizontal = $encabezados->first()?->video_horizontal;
        $vertical = $encabezados->first()?->video_vertical;

        function isVideo($file)
        {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'ogg']);
        }
        @endphp

        @if ($horizontal)
        @if (isVideo($horizontal))
        <video autoplay muted loop playsinline class="hidden md:block w-full h-full object-cover" preload="none">
            <source src="{{ asset($horizontal) }}" type="video/mp4">
        </video>
        @else
        <img src="{{ asset($horizontal) }}" alt="Banner" class="hidden md:block w-full h-full object-cover"
            loading="lazy" fetchpriority="high">
        @endif
        @endif

        @if ($vertical)
        @if (isVideo($vertical))
        <video autoplay muted loop playsinline class="block md:hidden w-full h-full object-cover" preload="none">
            <source src="{{ asset($vertical) }}" type="video/mp4">
        </video>
        @else
        <img src="{{ asset($vertical) }}" alt="Banner" class="block md:hidden w-full h-full object-cover"
            loading="lazy" fetchpriority="high">
        @endif
        @endif

        <div class="absolute w-full h-full bg-gradient-to-b from-black/40 via-black/20 to-black/40"></div>
    </div>

    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/30 to-black/50"></div>

    <div class="relative z-30 max-w-5xl px-6 md:px-16 text-center mx-auto flex flex-col items-center gap-3 sm:gap-4">

        {{-- Contenido dinámico desde BD (Fuera de uso) --}}
        {{-- <p class="font-[Playfair Display] text-lg sm:text-xl md:text-2xl font-medium leading-snug">
            {{ $encabezados->first()->titulo ?? 'JIJi ajsiad salsa psapl pepsi' }}
        </p>

        <h1 class="font-[Playfair Display] text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-bold leading-tight">
            {{ $encabezados->first()->contenido ?? 'solo masa mdeso aosodako asopas apsde' }}
        </h1>

        <p class="font-[Playfair Display] text-lg sm:text-xl md:text-2xl font-medium leading-snug">
            {{ $encabezados->first()->subtitulo ?? 'aewuyrtg gyudawg' }}
        </p> --}}

        {{-- Contenido estático --}}
        <h1 class="font-[Playfair Display] text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-bold leading-tight">
            Lorem ipsum dolor sit
        </h1>
        <h2 class="font-[Playfair Display] font-semibold leading-tight text-3xl md:text-4xl ">
           Integer bibendum aliquam risus sit amet elementum. Cras cursus.
        </h2>
        <p class="font-[Playfair Display] text-lg sm:text-xl md:text-2xl font-medium leading-snug">
            Sed eu nulla porttitor, egestas lorem id, bibendum sapien. Ut ut rutrum ipsum. Vestibulum a eros vel neque porta commodo nec vel elit.
        </p>
        <br>
        <a href="#contacto"
            class="mt-6 bg-[#1C6C73] text-white px-8 py-3 font-semibold hover:bg-tealOscuro transition rounded-md text-sm sm:text-base">
            AGENDA TU DIAGNÓSTICO
        </a>
    </div>

    <div class="relative z-30 w-full pt-10 pb-8 sm:pt-16 sm:pb-10 px-6 md:px-16">
        <div
            class="max-w-5xl mx-auto flex flex-col sm:flex-row justify-center sm:justify-between gap-3 sm:gap-x-12 text-white font-[Playfair Display] text-sm md:text-base">

            <a href="/servicios" class="flex items-center gap-2 hover:text-teal-400 transition text-center">
                <span class="w-3 h-3 bg-[#1C6C73] rounded-full inline-block"></span>
                Phasellus ut tellus elementum
            </a>

            <a href="/servicios" class="flex items-center gap-2 hover:text-teal-400 transition text-center">
                <span class="w-3 h-3 bg-[#1C6C73] rounded-full inline-block"></span>
                Proin eget lorem ante
            </a>

            <a href="/servicios" class="flex items-center gap-2 hover:text-teal-400 transition text-center">
                <span class="w-3 h-3 bg-[#1C6C73] rounded-full inline-block"></span>
                Fusce iaculis faucibus nibh vel efficitur
            </a>
        </div>
    </div>
</section>