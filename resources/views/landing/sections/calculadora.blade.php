<section id="calculadora" class="py-28 px-6 bg-gradient-to-r from-gray-100 via-white to-gray-100">
    <div class="max-w-6xl mx-auto text-center mb-16">
        <h2 class="text-5xl font-[Poppins] text-[#1C6C73]">
           Lorem ipsum dolor sit amet consectetur adipiscing elit. 
        </h2>
        <p class="text-xl text-[#4298A7] mt-4 font-[Cinzel]">
            Quisque faucibus ex sapien vitae pellentesque sem placerat:
            <br /> 
            <strong>Tempus leo eu aenean sed diam urna tempor.</strong>
        </p>
        @include('landing.sections.casosExito') <!-- Nueva sección -->
    </div>
    <div>
        <p class="text-center lg:text-[18px] text-[16px]">
           Lorem ipsum dolor sit amet consectetur adipiscing elit.
        </p>
    </div>
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-14 px-6  my-4">
        @foreach ($resultados as $resultado)
        <div class="shadow-2xl rounded-3xl p-14 flex flex-col md:flex-row items-center justify-between hover:scale-105 transition-transform duration-300"
            style="background: linear-gradient(to bottom right, {{ $resultado->color }}20, #ffffff);">

            <div class="mb-6 md:mb-0 text-center">
                <h3 class="text-2xl font-[Poppins] text-gray-700">{{ $resultado->titulo }}</h3>
                <p class="text-5xl font-[Poppins] mt-6 counter" data-target="{{ $resultado->numero }}">0</p>
            </div>

            <div class="w-28 h-28 rounded-full flex items-center justify-center bg-white shadow-lg">
                @if ($resultado->icono_svg)
                <img src="{{ asset($resultado->icono_svg) }}" alt="Icono" class="w-28 h-28 object-contain rounded-full">
                @else
                <span class="text-gray-400">Sin imagen</span>
                @endif
            </div>

        </div>
        @endforeach
    </div>
    <div>
        <p class="text-center lg:text-[18px] text-[16px] mb-4">
            Placerat in id cursus mi pretium tellus duis.
        </p>
        <div class="flex flex-row justify-center">
            <a href="#contacto"
                class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
                ¡Contáctanos!
            </a>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll(".counter");

        counters.forEach(counter => {
            counter.innerText = "0";
            const target = +counter.getAttribute("data-target");

            let current = 0;
            const duration = 2000; // Duración total del conteo en ms (2 segundos)
            const stepTime = 15; // Cada cuánto se actualiza el contador
            const steps = Math.ceil(duration / stepTime);
            const increment = target / steps;

            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.innerText = Math.ceil(current).toLocaleString();
                    setTimeout(updateCounter, stepTime);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };

            updateCounter();
        });
    });
</script>