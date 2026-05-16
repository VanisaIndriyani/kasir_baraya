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
        $receiptText = $this->buildReceiptText($trx);

        return view('kasir.receipt', [
            'trx' => $trx,
            'receiptText' => $receiptText,
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
        $content = $this->buildReceiptText($trx);
        $filename = 'resi_' . (string) $trx->invoice . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildReceiptText(Transaction $trx): string
    {
        $items = $trx->items()->orderBy('id')->get(['product_name', 'price', 'qty', 'subtotal']);

        $W = 32;
        $line = str_repeat('-', $W);
        $line2 = str_repeat('=', $W);

        $createdAt = $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '';

        $out = [];
        $out[] = $line2;
        $out[] = str_pad('ES BARAYA', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('Jl. Sukun No.26', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('Ngringin, Condongcatur', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('Sleman, DIY 55283', $W, ' ', STR_PAD_BOTH);
        $out[] = $line2;
        $out[] = $line;
        $out[] = $this->formatMeta('TGL', $createdAt, $W);
        $out[] = $this->formatMeta('INV', (string) $trx->invoice, $W);
        $out[] = $this->formatMeta('PAY', strtoupper((string) $trx->payment_method), $W);
        $out[] = $line;

        foreach ($items as $it) {
            $name = (string) $it->product_name;
            $qty = (int) $it->qty;
            $price = (int) $it->price;
            $subtotal = (int) $it->subtotal;

            foreach ($this->wrapReceiptText($name, $W) as $nameLine) {
                $out[] = $nameLine;
            }
            $left = $qty . 'x' . number_format($price, 0, ',', '.');
            $right = number_format($subtotal, 0, ',', '.');
            $out[] = $this->padRight($left, $W - mb_strwidth($right, 'UTF-8') - 1) . ' ' . $right;
        }

        $out[] = $line;
        $out[] = $this->padRight('TOTAL', 10) . $this->padLeft('Rp ' . number_format((int) $trx->total, 0, ',', '.'), $W - 10);
        $out[] = $this->padRight('BAYAR', 10) . $this->padLeft('Rp ' . number_format((int) $trx->paid_amount, 0, ',', '.'), $W - 10);
        $out[] = $this->padRight('KEMBALI', 10) . $this->padLeft('Rp ' . number_format((int) $trx->change_amount, 0, ',', '.'), $W - 10);
        $out[] = $line;
        $out[] = $line2;
        $out[] = str_pad('Terima kasih!', $W, ' ', STR_PAD_BOTH);
        $out[] = $line2;
        $out[] = '';
        $out[] = '';
        $out[] = '';
        $out[] = '';

        return implode("\r\n", $out) . "\r\n";
    }

    private function formatMeta(string $label, string $value, int $w): string
    {
        $prefix = $this->padRight($label, 4) . ': ';
        $v = $value;
        $max = $w - mb_strwidth($prefix, 'UTF-8');
        if ($max <= 0) {
            return mb_strimwidth($prefix, 0, $w, '', 'UTF-8');
        }
        if (mb_strwidth($v, 'UTF-8') > $max) {
            $v = mb_strimwidth($v, 0, $max, '', 'UTF-8');
        }
        return $prefix . $v;
    }

    private function wrapReceiptText(string $text, int $w): array
    {
        $t = trim((string) (preg_replace('/\s+/u', ' ', $text) ?? $text));
        if ($t === '') {
            return [''];
        }

        $words = preg_split('/\s+/u', $t) ?: [$t];
        $lines = [];
        $cur = '';

        foreach ($words as $word) {
            $word = (string) $word;
            if ($word === '') {
                continue;
            }
            if (mb_strwidth($word, 'UTF-8') > $w) {
                if ($cur !== '') {
                    $lines[] = $cur;
                    $cur = '';
                }
                $lines[] = mb_strimwidth($word, 0, $w, '', 'UTF-8');
                continue;
            }

            $candidate = $cur === '' ? $word : ($cur . ' ' . $word);
            if (mb_strwidth($candidate, 'UTF-8') <= $w) {
                $cur = $candidate;
                continue;
            }

            if ($cur !== '') {
                $lines[] = $cur;
            }
            $cur = $word;
        }

        if ($cur !== '') {
            $lines[] = $cur;
        }

        return array_map(fn ($l) => mb_strimwidth($l, 0, $w, '', 'UTF-8'), $lines);
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
