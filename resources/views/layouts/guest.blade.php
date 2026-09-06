<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="h-screen flex flex-col lg:flex-row overflow-hidden">

            {{-- ================= PANNEAU GAUCHE — pitch ================= --}}
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-pine-800 to-pine-600 text-white flex-col justify-between p-8 xl:p-10 overflow-y-auto">

                <a href="/" class="inline-flex items-center gap-4">
                    <x-application-logo class="h-24 w-auto" />
                    <span class="font-serif text-4xl font-bold">
                        <span class="text-white">Campus</span><span class="text-amber-300">Connect</span>
                    </span>
                </a>

                <div>
                    <p class="text-xs font-semibold tracking-widest text-amber-300 mb-3">
                        LA VIE ASSOCIATIVE COMMENCE ICI
                    </p>

                    <h1 class="font-serif text-3xl xl:text-4xl font-bold leading-tight">
                        Ton campus, tes clubs, ta communauté.
                    </h1>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                            <x-lucide-users class="w-4 h-4" />
                        </div>
                        <span class="text-sm text-pine-50">Rejoins les clubs qui te ressemblent</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                            <x-lucide-calendar class="w-4 h-4" />
                        </div>
                        <span class="text-sm text-pine-50">Ne rate plus aucun événement du campus</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                            <x-lucide-sparkles class="w-4 h-4" />
                        </div>
                        <span class="text-sm text-pine-50">Crée ton propre club en 3 étapes</span>
                    </div>
                </div>

            </div>

            {{-- ================= PANNEAU DROIT — formulaire ================= --}}
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-6 bg-gray-50 overflow-y-auto">

                <div class="w-full max-w-md">

                    {{-- Logo visible uniquement sur mobile (le panneau gauche est caché) --}}
                    <a href="/" class="flex lg:hidden items-center justify-center gap-2 mb-4">
                        <x-application-logo class="h-12 w-auto" />
                        <span class="font-serif text-xl font-bold">
                            <span class="text-ink">Campus</span><span class="text-amber-500">Connect</span>
                        </span>
                    </a>

                    @if (request()->routeIs('login') || request()->routeIs('register'))
                        <div class="flex bg-white border border-gray-200 rounded-full p-1 mb-4">
                            <a href="{{ route('login') }}"
                               class="flex-1 text-center text-sm font-medium py-2 rounded-full transition {{ request()->routeIs('login') ? 'bg-pine-700 text-white' : 'text-muted-500 hover:text-ink' }}">
                                Connexion
                            </a>
                            <a href="{{ route('register') }}"
                               class="flex-1 text-center text-sm font-medium py-2 rounded-full transition {{ request()->routeIs('register') ? 'bg-pine-700 text-white' : 'text-muted-500 hover:text-ink' }}">
                                Inscription
                            </a>
                        </div>
                    @endif

                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        {{ $slot }}
                    </div>

                    <p class="text-xs text-muted-500 text-center mt-4">
                        En continuant, tu acceptes le règlement intérieur de la vie associative du campus.
                    </p>

                </div>

            </div>

        </div>
    </body>
</html>