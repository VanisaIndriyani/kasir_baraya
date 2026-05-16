<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (!empty($request->session()->get('admin.id'))) {
            return redirect('/admin/index.php');
        }

        return view('admin.login', [
            'pageTitle' => 'Login Admin',
            'error' => (string) $request->session()->get('login_error', ''),
        ]);
    }

    public function login(Request $request)
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            $request->session()->flash('login_error', 'Email dan password wajib diisi.');
            return redirect('/admin/login.php')->withInput();
        }

        $admin = Admin::query()->where('email', $email)->first();
        if (!$admin || !password_verify($password, (string) $admin->password_hash)) {
            $request->session()->flash('login_error', 'Email atau password salah.');
            return redirect('/admin/login.php')->withInput();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate();

        $request->session()->put('admin', [
            'id' => (int) $admin->id,
            'email' => (string) $admin->email,
        ]);

        return redirect('/admin/index.php');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login.php');
    }
}

