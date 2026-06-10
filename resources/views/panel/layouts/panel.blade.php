<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: false, sidebarOpen: false }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'CRM Clínica Capilar Elite')</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Vite: CSS y JS --}} 
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script src="https://unpkg.com/alpinejs" defer></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- jQuery (para DataTables y AJAX) --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    {{-- Botones de exportación --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

    {{-- DateTime picker para filtros de fecha --}}
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.1/css/dataTables.dateTime.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.5.1/js/dataTables.dateTime.min.js"></script>

    <!-- Swiper para carrusel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

    {{-- FullCalendar --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    <!-- Importar fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@400;500&display=swap"
        rel="stylesheet">
    <!-- Scripts de SweetAlert  -->
    <script src="
        https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.all.min.js
        "></script>
    <link href="
    https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css
    " rel="stylesheet">
    <!-- Flatpickr para seleccionar fechas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Idioma español -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <!-- fontawesome para las estrellas de procedimientos-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    {{-- Estilos personalizados --}}
    <style>
        /* ocultar modales hasta que Alpine los cargue */
        [x-cloak] {
            display: none !important;
        }

        /* Estilos para DataTables */
        .dt-button.bg-green-600 {
            background-color: #16a34a !important;
            color: #fff !important;
        }

        .dt-button.bg-red-600 {
            background-color: #dc2626 !important;
            color: #fff !important;
        }

        body {
            background-color: #DED5CE;
        }
    </style>

</head>

<body class="flex h-screen overflow-hidden">
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content')
    }
});
</script>
    {{-- Sidebar --}}
    @include('panel.layouts.menu')

    {{-- Contenido principal --}}
    <div class="flex flex-col flex-1 overflow-hidden">
        {{-- Topbar --}}
        @include('panel.layouts.topbar')

        {{-- Contenido --}}
        <main class="flex-1 overflow-y-auto p-6 z-index-0">
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <!-- Scripts de swiper para carrusel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Dropzone ese -->
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <!-- Mas Scripts de datatables ajajs-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.1/css/dataTables.dateTime.min.css">

    <script>
        lucide.createIcons();
    </script>
    {{-- Scripts adicionales por vista --}}
    @yield('scripts')
</body>

</html>
