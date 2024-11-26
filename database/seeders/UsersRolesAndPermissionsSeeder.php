<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UsersRolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'manage appointments',    // Assistant permissions
            'manage waiting room',    // Assistant permissions
            'manage patients',        // Assistant permissions
            'manage consultations',   // Medecin only
            'view all data',          // Medecin only
            'manage prescriptions'    // Medecin only
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles
        $medecinRole = Role::firstOrCreate(['name' => 'medecin']);
        $assistanteRole = Role::firstOrCreate(['name' => 'assistante']);

        // Assign permissions to roles
        $medecinRole->givePermissionTo(Permission::all()); // Medecin can do everything
        $assistanteRole->givePermissionTo(['manage appointments', 'manage waiting room', 'manage patients']);

        // Create a sample user with the role of medecin
        $medecinUser = User::firstOrCreate([
            'email' => 'medecin@example.com',
        ], [
            'name' => 'Dr. Medecin',
            'password' => Hash::make('password'),
        ]);
        $medecinUser->assignRole($medecinRole);

        // Create a sample user with the role of assistante
        $assistanteUser = User::firstOrCreate([
            'email' => 'assistante@example.com',
        ], [
            'name' => 'Mme Assistante',
            'password' => Hash::make('password'),
        ]);
        $assistanteUser->assignRole($assistanteRole);
    }
}
