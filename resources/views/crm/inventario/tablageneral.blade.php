<h2 class="text-2xl text-center font-[Poppins] mb-4">Vista General de inventario</h2>
   <!-- Filtros de fecha, sucursal y tipo -->
        <table border="0" cellspacing="5" cellpadding="5" class="date-table mb-4">
            <tbody>
                <tr>
                    <td style="font-[Poppins]">Sucursal:</td>
                    <td>
                        <select id="filterClinic" class="w-full border-gray-300 rounded-lg">
                            <option value="">Todas</option>
                            <option value="Santa Fe">Santa Fe</option>
                            <option value="Pedregal">Pedregal</option>
                            <option value="Queretaro">Querétaro</option>
                        </select>
                    </td>
                    <td style="font-[Poppins]">Categoria:</td>
                    <td>
                        <select id="filterType" class="w-full border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <option value="Farmacia">Farmacia</option>
                            <option value="Lanceta">Lanceta</option>
                            <option value="La paz">La paz</option>
                            <option value="TIM">TIM</option>
                            <option value="Imprenta">Imprenta</option>
                            <option value="Sams">Sams</option>
                            <option value="Amazon">Amazon</option>
                            <option value="Office">Office</option>
                            <option value="Instituto de tricologia">Instituto de tricologia</option>
                            <option value="Turquia">Turquia</option>
                            <option value="mercado libre">mercado libre</option>
                            <option value="Kabla">Kabla</option>
                            <option value="Varios">Varios</option>
                            <option value="walmart">Walmart</option>
                        </select>
                    </td>
                </tr> 
            </tbody>
        </table>
<br>
@if ($inventarios->isEmpty())
    <p>No hay articulos disponibles.</p> 
@else
<div id="TablaGeneralWrap" class="opacity-0 transition-opacity duration-200">
    <table id="TablaGeneral" style="width:100%" class="table table-striped table-bordered display nowrap">
        <thead class="bg-gray-dark color-palette text-white">
            <tr style="background-color: #4298a7">
                <th>id</th>
                <th>Nombre</th>
                <th>Cantidad Minima <p> Requerida</th>
                <th>Stock actual</th>
                <th>Pendiente por comprar</th>
                <th>Categoria</th>
                <th>Sucursal</th> 
                <th class="no-export">Acciones</th>
            </tr> 
        </thead>
            <tbody>
                @foreach ($inventarios as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->name }}</td>
                    <td>{{ $inv->minimum_required }} Piezas</td>
                    <td>{{ $inv->stock }} Piezas</td>
                   <td class="{{ $inv->faltante > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
    @if ($inv->faltante > 0)
        <i class="fa fa-exclamation-triangle text-red-600 mr-1" aria-hidden="true"></i>
        {{ $inv->faltante }} Piezas
    @else
        <i class="fa fa-check-circle text-green-600 mr-1" aria-hidden="true"></i>
        No hay pendiente
        <p class="text-xs text-green-500">por comprar</p>
    @endif
</td>

                    <td>{{ $inv->category }}</td>
                    <td>{{ $inv->clinic }}</td>
                    <td class="flex space-x-2">
                        <!-- boton para editar -->
                        <button type="button"
                            onclick="openEditModal({{ $inv->id }}, '{{ $inv->name }}', '{{ $inv->category }}', {{ $inv->stock }}, {{ $inv->minimum_required }}, {{ $inv->pending_purchase }})"
                            class="bg-[#1C6C73] hover:bg-[#14565c] text-white px-3 py-1 rounded-lg shadow-md transition">
                            Editar
                        </button>


                        <!-- boton para salida de productos -->
                        <button type="button" onclick="openSalidaModal('{{ $inv->id }}', '{{ $inv->name }}')"
                            class="bg-[#FF7014] hover:bg-[#D66820] text-white px-3 py-1 rounded-lg shadow-md transition">
                            Salida de producto
                        </button>


                        <!-- boton para borrar registros -->
                        <form action="{{ route('panel.inventario.destroy', $inv->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este producto?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg shadow-md transition">
                                Eliminar
                            </button>
                        </form>


                    </td>
                </tr>
            @endforeach
            </tbody>
    </table>
