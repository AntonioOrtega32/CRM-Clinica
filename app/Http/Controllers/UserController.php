<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Movimiento;
class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all(); // para el modal y select de roles
        return view('panel.usuarios.index', compact('users', 'roles'));
    }

    // Mostrar formulario para crear usuario
    public function create()
    {
        $roles = Role::all();
        return view('panel.usuarios.create', compact('roles'));
    }

    // Guardar nuevo usuario
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('panel.usuarios.index')
                         ->with('success', 'Usuario creado correctamente.');
    }



// Actualizar usuario
public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required|string|exists:roles,name',
        'password' => 'nullable|string|confirmed|min:6',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    $user->syncRoles([$request->role]);

    return redirect()->route('panel.usuarios.index')
                     ->with('success', 'Usuario actualizado correctamente.');
}

// Eliminar usuario
public function destroy(User $user)
{
    $user->delete();
    return redirect()->route('panel.usuarios.index')
                     ->with('success', 'Usuario eliminado correctamente.');
}
public function show(User $user)
{
    // Cargar movimientos relacionados
    $movimientos = $user->movimientos()->latest()->get();

    return view('panel.usuarios.ver', compact('user', 'movimientos'));
}


}
