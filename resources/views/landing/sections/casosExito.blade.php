<!-- Sección: Casos de éxito -->
<section class="py-20 bg-gradient-to-b from-gray-100 via-white to-gray-100">
    <div class="max-w-6xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-verdeOscuro mb-6">
            ¡Transformaciones reales. Sin filtros. Sin promesas vacías!
        </h2>
 
        <!-- Carrusel -->
       @if ($casos->isEmpty())
    <p>No hay casos de éxito disponibles.</p>
@else
    <div class="swiper mySwipers">
        <div class="swiper-wrapper">

            @foreach ($casos as $cas)
                <div class="swiper-slide bg-white rounded-xl shadow-lg p-6">

                    <picture>
                        <source srcset="{{ str_replace('.jpg', '.webp', $cas->imagen) }}" type="image/webp">
                        <img src="{{ $cas->imagen }}"
                             alt="{{ $cas->titulo }}"
                             width="900"
                             height="600"
                             class="w-full h-auto object-cover rounded-xl shadow-md"
                             loading="lazy"
                             decoding="async"
                             fetchpriority="low">
                    </picture>

                </div>
            @endforeach

        </div>

        <!-- Botones de navegación -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
@endif

    </div>
</section>
<!-- Estilos personalizados -->
<style>
    /* descripción colapsada / expandida */
    .description {
        max-height: 4.5rem;
        /* altura aproximada para ~3 líneas */
        overflow: hidden;
        transition: max-height 0.35s ease-in-out, opacity 0.25s ease-in-out;
        opacity: 1;
    }

    /* cuando esté expandida */
    .description.expanded {
        max-height: 1000px;
        /* suficientemente grande */
    }

    /* conservar saltos de línea */
    .preserve-lines {
        white-space: pre-line;
    }

    /* Paginación (bullets) */
    .swiper-pagination {
        bottom: -30px !important;
        /* bajarlos */
    }

    .swiper-pagination-bullet {
        width: 14px !important;
        height: 14px !important;
        background: #1C6C73 !important;
        opacity: 0.4 !important;
        transition: all 0.25s ease;
    }

    .swiper-pagination-bullet-active {
        width: 18px !important;
        height: 18px !important;
        background: #9d7e4f !important;
        opacity: 1 !important;
    }

    /* Flechas personalizadas */
    .swiper-button-next,
    .swiper-button-prev {
        color: #1C6C73 !important;
        background: rgba(28, 108, 115, 0.06);
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    /* Ajustes responsivos si quieres slides más altos en desktop */
    .swiper-slide img {
        transition: transform .3s ease;
    }
</style>
<style>
    .swiper-pagination {
        display: none !important;
    }
</style>
<!-- Script: inicializar Swiper + Ver más/más abajo -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Swiper con autoHeight para que el contenedor ajuste cuando el contenido cambie
        const swiper = new Swiper(".mySwipers", {
            loop: true,
            autoHeight: true, // <-- muy importante
            slidesPerView: 1,
            spaceBetween: 20,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 3
                },
            }
        });

        // Delegación: manejar clicks en botones "Ver más"
        document.querySelectorAll('.mySwipers').forEach(container => {
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('.ver-mas');
                if (!btn) return;

                // Buscar el slide actual (el botón está dentro de una .swiper-slide)
                const slide = btn.closest('.swiper-slide');
                if (!slide) return;

                const desc = slide.querySelector('.description');
                if (!desc) return;

                // Alternar clase expandida
                desc.classList.toggle('expanded');

                // Cambiar texto del botón
                if (desc.classList.contains('expanded')) {
                    btn.textContent = 'Ver menos';
                } else {
                    btn.textContent = 'Ver más';
                }

                // Esperar la transición y luego actualizar la altura del Swiper
                // Usamos setTimeout con un pequeño retraso para que la transición comience
                setTimeout(() => {
                    // Preferimos updateAutoHeight si existe, si no, fallback a update()
                    if (typeof swiper.updateAutoHeight === 'function') {
                        try {
                            swiper.updateAutoHeight(
                            300); // opcional: duración del cálculo
                        } catch (err) {
                            swiper.update();
                        }
                    } else {
                        swiper.update();
                    }
                }, 10);
            });
        });

        // Cuando Swiper cambia de slide, podemos asegurarnos que botones reflejen el estado
        swiper.on('slideChange', function() {
            // quitar estado expanded de clones/otras slides para evitar inconsistencias visuales
            document.querySelectorAll('.description.expanded').forEach(el => {
                // si la slide no está activa, colapsarla
                const slide = el.closest('.swiper-slide');
                if (!slide.classList.contains('swiper-slide-active')) {
                    el.classList.remove('expanded');
                    const btn = slide.querySelector('.ver-mas');
                    if (btn) btn.textContent = 'Ver más';
                }
            });
            // actualizar altura por si cambió contenido
            if (typeof swiper.updateAutoHeight === 'function') {
                try {
                    swiper.updateAutoHeight();
                } catch (e) {
                    swiper.update();
                }
            } else {
                swiper.update();
            }
        });
    });
