<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    @include('admin.partials.sidebar')
    
    <div class="ml-64">
        @include('admin.partials.header')
        
        <main class="p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>