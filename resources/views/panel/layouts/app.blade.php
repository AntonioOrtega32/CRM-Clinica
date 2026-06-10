<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel CRM')</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-beigeClaro text-tealOscuro font-sans min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-tealOscuro text-white shadow p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <span class="font-bold text-lg">CRM Panel</span>
            <ul class="flex space-x-4">
                <li><a href="#" class="hover:text-beigeMedio">Inicio</a></li>
             
            </ul>
        </div>
    </nav>

    {{-- Contenido principal --}}
    <main class="flex-1 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-beigeNeutro text-tealOscuro p-4 text-center">
        &copy; {{ date('Y') }} Clínica Capilar Elite. Todos los derechos reservados.
    </footer>

</body>
</html>
