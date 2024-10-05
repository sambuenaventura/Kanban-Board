<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kanban Board</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased text-gray-300 bg-[#0f172a]">
    <div class="min-h-screen flex flex-col justify-center items-center p-4">
        <div class="w-full max-w-4xl bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <!-- Left side: Decorative area -->
                <div class="md:w-1/3 bg-gray-900 p-8 flex flex-col justify-between">
                    <div>
                        <a href="/" class="inline-block mb-8">
                            <x-application-logo class="w-16 h-16 fill-current text-blue-400 transition-transform duration-300 ease-in-out transform hover:scale-110" />
                        </a>
                        <h1 class="text-3xl font-bold gradient-text mb-4">Kanban Board</h1>
                        <p class="text-gray-400">Enhance project management with our intuitive Kanban Board.</p>
                    </div>
                    <div class="mt-8">
                        <div class="flex items-center text-gray-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Visual Workflow</span>
                        </div>
                        <div class="flex items-center text-gray-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Team Collaboration</span>
                        </div>
                        <div class="flex items-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Enhanced Productivity</span>
                        </div>
                    </div>
                </div>

                <!-- Right side: Content area -->
                <div class="md:w-2/3 p-8 md:p-12">
                    <div class="max-w-md mx-auto">
                        <h2 class="text-3xl font-bold text-blue-400 mb-6">Welcome Back</h2>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .gradient-text {
            background: linear-gradient(to right, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</body>
</html>