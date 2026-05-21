<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resi {{ $trx->invoice }}</title>
    <style>
        :root{ --paper:58mm; --page-h:auto; --cols:32; --logo-rot:-2deg; }
        html,body{ padding:0; margin:0; }
        body{
            font-family:"Roboto Mono",ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
            color:#000;
            background:#fff;
            font-size:12px;
            line-height:1.3;
            font-variant-numeric:tabular-nums;
        }
        .receipt-head{
            width:min(100%, var(--paper));
            margin:0 auto;
            padding:14px 12px 0;
            box-sizing:border-box;
        }
        .receipt-logo{
            display:flex;
            justify-content:center;
            margin-bottom:6px;
        }
        .receipt-logo-img{
            max-width:52mm;
            max-height:30mm;
            width:auto;
            height:auto;
            display:block;
            object-fit:contain;
            object-position:center;
            transform:rotate(var(--logo-rot));
            transform-origin:center;
        }
        .receipt{
            width:min(100%, var(--paper));
            margin:0 auto;
            padding:8px 12px 16px;
            box-sizing:border-box;
            white-space:pre;
            overflow-x:auto;
        }
        .btns{ margin-top:12px; display:flex; gap:10px; justify-content:center; }
        .btn{ padding:8px 12px; border:1px solid #e5e7eb; background:#fff; border-radius:12px; cursor:pointer; }
        @page{ size: var(--paper) var(--page-h); margin:0; }
        @media screen{
            body{ background:#fff; }
        }
        @media print{
            .btns{ display:none; }
            html,body{ width:var(--paper); }
            body{ margin:0; }
            body{ font-size:15px; line-height:1.28; font-weight:600; }
            .receipt-head{ width:var(--paper); margin:0; padding:1.5mm 1.5mm 0; }
            .receipt-logo{ margin-bottom:1mm; }
            .receipt-logo-img{
                max-width:52mm;
                max-height:30mm;
                filter:grayscale(1) contrast(1.2);
            }
            .receipt{ width:var(--paper); margin:0; padding:0 1.5mm 24mm; white-space:pre; overflow:visible; }
        }
    </style>
</head>
<body>
    <div class="receipt-head">
        <div class="receipt-logo" aria-hidden="true">
            <img class="receipt-logo-img" src="{{ asset('logo.jpeg') }}" alt="ES BARAYA">
        </div>
    </div>
    <pre class="receipt" id="receiptPaper">{{ $receiptText }}</pre>
    <div class="btns">
        <button class="btn" onclick="window.print()">Print</button>
        <a class="btn" href="{{ url('/kasir/receipt.txt?id=' . (int) $trx->id) }}">TXT (RawBT)</a>
        <button class="btn" onclick="window.close()">Tutup</button>
    </div>

    <script>
        (() => {
            const isMobile = () => {
                const ua = navigator.userAgent || '';
                return /Android|iPhone|iPad|iPod/i.test(ua);
            };

            const setPageHeight = () => {
                const el = document.getElementById('receiptPaper');
                if (!el) return;
                const px = el.getBoundingClientRect().height;
                const mm = Math.ceil((px * 25.4) / 96) + 28;
                document.documentElement.style.setProperty('--page-h', mm + 'mm');
            };

            window.addEventListener('beforeprint', setPageHeight);
            window.addEventListener('load', () => {
                setPageHeight();
                if (!isMobile()) {
                    setTimeout(() => window.print(), 80);
                }
            });
        })();
    </script>
</body>
</html>
