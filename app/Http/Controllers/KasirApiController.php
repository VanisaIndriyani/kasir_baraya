<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirApiController extends Controller
{
    public function products(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $limit = 80;

        $query = Product::query()->select(['id', 'name', 'price', 'stock', 'image'])->orderBy('name');
        if ($q !== '') {
            $normalized = mb_strtolower($q, 'UTF-8');
            $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? $normalized;
            $tokens = preg_split('/\s+/u', trim($normalized)) ?: [];
            $tokens = array_values(array_filter(array_unique($tokens), fn ($t) => $t !== ''));

            $syn = [
                'l' => 'large',
                'lg' => 'large',
                'm' => 'medium',
                'md' => 'medium',
                'j' => 'jumbo',
                'jb' => 'jumbo',
            ];

            $tokens = array_slice($tokens, 0, 6);
            foreach ($tokens as $t) {
                $t2 = $syn[$t] ?? $t;
                $query->where('name', 'like', '%' . $t2 . '%');
            }
        }
        $rows = $query->limit($limit)->get();

        $data = $rows->map(function (Product $p) {
            $image = (string) ($p->image ?? '');
            $imageUrl = $this->productImageUrl($image, (string) $p->name, (int) $p->id);
            return [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'price' => (int) $p->price,
                'stock' => (int) $p->stock,
                'image_url' => $imageUrl,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function cart(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
        }

        $action = (string) $request->input('action', '');

        if ($action === 'clear') {
            $request->session()->put('cart', []);
            return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
        }

        $productId = (int) $request->input('product_id', 0);
        if ($productId <= 0) {
            return response()->json(['ok' => false, 'message' => 'Produk tidak valid.'], 422);
        }

        $product = Product::query()->select(['id', 'name', 'price', 'stock', 'image'])->whereKey($productId)->first();
        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $stock = (int) $product->stock;
        $cart = $this->cartGetRaw($request);
        $currentQty = (int) (($cart[$productId]['qty'] ?? 0));

        if ($action === 'add') {
            $qty = max(1, (int) $request->input('qty', 1));
            $newQty = $currentQty + $qty;
            if ($newQty > $stock) {
                return response()->json(['ok' => false, 'message' => 'Stok tidak cukup.'], 422);
            }
            $cart[$productId] = [
                'name' => (string) $product->name,
                'price' => (int) $product->price,
                'image' => (string) ($product->image ?? ''),
                'qty' => $newQty,
            ];
            $this->cartSetRaw($request, $cart);
            return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
        }

        if ($action === 'adjust') {
            $delta = (int) $request->input('delta', 0);
            if ($delta === 0) {
                return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
            }
            $newQty = $currentQty + $delta;
            if ($newQty <= 0) {
                unset($cart[$productId]);
                $this->cartSetRaw($request, $cart);
                return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
            }
            if ($newQty > $stock) {
                return response()->json(['ok' => false, 'message' => 'Stok tidak cukup.'], 422);
            }
            if (!isset($cart[$productId])) {
                $cart[$productId] = [
                    'name' => (string) $product->name,
                    'price' => (int) $product->price,
                    'image' => (string) ($product->image ?? ''),
                    'qty' => $newQty,
                ];
            } else {
                $cart[$productId]['qty'] = $newQty;
            }
            $this->cartSetRaw($request, $cart);
            return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
        }

        if ($action === 'remove') {
            unset($cart[$productId]);
            $this->cartSetRaw($request, $cart);
            return response()->json(['ok' => true, 'data' => $this->cartBuildResponse($request)]);
        }

        return response()->json(['ok' => false, 'message' => 'Aksi tidak dikenali.'], 400);
    }

    public function checkout(Request $request)
    {
        $paymentMethod = (string) $request->input('payment_method', 'cash');
        $paymentMethod = in_array($paymentMethod, ['cash', 'qris'], true) ? $paymentMethod : 'cash';

        $paidAmount = (int) $request->input('paid_amount', 0);
        if ($paidAmount < 0) {
            $paidAmount = 0;
        }

        $cart = (array) $request->session()->get('cart', []);
        if (!$cart) {
            return response()->json(['ok' => false, 'message' => 'Keranjang kosong.'], 422);
        }

        $productIds = array_values(array_filter(array_map('intval', array_keys($cart)), fn ($v) => $v > 0));
        if (!$productIds) {
            return response()->json(['ok' => false, 'message' => 'Keranjang tidak valid.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($productIds, $cart, $paymentMethod, $paidAmount) {
                $products = Product::query()
                    ->select(['id', 'name', 'price', 'stock'])
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $items = [];
                $total = 0;

                foreach ($cart as $pidStr => $row) {
                    $pid = (int) $pidStr;
                    $qty = (int) ($row['qty'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $p = $products->get($pid);
                    if (!$p) {
                        throw new \RuntimeException('Produk tidak ditemukan: #' . $pid);
                    }

                    $stock = (int) $p->stock;
                    if ($qty > $stock) {
                        throw new \RuntimeException('Stok tidak cukup untuk: ' . (string) $p->name);
                    }

                    $price = (int) $p->price;
                    $subtotal = $price * $qty;
                    $total += $subtotal;
                    $items[] = [
                        'product_id' => $pid,
                        'product_name' => (string) $p->name,
                        'price' => $price,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                    ];
                }

                if (!$items || $total <= 0) {
                    throw new \RuntimeException('Keranjang kosong.');
                }

                if ($paymentMethod === 'cash') {
                    if ($paidAmount < $total) {
                        throw new \RuntimeException('Uang bayar kurang.');
                    }
                    $changeAmount = $paidAmount - $total;
                } else {
                    $paidAmount = $total;
                    $changeAmount = 0;
                }

                $today = now()->format('Ymd');
                $prefix = 'EB' . $today . '-';
                $last = Transaction::query()
                    ->select(['invoice'])
                    ->where('invoice', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $seq = 1;
                if ($last && is_string($last->invoice) && str_starts_with($last->invoice, $prefix)) {
                    $tail = substr($last->invoice, strlen($prefix));
                    $n = (int) preg_replace('/\D+/', '', (string) $tail);
                    $seq = max(1, $n + 1);
                }
                $invoice = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

                $trx = Transaction::query()->create([
                    'invoice' => $invoice,
                    'order_type' => 'offline',
                    'platform' => null,
                    'payment_method' => $paymentMethod,
                    'total' => $total,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'created_at' => now(),
                ]);

                foreach ($items as $it) {
                    TransactionItem::query()->create([
                        'transaction_id' => (int) $trx->id,
                        'product_id' => $it['product_id'],
                        'product_name' => $it['product_name'],
                        'price' => $it['price'],
                        'qty' => $it['qty'],
                        'subtotal' => $it['subtotal'],
                    ]);

                    Product::query()->whereKey($it['product_id'])->decrement('stock', $it['qty']);
                }

                return ['transaction_id' => (int) $trx->id, 'invoice' => $invoice];
            }, 3);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $request->session()->put('cart', []);
        $request->session()->put('last_transaction_id', $result['transaction_id']);

        return response()->json(['ok' => true, 'data' => $result]);
    }

    public function printServer(Request $request)
    {
        $printMode = (string) (Setting::find('print_mode')->value ?? 'browser');
        if ($printMode !== 'server') {
            return response()->json(['ok' => false, 'message' => 'Mode print bukan server.'], 422);
        }

        $printerName = (string) (Setting::find('printer_name')->value ?? '');
        if ($printerName === '') {
            return response()->json(['ok' => false, 'message' => 'Nama printer belum diatur.'], 422);
        }

        if (!$this->validatePrinterName($printerName)) {
            return response()->json(['ok' => false, 'message' => 'Nama printer tidak valid.'], 422);
        }

        $id = (int) $request->input('transaction_id', 0);
        if ($id <= 0) {
            return response()->json(['ok' => false, 'message' => 'Transaksi tidak valid.'], 422);
        }

        $trx = Transaction::query()->whereKey($id)->first();
        if (!$trx) {
            return response()->json(['ok' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }
        $items = $trx->items()->orderBy('id')->get();

        $W = 32;
        $line = str_repeat('-', $W);
        $createdAt = $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '';

        $out = [];
        $out[] = str_pad('ES BARAYA', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('POS - Minuman', $W, ' ', STR_PAD_BOTH);
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
            $left = $qty . 'x' . $this->rupiah($price);
            $right = $this->rupiah($subtotal);
            $out[] = $this->padRight($left, $W - mb_strwidth($right, 'UTF-8') - 1) . ' ' . $right;
        }

        $out[] = $line;
        $out[] = $this->padRight('TOTAL', 10) . $this->padLeft('Rp ' . $this->rupiah((int) $trx->total), $W - 10);
        $out[] = $this->padRight('BAYAR', 10) . $this->padLeft('Rp ' . $this->rupiah((int) $trx->paid_amount), $W - 10);
        $out[] = $this->padRight('KEMBALI', 10) . $this->padLeft('Rp ' . $this->rupiah((int) $trx->change_amount), $W - 10);
        $out[] = $line;
        $out[] = str_pad('Terima kasih', $W, ' ', STR_PAD_BOTH);
        $out[] = "\r\n";

        $content = implode("\r\n", $out);
        $tmp = tempnam(sys_get_temp_dir(), 'eb_pos_');
        if (!$tmp) {
            return response()->json(['ok' => false, 'message' => 'Gagal membuat file print.'], 500);
        }

        $file = $tmp . '.txt';
        @rename($tmp, $file);
        file_put_contents($file, $content);

        $psQuote = function (string $s): string {
            return "'" . str_replace("'", "''", $s) . "'";
        };
        $script = 'Get-Content -Raw -Encoding UTF8 ' . $psQuote($file) . ' | Out-Printer -Name ' . $psQuote($printerName);
        $cmd = 'powershell -NoProfile -Command ' . escapeshellarg($script);
        @shell_exec($cmd);

        @unlink($file);

        return response()->json(['ok' => true]);
    }

    private function cartGetRaw(Request $request): array
    {
        $cart = $request->session()->get('cart');
        if (!is_array($cart)) {
            $cart = [];
            $request->session()->put('cart', $cart);
        }
        return $cart;
    }

    private function cartSetRaw(Request $request, array $cart): void
    {
        $request->session()->put('cart', $cart);
    }

    private function cartBuildResponse(Request $request): array
    {
        $cart = $this->cartGetRaw($request);
        $items = [];
        $total = 0;
        $count = 0;

        foreach ($cart as $productId => $row) {
            $qty = (int) ($row['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $price = (int) ($row['price'] ?? 0);
            $subtotal = $price * $qty;
            $total += $subtotal;
            $count += $qty;
            $image = (string) ($row['image'] ?? '');
            $name = (string) ($row['name'] ?? '');
            $imageUrl = $this->productImageUrl($image, $name, (int) $productId);
            $items[] = [
                'product_id' => (int) $productId,
                'name' => $name,
                'price' => $price,
                'qty' => $qty,
                'subtotal' => $subtotal,
                'image_url' => $imageUrl,
            ];
        }

        return ['items' => $items, 'total' => $total, 'count' => $count];
    }

    private function productImageUrl(string $image, string $name, int $id): string
    {
        $image = trim($image);
        if ($image === '') {
            return $this->productPlaceholder($name, $id);
        }

        if (preg_match('~^https?://~i', $image)) {
            return $image;
        }

        $path = ltrim($image, '/');
        if (str_contains($path, '/')) {
            return asset($path);
        }

        return asset('uploads/products/' . $path);
    }

    private function validatePrinterName(string $name): bool
    {
        if (mb_strlen($name) < 1 || mb_strlen($name) > 100) {
            return false;
        }
        return (bool) preg_match('/^[\pL\pN _\-\(\)\.,#]+$/u', $name);
    }

    private function rupiah(int $n): string
    {
        return number_format($n, 0, ',', '.');
    }

    private function productPlaceholder(string $name, int $id): string
    {
        $base = trim($name) !== '' ? trim($name) : 'Produk';
        $label = mb_strimwidth($base, 0, 20, '', 'UTF-8');
        $abbr = $this->abbr($base);

        $h = abs(crc32((string) $id . '|' . $base)) % 360;
        $c1 = "hsl({$h}, 78%, 46%)";
        $c2 = 'hsl(' . (($h + 28) % 360) . ', 78%, 36%)';

        $esc = fn (string $s) => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480">'
            . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0" stop-color="' . $esc($c1) . '"/>'
            . '<stop offset="1" stop-color="' . $esc($c2) . '"/>'
            . '</linearGradient></defs>'
            . '<rect width="640" height="480" rx="28" fill="url(#g)"/>'
            . '<circle cx="320" cy="200" r="96" fill="rgba(255,255,255,.16)"/>'
            . '<text x="50%" y="215" text-anchor="middle" font-family="Inter,Arial" font-size="82" font-weight="900" fill="rgba(255,255,255,.96)">' . $esc($abbr) . '</text>'
            . '<text x="50%" y="318" text-anchor="middle" font-family="Inter,Arial" font-size="34" font-weight="800" fill="rgba(255,255,255,.94)">' . $esc($label) . '</text>'
            . '<text x="50%" y="356" text-anchor="middle" font-family="Inter,Arial" font-size="20" font-weight="600" fill="rgba(255,255,255,.82)">ES BARAYA POS</text>'
            . '</svg>';

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private function abbr(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $letters = '';
        foreach ($parts as $p) {
            $p = trim((string) $p);
            if ($p === '') {
                continue;
            }
            $letters .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
            if (mb_strlen($letters, 'UTF-8') >= 3) {
                break;
            }
        }
        if ($letters === '') {
            $letters = 'P';
        }
        return $letters;
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
