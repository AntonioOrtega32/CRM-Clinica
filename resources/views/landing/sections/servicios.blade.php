<section id="certificaciones" class="py-20 px-6 bg-white">
    <div class="container mx-auto text-center">
        <h2 class="text-4xl font-bold text-verdeOscuro mb-12">Nuestras Certificaciones</h2>

        <!-- Carrusel tipo cinta -->
        <div class="overflow-hidden relative">
            <div id="carouselTrack" class="flex gap-8 transition-transform duration-300">
                <div class="flex-shrink-0 w-28 md:w-36 lg:w-44">
                    <img src="{{ asset('images/certificados/cert1.webp') }}" alt="Certificación 1"
                        class="w-full object-contain">
                </div>
                <div class="flex-shrink-0 w-28 md:w-36 lg:w-44">
                    <img src="{{ asset('images/certificados/cert2.png') }}" alt="Certificación 2"
                        class="w-full object-contain">
                </div>
                <div class="flex-shrink-0 w-28 md:w-36 lg:w-44">
                    <img src="{{ asset('images/certificados/cert3.jpg') }}" alt="Certificación 3"
                        class="w-full object-contain">
                </div>
                <div class="flex-shrink-0 w-28 md:w-36 lg:w-44">
                    <img src="{{ asset('images/certificados/cert4.webp') }}" alt="Certificación 4"
                        class="w-full object-contain">
                </div>
                <div class="flex-shrink-0 w-28 md:w-36 lg:w-44">
                    <img src="{{ asset('images/certificados/cert5.webp') }}" alt="Certificación 5"
                        class="w-full object-contain">
                </div>
                <div class="flex-shrink-0 w-28 md:w-36 lg:w-44">
                    <img src="{{ asset('images/certificados/cert6.webp') }}" alt="Certificación 6"
                        class="w-full object-contain">
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const track = document.getElementById('carouselTrack');

    // Clonar los slides para loop infinito
    track.innerHTML += track.innerHTML;

    let position = 0;
    let speed = 1; // píxeles por frame

    function animate() {
        position -= speed;
        if (position <= -track.scrollWidth / 2) {
            position = 0;
        }
        track.style.transform = `translateX(${position}px)`;
        requestAnimationFrame(animate);
    }

    animate();

    // Responsivo: ajustar ancho de slides si quieres puedes añadir lógica para cambiar speed según screen width
</script>
