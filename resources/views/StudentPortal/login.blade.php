<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal | ShuleXpert</title>
    <link rel="stylesheet" href="{{ asset('css/student-portal.css') }}?v=4">
</head>
<body class="sp-login">
    <aside class="sp-login-visual">
        <div>
            <span class="sp-mark">S</span>
            <h1>Student Portal</h1>
            <p>Notes, books, and quizzes from your school — sign in with your admission number.</p>
        </div>
        <div class="small" style="opacity:0.7;letter-spacing:0.12em;text-transform:uppercase;font-size:0.7rem;">Powered by ShuleXpert</div>
    </aside>
    <div class="sp-login-form-wrap">
        <div class="sp-login-card">
            <h2>Sign in</h2>
            <p class="sp-login-lead">Use the admission number issued by your school. No password required.</p>
            @if(session('error'))<div class="sp-flash err" style="margin-bottom:18px;">{{ session('error') }}</div>@endif
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
