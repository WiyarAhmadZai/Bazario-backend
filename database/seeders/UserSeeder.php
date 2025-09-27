<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin users
        $admins = [];
        for ($i = 1; $i <= 2; $i++) {
            $admin = User::create([
                'name' => 'Admin ' . $i,
                'email' => 'admin' . $i . '@example.com',
                'phone' => '+93 000 000 000' . $i,
                'password' => Hash::make('password'),
                'verified' => true,
                'wallet_balance' => 0.00,
            ]);
            $admin->assignRole('admin');
            $admins[] = $admin;
        }

        // Create seller users
        $sellers = [];
        for ($i = 1; $i <= 5; $i++) {
            $seller = User::create([
                'name' => 'Seller ' . $i,
                'email' => 'seller' . $i . '@example.com',
                'phone' => '+93 111 111 111' . $i,
                'password' => Hash::make('password'),
                'verified' => true,
                'wallet_balance' => 0.00,
                'bank_account_info' => json_encode([
                    'account_name' => 'Seller ' . $i,
                    'account_number' => 'ACC0000000' . $i,
                    'bank_name' => 'Sample Bank',
                ]),
            ]);
            $seller->assignRole('seller');
            $sellers[] = $seller;
        }

        // Create buyer users
        $buyers = [];
        for ($i = 1; $i <= 20; $i++) {
            $buyer = User::create([
                'name' => 'Buyer ' . $i,
                'email' => 'buyer' . $i . '@example.com',
                'phone' => '+93 222 222 222' . $i,
                'password' => Hash::make('password'),
                'verified' => true,
                'wallet_balance' => 1000.00, // Give buyers some initial balance
            ]);
            $buyer->assignRole('buyer');
            $buyers[] = $buyer;
        }
    }
}
