<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function index()
    {
        $printMode = (string) (Setting::find('print_mode')->value ?? 'browser');
        if (!in_array($printMode, ['browser', 'server'], true)) {
            $printMode = 'browser';
        }

        return view('kasir.index', [
            'pageTitle' => 'Kasir - ' . config('app.name'),
            'bodyClass' => 'kasir-page',
            'csrf' => csrf_token(),
            'baseUrl' => url(''),
            'printMode' => $printMode,
        ]);
    }

    public function receipt(Request $request)
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $trx = Transaction::query()->whereKey($id)->first();
        if (!$trx) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $items = $trx->items()->orderBy('id')->get();

        $createdAt = $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '';

        return view('kasir.receipt', [
            'trx' => $trx,
            'items' => $items,
            'createdAt' => $createdAt,
        ]);
    }
}
