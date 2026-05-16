<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 64 64&quot;><defs><linearGradient id=&quot;g&quot; x1=&quot;0&quot; y1=&quot;0&quot; x2=&quot;1&quot; y2=&quot;1&quot;><stop offset=&quot;0&quot; stop-color=&quot;#e30613&quot;/><stop offset=&quot;1&quot; stop-color=&quot;#b60510&quot;/></linearGradient></defs><rect width=&quot;64&quot; height=&quot;64&quot; rx=&quot;16&quot; fill=&quot;url(#g)&quot;/><text x=&quot;50%&quot; y=&quot;56%&quot; text-anchor=&quot;middle&quot; font-family=&quot;Inter,Arial&quot; font-size=&quot;26&quot; font-weight=&quot;800&quot; fill=&quot;#fff&quot;>EB</text></svg>') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}" rel="stylesheet">

    @stack('head')
</head>
<body class="app-body {{ $bodyClass ?? '' }}">
<div id="app-loading" class="app-loading d-none" aria-hidden="true">
    <div class="app-loading-backdrop"></div>
    <div class="app-loading-spinner">
        <div class="spinner-border text-danger" role="status" aria-label="Loading"></div>
    </div>
</div>

@yield('navbar')

@yield('content')

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="appToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="appToastBody">Notifikasi</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>

@stack('scripts')
</body>
</html>
