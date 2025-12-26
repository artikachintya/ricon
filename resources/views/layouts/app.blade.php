<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons (UNTUK 🔔) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

      <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('dashboard-assets/css/adminlte.css') }}">
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    {{-- Custom CSS --}}
    <style>
        body {
            background: linear-gradient(90deg, #5bb6c6, #6bb7e8);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .btn-rounded {
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 600;
        }

        .navbar {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .hero {
            color: white;
            padding: 40px 0;
        }

        .locker-number {
            font-size: 56px;
            font-weight: bold;
            color: #1f3b75;
        }
    </style>
</head>
<body>

@include('layouts.navbar')

<div class="container pb-5">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('styles')
</body>

@stack('scripts')
</html>
