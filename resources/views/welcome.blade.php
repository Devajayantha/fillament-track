<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Filament Track') }} &mdash; Control your money</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,600" rel="stylesheet" />
        <style>
            :root {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                color: #f8fafc;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                background: #050505;
                color: inherit;
            }

            main {
                width: min(520px, 100%);
                padding: 3rem 2.5rem;
                border-radius: 24px;
                background: #ffffff;
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);
                text-align: center;
                color: #050505;
            }

            h1 {
                margin: 0 0 1rem;
                font-size: clamp(2rem, 6vw, 3rem);
                color: #050505;
            }

            p {
                margin: 0 auto 1.5rem;
                max-width: 36rem;
                color: #1f2937;
                line-height: 1.6;
            }

            ul {
                list-style: none;
                padding: 0;
                margin: 0 0 2rem;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                color: #0f172a;
                font-weight: 500;
            }

            ul li::before {
                content: '•';
                color: #fbbf24;
                margin-right: 0.4rem;
            }

            .actions {
                display: flex;
                justify-content: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            a {
                text-decoration: none;
                font-weight: 600;
                padding: 0.85rem 1.75rem;
                border-radius: 999px;
                border: 1px solid transparent;
                transition: background 0.2s ease, color 0.2s ease;
            }

            .primary {
                background: #fbbf24;
                color: #050505;
            }

            .primary:hover {
                background: #fcd34d;
            }

            .ghost {
                background: transparent;
                color: #fbbf24;
                border-color: rgba(251, 191, 36, 0.5);
            }

            .ghost:hover {
                border-color: #fbbf24;
                color: #fcd34d;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Finance, ready in minutes.</h1>
            <p>Use {{ config('app.name', 'Filament Track') }} to view balances, log transactions, and keep every account honest without digging through spreadsheets.</p>
            <ul>
                <li>Balances stay locked once transactions post</li>
                <li>Filament dashboard and quick filters out of the box</li>
                <li>Invite teammates or register yourself in seconds</li>
            </ul>
            <div class="actions">
                @auth
                    <a class="primary" href="{{ route('filament.dashboard.pages.dashboard') }}">Go to dashboard</a>
                @else
                    <a class="primary" href="{{ route('register') }}">Create account</a>
                    <a class="ghost" href="{{ route('login') }}">Log in</a>
                @endauth
            </div>
        </main>
    </body>
</html>
