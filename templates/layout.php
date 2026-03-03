<!DOCTYPE html>
<html lang="{{ config('app.lang', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf() }}">
    
    <title>{{ meta.title }} - {{ config('app.name', 'Application') }}</title>
    
    <meta name="description" content="{{ meta.description }}">
    <meta name="keywords" content="{{ meta.keywords }}">
    <meta name="author" content="{{ meta.author }}">
    
    <!-- CSS -->
    {{ asset('css/app.css')|css }}
    
    <!-- Additional head content -->
    @yield('head')
</head>
<body>
    <!-- Navigation -->
    @include('partials.navigation')
    
    <!-- Flash Messages -->
    @include('partials.messages')
    
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('partials.footer')
    
    <!-- JavaScript -->
    {{ asset('js/app.js')|js }}
    
    <!-- Additional scripts -->
    @yield('scripts')
</body>
</html>
