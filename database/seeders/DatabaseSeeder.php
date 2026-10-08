<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos iniciales: categorías base y configuración por defecto.
     * El usuario del panel se crea con: php artisan make:filament-user
     */
    public function run(): void
    {
        foreach (['Aros', 'Collares', 'Anillos', 'Pulseras'] as $i => $name) {
            Category::firstOrCreate(['name' => $name], ['sort_order' => $i + 1]);
        }

        $defaults = [
            'whatsapp_number' => '',
            'whatsapp_greeting' => '¡Hola Kira! Quiero consultar por estos productos:',
            'instagram_url' => '',
            'contact_email' => '',
            'announcement' => '',
            'about_text' => '',
        ];

        // Solo completa las claves que todavía no existen.
        Setting::setMany(array_diff_key($defaults, Setting::pluck('value', 'key')->all()));
    }
}
