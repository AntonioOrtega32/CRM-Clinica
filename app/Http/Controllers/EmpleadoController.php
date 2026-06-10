<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\User;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EmpleadoController extends Controller
{
    public function index()
    {
        $empleados = Empleado::all();
        $users = User::all();

        return view('panel.usuarios.empleados', compact('empleados', 'users'));
    }

public function store(Request $request)
{
    $data = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'clinica' => 'required|string|max:255', // <--- agregado
        'puesto' => 'required|string|max:255',
        'departamento' => 'nullable|string|max:255',
        'fecha_ingreso' => 'nullable|date',
        'estatus' => 'required|in:Activo,Inactivo',
        'telefono' => 'nullable|string|max:50',
        'fecha_nacimiento' => 'nullable|date',
        'direccion' => 'nullable|string|max:500',
        'identificacion' => 'nullable|string|max:100',
        'emergencia_nombre' => 'nullable|string|max:255',
        'emergencia_telefono' => 'nullable|string|max:50',
        'emergencia2_nombre' => 'nullable|string|max:255',
        'emergencia2_telefono' => 'nullable|string|max:50',
        'notas' => 'nullable|string',
        'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Guardar foto si existe
    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $nombreArchivo = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('images/empleados'), $nombreArchivo);
        $data['foto'] = 'images/empleados/' . $nombreArchivo;
    }

    $empleado = Empleado::create($data);

    $this->registrarMovimiento('Crear', "Se creó el empleado {$empleado->nombre} {$empleado->apellido}", 'empleados', $empleado->id);

    return redirect()->route('panel.empleados.index')->with('success', 'Empleado creado correctamente.');
}

public function update(Request $request, Empleado $empleado)
{
    $data = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'clinica' => 'required|string|max:255', // <--- agregado
        'puesto' => 'required|string|max:255',
        'departamento' => 'nullable|string|max:255',
        'fecha_ingreso' => 'nullable|date',
        'estatus' => 'required|in:Activo,Inactivo',
        'telefono' => 'nullable|string|max:50',
        'fecha_nacimiento' => 'nullable|date',
        'direccion' => 'nullable|string|max:500',
        'identificacion' => 'nullable|string|max:100',
        'emergencia_nombre' => 'nullable|string|max:255',
        'emergencia_telefono' => 'nullable|string|max:50',
        'emergencia2_nombre' => 'nullable|string|max:255',
        'emergencia2_telefono' => 'nullable|string|max:50',
        'notas' => 'nullable|string',
        'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($request->hasFile('foto')) {
        if ($empleado->foto && file_exists(public_path($empleado->foto))) {
            unlink(public_path($empleado->foto)); // eliminar foto antigua
        }

        $file = $request->file('foto');
        $nombreArchivo = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('images/empleados'), $nombreArchivo);
        $data['foto'] = 'images/empleados/' . $nombreArchivo;
    }

    $empleado->update($data);

    $this->registrarMovimiento('Editar', "Se actualizó el empleado {$empleado->nombre} {$empleado->apellido}", 'empleados', $empleado->id);

    return redirect()->route('panel.empleados.index')->with('success', 'Empleado actualizado correctamente.');
}

    public function destroy(Empleado $empleado)
    {
        if ($empleado->foto && Storage::disk('public')->exists($empleado->foto)) {
            Storage::disk('public')->delete($empleado->foto);
        }

        $empleado->delete();

        $this->registrarMovimiento('Eliminar', "Se eliminó el empleado {$empleado->nombre} {$empleado->apellido}", 'empleados', $empleado->id);

        return redirect()->route('panel.empleados.index')->with('success', 'Empleado eliminado correctamente.');
    }

    protected function registrarMovimiento($tipo, $descripcion, $tabla, $registro_id = null)
    {
        Movimiento::create([
            'usuario_id' => Auth::id(),
            'tipo_movimiento' => $tipo,
            'descripcion' => $descripcion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registro_id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
