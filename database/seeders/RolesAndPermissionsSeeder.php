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

        // create permissions
        $permissions = [
            'manage users',
            'view all projects',
            'view own projects',
            'create project',
            'edit project',
            'delete project',
            'manage billings',
            'manage invoices',
            'manage payments',
            'manage suppliers',
            'manage purchase_orders',
            'approve purchase_orders',
            'manage inventory',
            'view inventory',
            'manage ahs_library',
            'manage work_types',
            'manage equipment',
            'manage labor_rates',
            'create material_request',
            'approve material_request',
            'create progress_update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles and assign existing permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        // Give Admin all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Create other roles (with no permissions by default)
        Role::firstOrCreate(['name' => 'Project Manager']);
        Role::firstOrCreate(['name' => 'Site Manager']);
    }
}