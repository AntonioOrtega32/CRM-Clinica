<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // --- Crear los roles ---
        $roles = [
            'super_usuario',
            'administrador',
            'recepcion',
            'medicos',
            'ventas',
            'marketing',
            'enfermeria',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // --- Crear usuarios de ejemplo ---
        $users = [
            [
                'name' => 'Super Usuario',
                'email' => 'super@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'super_usuario',
            ],
            [
                'name' => 'Admin Principal',
                'email' => 'admin1@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'administrador',
            ],
            [
                'name' => 'Recepción General',
                'email' => 'recepcion@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'recepcion',
            ],
            [
                'name' => 'Dr. General',
                'email' => 'medico@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'medicos',
            ],
            [
                'name' => 'Ventas',
                'email' => 'ventas@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'ventas',
            ],
            [
                'name' => 'Marketing',
                'email' => 'marketing@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'marketing',
            ],
            [
                'name' => 'Enfermería',
                'email' => 'enfermeria@clinica.com',
                'password' => Hash::make('password123'),
                'role' => 'enfermeria',
            ],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => $u['password']]
            );

            $user->assignRole($u['role']);
        }
    }
}
