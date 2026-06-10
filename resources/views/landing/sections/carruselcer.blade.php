<section id="certificaciones" class="py-20 px-6 bg-white">
    <div class="container mx-auto text-center">
        <h2 class="text-4xl font-bold text-verdeOscuro mb-4" data-aos="fade-up">
            Certificaciones y Respaldo Médico
        </h2>
        <p class="mb-12 lg:text-[18px] text-[16px] font-[Playfair Display]">
            Nuestra <strong>clínica </strong> cumple con las regulaciones sanitarias vigentes y cuenta con certificaciones nacionales e internacionales que respaldan cada procedimiento.
        </p> 

        <!-- Carrusel -->
        <div class="swiper mySwipersCertificaciones" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper items-center">
                <!-- Slides -->
                <!-- Certificación 1 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C1.png" type="image/png">
                        <img src="/images/certificados/C1.png"
                            alt="Certificación 1"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>

                <!-- Certificación 2 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C2.PNG" type="image/png">
                        <img src="/images/certificados/C2.png"
                            alt="Certificación 2"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>

                <!-- Certificación 3 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C3.png" type="image/webp">
                        <img src="/images/certificados/C3.png"
                            alt="Certificación 3"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>

                <!-- Certificación 4 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C4.png" type="image/webp">
                        <img src="/images/certificados/C4.png"
                            alt="Certificación 4"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>

                <!-- Certificación 5 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C5.png" type="image/webp">
                        <img src="/images/certificados/C5.png"
                            alt="Certificación 5"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>

                <!-- Certificación 6 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C6.png" type="image/webp">
                        <img src="/images/certificados/C6.png"
                            alt="Certificación 6"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>

                <!-- Certificación 7 -->
                <div class="swiper-slide flex justify-center">
                    <picture>
                        <source srcset="/images/certificados/C7.png" type="image/webp">
                        <img src="/images/certificados/C7.png"
                            alt="Certificación 7"
                            width="180"
                            height="90"
                            class="w-28 md:w-36 lg:w-44 object-contain transition duration-300"
                            loading="lazy"
                            decoding="async"
                            fetchpriority="low">
                    </picture>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const swiper = new Swiper('.mySwipersCertificaciones', {
            // 1. Configuración base para Móvil (slidesPerView: 2)
            slidesPerView: 2,
            spaceBetween: 20,

            // 2. Clave para el loop infinito de scroll continuo
            loop: true,
            loopFillGroupWithBlank: true,
            speed: 3000,
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },

            // 3. QUITAMOS freeMode de la base, solo se habilita por breakpoint si es necesario.
            // Si quieres un loop infinito CONTINUO, NO uses freeMode: true.

            breakpoints: {
                // sm (640px - Tablet)
                640: {
                    slidesPerView: 3,
                    // freeMode: true, // Si lo usas, puede detener el loop continuo
                },
                // lg (1024px - PC)
                1024: {
                    // Aumentamos slidesPerView para que el loop infinito sea más fluido
                    slidesPerView: 5,
                    // Si usas 5 slides, necesita ver más de 5 en total. 
                    // Al usar 7, aseguras que los 7 slides originales pasen de forma infinita.
                    freeMode: false,
                },
            },
        });
    });
</script>


<style>
    .swiper-slide img:hover {
        transform: scale(1.05);
        transition: transform 0.3s;
    }
</style>