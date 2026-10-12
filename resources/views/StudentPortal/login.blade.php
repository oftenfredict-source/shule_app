<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal | ShuleXpert</title>
    @if(is_file(public_path('css/student-portal.css')))
        <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}?v={{ filemtime(public_path('css/student-portal.css')) }}">
    @endif
    <style>
        :root {
            --sp-brand: #940000;
            --sp-deep: #6b0000;
            --sp-ink: #1a1212;
            --sp-muted: #6b5c5c;
            --sp-font: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
        }
        * { box-sizing: border-box; }
        body.sp-login {
            margin: 0;
            min-height: 100vh;
            font-family: var(--sp-font);
            color: var(--sp-ink);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: #1a0808;
        }
        .sp-login-visual {
            position: relative;
            min-height: 100vh;
            padding: clamp(32px, 6vw, 64px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff;
            overflow: hidden;
            background:
                radial-gradient(ellipse 70% 50% at 30% 20%, rgba(255,255,255,0.12), transparent 50%),
                linear-gradient(155deg, #4a0000 0%, #940000 48%, #2a0000 100%);
        }
        .sp-login-visual::after {
            content: "";
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(-18deg, transparent, transparent 28px, rgba(255,255,255,0.025) 28px, rgba(255,255,255,0.025) 29px);
            pointer-events: none;
        }
        .sp-login-visual > * { position: relative; z-index: 1; }
        .sp-mark {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            background: #fff;
            color: var(--sp-brand);
            font-weight: 800;
            font-size: 1.6rem;
            line-height: 1;
        }
        .sp-login-visual h1 {
            font-size: clamp(2.4rem, 5vw, 3.6rem);
            font-weight: 800;
            line-height: 1.05;
            max-width: 10ch;
            margin: 24px 0 16px;
            color: #fff;
        }
        .sp-login-visual p {
            max-width: 34ch;
            opacity: 0.9;
            font-size: 1.05rem;
            color: #fff;
            margin: 0;
        }
        .sp-login-powered {
            opacity: 0.75;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .sp-login-form-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
            background:
                radial-gradient(600px 400px at 80% 10%, rgba(148,0,0,0.08), transparent 50%),
                #f7f2f2;
        }
        .sp-login-card {
            width: 100%;
            max-width: 400px;
        }
        .sp-login-card h2 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 8px;
            color: var(--sp-brand);
        }
        .sp-login-lead {
            color: var(--sp-muted);
            margin: 0 0 28px;
            font-size: 0.92rem;
        }
        .sp-login-card label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--sp-ink);
        }
        .sp-login-card input {
            width: 100%;
            border: 1px solid rgba(148,0,0,0.2);
            background: #fff;
            padding: 14px 16px;
            font-family: inherit;
            font-size: 1rem;
            margin-bottom: 18px;
            outline: none;
        }
        .sp-login-card input:focus {
            border-color: var(--sp-brand);
            box-shadow: 0 0 0 3px rgba(148,0,0,0.12);
        }
        .sp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            border: 0;
            background: var(--sp-brand);
            color: #fff !important;
            font-family: inherit;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 14px 22px;
            cursor: pointer;
            text-decoration: none;
        }
        .sp-btn:hover { background: var(--sp-deep); }
        .sp-login-foot {
            margin-top: 22px;
            font-size: 0.85rem;
            color: var(--sp-muted);
        }
        .sp-login-foot a {
            color: var(--sp-brand);
            font-weight: 700;
            text-decoration: none;
        }
        .sp-login-foot a:hover { text-decoration: underline; }
        .sp-flash.err {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-left: 3px solid var(--sp-brand);
            background: #fdeeee;
            color: #6b0000;
            font-size: 0.9rem;
        }
        @media (max-width: 900px) {
            body.sp-login { grid-template-columns: 1fr; }
            .sp-login-visual { min-height: auto; padding: 36px 24px 48px; }
            .sp-login-visual h1 { font-size: 2.4rem; }
        }
    </style>
</head>
<body class="sp-login">
    <aside class="sp-login-visual">
        <div>
            <span class="sp-mark">S</span>
            <h1>Student Portal</h1>
            <p>Notes, books, and quizzes from your school - sign in with your admission number.</p>
        </div>
        <div class="sp-login-powered">Powered by ShuleXpert</div>
    </aside>
    <div class="sp-login-form-wrap">
        <div class="sp-login-card">
            <h2>Sign in</h2>
            <p class="sp-login-lead">Use the admission number issued by your school. No password required.</p>
            @if(session('error'))<div class="sp-flash err">{{ session('error') }}</div>@endif
            <form method="POST" action="{{ route('student_portal.login.submit') }}">
                @csrf
                <label for="admission_number">Admission number</label>
                <input id="admission_number" name="admission_number" value="{{ old('admission_number') }}" required autofocus placeholder="e.g. SCH001/001/2026">
                @error('admission_number')<div style="color:#940000;font-size:0.85rem;margin:-10px 0 14px;">{{ $message }}</div>@enderror
                <button class="sp-btn" type="submit">Continue</button>
            </form>
            <p class="sp-login-foot"><a href="{{ route('login') }}">Teacher or admin sign in</a></p>
        </div>
    </div>
</body>
</html>
