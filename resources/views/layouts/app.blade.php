<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mite')</title>
    <style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: system-ui, sans-serif;
        background: #f4f4f4;
        color: #222;
        font-size: 18px;
    }

    nav {
        background: #2c3e50;
        padding: 0.5rem 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    nav a {
        color: #ecf0f1;
        text-decoration: none;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        font-size: 1rem;
    }

    nav a:hover,
    nav a.active {
        background: #34495e;
    }

    nav .brand {
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: auto;
    }

    main {
        max-width: 900px;
        margin: 1.5rem auto;
        padding: 0 1rem;
    }

    h1 {
        font-size: 1.6rem;
        margin-bottom: 1rem;
    }

    h2 {
        font-size: 1.3rem;
        margin-bottom: 0.75rem;
    }

    .card {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    /* Touch-friendly buttons */
    .btn {
        display: inline-block;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        text-decoration: none;
        text-align: center;
        min-width: 80px;
        min-height: 48px;
        line-height: 1.2;
    }

    .btn-primary {
        background: #2980b9;
        color: white;
    }

    .btn-success {
        background: #27ae60;
        color: white;
    }

    .btn-warning {
        background: #f39c12;
        color: white;
    }

    .btn-danger {
        background: #c0392b;
        color: white;
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
    }

    .btn:hover {
        opacity: 0.88;
    }

    /* Touch-friendly form elements */
    label {
        display: block;
        margin-bottom: 0.3rem;
        font-weight: 600;
    }

    input[type=text],
    input[type=email],
    input[type=password],
    input[type=date],
    input[type=number],
    select,
    textarea {
        width: 100%;
        padding: 0.75rem;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 8px;
        margin-bottom: 1rem;
        background: white;
    }

    input[type=file] {
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 0.5rem;
    }

    .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: left;
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid #eee;
    }

    th {
        background: #ecf0f1;
        font-weight: 600;
    }

    .alert {
        padding: 0.9rem 1.1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-success {
        background: #d5f5e3;
        color: #1e8449;
    }

    .alert-danger {
        background: #fadbd8;
        color: #922b21;
    }

    .colour-dot {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        vertical-align: middle;
        margin-right: 6px;
        border: 1px solid #ccc;
    }

    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        vertical-align: middle;
        margin-right: 6px;
    }

    .balance-positive {
        color: #c0392b;
        font-weight: bold;
    }

    .balance-zero {
        color: #27ae60;
        font-weight: bold;
    }

    .pagination {
        margin-top: 1rem;
    }
    </style>
    @yield('head')
</head>

<body>

    <nav>
        <a class="brand" href="{{ route('picks.today') }}">🌱 Mite</a>
        <a href="{{ route('picks.today') }}">Today</a>
        <a href="{{ route('picks.index') }}">History</a>
        <a href="{{ route('causes.index') }}">Causes</a>
        <a href="{{ route('donations.index') }}">Donations</a>
        <a href="{{ route('users.index') }}">Users</a>
        <a href="{{ route('setup.index') }}">Setup</a>
    </nav>

    <main>
        @include('partials.flash')
        @yield('content')
    </main>

</body>

</html>