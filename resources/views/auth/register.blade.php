<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Filament Track') }} &mdash; Register</title>
        <style>
            :root {
                font-family: "Inter", "Segoe UI", Arial, sans-serif;
                color: #0f172a;
                background: #0f172a;
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
                padding: 1.5rem;
                background: radial-gradient(circle at top, rgba(59, 130, 246, 0.12), transparent 55%),
                    radial-gradient(circle at bottom, rgba(248, 250, 252, 0.18), transparent 50%),
                    #0f172a;
            }

            .card {
                width: min(440px, 100%);
                border-radius: 18px;
                padding: 2.5rem;
                background: #ffffff;
                border: 1px solid rgba(15, 23, 42, 0.08);
                box-shadow: 0 35px 80px rgba(15, 23, 42, 0.25);
            }

            .eyebrow {
                display: inline-block;
                padding: 0.35rem 1rem;
                border-radius: 999px;
                font-size: 0.75rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #b45309;
                background: rgba(251, 191, 36, 0.15);
                border: 1px solid rgba(251, 191, 36, 0.35);
                margin-bottom: 1rem;
            }

            h1 {
                margin: 0 0 0.5rem;
                font-size: 2rem;
            }

            .lead {
                margin: 0 0 2rem;
                color: #475569;
                line-height: 1.5;
            }

            form {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            label {
                font-size: 0.9rem;
                font-weight: 600;
                margin-bottom: 0.35rem;
                color: #0f172a;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                width: 100%;
                border-radius: 10px;
                border: 1px solid #cbd5f5;
                padding: 0.65rem 0.9rem;
                font-size: 1rem;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }

            input:focus {
                outline: none;
                border-color: #fbbf24;
                box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.3);
            }

            button {
                border: none;
                border-radius: 10px;
                padding: 0.8rem 1rem;
                background: #fbbf24;
                color: #0a0a0a;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: background 0.2s ease;
            }

            button:hover {
                background: #fcd34d;
            }

            .errors {
                border-radius: 12px;
                padding: 1rem 1.25rem;
                background: rgba(248, 113, 113, 0.14);
                border: 1px solid rgba(248, 113, 113, 0.35);
                color: #b91c1c;
                margin-bottom: 1.5rem;
            }

            .footnote {
                margin-top: 1.75rem;
                text-align: center;
                font-size: 0.95rem;
                color: #475569;
            }

            .footnote a {
                color: #fbbf24;
                font-weight: 600;
                text-decoration: none;
            }

            .footnote a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <main class="card">
            <span class="eyebrow">Create account</span>
            <h1>{{ config('app.name', 'Filament Track') }} &mdash; Register</h1>
            <p class="lead">Create a regular user account to access the dashboard and start tracking finances securely.</p>

            @if ($errors->any())
                <div class="errors">
                    <p><strong>We couldn't save your details:</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div>
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                </div>

                <div>
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div>
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <button type="submit">Register</button>
            </form>

            <p class="footnote">
                Already have an account?
                <a href="{{ route('login') }}">Log in</a>
            </p>
        </main>
    </body>
</html>
