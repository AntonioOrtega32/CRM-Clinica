@extends('panel.layouts.panel')

@section('title', 'Inicio - Dashboard')

@section('content') 

    {{-- ==== ESTILOS SWIPER + TAILWIND ==== --}}
    <style>
        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #6366f1;
            opacity: .4;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
        }

        .blog-slider__item {
            display: flex;
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .blog-slider__img img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }

        .blog-slider__content {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .blog-slider__title {
            font-weight: 600;
            font-size: 1.2rem;
        }

        .blog-slider__text {
            font-size: 0.9rem;
            color: #555;
            margin-top: 0.5rem;
        }

        .blog-slider__code {
            font-size: 0.8rem;
            color: #999;
        }
    </style>

    <div class="bg-white shadow-xl rounded-2xl p-6 md:p-10 mt-6">

        {{-- HEADER SUPERIOR --}}
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <nav class="text-gray-500 text-sm">
                <ol class="flex gap-2">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 font-semibold">Inicio</a></li>
                    <li>/</li>
                    <li class="font-bold text-gray-800">Dashboard</li>
                </ol>
            </nav>

            {{-- Selector clínica --}}
            <div class="relative">
                <button id="clinicButton"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700 transition flex items-center">
                    Clínica
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <ul class="dropdown-menu hidden absolute mt-2 w-40 bg-white shadow-lg rounded-lg py-2 z-20">
                    <li><a href="#" data-clinic="Santa Fe"
                            class="item_clinic block px-4 py-2 hover:bg-indigo-100">Santa Fe</a></li>
                    <li><a href="#" data-clinic="Queretaro"
                            class="item_clinic block px-4 py-2 hover:bg-indigo-100">Queretaro</a></li>
                </ul>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mt-6 mb-5">Dashboard</h1>



        {{-- SWIPER RESPONSIVO --}}
        <div class="max-w-5xl mx-auto">
            <div class="swiper blog-slider">
                <div class="swiper-wrapper" id="cards">
                    {{-- Las cards se cargarán dinámicamente vía AJAX --}}
                </div>
                <div class="swiper-pagination mt-4"></div>

            </div>

        </div>


    </div>
   @role('super_usuario|Administrador|Marketing')
    <div id="vacacionesCard" class="mt-6 p-5 bg-indigo-50 border border-indigo-200 rounded-xl shadow-inner hidden">
        <h2 class="text-xl font-bold text-indigo-700 mb-2">Empleados de Vacaciones</h2>
        <div id="vacacionesList" class="text-gray-700 text-sm">
            <p class="text-gray-500">Cargando información...</p>
        </div>
    </div>

    <div id="aniversarioCard" class="mt-6 p-5 bg-green-50 border border-green-200 rounded-xl shadow-inner hidden">
        <h2 class="text-xl font-bold text-green-700 mb-2">Empleados con 1 año o más</h2>
        <div id="aniversarioList" class="text-gray-700 text-sm">
            <p class="text-gray-500">Cargando información...</p>
        </div>
    </div>
@endrole

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            // ===============================
            //      TOGGLE MENÚ CLÍNICA
            // ===============================
            $("#clinicButton").on("click", function() {
                $(".dropdown-menu").toggleClass("hidden");
            });

            // Recuperar clínica seleccionada
            let clinica = localStorage.getItem('clinica');
            if (clinica) {
                $('#clinicButton').text(clinica);
                $('.item_clinic').each(function() {
                    $(this).toggleClass('bg-indigo-100', $(this).data('clinic') === clinica);
                });
            }

            // Cargar inicialmente pacientes y vacaciones
            loadPatients(clinica);
            loadVacaciones(clinica);

            // Cambiar clínica
            $(document).on('click', '.item_clinic', function(e) {
                e.preventDefault();
                const clinic = $(this).data('clinic');
                localStorage.setItem('clinica', clinic);
                $('#clinicButton').text(clinic);
                $(".dropdown-menu").addClass("hidden");
                $('.item_clinic').removeClass('bg-indigo-100');
                $(this).addClass('bg-indigo-100');

                // Recargar info
                loadPatients(clinic);
                loadVacaciones(clinic);
            });

            // =====================================
            //      FUNCION: CARGAR PACIENTES
            // =====================================
            function loadPatients(clinic) {
                if (!clinic) return; // evitar llamadas sin clínica

                $.ajax({
                        url: "{{ route('panel.loadPatients') }}",
                        method: "POST",
                        data: {
                            clinic: clinic,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json"
                    })
                    .done(function(res) {
                        if (res.success) {
                            $("#cards").html(res.cards);
                            initSwiper(); // inicializa Swiper
                        } else {
                            $("#cards").html(`
                    <div class="flex justify-center items-center h-40">
                        <h4 class="text-gray-500 text-lg">${res.message}</h4>
                    </div>
                `);
                        }
                    })
                    .fail(function() {
                        Swal.fire("Error", "No se pudo cargar información de pacientes", "error");
                    });
            }

            // =====================================
            //      FUNCION: CARGAR VACACIONES
            function loadVacaciones(clinic) {
                $.ajax({
                        url: "{{ route('panel.vacacionesCard') }}",
                        method: "GET", // usamos GET
                        data: {
                            clinic: clinic
                        },
                        dataType: "json"
                    })
                    .done(function(res) {
                        console.log("JSON completo de vacaciones:", res);

                        if (res.success && res.empleados.length > 0) {
                            $("#vacacionesCard").removeClass("hidden"); // Mostrar tarjeta

                            // Generar HTML dinámicamente
                            let html = "";
                            res.empleados.forEach(e => {
                                html += `
                    <div class='p-3 bg-white border rounded-lg mb-2 shadow'>
                        <p class='font-semibold text-gray-800'>${e.nombre} ${e.apellido}</p>
                        <p class='text-sm text-gray-500'>Puesto: ${e.puesto}</p>
                        <p class='text-sm mt-1 text-red-500 font-semibold'>
                            En vacaciones hasta: ${e.vacaciones_hasta ?? "Sin fecha"}
                        </p>
                    </div>
                `;
                            });

                            $("#vacacionesList").html(html);

                        } else {
                            $("#vacacionesCard").addClass("hidden");
                            $("#vacacionesList").html("");
                        }
                    })
                    .fail(function(err) {
                        console.error("Error al cargar vacaciones:", err);
                        Swal.fire("Error", "No se pudo cargar información de vacaciones", "error");
                    });
            }



            // =====================================
            //      FUNCION: INICIALIZAR SWIPER
            // =====================================
            function initSwiper() {
                if (window.mySwiper) window.mySwiper.destroy(true, true);

                window.mySwiper = new Swiper('.blog-slider', {
                    effect: 'slide',
                    loop: true,
                    spaceBetween: 30,
                    pagination: {
                        el: '.swiper-pagination',
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
                    },
                });
            }

        });
        // =====================================
        //      FUNCION: CARGAR ANIVERSARIOS
        // =====================================
