<div x-data="{ sidebarOpen: false }" class="relative min-h-screen flex">

    <!-- Botón hamburguesa -->
    <button @click="sidebarOpen = true" class="p-3 m-2 rounded bg-[#1C6C73] text-[#CDAF95] lg:hidden fixed z-50">
        <i data-lucide="menu" class="w-6 h-6"></i>
    </button>

    <!-- Overlay para móviles -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden transition-opacity"></div>

    <!-- Sidebar -->
    <aside
        class="w-64 bg-[#999999] text-white flex flex-col fixed inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-40
               lg:translate-x-0 lg:static lg:inset-0"
        :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-[#4298A7]">
            <span class="text-xl font-[Arial] text-[#ffffff]">CRM Clínica</span>
            <button class="lg:hidden" @click="sidebarOpen=false">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Navegación -->
        <nav class="p-4 space-y-2 overflow-y-auto flex-1">

            <!-- Inicio -->
            <a href="{{ route('panel.panel.index') }}" class="flex items-center p-2 rounded-lg hover:bg-[#C8BAAF]">
                <i data-lucide="home" class="w-5 h-5 mr-2"></i> Inicio
            </a>
            @role('super_usuario|administrador')
                <!-- Administrativo
                        <a href="administracion.html"
                            class="flex items-center p-2 rounded-lg hover:bg-[#C8BAAF]">
                            <i data-lucide="file-text" class="w-5 h-5 mr-2"></i> Administrativo
                        </a>
                        -->
            @endrole

            <!-- Agenda -->
            <div x-data="{ open: false }">
                <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                    <i data-lucide="calendar" class="w-5 h-5 mr-2"></i> Agenda
                    <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                        class="ml-auto w-4 h-4 transition-transform"></i>
                </button>
                <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                    <a href="{{ route('panel.calendar') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Santa Fe</a>
                    <a href="{{ route('panel.calendar.pedregal') }}"
                        class="block p-2 rounded hover:bg-[#CDAF95]">Pedregal</a>
                    <a href="{{ route('panel.calendar.queretaro') }}"
                        class="block p-2 rounded hover:bg-[#CDAF95]">Querétaro</a>
                </div>
            </div>
            @role('super_usuario|Administrador|Recepción')
                <!-- Inventario -->
                <a href="{{ route('panel.inventario.index') }}" class="flex items-center p-2 rounded-lg hover:bg-[#C8BAAF]">
                    <i data-lucide="package" class="w-5 h-5 mr-2"></i> Inventario
                </a>
            @endrole

            <!-- Procedimientos -->
            <div x-data="{ open: false }">
                <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                    <i data-lucide="scissors" class="w-5 h-5 mr-2"></i> Procedimientos
                    <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                        class="ml-auto w-4 h-4 transition-transform"></i>
                </button>
                <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                    <a href="{{ route('panel.procedimientos.index') }}"
                        class="block p-2 rounded hover:bg-[#CDAF95]">Procedimientos</a>
                    <a href="{{ route('panel.tratamientos.index') }}"
                        class="block p-2 rounded hover:bg-[#CDAF95]">Tratamientos</a>
                </div>
            </div>

            <!-- Ventas -->
            <div x-data="{ open: false }">
                <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                    <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i> Ventas
                    <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                        class="ml-auto w-4 h-4 transition-transform"></i>
                </button>
                <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                    <a href="{{ route('panel.ventas.create') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Generar
                        Lead</a>
                    <a href="{{ route('panel.leads.index') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Ver
                        Leads</a>
                </div>
            </div>

            @role('super_usuario|Administrador|Ventas|Recepción')
                <!-- Finanzas -->
                <div x-data="{ open: false }">
                    <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                        <i data-lucide="wallet" class="w-5 h-5 mr-2"></i> Finanzas
                        <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                            class="ml-auto w-4 h-4 transition-transform"></i>
                    </button>
                    <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                        <a href="{{ route('panel.gastos.index') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Gastos</a>
                        <a href="{{ route('panel.ingresos.index') }}"
                            class="block p-2 rounded hover:bg-[#CDAF95]">Ingresos</a>
                        <a href="{{ route('panel.cortesDiarios.index') }}"
                            class="block p-2 rounded hover:bg-[#CDAF95]">Cortes diarios</a>
                        <a href="{{ route('panel.budgets.index') }}"
                            class="block p-2 rounded hover:bg-[#CDAF95]">Presupuestos</a>
                        <a href="{{ route('panel.nomina.index') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Layout de
                            BBVA</a>
                    </div>
                </div>
            @endrole

            <!-- Clientes -->
            <a href="{{ route('panel.clientes.index') }}" class="flex items-center p-2 rounded-lg hover:bg-[#C8BAAF]">
                <i data-lucide="users" class="w-5 h-5 mr-2"></i> Clientes
            </a>
            @role('super_usuario|Administrador')
                <!-- Eventos -->
                <a href="{{ route('panel.holidays.index') }}" class="flex items-center p-2 rounded-lg hover:bg-[#C8BAAF]">
                    <i data-lucide="calendar-days" class="w-5 h-5 mr-2"></i> Vacaciones
                </a>
            @endrole
            @role('super_usuario|Administrador|Marketing')
                <!-- Marketing -->
                <div x-data="{ open: false }">
                    <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                        <i data-lucide="megaphone" class="w-5 h-5 mr-2"></i> Marketing
                        <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                            class="ml-auto w-4 h-4 transition-transform"></i>
                    </button>
                    <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                        <a href="{{ route('panel.marketing.index') }}"
                            class="block p-2 rounded hover:bg-[#CDAF95]">Seguimiento de PX</a>
                        <a href="{{ route('panel.revista.links') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Link
                            revista digital</a>
                        <!--<a href="#" class="block p-2 rounded hover:bg-[#CDAF95]">Redes Sociales</a>
                                <a href="#" class="block p-2 rounded hover:bg-[#CDAF95]">Email Marketing</a>-->
                    </div>
                </div>
            @endrole
            @role('super_usuario|Administrador')
                <!-- Usuarios     -->
                <div x-data="{ open: false }">
                    <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                        <i data-lucide="user-cog" class="w-5 h-5 mr-2"></i> Usuarios
                        <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                            class="ml-auto w-4 h-4 transition-transform"></i>
                    </button>
                    <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                        <a href="{{ route('panel.usuarios.create') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Nuevo
                            Usuario</a>
                        <a href="{{ route('panel.usuarios.index') }}"
                            class="block p-2 rounded hover:bg-[#CDAF95]">Usuarios</a>
                        <a href="{{ route('panel.empleados.index') }}"
                            class="block p-2 rounded hover:bg-[#CDAF95]">Empleados</a>
                    </div>
                </div>


                <!-- Monitoreo
                        <div x-data="{ open: false }">
                            <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                                <i data-lucide="activity" class="w-5 h-5 mr-2"></i> Monitoreo
                                <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                                    class="ml-auto w-4 h-4 transition-transform"></i>
                            </button>
                            <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                                <a href="logeos.html" class="block p-2 rounded hover:bg-[#CDAF95]">Logs</a>
                                <a href="alertas.html" class="block p-2 rounded hover:bg-[#CDAF95]">Alertas</a>
                            </div>
                        </div>
                        -->
            @endrole

            @role('super_usuario|administrador|marketing')
                        <div x-data="{ open: false }">
                            <button @click="open=!open" class="flex items-center w-full p-2 rounded-lg hover:bg-[#C8BAAF]">
                                <i data-lucide="globe" class="w-5 h-5 mr-2"></i> Sitio Web
                                <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down"
                                    class="ml-auto w-4 h-4 transition-transform"></i>
                            </button>
                            <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">
                                <a href="{{ route('panel.landing.index') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Página principal</a>
                                <!--<a href="{{ route('panel.drsantana.index') }}" class="block p-2 rounded hover:bg-[#CDAF95]">Blog Dr. Alejandro</a>-->
                            
                            </div>
                        </div>
            @endrole
            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full px-4 py-2 bg-[#EDEDED] text-black rounded hover:bg-[#787878] transition">
                    Cerrar sesión
                </button>
            </form>

        </nav>
    </aside>


</div>