</div>
@endif
<!-- MOdales -->
@include('crm.inventario.modalEdit')
@include('crm.inventario.salidaModal')

<!--- Inicia script de DataTable --->
<script>
    function openEditModal(id, name, category, stock, minimum_required, pending_purchase) {
        // Llenar inputs del modal
        document.getElementById('editProductId').value = id;
        document.getElementById('editProductName').value = name;
        document.getElementById('itemsCategory').value = category;
        document.getElementById('itemStock').value = stock;
        document.getElementById('itemMinima').value = minimum_required;
        document.getElementById('itemPendiente').value = pending_purchase;

        // Mostrar modal
        document.getElementById('editProductModal').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>
<script>
  $(document).ready(function () {
    const $wrap = $('#TablaGeneralWrap');
    // Inicializa DataTable y guarda la instancia
    const table = $('#TablaGeneral').DataTable({
      responsive: true,
      scrollX: true,
      autoWidth: true,
      processing: true,
        initComplete: function(){
            $wrap.removeClass('opacity-0').addClass('opacity-100');
        },
      buttons: [
        { extend: 'excel', 
            text: 'Excel',
            className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
            exportOptions: {
              columns: [0,1,2,3,4,5,6]
            }  
        },
        { extend: 'pdf', 
            text: 'PDF', 
            className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg', 
            exportOptions: {
              columns: [0,1,2,3,4,5,6]
            }          
        }
      ],
      dom: 'Bfrtip',
      language: {
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        lengthMenu: "Mostrar _MENU_ registros",
        search: "Buscar:",
        loadingRecords: "Loading...",
        processing: "Procesando...",
        zeroRecords: "No hay registros aún",
        paginate: {
          next: '→',
          previous: '←',
          first: 'Inicio',
          last: 'Ultimo'
        },
         processing: `
      <div class="dt-loader">
        <div class="dt-spinner"></div>
        <div class="dt-text">Cargando...</div>
      </div>
    `,
      },
      
    });

    // Helper para regex exacto (match exacto en la celda)
    function exact(val) {
      if (!val) return '';
      // escapa caracteres regex
      const escaped = val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      return '^' + escaped + '$';
    }

    // IDs de columnas
    const COL_CATEGORIA = 5;
    const COL_CLINICA   = 6;

    // Eventos de filtros
    $('#filterClinic').on('change', function () {
        const val = $(this).val();
        table
            .column(COL_CLINICA)
            .search(exact(val), true, false) // true=regex, false=smart
            .draw();
        });

    $('#filterType').on('change', function () {
    const val = $(this).val();
    table
        .column(COL_CATEGORIA)
        .search(exact(val), true, false)
        .draw();
    });

    // (Opcional) si quieres que al cargar respete valores preseleccionados
    $('#filterClinic').trigger('change');
    $('#filterType').trigger('change');
    });
</script>
<style>
  /* Estilos para el loader personalizado */
  /* overlay centrado */
div.dataTables_processing{
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  z-index: 9999 !important;
  padding: 16px 22px !important;
  border-radius: 14px !important;
  background: rgba(255,255,255,.92) !important;
  box-shadow: 0 10px 30px rgba(0,0,0,.15) !important;
  border: 1px solid rgba(0,0,0,.08) !important;
}

/* contenido del loader */
.dt-loader{
  display:flex;
  align-items:center;
  gap:12px;
  font-weight:600;
  color:#1C6C73;
}

.dt-spinner{
  width:22px;
  height:22px;
  border:3px solid rgba(28,108,115,.2);
  border-top-color:#1C6C73;
  border-radius:50%;
  animation: dtspin .8s linear infinite;
}

@keyframes dtspin{ to{ transform: rotate(360deg);} }
</style>
