<!-- Topbar -->
<header class="flex items-center justify-between p-4 bg-[#363636] shadow-md">
    <div class="flex items-center space-x-2">
        <button class="lg:hidden" @click="sidebarOpen=true">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <h1 class="text-lg text-gray-200 font-semibold">@yield('title', 'Dashboard')</h1>
    </div>

    <div class="flex items-center space-x-4">
        <!-- Buscador -->
    <!--    <div class="relative">
            <input type="text" x-model="search" placeholder="Buscar..."
                class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <i data-lucide="search"
                class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
        </div>

         Botón Generar Recibos -->
       <!-- <button onclick="window.location.href=''"
            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg shadow transition-all duration-200">
            <i data-lucide="file-text" class="w-5 h-5"></i>
            Generar Recibos
        </button>

         Dark Mode -->
      

        <!-- Avatar Dropdown con icono -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open=!open"
                class="flex items-center space-x-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-8 h-8 text-gray-700 dark:text-gray-200 rounded-full border border-gray-300 p-1"
                    fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" />
                </svg>

                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <div x-show="open" @click.away="open=false" x-transition
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 shadow-lg rounded-md py-2">
                <a href="{{ route('profile.index') }}"
                    class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                    Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
