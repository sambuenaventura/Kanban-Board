<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kanban Board - Enhance Your Team's Workflow</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
</head>
<body class="antialiased text-gray-300 bg-[#0f172a]">
    <div class="relative min-h-screen flex flex-col">
        <nav class="bg-gray-900 text-white py-6">
            <div class="container mx-auto px-6 flex justify-between items-center">
                <a href="#" class="text-2xl font-bold gradient-text">Kanban Board</a>
                <div>
                    <a href="#features" class="mx-4 hover:text-blue-400 transition">Features</a>
                    <a href="#demo" class="mx-4 hover:text-blue-400 transition">Demo</a>
                    <a href="/boards" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full transition">Get Started</a>
                </div>
            </div>
        </nav>

        <header class="py-24 text-center relative">
            <h1 class="text-6xl font-bold gradient-text mb-6">Optimize Your Workflow</h1>
            <p class="text-2xl text-gray-400 mb-10">Enhance project management with our intuitive Kanban Board.</p>
            <a href="/boards" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-4 px-10 rounded-full text-xl transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-lg inline-block">
                Start Organizing Today
            </a>
            <div class="mt-16">
                <img src="{{ asset('image/kanban-home.png') }}" alt="Kanban Board Interface" class="rounded-lg shadow-2xl mx-auto max-w-5xl w-full" />
            </div>
        </header>

        <main class="flex-grow">
            <section id="features" class="py-24 bg-gray-800">
                <div class="container mx-auto px-6">
                    <h2 class="text-4xl font-bold text-center mb-16 gradient-text">Key Features of Our Kanban Board</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div class="bg-gray-700 p-8 rounded-xl card-hover feature-card opacity-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <h3 class="text-2xl font-semibold mb-4">Visual Workflow</h3>
                            <p class="text-gray-300">Get a clear overview of your projects and track progress efficiently with our intuitive board layout.</p>
                        </div>
                        <div class="bg-gray-700 p-8 rounded-xl card-hover feature-card opacity-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-purple-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="text-2xl font-semibold mb-4">Team Collaboration</h3>
                            <p class="text-gray-300">Improve team coordination with real-time updates, task assignments, and shared project visibility.</p>
                        </div>
                        <div class="bg-gray-700 p-8 rounded-xl card-hover feature-card opacity-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <h3 class="text-2xl font-semibold mb-4">Enhanced Productivity</h3>
                            <p class="text-gray-300">Streamline your workflow, identify bottlenecks, and increase overall team efficiency.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="demo" class="py-24">
                <div class="container mx-auto px-6">
                    <h2 class="text-4xl font-bold text-center mb-16 gradient-text">Kanban Board in Action</h2>
                    <div class="bg-gray-700 p-10 rounded-xl shadow-lg">
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ asset('image/kanban-home.png') }}" alt="Kanban Board Demo" class="rounded-lg object-cover" />
                        </div>
                        <div class="mt-8 text-center">
                            <a href="#" class="text-blue-400 hover:text-blue-300 font-semibold text-xl">View Full Demo</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-24 bg-gray-800">
                <div class="container mx-auto px-6 text-center">
                    <h2 class="text-4xl font-bold mb-10 gradient-text">Ready to Improve Your Workflow?</h2>
                    <a href="/boards" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-4 px-10 rounded-full text-xl transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-lg inline-block">
                        Start Your Free Trial
                    </a>
                </div>
            </section>
        </main>

        <footer class="bg-gray-900 text-gray-400 py-12">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <span class="font-bold text-3xl gradient-text">Kanban Board</span>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="hover:text-white transition">About</a>
                        <a href="#" class="hover:text-white transition">Blog</a>
                        <a href="#" class="hover:text-white transition">Contact</a>
                        <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    </div>
                </div>
                <div class="mt-10 text-center">
                    <p>&copy; 2024 Kanban Board. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript files with Vite -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/update-time.js',
        'resources/js/navigation.js',
        'resources/js/welcome.js',
    ])
</body>
</html>