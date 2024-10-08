<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Kanban Board</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 
                'resources/js/app.js', 
                'resources/js/update-time.js',
                'resources/js/navigation.js',
                ])
        
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="h-full bg-white shadow flex-grow overflow-hidden sm:px-6 lg:px-8">
                    <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page message -->
            <div class="mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <x-page-message />
            </div>


            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            
            <script type="module">
                window.currentUserId = {{ auth()->id() }};
            </script>


        </div>
    </body>
</html>
