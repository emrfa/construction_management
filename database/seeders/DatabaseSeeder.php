<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StockLocationSeeder::class);
        // --- THIS IS THE PROPER FLOW ---

        // 1. Call the Roles and Permissions seeder first.
        // This creates 'Admin', 'Site Manager', etc.
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Create your default Admin User
        // firstOrCreate prevents errors if the user already exists.
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mail.com'], // Find user by this email
            [ // If not found, create with this data
                'name' => 'Admin User',
                'password' => bcrypt('password') // 'password' is 'password'
            ]
        );
        
        // 3. Find the 'Admin' role and assign it to your new user.
        $adminRole = Role::findByName('Admin');
        $adminUser->assignRole($adminRole);
        
        // (Optional) Create a default Site Manager user for testing
        $siteManagerUser = User::firstOrCreate(
            ['email' => 'sitemanager@mail.com'],
            [
                'name' => 'Site Manager',
                'password' => bcrypt('password')
            ]
        );
        $siteManagerUser->assignRole('Site Manager');
    }
}