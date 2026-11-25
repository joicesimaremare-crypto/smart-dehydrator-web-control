<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeviceController extends Controller
{
    // Halaman dashboard
    public function index()
    {
        return view('dashboard');
    }

    // Data untuk dashboard (dipanggil JavaScript)
    private function payload()
    {
        $last = Cache::get('esp_last_seen');

        return [
            'esp_online' => $last && now()->diffInSeconds($last, true) <= 10,
            'devices'    => Device::orderBy('id')->get(),
        ];
    }

    public function list()
    {
        return response()->json($this->payload());
    }

    // Klik ON/OFF satu perangkat
    public function toggle(Device $device)
    {
        $device->update(['state' => ! $device->state]);

        return response()->json($this->payload());
    }

    // Tombol matikan semua
    public function allOff()
    {
        Device::query()->update(['state' => false]);

        return response()->json($this->payload());
    }

    // Endpoint yang dibaca ESP32 -> contoh balasan: 1,0,0,1
    public function esp(Request $request)
    {
        if ($request->query('token') !== env('ESP_TOKEN')) {
            return response('Unauthorized', 401);
        }

        Cache::put('esp_last_seen', now());

        $states = Device::orderBy('id')->pluck('state')
            ->map(fn ($s) => $s ? 1 : 0)
            ->implode(',');

        return response($states, 200)->header('Content-Type', 'text/plain');
    }
}