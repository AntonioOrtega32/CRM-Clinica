<footer class="bg-white text-verdeOscuro py-12 px-6">

    <section class="relative w-full h-96 lg:h-screen/2 flex items-center justify-center">

        <div class="absolute inset-0">

            <!-- Imagen para móviles y tablets -->
            <img src="{{ asset('images/equipo/Equipo 2.jpg') }}" alt="Equipo de profesionales (Vista móvil/tablet)"
                class="w-full h-full object-cover object-top block lg:hidden" loading="lazy">

            <!-- Imagen para escritorio -->
            <img src="{{ asset('images/equipo/Equipo1X.jpg') }}" alt="Equipo de profesionales (Vista escritorio)"
                class="w-full h-full object-cover object-center hidden lg:block" loading="lazy">

            <!-- Overlay negro semi-transparente -->
            <div class="absolute inset-0 bg-black/40"></div>
        </div>

        <!-- Contenido -->
        <div class="relative z-10 text-center px-4">
            <h2 class="text-white text-2xl md:text-4xl font-bold">
                Cum sociis natoque penatibus et magnis dis parturient montes.
            </h2>
        </div> 

    </section>

    <br><br><br><br>

    <div class="max-w-7xl mx-auto grid md:grid-cols-3 gap-8">

        <!-- Información de la clínica -->
        <div>
            <h3 class="font-bold text-xl mb-4">Lumina Health</h3>
            <p class="text-sm mb-2">Especialistas en tratamientos complementarios. Resultados
                naturales
                y personalizados.</p>
            <p class="text-sm">Contactanos: </p>
            <p class="text-sm">
                Tel: <a href="" class="text-[#1C6C73] hover:underline">+52 55 1234 5678</a>
            </p>

        </div>

        <!-- Clínicas -->
        <div>
            <h2 class="font-bold text-sm">Lorem ipsum dolor sit amet consectetur adipiscing elit. </h2>
            <p class="text-md my-4">
                 Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
            </p>
            <ul class="space-y-2 text-sm">
                <li class="space-y-1">
                    <h3>
                        <strong>Santa Fe:</strong> Santa Fe - Neque porro quisquam est, qui dolorem ipsum quia dolor sit ame
                    </h3>
                    <a href="clinicas/santafe"
                        class="inline-block bg-[#1C6C73] w-auto hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                        Conocer clínica
                    </a>
                </li>
                <li class="space-y-1">
                    <h3>
                        <strong>placeat:</strong> consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.
                    </h3>
                    <a href="clinicas/pedregal"
                        class="inline-block bg-[#1C6C73] w-auto hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                        Conocer clínica
                    </a>
                </li>
                <li class="space-y-1">
                    <h3>
                        <strong>blanditiis:</strong>  Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore.
                    </h3>
                    <a href="clinicas/queretaro"
                        class="inline-block bg-[#1C6C73] w-auto hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                        Conocer clínica
                    </a>
                </li>
            </ul>
        </div>



    </div>

    <div class="mt-8 border-t border-gray-200 pt-4 text-center text-sm text-gray-500">
         2026
    </div>
</footer>
