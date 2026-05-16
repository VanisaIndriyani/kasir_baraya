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

    public function receiptText(Request $request)
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $trx = Transaction::query()->whereKey($id)->first();
        if (!$trx) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $items = $trx->items()->orderBy('id')->get(['product_name', 'price', 'qty', 'subtotal']);

        $W = 32;
        $line = str_repeat('-', $W);

        $createdAt = $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '';

        $out = [];
        $out[] = str_pad('ES BARAYA', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('Jl. Sukun No.26', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('Condongcatur - Depok', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('Sleman, DIY 55283', $W, ' ', STR_PAD_BOTH);
        $out[] = $line;
        $out[] = 'Tgl: ' . $createdAt;
        $out[] = 'Inv: ' . (string) $trx->invoice;
        $out[] = 'Pay: ' . strtoupper((string) $trx->payment_method);
        $out[] = $line;

        foreach ($items as $it) {
            $name = (string) $it->product_name;
            $qty = (int) $it->qty;
            $price = (int) $it->price;
            $subtotal = (int) $it->subtotal;

            $out[] = mb_strimwidth($name, 0, $W, '', 'UTF-8');
            $left = $qty . 'x' . number_format($price, 0, ',', '.');
            $right = number_format($subtotal, 0, ',', '.');
            $out[] = $this->padRight($left, $W - mb_strwidth($right, 'UTF-8') - 1) . ' ' . $right;
        }

        $out[] = $line;
        $out[] = $this->padRight('TOTAL', 10) . $this->padLeft('Rp ' . number_format((int) $trx->total, 0, ',', '.'), $W - 10);
        $out[] = $this->padRight('BAYAR', 10) . $this->padLeft('Rp ' . number_format((int) $trx->paid_amount, 0, ',', '.'), $W - 10);
        $out[] = $this->padRight('KEMBALI', 10) . $this->padLeft('Rp ' . number_format((int) $trx->change_amount, 0, ',', '.'), $W - 10);
        $out[] = $line;
        $out[] = str_pad('Terima kasih!', $W, ' ', STR_PAD_BOTH);
        $out[] = "\r\n";

        $content = implode("\r\n", $out);
        $filename = 'resi_' . (string) $trx->invoice . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function padRight(string $s, int $w): string
    {
        $len = mb_strwidth($s, 'UTF-8');
        if ($len >= $w) {
            return mb_strimwidth($s, 0, $w, '', 'UTF-8');
        }
        return $s . str_repeat(' ', $w - $len);
    }

    private function padLeft(string $s, int $w): string
    {
        $len = mb_strwidth($s, 'UTF-8');
        if ($len >= $w) {
            return mb_strimwidth($s, 0, $w, '', 'UTF-8');
        }
        return str_repeat(' ', $w - $len) . $s;
    }
}
