<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminSalesController extends Controller
{
    public function index(Request $request)
    {
        $start = (string) $request->query('start', now()->toDateString());
        $end = (string) $request->query('end', now()->toDateString());

        $isDate = fn (string $d) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
        if (!$isDate($start)) {
            $start = now()->toDateString();
        }
        if (!$isDate($end)) {
            $end = now()->toDateString();
        }

        $salesQuery = Transaction::query()
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);

        $sales = (clone $salesQuery)->orderByDesc('id')->get();
        $totalRevenue = (int) (clone $salesQuery)->sum('total');
        $totalTrx = (int) (clone $salesQuery)->count();

        return view('admin.sales', [
            'pageTitle' => 'Penjualan',
            'bodyClass' => 'admin',
            'admin' => session('admin'),
            'sales' => $sales,
            'start' => $start,
            'end' => $end,
            'totalRevenue' => $totalRevenue,
            'totalTrx' => $totalTrx,
            'detailUrl' => url('/admin/api/transaction_detail.php'),
        ]);
    }

    public function transactionDetail(Request $request)
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return response()->json(['ok' => false, 'message' => 'Transaksi tidak valid.'], 422);
        }

        $trx = Transaction::query()->whereKey($id)->first();
        if (!$trx) {
            return response()->json(['ok' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $items = $trx->items()->orderBy('id')->get(['product_name', 'price', 'qty', 'subtotal']);

        $createdAt = $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '';

        return response()->json([
            'ok' => true,
            'data' => [
                'transaction' => [
                    'id' => (int) $trx->id,
                    'invoice' => (string) $trx->invoice,
                    'order_type' => (string) $trx->order_type,
                    'platform' => $trx->platform ? (string) $trx->platform : '',
                    'payment_method' => (string) $trx->payment_method,
                    'total' => (int) $trx->total,
                    'paid_amount' => (int) $trx->paid_amount,
                    'change_amount' => (int) $trx->change_amount,
                    'created_at' => $createdAt,
                ],
                'items' => $items->map(fn ($it) => [
                    'product_name' => (string) $it->product_name,
                    'price' => (int) $it->price,
                    'qty' => (int) $it->qty,
                    'subtotal' => (int) $it->subtotal,
                ])->values(),
            ],
        ]);
    }
}
