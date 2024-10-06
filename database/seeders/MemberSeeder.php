<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
            'first_name' => 'Member',
            'last_name' => 'Member',
            'email' => 'member@member.com',
            'password' => Hash::make('password'),
            'phone' => '0123456789',
            'job_title' => 'Software Engineer',
            'status' => 'pending',
        ]);
        DB::table('users')->insert([
            'first_name' => 'Member2',
            'last_name' => 'Member2',
            'email' => 'member2@member.com',
            'password' => Hash::make('password'),
            'phone' => '0123456789',
            'job_title' => 'Software Engineer',
            
        ]);
    }
}
