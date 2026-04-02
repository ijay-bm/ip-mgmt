<?php

namespace Database\Seeders;

use Database\Factories\IpAddressFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IpAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IpAddressFactory::new()->count(10)->create();
    }
}
