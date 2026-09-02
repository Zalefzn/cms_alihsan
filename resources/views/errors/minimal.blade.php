@php
    $code ??= 500;
    $emoji ??= '⚠️';
    $title ??= 'Terjadi kesalahan';
    $message ??= 'Silakan coba lagi, atau hubungi admin jika masalah berlanjut.';
    $buttonUrl ??= url('/admin');
    $buttonLabel ??= 'Kembali ke Dasbor';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(155deg, #ffffff 0%, #f3e8ff 35%, #ddd6fe 60%, #f9a8d4 100%);
            color: #1f2937;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 40px -12px rgba(30, 27, 75, 0.18);
            padding: 2.5rem 2.5rem 2rem;
            max-width: 26rem;
            width: 100%;
            text-align: center;
        }
        .logo { height: 3.5rem; width: 3.5rem; border-radius: 0.75rem; margin-bottom: 1rem; }
        .emoji { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .code { font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em; color: #7c3aed; text-transform: uppercase; }
        h1 { font-size: 1.35rem; font-weight: 700; margin: 0.35rem 0 0.5rem; color: #111827; }
        p { color: #6b7280; font-size: 0.9rem; line-height: 1.5; margin: 0 0 1.5rem; }
        a.btn {
            display: inline-block;
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.6rem 1.5rem;
            border-radius: 0.6rem;
            transition: background 150ms ease;
        }
        a.btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="card">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="Al-Ihsan Islamic School">
        <div class="emoji">{{ $emoji }}</div>
        <div class="code">Kesalahan {{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a class="btn" href="{{ $buttonUrl }}">{{ $buttonLabel }}</a>
    </div>
</body>
</html>
