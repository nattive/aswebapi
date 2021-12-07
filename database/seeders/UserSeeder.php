<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Femi Ola',
            'email' => 'ola@example.com',
            'password' => bcrypt('password'),
            "role" => "DIRECTOR",
            "phonenumber" => "08045125632",
            "store_id" => "1",
            "address" => "18 lorem",
        ]);
    }


}
