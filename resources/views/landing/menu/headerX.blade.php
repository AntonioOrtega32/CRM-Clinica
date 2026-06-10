<header id="mainHeader" class="fixed top-0 left-0 w-full z-50 md:z-60">
     <nav
    class="bg-transparent backdrop-blur-sm border-b border-white/40 text-white shadow-none transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-4 lg:px-6 py-4 flex items-center justify-between gap-4">

        <!-- Logo -->
        <a href="/"
            class="flex items-center gap-4 font-extrabold text-2xl text-white hover:scale-105 transform transition-all duration-300">
            <img src="{{ asset('images/logos.png') }}" alt="Logo"
                class="h-14 sm:h-16 md:h-14 lg:h-20 w-auto object-contain" loading="lazy">
        </a>
 
            <!-- Menú Desktop -->
            <ul class="hidden md:flex items-center gap-8 text-md font-bold text-white">
                <li><a href="/"
                        class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Inicio</a></li>
                <li><a href="{{ route('landing.servicios') }}"
                        class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Servicios</a></li>
                <li><a href="{{ route('landing.tecnologias') }}"
                        class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Tecnologías</a></li>
                <li><a href="{{ route('landing.equipo') }}"
                        class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Equipo</a></li>

                <!-- Dropdown Clínicas Desktop -->
                <li class="relative" id="desktopClinicas">
                    <button
                        class="flex items-center gap-1 hover:text-beigeCalido transition-all duration-300 hover:scale-105">
                        Clínicas ▼
                    </button>
                    <ul id="desktopClinicasList"
                        class="absolute left-1/2 -translate-x-1/2 mt-3 w-60 bg-white/10 backdrop-blur-md border border-white/30 text-white rounded-3xl shadow-2xl opacity-0 pointer-events-none transition-all duration-300 transform -translate-y-2">
                        <li><a href="{{ route('landing.santafe') }}"
                                class="block px-5 py-3 hover:bg-white/20 rounded-xl transition-all duration-300">Santa
                                Fe</a></li>
                        <li><a href="{{ route('landing.pedregal') }}"
                                class="block px-5 py-3 hover:bg-white/20 rounded-xl transition-all duration-300">Limus</a>
                        </li>
                        <li><a href="{{ route('landing.queretaro') }}"
                                class="block px-5 py-3 hover:bg-white/20 rounded-xl transition-all duration-300">Watra</a>
                        </li>
                    </ul>
                </li>
            </ul>

             <!-- Botón blanco -->
            <a href="{{ route('landing.index') }}#contacto"
                class="hidden md:inline-block border border-white text-white 
                    px-4 py-2 md:px-5 md:py-2.5 lg:px-6 lg:py-3
                    text-sm md:text-sm lg:text-base text-center
                    font-bold shadow-lg hover:bg-white hover:text-black
                    transition-transform transform hover:-translate-y-1 hover:scale-105">
                Agenda tu Cita
            </a>

            <!-- Botón menú móvil -->
            <button id="menuBtn"
                class="md:hidden text-3xl text-white hover:scale-110 transition-transform relative z-50">☰</button>
        </div>

        <!-- Menú móvil -->
        <div id="mobileMenu"
            class="fixed top-0 left-0 w-full bg-white backdrop-blur-xl duration-300 z-50 scale-0 opacity-0 overflow-auto">
            <div
                class="max-w-7xl mx-auto px-6 pt-32 flex flex-col items-center space-y-6 text-2xl text-black h-auto justify-start">

                <a href="/" class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Inicio</a>
                <a href="{{ route('landing.servicios') }}"
                    class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Servicios</a>
                <a href="{{ route('landing.tecnologias') }}"
                    class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Tecnologías</a>
                <a href="{{ route('landing.equipo') }}"
                    class="hover:text-beigeCalido transition-all duration-300 hover:scale-105">Equipo</a>

                <!-- Dropdown Clínicas Móvil -->
                <div class="relative w-full max-w-xs">
                    <button
                        class="accordion-btn w-full py-3 bg-beigeCalido text-verdeOscuro rounded-3xl font-bold shadow-lg hover:scale-105 transition-transform">
                        Clínicas <span class="arrow transition-transform">▼</span>
                    </button>
                    <div class="accordion-content max-h-0 overflow-hidden transition-all duration-300">
                        <a href="{{ route('landing.santafe') }}"
                            class="block py-2 pl-6 hover:text-beigeCalido transition-all duration-300">Santa Fe</a>
                        <a href="{{ route('landing.pedregal') }}"
                            class="block py-2 pl-6 hover:text-beigeCalido transition-all duration-300">Limus</a>
                        <a href="{{ route('landing.queretaro') }}"
                            class="block py-2 pl-6 hover:text-beigeCalido transition-all duration-300">Watra</a>
                    </div>
                </div>
            </div>

            <!-- Botón cerrar -->
            <button id="closeMobileMenu"
                class="absolute top-6 right-6 text-5xl text-beigeClaro hover:scale-110 transition-transform">×</button>
        </div>
    </nav>