</script>
<!-- SECCIÓN DE TESTIMONIOS TIPO CARRUSEL -->
<section id="testimonios" class="py-20 bg-gradient-to-b from-gray-100 via-white to-gray-100">
    <div class="container mx-auto text-center px-6">
        <h2 class="text-4xl font-bold text-verdeOscuro mb-12" data-aos="fade-up">Testimonios</h2>
        <a href="https://share.google/EceoMN4Qn5XOpjlyq"
            class="inline-block bg-[#1C6C73] hover:bg-[#4298A7] text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300 mb-12">
            ¡Comparte tu experiencia!
        </a>
        <br><br>
        <div class="swiper mySwiper" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper">

                <!-- Testimonio 1 -->
                <div
                    class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
                    <p class="text-gray-700 mb-4">"Quiero expresar mi más sincero agradecimiento al equipo médico que me
                        atendió durante mi procedimiento de injerto capilar. Desde la primera consulta, recibí un trato
                        sumamente profesional, humano y atento, lo cual me brindó una gran tranquilidad y confianza en
                        cada etapa del proceso."</p>
                    <div class="flex justify-center mb-4">
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                    </div>
                    <p class="font-semibold text-verdeOscuro">Lancelot Romero</p>
                </div>

                <!-- Testimonio 2 -->
                <div
                    class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
                    <p class="text-gray-700 mb-4">"Very happy and satisfied with everything, they quote me for 4k
                        implants and they implanted over 6k hairs, highly recommend. Muy contento y satisfecho con todo
                        el procedimiento. Me cotisaron 4k injertos y al final me pusieron más de 6k. Un excelente equipo
                        se los recomiendo."</p>
                    <div class="flex justify-center mb-4">
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                    </div>
                    <p class="font-semibold text-verdeOscuro">Adrian Paco</p>
                </div>

                <!-- Testimonio 3 -->
                <div
                    class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
                    <p class="text-gray-700 mb-4">"Después de casi un año de meditarlo y visitar otras clínicas, el
                        trato y la calidad del servicio de esta clínica me parece insuperable. La Dra. Oriana, el Dr.
                        Joaquín y la Dra. Samanta son increíblemente profesionales aparte de todo el staff. Tengo 24 hrs
                        con mi injerto y todo ha salido de maravilla, estoy sumamente contento con los reyes del
                        injerto. Sigan así, les daré una actualización conforme vaya avanzando en los cuidados y
                        recomendaciones."</p>
                    <div class="flex justify-center mb-4">
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                    </div>
                    <p class="font-semibold text-verdeOscuro">Carlos Ruiz</p>
                </div>

                <!-- Testimonio 4 -->
                <div
                    class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
                    <p class="text-gray-700 mb-4">"La verdad, en general, una excelente atención desde un inicio,
                        siempre un trato muy cordial y amable, te explican todos los detalles necesarios y te resuelven
                        todas las dudas, quiero destacar a la Dra. Amairani Romero quien tuvo una dedicación y atención
                        de primer nivel desde el inicio de mi procedimiento hasta la conclusión del mismo."</p>
                    <div class="flex justify-center mb-4">
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                    </div>
                    <p class="font-semibold text-verdeOscuro">Emmanuel Enriquez</p>
                </div>

                <!-- Testimonio 5 -->
                <div
                    class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
                    <p class="text-gray-700 mb-4">"100% satisfecho y contento. Un equipo que te hace sentir en
                        confianza, te sabe explicar detalle por detalle. La verdad, mil gracias por su trabajo, 100%
                        recomendado."</p>
                    <div class="flex justify-center mb-4">
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                        <span class="text-yellow-400 text-lg">★</span>
                    </div>
                    <p class="font-semibold text-verdeOscuro">Martin Arizaga Olvera</p>
                </div>

            </div> <!-- Botones de navegación -->

            <!-- Paginación -->
            <div class="swiper-pagination mt-6"></div>
        </div>
    </div>
</section>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
 

<script>
    const swiper = new Swiper(".mySwiper", {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 3
            },
        }
    });
</script>
