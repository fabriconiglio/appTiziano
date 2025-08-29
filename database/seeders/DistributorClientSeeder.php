<?php

namespace Database\Seeders;

use App\Models\DistributorClient;
use Illuminate\Database\Seeder;

class DistributorClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $distributors = [
            [
                'name' => 'María',
                'surname' => 'González',
                'email' => 'maria.gonzalez@email.com',
                'dni' => '12345678',
                'phone' => '3511234567',
                'birth_date' => '1985-03-15',
                'domicilio' => 'Av. San Martín 123, Córdoba',
                'observations' => 'Distribuidora de productos de belleza'
            ],
            [
                'name' => 'Carlos',
                'surname' => 'Rodríguez',
                'email' => 'carlos.rodriguez@email.com',
                'dni' => '23456789',
                'phone' => '3512345678',
                'birth_date' => '1978-07-22',
                'domicilio' => 'Belgrano 456, Córdoba',
                'observations' => 'Peluquería y distribuidor'
            ],
            [
                'name' => 'Ana',
                'surname' => 'López',
                'email' => 'ana.lopez@email.com',
                'dni' => '34567890',
                'phone' => '3513456789',
                'birth_date' => '1990-11-08',
                'domicilio' => 'Rivadavia 789, Córdoba',
                'observations' => 'Centro de belleza integral'
            ],
            [
                'name' => 'Roberto',
                'surname' => 'Martínez',
                'email' => 'roberto.martinez@email.com',
                'dni' => '45678901',
                'phone' => '3514567890',
                'birth_date' => '1982-05-12',
                'domicilio' => 'Independencia 321, Córdoba',
                'observations' => 'Distribuidor mayorista'
            ],
            [
                'name' => 'Laura',
                'surname' => 'Fernández',
                'email' => 'laura.fernandez@email.com',
                'dni' => '56789012',
                'phone' => '3515678901',
                'birth_date' => '1988-09-30',
                'domicilio' => 'Colón 654, Córdoba',
                'observations' => 'Salón de belleza premium'
            ]
        ];

        foreach ($distributors as $distributor) {
            DistributorClient::create($distributor);
        }
    }
}
