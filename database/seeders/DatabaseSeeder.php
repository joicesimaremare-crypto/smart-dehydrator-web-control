<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            ['name' => 'Heater 1', 'type' => 'heater', 'gpio' => 25],
            ['name' => 'Heater 2', 'type' => 'heater', 'gpio' => 26],
            ['name' => 'Fan 1',    'type' => 'fan',    'gpio' => 27],
            ['name' => 'Fan 2',    'type' => 'fan',    'gpio' => 33],
        ];

        foreach ($devices as $d) {
            Device::create($d + ['state' => false]);
        }
    }
}