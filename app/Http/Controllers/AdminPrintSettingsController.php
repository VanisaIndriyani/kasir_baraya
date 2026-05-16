<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AdminPrintSettingsController extends Controller
{
    public function index(Request $request)
    {
        $mode = (string) (Setting::find('print_mode')->value ?? 'browser');
        if (!in_array($mode, ['browser', 'server'], true)) {
            $mode = 'browser';
        }
        $printerName = (string) (Setting::find('printer_name')->value ?? '');

        $settingsOk = true;
        try {
            Setting::query()->limit(1)->get();
        } catch (QueryException) {
            $settingsOk = false;
        }

        $toastMessage = '';
        if ($request->query('saved')) {
            $toastMessage = 'Pengaturan print tersimpan.';
        } elseif ($request->query('error')) {
            $err = (string) $request->query('error');
            if ($err === 'db') {
                $toastMessage = 'Database belum siap (tabel settings belum ada). Jalankan migrate.';
            } elseif ($err === 'mode') {
                $toastMessage = 'Aktifkan mode Server Print dulu.';
            } elseif ($err === 'printer') {
                $toastMessage = 'Nama printer belum benar. Isi nama printer persis dari Windows.';
            } else {
                $toastMessage = 'Gagal menyimpan pengaturan.';
            }
        } elseif ($request->query('printed')) {
            $toastMessage = 'Test print dikirim ke printer.';
        }

        $lastId = (int) (Transaction::query()->orderByDesc('id')->value('id') ?? 0);

        return view('admin.print_settings', [
            'pageTitle' => 'Pengaturan Print',
            'bodyClass' => 'admin',
            'admin' => session('admin'),
            'mode' => $mode,
            'printerName' => $printerName,
            'settingsOk' => $settingsOk,
            'toastMessage' => $toastMessage,
            'lastTransactionId' => $lastId,
        ]);
    }

    public function save(Request $request)
    {
        $mode = (string) $request->input('print_mode', 'browser');
        if (!in_array($mode, ['browser', 'server'], true)) {
            return redirect('/admin/print_settings.php?error=1');
        }

        $printerName = trim((string) $request->input('printer_name', ''));
        if ($mode !== 'server') {
            $printerName = '';
        }

        if ($mode === 'server' && $printerName !== '' && !$this->validatePrinterName($printerName)) {
            return redirect('/admin/print_settings.php?error=printer');
        }

        try {
            Setting::query()->updateOrCreate(['key' => 'print_mode'], ['value' => $mode]);
            Setting::query()->updateOrCreate(['key' => 'printer_name'], ['value' => $printerName]);
        } catch (QueryException) {
            return redirect('/admin/print_settings.php?error=db');
        }

        return redirect('/admin/print_settings.php?saved=1');
    }

    public function testPrint()
    {
        try {
            $mode = (string) (Setting::find('print_mode')->value ?? 'browser');
            $printerName = trim((string) (Setting::find('printer_name')->value ?? ''));
        } catch (QueryException) {
            return redirect('/admin/print_settings.php?error=db');
        }

        if ($mode !== 'server') {
            return redirect('/admin/print_settings.php?error=mode');
        }

        if ($printerName === '' || !$this->validatePrinterName($printerName)) {
            return redirect('/admin/print_settings.php?error=printer');
        }

        $W = 32;
        $line = str_repeat('-', $W);
        $out = [];
        $out[] = str_pad('ES BARAYA', $W, ' ', STR_PAD_BOTH);
        $out[] = str_pad('TEST PRINT', $W, ' ', STR_PAD_BOTH);
        $out[] = $line;
        $out[] = 'Tgl: ' . now()->format('d/m/Y H:i');
        $out[] = $line;
        $out[] = str_pad('OK', $W, ' ', STR_PAD_BOTH);
        $out[] = "\r\n";

        $content = implode("\r\n", $out);
        $tmp = tempnam(sys_get_temp_dir(), 'eb_pos_');
        if (!$tmp) {
            return redirect('/admin/print_settings.php?error=1');
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

        return redirect('/admin/print_settings.php?printed=1');
    }

    private function validatePrinterName(string $name): bool
    {
        if (mb_strlen($name) < 1 || mb_strlen($name) > 100) {
            return false;
        }
        return (bool) preg_match('/^[\pL\pN _\-\(\)\.,#]+$/u', $name);
    }
}

