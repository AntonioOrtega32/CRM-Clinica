<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\AdInventoryItem;
use App\Models\TreatmentProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index()
        {
            //Consulta de todos los articulos de inventario
            $inventarios = AdInventoryItem::query()
            ->select('*')
            ->selectRaw('GREATEST(minimum_required - stock, 0) as faltante')
            ->get();


            /* consulta para traer los movimientos con el nombre del producto
            con inner join porque Eloquent esta tonto */
            $movimientos = DB::table('ad_inventory_movements as m')
                ->join('ad_inventory_items as i', 'm.item_id', '=', 'i.id')
                ->select(
                    'm.id',
                    'm.item_id',
                    'i.name as nombreProducto', // traemos el nombre del producto
                    'm.batch_id',
                    'm.movement_type',
                    'm.quantity',
                    'm.movement_date',
                    'm.delivered_by',
                    'm.received_by',
                    'm.location'
                )
                ->orderBy('m.movement_date', 'desc')
                ->get();

            //Consulta para los medicamentos
            $medicamentos = DB::table('ad_inventory_items as m')
            ->select('m.id',
            'm.name',
            'm.stock',
            'm.expiry_date',)
            ->where('m.has_expiry', 1)
            ->get();

            //consultas de kits medicos
            $kits = DB::table('treatment_products as p')
            ->join('ad_inventory_items as n', 'p.product_id', '=', 'n.id')
            ->select(
                'p.id',
                'n.name as nombre',
                'p.product_id',
                'p.treatment_type',
                'p.quantity',
                'p.clinic'
            )
            ->get();

        //dd($inventarios);
        return view('crm.inventario.inventario', compact('inventarios', 'movimientos', 'medicamentos', 'kits'));
        }

         //Un jeison para traer el inventario general
        public function generalJson(Request $request)
        {
            $q = AdInventoryItem::query()
                ->select([
                    'id','name','minimum_required','stock','category','clinic'
                ])
                ->selectRaw('GREATEST(minimum_required - stock, 0) as faltante');

            // Filtros externos (los selects)
            if ($request->filled('clinic')) {
                $q->where('clinic', $request->clinic);
            }
            if ($request->filled('category')) {
                $q->where('category', $request->category);
            }

            // DataTables params
            $draw   = (int) $request->input('draw', 1);
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);

            // Search global
            $search = $request->input('search.value');
            if ($search) {
                $q->where(function($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('clinic', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Conteos
            $recordsFiltered = (clone $q)->count();
            $recordsTotal = AdInventoryItem::count();

            // Ordenamiento
            $orderColIndex = $request->input('order.0.column', 0);
            $orderDir      = $request->input('order.0.dir', 'asc');

            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'minimum_required',
                3 => 'stock',
                4 => 'faltante', // alias selectRaw
                5 => 'category',
                6 => 'clinic',
            ];

            $orderCol = $columns[$orderColIndex] ?? 'id';
            $q->orderBy($orderCol, $orderDir);

            // Paging
            $rows = $q->skip($start)->take($length)->get();

            // Respuesta
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $rows,
            ]);
        }

   public function movimientoInv(Request $request)
{
    if ($request->is_new_product) { 
        // Crear producto nuevo
        $producto = AdInventoryItem::create([
            'name' => $request->item_name,
            'category' => $request->category,
            'stock' => $request->stock,
            'location' => $request->ubicacion ?? 'Bodega',
            'minimum_required' => $request->minimum_value ?? 0,
            'has_expiry' => $request->has_expiry ?? 0,
            'expiry_date' => $request->expirationDate ?? null,
            'manual_price' => $request->manualPrice ?? 0,
        ]);

        // Registrar movimiento
        MovimientoInventario::create([
            'item_id' => $producto->id,
            'batch_id' => null,
            'movement_type' => 'entrada',
            'quantity' => $request->stock,
            'delivered_by' => auth()->user()->name ?? 'Sistema',
            'received_by' => null,
            'movement_date' => now(),
            'location' => $producto->location,
        ]);

    } else {
        // Actualizar stock y categoría de producto existente
        $producto = AdInventoryItem::findOrFail($request->item_name); // item_name = id del select
        $producto->stock += $request->stock; // Sumar la entrada
        $producto->category = $request->category; // Actualizar categoría
        $producto->save();

        // Registrar movimiento
        MovimientoInventario::create([
            'item_id' => $producto->id,
            'batch_id' => null,
            'movement_type' => 'entrada',
            'quantity' => $request->stock,
            'delivered_by' => auth()->user()->name ?? 'Sistema',
            'received_by' => null,
            'movement_date' => now(),
            'location' => $producto->location,
        ]);
    }

    return redirect()->back()->with('success', 'Movimiento registrado correctamente.');
}


    //Funcion para mostrar el inventario general (En desuso)
    public function getProducts()
    {
        // Traer todos los productos existentes
        $products = Inventario::select('id', 'nombre', 'categoria')->get();

        return response()->json($products);
    }


    public function updateProd(Request $request)
    {
        $producto = AdInventoryItem::findOrFail($request->id);
        $producto->update($request->only(['nombre', 'categoria', 'stock', 'cantidad_minima', 'pending_purchase']));
        return back()->with('success', 'Producto actualizado correctamente.');
    }


    public function destroyProd($id)
    {
        $inventario = AdInventoryItem::findOrFail($id);
        $inventario->delete();

        return back()->with('success', 'Producto eliminado correctamente.');
    }

   public function salidaProducto(Request $request)
{

    // Buscar producto
    $producto = AdInventoryItem::findOrFail($request->product_id);

    // Verificar que haya stock suficiente
    if ($producto->stock < $request->output_quantity) {
        return back()->withErrors(['output_quantity' => 'No hay suficiente stock disponible para esta salida.']);
    }

    // Restar del stock
    $producto->stock -= $request->output_quantity;
    $producto->save();

    // Registrar movimiento en movimientos_inventario
    MovimientoInventario::create([
        'item_id'     => $producto->id,
        'batch_id'        => null, // aún no se usa
        'movement_type' => 'salida',
        'quantity'       => $request->output_quantity,
        'delivered_by'    => auth()->user()->name ?? 'Sistema',
        'received_by'     => $request->received_by,
        'movement_date' => $request->output_date,
        'location'      => $producto->location,
    ]);

    return back()->with('success', 'Salida registrada correctamente.');
    }

    /*------------------------------------------------------
    ----------------- Seccion de kits medicos ------------
    ---------------------------------------------------*/

     public function getProductos()
    {
        $productos = AdInventoryItem::select('id', 'name')->orderBy('name', 'asc')->get();
        return response()->json($productos);
    }

    // 🔹 Trae el kit actual según tipo y clínica
    public function getKit(Request $request)
    {
        $tipo = $request->query('treatment_type');
        $clinic = $request->query('clinic');

        $kit = TreatmentProducts::where('treatment_type', $tipo)
            ->where('clinic', $clinic)
            ->select('id', 'product_id', 'quantity')
            ->get();

        return response()->json($kit);
    }

    // Guarda o actualiza el kit
    public function guardarKit(Request $request)
{
    $productos = $request->input('productos', []);
    $cantidades = $request->input('cantidades', []);
    $tipo = $request->input('treatment_type');
    $clinic = $request->input('clinic');

    foreach ($productos as $index => $productoId) {
        TreatmentProducts::updateOrCreate(
            [
                'product_id' => $productoId,
                'treatment_type' => $tipo,
                'clinic' => $clinic
            ],
            [
                'quantity' => $cantidades[$index]
            ]
        );
    }

    return redirect()->back()->with('success', 'Kit actualizado correctamente.');
}


    /* --------------------------------------------------
    ---------------- Seccion de salidas rapidas ------------
    -------------------------------------------------- */

    public function registrarSalidaRapida(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'received_by' => 'required',
            'output_date' => 'required|date',
            'type'        => 'required|string', // capilar o barba
            'clinic'      => 'required|string',
        ]);

        // Iniciar una transacción por seguridad
        DB::beginTransaction();

        try {
            // Buscar los productos del kit
            $kit = TreatmentProducts::where('treatment_type', $validated['type'])
                ->where('clinic', $validated['clinic'])
                ->get();

            if ($kit->isEmpty()) {
                return back()->with('error', 'No se encontró el kit para ese tratamiento y clínica.');
            }

            // Verificar stock
            foreach ($kit as $producto) {
                $item = AdInventoryItem::find($producto->product_id);

                if (!$item) {
                    return back()->with('error', "Producto ID {$producto->product_id} no encontrado.");
                }

                if ($item->stock < $producto->quantity) {
                    return back()->with('error', "Stock insuficiente para {$item->name}. (Stock: {$item->stock}, Requiere: {$producto->quantity})");
                }
            }

            // Descontar stock y registrar movimiento
            foreach ($kit as $producto) {
                $item = AdInventoryItem::find($producto->product_id);

                // Descontar stock
                $item->stock -= $producto->quantity;
                $item->save();

                // Registrar movimiento
                MovimientoInventario::create([
                    'item_id'       => $item->id,
                    'movement_type' => 'Salida',
                    'quantity'      => $producto->quantity,
                    'movement_date' => $validated['output_date'],
                    'delivered_by'  => 'Sistema',
                    'received_by'   => $validated['received_by'],
                ]);
            }

            DB::commit();

            //dd($request->all());

            return back()->with('success', 'Salida rápida registrada y stock actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
