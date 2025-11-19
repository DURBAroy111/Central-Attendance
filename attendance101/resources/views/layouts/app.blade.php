<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', 'Central Attendance')</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800">
  <div class="min-h-screen flex flex-col">
    @include('partials.header')

    <div class="flex-1 container mx-auto px-4 py-6">
      @include('partials.flash')
      @yield('content')
    </div>

    @include('partials.footer')
  </div>
</body>
</html>