$(document).ready(function() {

    // Recuperar clínica seleccionada
    let clinica = localStorage.getItem('clinica');
    if (clinica) {
        $('#clinicButton').text(clinica);
        $('.item_clinic').each(function() {
            $(this).toggleClass('bg-indigo-100', $(this).data('clinic') === clinica);
        });
    }

    // Cargar inicialmente pacientes, vacaciones y aniversarios
    loadPatients(clinica);
    loadVacaciones(clinica);
    loadAniversario(clinica); // <-- agrega esto

    // Cambiar clínica
    $(document).on('click', '.item_clinic', function(e) {
        e.preventDefault();
        const clinic = $(this).data('clinic');
        localStorage.setItem('clinica', clinic);
        $('#clinicButton').text(clinic);
        $(".dropdown-menu").addClass("hidden");
        $('.item_clinic').removeClass('bg-indigo-100');
        $(this).addClass('bg-indigo-100');

        // Recargar info
        loadPatients(clinic);
        loadVacaciones(clinic);
        loadAniversario(clinic); // <-- y aquí también
    });

});

        function loadAniversario(clinic) {
            if (!clinic) return; // evitar llamadas sin clínica

            $.ajax({
                    url: "{{ route('panel.empleadosAniversario') }}",
                    method: "POST",
                    data: {
                        clinic: clinic,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json"
                })
                .done(function(res) {
                    console.log("JSON completo de aniversarios recibido:", res); // <-- Aquí ves lo que llega

                    if (res.success && res.empleados.length > 0) {
                        $("#aniversarioCard").removeClass("hidden"); // mostrar tarjeta

                        let html = "";
                        res.empleados.forEach(e => {
                            console.log("Empleado individual:", e); // <-- Aquí ves cada empleado

                            // Formatear la fecha a "8 de diciembre de 2024"
                            let fechaFormateada = new Date(e.fecha_ingreso).toLocaleDateString('es-MX', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            });
                            console.log("Fecha formateada:", fechaFormateada); // <-- Ver fecha formateada

                            html += `
                    <div class='p-3 bg-white border rounded-lg mb-2 shadow'>
                        <p class='font-semibold text-gray-800'>${e.nombre} ${e.apellido}</p>
                        <p class='text-sm text-gray-500'>Puesto: ${e.puesto}</p>
                        <p class='text-sm mt-1 text-green-600 font-semibold'>
                            Fecha de ingreso: ${fechaFormateada} (${e.años_en_empresa} años)
                        </p>
                    </div>
                `;
                        });

                        $("#aniversarioList").html(html);

                    } else {
                        $("#aniversarioCard").addClass("hidden");
                        $("#aniversarioList").html("");
                    }
                })
                .fail(function(err) {
                    console.error("Error al cargar aniversarios:", err);
                    Swal.fire("Error", "No se pudo cargar información de aniversarios", "error");
                });
                                    console.error("Error al cargar aniversarios:", err);

        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
