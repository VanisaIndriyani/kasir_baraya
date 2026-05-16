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
            ['name' => 'Teh Kampull', 'price' => 4000],
            ['name' => 'Teh Jumbo', 'price' => 3000],

            ['name' => 'Sultan Alpokat (Medium)', 'price' => 12000],
            ['name' => 'Sultan Alpokat (Large)', 'price' => 15000],

            ['name' => 'Durian Sultan (Medium)', 'price' => 13000],
            ['name' => 'Durian Sultan (Large)', 'price' => 16000],

            ['name' => 'Teller Nangka Creamy (Medium)', 'price' => 10000],
            ['name' => 'Teller Nangka Creamy (Large)', 'price' => 13000],

            ['name' => 'Teller Durian Royale (Medium)', 'price' => 13000],
            ['name' => 'Teller Durian Royale (Large)', 'price' => 15000],

            ['name' => 'Dawet Original Baraya (Medium)', 'price' => 12000],
            ['name' => 'Dawet Original Baraya (Jumbo)', 'price' => 14000],

            ['name' => 'Dawet Lava Durian (Medium)', 'price' => 15000],
            ['name' => 'Dawet Lava Durian (Large)', 'price' => 17000],
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
                    'image' => null,
                    'created_at' => $now,
                    'updated_at' => null,
                ];
            }, $menu));
            return;
        }

        foreach ($menu as $row) {
            $exists = DB::table('products')->where('name', $row['name'])->exists();
            if ($exists) {
                continue;
            }
            DB::table('products')->insert([
                'name' => $row['name'],
                'price' => $row['price'],
                'stock' => $defaultStock,
                'image' => null,
                'created_at' => $now,
                'updated_at' => null,
            ]);
        }
    }
}
