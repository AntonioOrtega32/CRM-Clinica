<!-- Blog Section -->
<section id="blog" class="py-20 bg-gradient-to-b from-gray-100 via-white to-gray-100">
  <div class="container mx-auto px-4 text-justify">
    <h2 class="text-4xl font-extrabold mb-14 text-center text-[#1C6C73]">
      Preguntas Frecuentes
    </h2>

    <!-- Carrusel -->
    <div class="swiper mySwiper">
      <div class="swiper-wrapper">
        @foreach ($blogs as $blog)
          <div class="swiper-slide" style="width: 360px;"> <!-- ancho mayor -->
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden transform transition duration-500 hover:scale-105 card flex flex-col h-full">

              <!-- Imagen -->
              @if($blog->imagen)
                <img src="{{ asset($blog->imagen) }}" alt="{{ $blog->titulo }}" class="w-full h-48 object-cover">
              @endif

              <div class="p-6 flex flex-col h-full">
                <!-- Título -->
                <h3 class="text-2xl font-semibold mb-3 text-[#4298A7]">
                  {{ $blog->titulo }}
                </h3>

                <!-- Contenido completo -->
                <div class="text-gray-700">
                  <p>{{ $blog->contenido }}</p>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Controles -->
      <div class="flex justify-center mt-6 space-x-4">
        <div class="swiper-button-prev !text-[#1C6C73]"></div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next !text-[#1C6C73]"></div>
      </div>
    </div>
  </div>
</section>

<!-- SwiperJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
  const swiper = new Swiper('.mySwiper', {
    slidesPerView: 'auto',
    spaceBetween: 20,
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });
</script>
