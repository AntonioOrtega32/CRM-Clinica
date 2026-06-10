<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Clínica')</title>
    <meta name="description" content="@yield('meta_description', 'Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat.')">


    <link href="https://unpkg.com/flowbite@1.7.0/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/flowbite@1.7.0/dist/flowbite.js"></script>

    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('logos.ico') }}">

   <!-- Google Tag Manager 
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TT34CW5X');</script>
    -->
    <!-- End Google Tag Manager -->


     <!-- Favicon -->
    <link rel="icon" href="{{ asset('logos.ico') }}" type="image/x-icon">

    <!-- Palabras clave (opcional) -->
    <meta name="keywords" content="Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat.">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

     @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Preconexiones para rendimiento -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@400;500&display=swap"
        rel="stylesheet">

    <!-- Flowbite -->
    <link href="https://unpkg.com/flowbite@1.7.0/dist/flowbite.min.css" rel="stylesheet" />

    <meta name="description"
        content="Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat.">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Importar Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        beigeClaro: '#DED5CE',
                        beigeCalido: '#CDAF95',
                        verdeOscuro: '#1C6C73',
                        verdeClaro: '#4298A7',
                        grisCalido: '#C8BAAF',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif']
                    },
                    keyframes: {
                        fadeUp: {
                            '0%': {
                                opacity: 0,
                                transform: 'translateY(12px)'
                            },
                            '100%': {
                                opacity: 1,
                                transform: 'translateY(0)'
                            }
                        },
                        glow: {
                            '0%,100%': {
                                boxShadow: '0 0 0 rgba(0,0,0,0)'
                            },
                            '50%': {
                                boxShadow: '0 8px 24px rgba(28,108,115,.25)'
                            }
                        },
                        floaty: {
                            '0%,100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-4px)'
                            }
                        }
                    },
                    animation: {
                        fadeUp: 'fadeUp 1.2s ease-out both',
                        glow: 'glow 3s ease-in-out infinite',
                        floaty: 'floaty 4s ease-in-out infinite'
                    }
                }
            }
        }
    </script>
    <!-- BOTÓN FLOTANTE DE WHATSAPP -->
    <!-- BOTÓN FLOTANTE DE WHATSAPP CON OPCIONES -->
    <!-- Contenedor flotante -->
    <div class="fixed bottom-6 right-6 flex flex-col items-end space-y-4 z-50">

        <!-- WhatsApp -->
        <div class="flex flex-col items-end">
            <button id="whatsappBtn"
                class="w-14 h-14 bg-green-500 hover:bg-green-600 rounded-full shadow-lg flex items-center justify-center transition transform hover:scale-110">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp"
                    class="w-7 h-7">
            </button>
            <div id="whatsappMenu" class="mb-2 hidden flex-col space-y-2">
                <a href="https://wa.me/" target="_blank"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition">Santa
                    Fe</a>
                <a href="https://wa.me/" target="_blank"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition">Pedregal</a>
                <a href="https://wa.me/" target="_blank"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition">Querétaro</a>
            </div>
        </div>

        <!-- Facebook -->
        <div class="flex flex-col items-end">
            <button id="facebookBtn"
                class="w-14 h-14 bg-blue-600 hover:bg-blue-700 rounded-full shadow-lg flex items-center justify-center transition transform hover:scale-110">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png"
                    alt="Facebook" class="w-7 h-7">
            </button>
            <div id="facebookMenu" class="mb-2 hidden flex-col space-y-2">
                <a href="https://www.facebook.com/" target="_blank"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg transition">Facebook
                    Oficial</a>
            </div>
        </div>

        <!-- Instagram -->
        <div class="flex flex-col items-end">
            <button id="instagramBtn"
                class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition transform hover:scale-110 bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-500">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram"
                    class="w-7 h-7">
            </button>
            <div id="instagramMenu" class="mb-2 hidden flex-col space-y-2">
                <a href="https://www.instagram.com/" target="_blank"
                    class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg shadow-lg transition">Instagram
                    Oficial</a>
            </div>
        </div>

        <!-- TikTok -->
        <!-- TikTok -->
        <div class="flex flex-col items-end">
            <button id="tiktokBtn"
                class="w-14 h-14 bg-white hover:bg-gray-200 rounded-full shadow-lg flex items-center justify-center transition transform hover:scale-110">
                <!-- Ícono TikTok en blanco con filtro invert -->
                <img src="https://upload.wikimedia.org/wikipedia/en/a/a9/TikTok_logo.svg" alt="TikTok"
                    class="w-7 h-7 filter invert">
            </button>
            <div id="tiktokMenu" class="mb-2 hidden flex-col space-y-2">
                <a href="https://www.tiktok.com/" target="_blank"
                    class="bg-white hover:bg-gray-800 text-black px-4 py-2 rounded-lg shadow-lg transition">TikTok
                    Oficial</a>
            </div>
        </div>


    </div>

    <script>
        function toggleMenu(btnId, menuId) {
            const btn = document.getElementById(btnId);
            const menu = document.getElementById(menuId);
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }

        toggleMenu('whatsappBtn', 'whatsappMenu');
        toggleMenu('facebookBtn', 'facebookMenu');
        toggleMenu('instagramBtn', 'instagramMenu');
        toggleMenu('tiktokBtn', 'tiktokMenu');
    </script>




</head>

<body class="font-poppins bg-white text-verdeOscuro overflow-x-hidden">
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TT34CW5X"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

    
    @yield('content')

</body>

</html>
