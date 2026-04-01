<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'whatsapp_number'],
            [
                'value' => '6281234567890',
                'description' => 'Nomor WhatsApp untuk tombol bantuan pelanggan'
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'whatsapp_message'],
            [
                'value' => 'Halo Admin SonaraRent, saya ingin bertanya mengenai penyewaan alat musik.',
                'description' => 'Pesan pembuka otomatis saat pelanggan klik tombol WhatsApp'
            ]
        );
    }
}
