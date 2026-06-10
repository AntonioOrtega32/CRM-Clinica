@extends('panel.layouts.panel')

@section('title', 'Administración de Cortes Diarios')

@section('content')
    <script>
        window.AUTH_USER = {
            id: {{ auth()->id() }},
            nombre: "{{ auth()->user()->nombre ?? auth()->user()->name }}"
        };
        console.log('Usuario autenticado:', window.AUTH_USER);
    </script>

    <div class="container-fluid">
        <div class="bg-white p-4 rounded shadow mb-4">

            {{-- TÍTULO --}}
            <h2 class="text-center mb-4 fw-[Poppins] text-teal">
                Administración de Cortes Diarios 
            </h2>

            {{-- CONTROLES --}}
            <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
                <input type="date" id="datepicker" class="form-control w-auto">
                <select id="clinicSelect" class="form-select w-auto">
                    <option value="Santa Fe">Santa Fe</option>
                    <option value="Pedregal">Pedregal</option>
                    <option value="Queretaro">Querétaro</option>
                </select>

                <select id="terminalFilter" onchange="loadDaily()">
    <option value="">Todas</option>
    <option value="moral">Persona Moral</option>
    <option value="fisica">Persona Física</option>
</select>

                <button id="btnLoad" class="btn btn-teal px-4">
                    Cargar Pagos
                </button>
            </div>

            {{-- TABLA --}}
            <div class="card shadow-sm mb-4">
                <div class="table-responsive">
                    <table id="expensesTable" class="table table-bordered table-hover mb-0" style="width:100%">
                        <thead class="table-teal">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Concepto</th>
                                <th>Tipo</th>
                                <th class="text-end">Importe</th>
                                <th>Método</th>
                                <th>Sucursal</th>
                                <th>Terminal</th>

                                <th class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- TOTALES --}}
            <div class="card shadow-sm mb-4 p-3">
                <h5 class="fw-bold mb-2">Totales por método</h5>
                <div id="totalesBox" class="text-muted">
                    Cargando...
                </div>
            </div>
            <div class="text-center mt-3">

                <button id="btnGenerateCorte" class="btn btn-primary px-5">
                    Generar Corte (Debug)
                </button>
                {{-- FIRMA --}}
                <div class="card shadow-sm p-4 text-center">
                    <h5 class="fw-bold mb-3">Firma</h5>

                    <div class="d-flex justify-content-center">
                        <canvas id="firmaCanvas" width="520" height="160" class="border rounded bg-white shadow-sm"
                            style="touch-action:none; max-width:520px; width:100%; height:auto;"></canvas>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-3">
                        @role('super_usuario')
                        <button id="btnClearFirma" class="btn btn-danger px-4">Borrar Firma</button>
@endrole
                        <button id="btnGuardarFirma" class="btn btn-success px-4">Guardar Firma</button>
                    </div>

                    <div id="firmaStatus" class="mt-3 text-muted small"></div>
                </div>

                {{-- PDF DEL CORTE --}}
                <div id="pdfBox" class="mt-4" style="display:none;">
                    <iframe id="cortePdfFrame" src="" width="100%" height="500"
                        style="border:1px solid #ccc;border-radius:8px;"></iframe>

                    <div class="mt-2 text-muted fw-bold text-center">
                        Generado por: <span id="pdfUserName"></span>
                    </div>
                </div>

                <div id="corteUserBox" class="mt-2 text-muted fw-bold" style="display:none;">
                    Generado por: <span id="corteUserName"></span>
                </div>
            </div>
        </div>


        <div id="editTerminalModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="font-bold text-lg mb-4">Editar terminal</h3>

        <input type="hidden" id="edit_id">
        <input type="hidden" id="edit_source">

        <label class="block text-sm mb-2">Tipo de terminal</label>
        <select id="edit_terminal" class="w-full border rounded p-2">
            <option value="moral">Persona Moral</option>
            <option value="fisica">Persona Física</option>
        </select>

        <div class="flex justify-end gap-2 mt-4">
            <button onclick="closeEditTerminal()" class="px-3 py-1 border rounded">Cancelar</button>
            <button onclick="saveTerminal()" class="px-3 py-1 bg-emerald-600 text-white rounded">
                Guardar
            </button>
        </div>
    </div>
