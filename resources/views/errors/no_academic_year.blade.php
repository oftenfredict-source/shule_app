<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>No Academic Year — ShuleXpert</title>
    @include('includes.site_favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 55%, #f7f7f7 100%);
            font-family: "Century Gothic", "Public Sans", sans-serif;
            margin: 0;
            padding: 24px;
        }
        .box {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-top: 4px solid #940000;
            box-shadow: 0 10px 30px rgba(148, 0, 0, 0.08);
            padding: 32px 28px;
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff5f5;
            color: #940000;
            font-size: 34px;
            border: 2px solid #940000;
        }
        h1 {
            font-size: 1.35rem;
            color: #940000;
            font-weight: 800;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            margin-bottom: 8px;
        }
        .role {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #940000;
            opacity: 0.75;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .btn-brand {
            background: #940000;
            border-color: #940000;
            color: #fff;
            border-radius: 0;
            font-weight: 700;
        }
        .btn-brand:hover {
            background: #a80000;
            border-color: #a80000;
            color: #fff;
        }
        .btn-outline-brand {
            border: 1px solid #940000;
            color: #940000;
            border-radius: 0;
            font-weight: 700;
            background: #fff;
        }
        .btn-outline-brand:hover {
            background: #940000;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon"><i class="bx bx-calendar-x"></i></div>
        <div class="role">{{ strtoupper($userType ?? 'User') }} PORTAL</div>
        <h1>No Academic Year Defined</h1>
        <p>{{ $message ?? 'No academic year defined. Contact Admin.' }}</p>
        <p class="small text-muted mb-4">Ask your school administrator to register an academic year, then login again.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ route('login') }}" class="btn btn-outline-brand">Back to Login</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-brand">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>
