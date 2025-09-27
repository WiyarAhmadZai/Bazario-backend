<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'approve products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view orders',
            'edit orders',
            'view users',
            'edit users',
            'delete users',
            'manage roles',
            'manage permissions',
            'view commissions',
            'edit commissions',
            'view reports',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);

        // Assign permissions to roles
        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Seller permissions
        $sellerPermissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view orders',
            'edit orders',
        ];
        $sellerRole->givePermissionTo($sellerPermissions);

        // Buyer permissions
        $buyerPermissions = [
            'view products',
        ];
        $buyerRole->givePermissionTo($buyerPermissions);
    }
}
