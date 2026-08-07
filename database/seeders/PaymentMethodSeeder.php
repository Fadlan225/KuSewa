<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['BCA', 'bca'], ['Mandiri', 'mandiri'], ['BRI', 'bri'], ['BNI', 'bni'], ['QRIS', 'qris']] as $index => [$name, $code]) {
            PaymentMethod::updateOrCreate(['code' => $code], ['name' => $name, 'description' => 'Metode pembayaran yang tercatat.', 'is_active' => true, 'sort_order' => $index + 1]);
        }
    }
}
