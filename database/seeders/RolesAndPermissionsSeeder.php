<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Create All Permissions ---
        $permissions = [
            'manage users',
            'view all projects', 'view own projects', 'create project', 'edit project', 'delete project',
            'manage billings', 'manage invoices', 'manage payments',
            'manage suppliers', 'manage purchase_orders', 'approve purchase_orders',
            'manage inventory', 'view inventory',
            'manage ahs_library', 'manage work_types', 'manage equipment', 'manage labor_rates',
            'create material_request', 'approve material_request',
            'create progress_update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Create Roles & Assign Permissions ---

        // 1. Admin (Gets all permissions)
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. Project Manager
        Role::firstOrCreate(['name' => 'Project Manager'])
            ->givePermissionTo([
                'view all projects',
                'create project',
                'edit project',
                'approve material_request',
                'approve purchase_orders'
            ]);

        // 3. Site Manager (The one we discussed)
        Role::firstOrCreate(['name' => 'Site Manager'])
            ->givePermissionTo([
                'view own projects',
                'view inventory',
                'create material_request',
                'create progress_update'
            ]);
            
        // 4. Accountant
        Role::firstOrCreate(['name' => 'Accountant'])
            ->givePermissionTo([
                'manage billings',
                'manage invoices',
                'manage payments'
            ]);
            
        // 5. Client
        Role::firstOrCreate(['name' => 'Client']); // No permissions by default
    }
}