<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'phone' => '0123456789',
            'job_title' => 'Software Engineer',
            'status' => 'approved',
            'role' => 'super-admin',
           ]);

           DB::table('users')->insert([
            'first_name' => 'simple',
            'last_name' => 'Admin',
            'email' => 'simple@admin.com',
            'password' => Hash::make('password'),
            'phone' => '0123456789',
            'job_title' => 'medecin généraliste',
            'status' => 'approved',
            'role' => 'admin',
           ]);
    }
}
