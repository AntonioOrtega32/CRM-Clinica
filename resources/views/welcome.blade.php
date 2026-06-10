<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clínica Capilar - Injerto Capilar</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.4.1/swiper-bundle.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.4.1/swiper-bundle.min.js"></script>
<style>
  @layer base {
    .bg-verdeOscuro { background-color: #2f6a4f; }
    .text-verdeOscuro { color: #2f6a4f; }
  }
  /* Overlay en video */
  .video-overlay { background: rgba(0,0,0,0.4); }
</style>
</head>
<body class="font-sans overflow-x-hidden">

<!-- HEADER -->
<!-- HEADER MODERNO -->
<header class="fixed top-0 w-full z-50 backdrop-blur-md bg-white/70 shadow-md transition-all duration-300">
  <div class="container mx-auto flex justify-between items-center py-4 px-6">
    <a href="#inicio">
      <img src="logo.png" alt="Logo Clínica Capilar" class="h-12">
    </a>
    <nav>
      <ul class="flex space-x-4 md:space-x-6 items-center">
        <li>
          <a href="#inicio" class="relative px-3 py-2 rounded-lg text-gray-800 font-semibold hover:text-verdeOscuro transition-all duration-300">
            Inicio
            <span class="absolute left-0 bottom-0 w-0 h-1 bg-verdeOscuro rounded-full transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <li>
          <a href="#servicios" class="relative px-3 py-2 rounded-lg text-gray-800 font-semibold hover:text-verdeOscuro transition-all duration-300">
            Servicios
            <span class="absolute left-0 bottom-0 w-0 h-1 bg-verdeOscuro rounded-full transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <li>
          <a href="#testimonios" class="relative px-3 py-2 rounded-lg text-gray-800 font-semibold hover:text-verdeOscuro transition-all duration-300">
            Testimonios
            <span class="absolute left-0 bottom-0 w-0 h-1 bg-verdeOscuro rounded-full transition-all duration-300 group-hover:w-full"></span>
          </a>
        </li>
        <li>
          <a href="#contacto" class="relative px-4 py-2 bg-verdeOscuro text-white rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
            Agenda tu Consulta
          </a>
        </li>
      </ul>
    </nav>
  </div>
</header>

<!-- SECCIÓN DE BIENVENIDA CON VIDEO -->
<!-- SECCIÓN DE BIENVENIDA CON IMAGEN Y DEGRADADO -->
<section id="inicio" class="relative h-screen flex items-center justify-center">
  <!-- Imagen de fondo -->
  <div class="absolute w-full h-full">
    <img src="{{ asset('images/doctor.jpeg') }}" alt="Fondo Clínica Capilar" class="w-full h-full object-cover">
    <!-- Degradado oscuro sobre la imagen -->
    <div class="absolute w-full h-full bg-gradient-to-b from-black/40 via-black/20 to-black/40"></div>
  </div>

  <div class="absolute w-full h-full flex flex-col items-center justify-center text-center text-white px-6">
    <h1 class="text-5xl md:text-6xl font-bold mb-6" data-aos="fade-down">Recupera tu Confianza</h1>
    <p class="text-lg md:text-2xl mb-8" data-aos="fade-up" data-aos-delay="200">Resultados naturales con técnicas avanzadas de injerto capilar.</p>
    <a href="#contacto" class="text-verdeOscuro py-3 px-8 rounded-full shadow-lg transform transition-transform hover:scale-105" data-aos="zoom-in" data-aos-delay="400">Agenda tu Consulta</a>
  </div>
</section>


<!-- SECCIÓN DE SERVICIOS CON PARALLAX -->
<section id="servicios" class="py-20 bg-gray-50 relative overflow-hidden">
  <div class="absolute w-full h-full bg-green-50 top-0 left-0 -z-10 transform scale-150 rotate-6 opacity-30"></div>
  <div class="container mx-auto text-center px-6">
    <h2 class="text-4xl font-bold text-verdeOscuro mb-12" data-aos="fade-up">Nuestros Servicios</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
      <div class="bg-white p-8 rounded-2xl shadow-2xl transform transition-transform hover:scale-105" data-aos="fade-up" data-aos-delay="100">
        <h3 class="text-2xl font-semibold text-verdeOscuro mb-4">Técnica FUE</h3>
        <p class="text-gray-700">Extracción de unidades foliculares individualmente, recuperación rápida y sin cicatrices visibles.</p>
      </div>
      <div class="bg-white p-8 rounded-2xl shadow-2xl transform transition-transform hover:scale-105" data-aos="fade-up" data-aos-delay="200">
        <h3 class="text-2xl font-semibold text-verdeOscuro mb-4">Técnica FUT</h3>
        <p class="text-gray-700">Extracción de una tira de piel con folículos para áreas con mayor pérdida de cabello.</p>
      </div>
      <div class="bg-white p-8 rounded-2xl shadow-2xl transform transition-transform hover:scale-105" data-aos="fade-up" data-aos-delay="300">
        <h3 class="text-2xl font-semibold text-verdeOscuro mb-4">Mesoterapia Capilar</h3>
        <p class="text-gray-700">Tratamientos no invasivos para fortalecer y revitalizar el cabello existente.</p>
      </div>
    </div>
  </div>
</section>

<!-- SECCIÓN DE TESTIMONIOS TIPO CARRUSEL -->
<section id="testimonios" class="py-20 bg-gray-100">
  <div class="container mx-auto text-center px-6">
    <h2 class="text-4xl font-bold text-verdeOscuro mb-12" data-aos="fade-up">Testimonios</h2>
    <div class="swiper mySwiper" data-aos="fade-up" data-aos-delay="100">
      <div class="swiper-wrapper">
        <div class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
          <p class="text-gray-700 mb-4">"El equipo fue increíble. El proceso fue más sencillo de lo que imaginaba."</p>
          <p class="font-semibold text-verdeOscuro">Juan Pérez</p>
        </div>
        <div class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
          <p class="text-gray-700 mb-4">"Resultados naturales y atención personalizada que me hizo sentir seguro."</p>
          <p class="font-semibold text-verdeOscuro">Ana Gómez</p>
        </div>
        <div class="swiper-slide bg-white p-8 rounded-2xl shadow-xl transform transition-transform hover:scale-105">
          <p class="text-gray-700 mb-4">"Mi confianza volvió gracias a ellos. Muy recomendable."</p>
          <p class="font-semibold text-verdeOscuro">Carlos Ruiz</p>
        </div>
      </div>
      <div class="swiper-pagination mt-6"></div>
    </div>
  </div>
</section>

<!-- SECCIÓN DE CONTACTO -->
<section id="contacto" class="py-20" data-aos="fade-up">
  <div class="container mx-auto text-center px-6">
    <h2 class="text-4xl font-bold text-verdeOscuro mb-12">Contáctanos</h2>
    <form action="#" method="POST" class="max-w-xl mx-auto bg-white p-10 rounded-2xl shadow-2xl space-y-6">
      <div>
        <label for="nombre" class="block text-left text-gray-700 mb-2">Nombre</label>
        <input type="text" id="nombre" name="nombre" class="w-full p-3 border border-gray-300 rounded-lg" required>
      </div>
      <div>
        <label for="email" class="block text-left text-gray-700 mb-2">Correo Electrónico</label>
        <input type="email" id="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg" required>
      </div>
      <div>
        <label for="mensaje" class="block text-left text-gray-700 mb-2">Mensaje</label>
        <textarea id="mensaje" name="mensaje" rows="5" class="w-full p-3 border border-gray-300 rounded-lg" required></textarea>
      </div>
      <button type="submit" class="bg-verdeOscuro text-white py-3 px-8 rounded-full shadow-lg transform transition-transform hover:scale-105">Enviar</button>
    </form>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-verdeOscuro text-white py-10">
  <div class="container mx-auto text-center px-6">
    <p>&copy; 2025 Clínica Capilar. Todos los derechos reservados.</p>
    <p>Diseñado con ❤️ para tu bienestar capilar.</p>
  </div>
</footer>

<script>
  AOS.init({ duration: 1000, once: true });
  var swiper = new Swiper(".mySwiper", { loop: true, pagination: { el: ".swiper-pagination", clickable: true, }, autoplay: { delay: 4000 } });
</script>

</body>
</html>
