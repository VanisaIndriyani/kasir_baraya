<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalProducts = (int) DB::table('products')->count();

        $revenueToday = (int) DB::table('transactions')
            ->whereDate('created_at', now()->toDateString())
            ->sum('total');

        $trxToday = (int) DB::table('transactions')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $revenue30 = (int) DB::table('transactions')
            ->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
            ->sum('total');

        $topProducts = DB::table('transaction_items')
            ->selectRaw('product_name, SUM(qty) AS qty_sold')
            ->groupBy('product_name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get();

        $rows = DB::table('transactions')
            ->selectRaw('DATE(created_at) AS d, COALESCE(SUM(total),0) AS total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('d')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r->d] = (int) $r->total;
        }

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels[] = $d;
            $values[] = $map[$d] ?? 0;
        }

        return view('admin.dashboard', [
            'pageTitle' => 'Dashboard',
            'bodyClass' => 'admin',
            'admin' => session('admin'),
            'totalProducts' => $totalProducts,
            'revenueToday' => $revenueToday,
            'trxToday' => $trxToday,
            'revenue30' => $revenue30,
            'topProducts' => $topProducts,
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}

