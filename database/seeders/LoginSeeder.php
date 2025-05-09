<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class LoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::create([
            'name'        => "Admin",
            'contact'     => "03061594877",
            'email'       => "admin@admin.com",
            'email_verified_at' => now(),
            'password'    => Hash::make("admin1234"), // password
            'role_id'     => "1",
            'company_id'  => "0",
            'is_super_admin' => "1",
            'remember_token' => Str::random(10),
        ]);
    }
}