</header>

<script>
    // Menú móvil
    const menuBtn = document.getElementById('menuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMenu = document.getElementById('closeMobileMenu');

    menuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('scale-0');
        mobileMenu.classList.toggle('scale-100');
        mobileMenu.classList.toggle('opacity-0');
        mobileMenu.classList.toggle('opacity-100');
        document.body.classList.toggle('overflow-hidden');

        // Ocultar botón menú mientras el menú está abierto
        if (mobileMenu.classList.contains('scale-100')) {
            menuBtn.classList.add('hidden');
        } else {
            menuBtn.classList.remove('hidden');
        }
    });

    closeMenu.addEventListener('click', () => {
        mobileMenu.classList.add('scale-0', 'opacity-0');
        mobileMenu.classList.remove('scale-100', 'opacity-100');
        document.body.classList.remove('overflow-hidden');
        menuBtn.classList.remove('hidden');
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
            mobileMenu.classList.add('scale-0', 'opacity-0');
            mobileMenu.classList.remove('scale-100', 'opacity-100');
            document.body.classList.remove('overflow-hidden');
            menuBtn.classList.remove('hidden');
        }
    });

    // Dropdown Clínicas móvil
    const accordions = document.querySelectorAll('.accordion-btn');
    accordions.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const content = btn.nextElementSibling;
            const arrow = btn.querySelector('.arrow');
            if (content.style.maxHeight && content.style.maxHeight !== "0px") {
                content.style.maxHeight = "0";
                arrow && (arrow.style.transform = "rotate(0deg)");
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                arrow && (arrow.style.transform = "rotate(180deg)");
            }
        });
    });

    // Scroll suave
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Cierra menú móvil si está abierto
            mobileMenu.classList.add('scale-0', 'opacity-0');
            mobileMenu.classList.remove('scale-100', 'opacity-100');
            document.body.classList.remove('overflow-hidden');
            menuBtn.classList.remove('hidden');
        });
    });

    // Dropdown Desktop
    const desktopClinicas = document.getElementById('desktopClinicas');
    const desktopClinicasList = document.getElementById('desktopClinicasList');

    if (desktopClinicas && desktopClinicasList) {
        let dropdownTimeout;

        function openDesktopDropdown() {
            clearTimeout(dropdownTimeout);
            desktopClinicasList.style.opacity = '1';
            desktopClinicasList.style.pointerEvents = 'auto';
            desktopClinicasList.style.transform = 'translateY(0)';
        }

        function closeDesktopDropdown() {
            dropdownTimeout = setTimeout(() => {
                desktopClinicasList.style.opacity = '0';
                desktopClinicasList.style.pointerEvents = 'none';
                desktopClinicasList.style.transform = 'translateY(-0.5rem)';
            }, 200);
        }
        desktopClinicas.addEventListener('mouseenter', openDesktopDropdown);
        desktopClinicas.addEventListener('mouseleave', closeDesktopDropdown);
        desktopClinicasList.addEventListener('mouseenter', openDesktopDropdown);
        desktopClinicasList.addEventListener('mouseleave', closeDesktopDropdown);
    }

    // Cambios de scroll (solo aplica en escritorio)
    const header = document.getElementById('mainHeader');
    window.addEventListener('scroll', () => {
        const scrollY = window.scrollY;
        const nav = header.querySelector('nav');
        const buttons = header.querySelectorAll('a, button');

        // Verificamos si es móvil
        const isMobile = window.innerWidth < 768; // md breakpoint en Tailwind

        // En móvil mantenemos siempre letras negras
        if (isMobile) {
            nav.classList.remove('text-white', 'border-white/40');
            nav.classList.add('text-black', 'bg-white', 'border-gray-200');
            buttons.forEach(btn => {
                btn.classList.remove('text-white', 'border-white');
                btn.classList.add('text-black', 'border-gray-700');
            });
            return; // no ejecutar lo de abajo
        }

        // En escritorio: cambia según el scroll
        if (scrollY > 350) {
            nav.classList.remove('text-white', 'border-white/40');
            nav.classList.add('text-black', 'border-gray-800', 'bg-white/90', 'shadow-lg');
            buttons.forEach(btn => {
                btn.classList.remove('text-white', 'border-white');
                btn.classList.add('text-gray-800', 'border-gray-700');
            });
        } else {
            nav.classList.remove('text-black', 'border-gray-800', 'bg-white/90', 'shadow-lg');
            nav.classList.add('text-white', 'border-white/40');
            buttons.forEach(btn => {
                btn.classList.remove('text-gray-800', 'border-gray-700');
                btn.classList.add('text-white', 'border-white');
            });
        }
    });
</script>
