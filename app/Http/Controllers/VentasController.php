<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    // Obtiene los leads paginados (10 por página)
    $leads = Lead::orderBy('created_at', 'desc')->paginate(10);

    // Retorna la vista con los datos
    return view('crm.leads.indexLeads', compact('leads'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('panel.ventas.create'); // ejemplo de vista
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $data = $request->validate([
        'first_name'     => 'required|string|max:255',
        'last_name'      => 'nullable|string|max:255',
        'clinic'         => 'required|string|max:50',
        'origin'         => 'required|string|max:255',
        'phone'          => 'required|string|max:30',
        'interested_in'  => 'required|string|max:255',
        'stage'          => 'required|string|max:255',
        'quali'          => 'required|string|max:255',
        'seller'         => 'required|string|max:255',
        'link'           => 'nullable|string',
        'notes'          => 'nullable|string',
        'fecha_abierta'  => 'nullable|boolean',
    ]);

    $data['created_at'] = now();

    // 🔹 GUARDAMOS Y OBTENEMOS EL MODELO
    $lead = Lead::create($data);

    // 🔹 REDIRECCIÓN AL DETALLE DEL LEAD
    return redirect()
        ->route('panel.leads.show', $lead->id)
        ->with('success', 'Lead agregado correctamente');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
