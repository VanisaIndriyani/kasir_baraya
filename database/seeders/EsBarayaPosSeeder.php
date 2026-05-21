<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class EsBarayaPosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->updateOrInsert(
            ['email' => 'admin@esbaraya.com'],
            [
                'password_hash' => Hash::make('admin123'),
                'created_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'print_mode'],
            ['value' => 'browser', 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'printer_name'],
            ['value' => '', 'updated_at' => now()]
        );

        $menu = [
            ['name' => 'Teh Kampull', 'price' => 4000, 'image' => 'produk/teh kampul.jpeg'],
            ['name' => 'Teh Jumbo', 'price' => 3000, 'image' => 'produk/teh jumbo.jpeg'],

            ['name' => 'Alpukat Shake (Medium)', 'price' => 12000, 'image' => 'produk/alpukat shake.jpeg'],
            ['name' => 'Alpukat Shake (Large)', 'price' => 15000, 'image' => 'produk/alpukat shake.jpeg'],

            ['name' => 'Durian Shake (Medium)', 'price' => 13000, 'image' => 'produk/durian shake.jpeg'],
            ['name' => 'Durian Shake (Large)', 'price' => 16000, 'image' => 'produk/durian shake.jpeg'],

            ['name' => 'Teller Nangka Creamy (Medium)', 'price' => 10000, 'image' => 'produk/es teller nangka.jpeg'],
            ['name' => 'Teller Nangka Creamy (Large)', 'price' => 13000, 'image' => 'produk/es teller nangka.jpeg'],

            ['name' => 'Teller Durian Royale (Medium)', 'price' => 13000, 'image' => 'produk/es teller durian.jpeg'],
            ['name' => 'Teller Durian Royale (Large)', 'price' => 15000, 'image' => 'produk/es teller durian.jpeg'],

            ['name' => 'Dawet Original Baraya (Medium)', 'price' => 12000, 'image' => 'produk/dawet original barya.jpeg'],
            ['name' => 'Dawet Original Baraya (Jumbo)', 'price' => 14000, 'image' => 'produk/dawet original barya.jpeg'],

            ['name' => 'Dawet Durian Jumbo (Medium)', 'price' => 15000, 'image' => 'produk/es dawet durian.jpeg'],
            ['name' => 'Dawet Durian Jumbo (Large)', 'price' => 17000, 'image' => 'produk/es dawet durian.jpeg'],
        ];

        $hasTransactions = DB::table('transactions')->exists();

        if (!$hasTransactions) {
            DB::table('transaction_items')->delete();
            DB::table('transactions')->delete();
            DB::table('products')->delete();
        }

        $now = now();
        $defaultStock = 999;

        if (!$hasTransactions) {
            DB::table('products')->insert(array_map(function (array $row) use ($now, $defaultStock) {
                return [
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'stock' => $defaultStock,
                    'image' => $row['image'] ?? null,
                    'created_at' => $now,
                    'updated_at' => null,
                ];
            }, $menu));
            return;
        }

        foreach ($menu as $row) {
            $existing = DB::table('products')->select(['id', 'image'])->where('name', $row['name'])->first();
            if ($existing) {
                $existingImage = (string) (($existing->image ?? '') ?: '');
                $nextImage = (string) (($row['image'] ?? '') ?: '');
                if ($existingImage === '' && $nextImage !== '') {
                    DB::table('products')->where('id', (int) $existing->id)->update([
                        'image' => $nextImage,
                        'updated_at' => $now,
                    ]);
                }
                continue;
            }
            DB::table('products')->insert([
                'name' => $row['name'],
                'price' => $row['price'],
                'stock' => $defaultStock,
                'image' => $row['image'] ?? null,
                'created_at' => $now,
                'updated_at' => null,
            ]);
        }
    }
}
