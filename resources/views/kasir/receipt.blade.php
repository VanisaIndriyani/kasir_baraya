<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resi {{ $trx->invoice }}</title>
    <style>
        :root{ --paper:57mm; --page-h:auto; }
        html,body{ padding:0; margin:0; }
        body{
            font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
            color:#000;
            background:#fff;
            font-size:12px;
            line-height:1.2;
            font-variant-numeric:tabular-nums;
        }
        .receipt{ width:var(--paper); margin:0; padding:2mm 2mm 2.8mm; box-sizing:border-box; }
        .center{ text-align:center; }
        .muted{ color:#000; font-size:12px; line-height:1.2; }
        .title{ font-size:19px; font-weight:900; letter-spacing:1.2px; }
        .sub{ font-size:12px; font-weight:700; }
        .addr{ font-size:10.5px; line-height:1.25; margin-top:4px; }
        .sep{ margin:8px 0; }
        .sep:before{ content:"--------------------------------"; display:block; }
        .row{ display:flex; justify-content:space-between; gap:10px; }
        .mono{ font-family:inherit; }
        .right{ text-align:right; }
        .bold{ font-weight:900; }
        .small{ font-size:10px; }
        .items{ display:flex; flex-direction:column; gap:7px; }
        .item{ padding:0; }
        .item-name{ font-weight:800; font-size:11.2px; line-height:1.2; word-break:break-word; }
        .item-meta{ display:flex; justify-content:space-between; gap:10px; font-size:11px; }
        .totals{ width:100%; border-collapse:collapse; font-size:11px; }
        .totals td{ padding:2px 0; vertical-align:top; }
        .totals tr:first-child td{ padding-top:0; }
        .totals tr:last-child td{ padding-bottom:0; }
        .label{ font-weight:700; }
        .btns{ margin-top:12px; display:flex; gap:10px; justify-content:center; }
        .btn{ padding:8px 12px; border:1px solid #e5e7eb; background:#fff; border-radius:12px; cursor:pointer; }
        @page{ size: var(--paper) var(--page-h); margin:0; }
        @media screen{
            body{ background:#f3f4f6; }
            .receipt{
                margin:18px auto 12px;
                border-radius:14px;
                background:#fff;
                box-shadow:0 18px 50px rgba(16,24,40,.18);
            }
        }
        @media print{
            .btns{ display:none; }
            html,body{ width:var(--paper); }
            body{ margin:0; }
            .receipt{ width:var(--paper); margin:0; padding:2mm 2mm 2.8mm; border-radius:0; box-shadow:none; }
        }
    </style>
</head>
<body>
    <div class="receipt" id="receiptPaper">
        <div class="center">
            <div class="title">ES BARAYA</div>
            <div class="addr">
                <div>Jl. Sukun No.26</div>
                <div>Ngringin, Condongcatur, Depok</div>
                <div>Kab. Sleman, DI Yogyakarta 55283</div>
            </div>
        </div>
        <div class="sep"></div>
        <div class="row">
            <div>
                <div class="muted small">Tanggal</div>
                <div class="mono">{{ $createdAt }}</div>
            </div>
            <div class="right">
                <div class="muted small">Invoice</div>
                <div class="mono bold">{{ $trx->invoice }}</div>
            </div>
        </div>
        <div class="row" style="margin-top:6px;">
            <div>
                <div class="muted small">Order</div>
                <div class="mono">{{ $trx->order_type }}</div>
            </div>
            <div class="right">
                <div class="muted small">Payment</div>
                <div class="mono">{{ strtoupper((string) $trx->payment_method) }}</div>
            </div>
        </div>
        <div class="sep"></div>
        <div class="items">
            @foreach ($items as $it)
                <div class="item">
                    <div class="item-name">{{ $it->product_name }}</div>
                    <div class="item-meta">
                        <div class="muted mono">{{ $it->qty }} x {{ number_format($it->price, 0, ',', '.') }}</div>
                        <div class="mono bold">{{ number_format($it->subtotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="sep"></div>
        <table class="totals">
            <tr>
                <td class="bold">TOTAL</td>
                <td class="right mono bold">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="muted label">Bayar</td>
                <td class="right mono">Rp {{ number_format($trx->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="muted label">Kembalian</td>
                <td class="right mono">Rp {{ number_format($trx->change_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="sep"></div>
        <div class="center muted">
            <div class="bold">Terima kasih!</div>
            <div class="small">Simpan struk ini sebagai bukti pembayaran.</div>
        </div>
    </div>
    <div class="btns">
        <button class="btn" onclick="window.print()">Print</button>
        <button class="btn" onclick="window.close()">Tutup</button>
    </div>

    <script>
        (() => {
            const setPageHeight = () => {
                const el = document.getElementById('receiptPaper');
                if (!el) return;
                const px = el.getBoundingClientRect().height;
                const mm = Math.ceil((px * 25.4) / 96) + 6;
                document.documentElement.style.setProperty('--page-h', mm + 'mm');
            };

            window.addEventListener('beforeprint', setPageHeight);
            window.addEventListener('load', () => {
                setPageHeight();
                setTimeout(() => window.print(), 80);
            });
        })();
    </script>
</body>
</html>