</div>

        {{-- ================= SCRIPT ================= --}}
        <script>
            $(document).ready(function() {

                /* ==============================
                   FECHA + CLÍNICA POR DEFECTO
                ============================== */
                const today = new Date().toISOString().split('T')[0];
                $('#datepicker').val(today);

                let lastTotals = null;

                /* ==============================
                   DATATABLE
                ============================== */
                const table = $('#expensesTable').DataTable({
                    processing: true,
                    paging: false,
                    info: false,
                    searching: false,
                    ordering: false,
                    scrollX:true,
                    responsive:true,
                    language: {
                        emptyTable: "No hay movimientos para este día"
                    },
               columns: [
    { data: 'id' },
    { data: 'fecha' },
    { data: 'nombre' },
    { data: 'concepto' },
    { data: 'tipo' },
    {
        data: 'importe',
        className: 'text-end fw-bold',
        render: d => `$${parseFloat(d || 0).toFixed(2)}`
    },
    { data: 'metodo_de_pago' },
    { data: 'sucursal' },
    { data: 'terminal', className: 'text-center fw-bold' }, // ✅
    { data: 'options', className: 'text-center' }
]

                });

                /* ==============================
                   RESET PDF
                ============================== */
                function resetPdf() {
                    $('#pdfBox').hide();
                    $('#cortePdfFrame').attr('src', '');
                    $('#pdfUserName').text('');
                }

                /* ==============================
                   VERIFICAR SI EXISTE CORTE
                ============================== */
                function loadExistingCorte(fecha, clinic) {
                    $.ajax({
                        url: '/panel/finanzas/corte/existe',
                        type: 'POST',
                        data: {
                            fecha,
                            clinic,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(resp) {
                            if (resp.exists && resp.path) {
                                $('#cortePdfFrame').attr('src', resp.path);
                                $('#pdfBox').fadeIn();

                                if (resp.generatedBy) {
                                    $('#pdfUserName').text(resp.generatedBy);
                                }
                            } else {
                                resetPdf();
                            }
                        }
                    });
                }

                /* ==============================
                   CARGAR DATA DIARIA
                ============================== */
               function loadDaily() {
    const fecha = $('#datepicker').val();
    const clinic = $('#clinicSelect').val();
    const terminal = $('#terminalFilter').val(); // ✅ DEFINIDA

    if (!fecha || !clinic) return;

    table.clear().draw();
    $('#totalesBox').html('Cargando...');
    resetPdf();

    loadExistingCorte(fecha, clinic);

    $.ajax({
        url: '/api/finanzas/gastos/diarios', // ✅ WEB, NO API
        type: 'POST',
        data: {
            fecha,
            clinic,
            terminal, // ✅ OPCIONAL (si viene vacío no filtra)
            _token: '{{ csrf_token() }}' // ✅ CSRF
        },
        success: function(resp) {
            if (!resp.data || resp.data.length === 0) {
                $('#totalesBox').html('Sin datos');
                lastTotals = null;
                return;
            }

            table.rows.add(resp.data).draw();
            renderTotales(resp.totales || {});
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $('#totalesBox').html('Error al cargar totales');
        }
    });
}

                /* ==============================
                   TOTALES
                ============================== */
                function renderTotales(totales) {

                    lastTotals = {
                        'Efectivo': totales['Efectivo'] || 0,
                        'Dólares': totales['Dólares'] || 0,
                        'Tarjeta': totales['Tarjeta'] || 0,
                        'Depósito': totales['Depósito'] || 0,
                        'Transferencia': totales['Transferencia'] || 0,
                        'Otro': totales['Otro'] || 0,
                        'Enlace digital': totales['Enlace digital'] || 0,
                        'TDC': totales['TDC'] || 0,
                        'TDD': totales['TDD'] || 0
                    };

                    let html = '<ul class="mb-0">';
                    let total = 0;

                    Object.entries(lastTotals).forEach(([metodo, monto]) => {
                        total += monto;
                        html += `<li><strong>${metodo}:</strong> $${monto.toFixed(2)}</li>`;
                    });

                    html += `<li class="mt-2 fw-bold">TOTAL: $${total.toFixed(2)}</li></ul>`;
                    $('#totalesBox').html(html);
                }

                /* ==============================
                   GENERAR CORTE
                ============================== */
                $('#btnGenerateCorte').on('click', function() {

                    if (!lastTotals) {
                        Swal.fire('Sin datos', 'Primero carga los pagos del día.', 'warning');
                        return;
                    }

                    const firmaDataUrl = firmaToDataURL(); // tu función actual (regresa null si está en blanco)
                    if (!firmaDataUrl) {
                        Swal.fire('Falta firma', 'Dibuja y guarda la firma antes de generar el corte.',
                            'warning');
                        return;
                    }

                    const fecha = $('#datepicker').val();
                    const clinic = $('#clinicSelect').val();

                    const tableData = [
                        [
                            lastTotals['Efectivo'],
                            lastTotals['Dólares'],
                            lastTotals['Tarjeta'],
                            lastTotals['Depósito'],
                            lastTotals['Transferencia'],
                            lastTotals['Otro'],
                            lastTotals['Enlace digital'],
                            lastTotals['TDC'],
                            lastTotals['TDD']
                        ]
                    ];

                    Swal.fire({
                        title: 'Generando corte…',
                        text: 'No cierres esta ventana.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: '/panel/finanzas/corte/generar',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            fecha,
                            clinic,
                                    terminal, // ✅ NUEVO

                            tableData: JSON.stringify(tableData),
                            firma: firmaDataUrl // ✅ se manda firma
                        }),
                        success: function(resp) {
                            Swal.close();

                            if (resp.path) {
                                $('#cortePdfFrame').attr('src', resp.path);
                                $('#pdfBox').fadeIn();
                            }
                            if (resp.generatedBy) $('#pdfUserName').text(resp.generatedBy);

                            Swal.fire('Listo', 'Corte generado correctamente.', 'success');
                        },
                        error: function(xhr) {
                            Swal.close();
                            console.error(xhr.responseText);
                            Swal.fire('Error', 'Error al generar el corte.', 'error');
                        }
                    });
                });

                /* ==============================
                   ELIMINAR REGISTRO ✅ (CORREGIDO)
                ============================== */
                $(document).on('click', '.delete_record', function() {

                    const id = $(this).data('id');
                    const type = $(this).data('type');

                    if (!id || !type) {
                        alert('Datos inválidos');
                        return;
                    }

                    if (!confirm('¿Seguro que deseas eliminar este registro?')) return;

                    $.ajax({
                        url: '/api/finanzas/gastos/eliminar',
                        type: 'POST',
                        data: {
                            id: id,
                            type: type, // ✅ CLAVE CORRECTA
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(resp) {
                            if (resp.success) {
                                alert('Registro eliminado correctamente');
                                loadDaily();
                            } else {
                                alert(resp.message || 'No se pudo eliminar');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Error al eliminar');
                        }
                    });
                });

                /* ==============================
                   EVENTOS
                ============================== */
                $('#btnLoad').on('click', loadDaily);
                $('#clinicSelect, #datepicker').on('change', loadDaily);

                /* ==============================
                   AUTOLOAD
                ============================== */
                loadDaily();

                /* ==============================
                   FIRMA (CANVAS) + SAVE/LOAD
                ============================== */
                const canvas = document.getElementById('firmaCanvas');
                const ctx = canvas.getContext('2d');
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#111';

                let drawing = false;
                let hasInk = false;

                // ajustar coordenadas con escala (porque el canvas se hace responsive)
                function getPos(e) {
                    const rect = canvas.getBoundingClientRect();
                    const clientX = (e.touches ? e.touches[0].clientX : e.clientX);
                    const clientY = (e.touches ? e.touches[0].clientY : e.clientY);
                    const x = (clientX - rect.left) * (canvas.width / rect.width);
                    const y = (clientY - rect.top) * (canvas.height / rect.height);
                    return {
                        x,
                        y
                    };
                }

                function startDraw(e) {
                    drawing = true;
                    hasInk = true;
                    const p = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                }

                function moveDraw(e) {
                    if (!drawing) return;
                    e.preventDefault();
                    const p = getPos(e);
                    ctx.lineTo(p.x, p.y);
                    ctx.stroke();
                }

                function endDraw() {
                    drawing = false;
                }

                canvas.addEventListener('mousedown', startDraw);
                canvas.addEventListener('mousemove', moveDraw);
                canvas.addEventListener('mouseup', endDraw);
                canvas.addEventListener('mouseleave', endDraw);

                canvas.addEventListener('touchstart', (e) => startDraw(e), {
                    passive: false
                });
                canvas.addEventListener('touchmove', (e) => moveDraw(e), {
                    passive: false
                });
                canvas.addEventListener('touchend', endDraw);

                function clearFirma() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    hasInk = false;
                }

                $('#btnClearFirma').on('click', function() {
                    clearFirma();
                    $('#firmaStatus').text('Firma borrada.');
                });

                function firmaToDataURL() {
                    // si está en blanco, evita guardar basura
                    if (!hasInk) return null;
                    return canvas.toDataURL('image/png');
                }

                function loadFirma(fecha, clinic) {
                    $('#firmaStatus').text('Cargando firma…');
                    $.ajax({
                        url: '/panel/finanzas/corte/firma/get',
                        type: 'POST',
                        data: {
                            fecha,
                            clinic,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(resp) {
                            if (resp.success && resp.dataUrl) {
                                const img = new Image();
                                img.onload = function() {
                                    clearFirma();
                                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                    hasInk = true;
                                    $('#firmaStatus').text('Firma cargada.');
                                };
                                img.src = resp
                                    .dataUrl; // puede ser base64 o url pública (según como lo guardes)
                            } else {
                                clearFirma();
                                $('#firmaStatus').text('Sin firma registrada para este día.');
                            }
                        },
                        error: function() {
                            $('#firmaStatus').text('No se pudo cargar la firma.');
                        }
                    });
                }

                $('#btnGuardarFirma').on('click', function() {
                    if (!lastTotals) {
                        alert('Primero carga los pagos del día.');
                        return;
                    }

                    const fecha = $('#datepicker').val();
                    const clinic = $('#clinicSelect').val();

                    const dataUrl = firmaToDataURL();
                    if (!dataUrl) {
                        alert('Firma vacía. Dibuja la firma antes de guardar.');
                        return;
                    }

                    $('#btnGuardarFirma').prop('disabled', true);
                    $('#firmaStatus').text('Guardando firma…');

                    $.ajax({
                        url: '/panel/finanzas/corte/firma/save',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            fecha,
                            clinic,
                            firma: dataUrl,
                            _token: '{{ csrf_token() }}'
                        }),
                        success: function(resp) {
                            if (resp.success) {
                                $('#firmaStatus').text('Firma guardada correctamente.');
                            } else {
                                $('#firmaStatus').text(resp.message ||
                                    'No se pudo guardar la firma.');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            $('#firmaStatus').text('Error guardando firma.');
                        },
                        complete: function() {
                            $('#btnGuardarFirma').prop('disabled', false);
                        }
                    });
                });

                // cuando cambias fecha/clinica, carga la firma correspondiente
                function syncFirmaConSeleccion() {
                    const fecha = $('#datepicker').val();
                    const clinic = $('#clinicSelect').val();
                    if (!fecha || !clinic) return;
                    loadFirma(fecha, clinic);
                }
                $('#clinicSelect, #datepicker').on('change', syncFirmaConSeleccion);

                // al terminar loadDaily, también queremos refrescar firma del día
                const oldLoadDaily = loadDaily;
                loadDaily = function() {
                    oldLoadDaily();
                    syncFirmaConSeleccion();
                };

                // init
                syncFirmaConSeleccion();

            });


            function openEditTerminal(data) {
    @if(!auth()->user()->hasRole('super_usuario'))
        alert('No autorizado');
        return;
    @endif

    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_source').value = data.source;
    document.getElementById('edit_terminal').value = data.terminal || 'moral';

    document.getElementById('editTerminalModal').classList.remove('hidden');
}

function closeEditTerminal() {
    document.getElementById('editTerminalModal').classList.add('hidden');
}

function saveTerminal() {
    fetch('/panel/daily/update-terminal', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: document.getElementById('edit_id').value,
            source: document.getElementById('edit_source').value,
            terminal: document.getElementById('edit_terminal').value
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeEditTerminal();
            loadDaily(); // 🔄 recargar tabla
        } else {
            alert(res.message);
        }
    });
}

        </script>




        {{-- ================= ESTILOS ================= --}}
        <style>
            .text-teal {
                color: #1c6c73;
            }

            .btn-teal {
                background: #1c6c73;
                color: #fff;
                border: none;
            }

            .btn-teal:hover {
                background: #14585b;
            }

            .table-teal th {
                background: #1c6c73;
                color: #fff;
                text-align: center;
            }

            #expensesTable td {
                vertical-align: middle;
            }
        </style>

    @endsection
