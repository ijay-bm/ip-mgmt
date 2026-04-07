<?php

namespace Database\Seeders;

use App\Models\IpAddress;
use Illuminate\Database\Seeder;

class IpAddressSeeder extends Seeder
{
    public function run(): void
    {
        IpAddress::factory()->count(100)->create();
    }
}
