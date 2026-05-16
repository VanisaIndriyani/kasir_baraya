<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->orderByDesc('id')->get();

        $toastMessage = '';
        if ($request->query('saved')) {
            $toastMessage = 'Produk berhasil ditambahkan.';
        } elseif ($request->query('updated')) {
            $toastMessage = 'Produk berhasil diperbarui.';
        } elseif ($request->query('error')) {
            $toastMessage = 'Gagal menyimpan. Periksa input.';
        }

        return view('admin.products', [
            'pageTitle' => 'Produk',
            'bodyClass' => 'admin',
            'admin' => session('admin'),
            'products' => $products,
            'toastMessage' => $toastMessage,
        ]);
    }

    public function save(Request $request)
    {
        $id = (int) $request->input('id', 0);

        $name = trim((string) $request->input('name', ''));
        $price = (int) $request->input('price', 0);
        $stock = (int) $request->input('stock', 0);

        if ($name === '' || mb_strlen($name) > 120 || $price < 0 || $stock < 0) {
            return redirect('/admin/products.php?error=1');
        }

        $product = $id > 0 ? Product::query()->whereKey($id)->first() : null;
        $isNew = !$product;
        if (!$product) {
            $product = new Product();
        }

        $product->name = $name;
        $product->price = $price;
        $product->stock = $stock;

        $oldImage = (string) ($product->image ?? '');
        $upload = $request->file('image');
        if ($upload && $upload->isValid()) {
            $ext = strtolower((string) $upload->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                return redirect('/admin/products.php?error=1');
            }

            $filename = 'p_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
            $upload->move(public_path('uploads/products'), $filename);
            $product->image = $filename;

            if ($oldImage !== '' && is_file(public_path('uploads/products/' . $oldImage))) {
                @unlink(public_path('uploads/products/' . $oldImage));
            }
        }

        $product->save();

        return $isNew
            ? redirect('/admin/products.php?saved=1')
            : redirect('/admin/products.php?updated=1');
    }

    public function delete(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return response()->json(['ok' => false, 'message' => 'ID tidak valid.'], 422);
        }

        $product = Product::query()->whereKey($id)->first();
        if (!$product) {
            return response()->json(['ok' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $image = (string) ($product->image ?? '');
        $product->delete();

        if ($image !== '' && is_file(public_path('uploads/products/' . $image))) {
            @unlink(public_path('uploads/products/' . $image));
        }

        return response()->json(['ok' => true]);
    }
}

