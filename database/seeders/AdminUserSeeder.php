<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $adminExists = User::where('role', 'admin')->exists();

        if ($adminExists) {
            $this->command->info('Admin user already exists. Skipping...');
            return;
        }

        // Create first admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@formio.com',
            'password' => Hash::make('admin123456'),
            'role' => 'admin',
            'language' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'is_active' => true,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@formio.com');
        $this->command->info('Password: admin123456');
        $this->command->warn('⚠️  IMPORTANT: Change this password immediately after first login!');

        // Create additional admin for Arabic
        $adminAr = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin.ar@formio.com',
            'password' => Hash::make('admin123456'),
            'role' => 'admin',
            'language' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'is_active' => true,
        ]);

        $this->command->info('');
        $this->command->info('Arabic admin user created successfully!');
        $this->command->info('Email: admin.ar@formio.com');
        $this->command->info('Password: admin123456');
    }
}
