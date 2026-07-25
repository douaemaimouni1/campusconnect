<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">

                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-blue-600" />
                    </a>

                </div>

                <!-- Navigation Desktop -->
                <div class="hidden sm:flex space-x-8 ms-10">

                    <x-nav-link
                        :href="route('home')"
                        :active="request()->routeIs('home')">

                        🏠 Accueil

                    </x-nav-link>

                    <x-nav-link
                        :href="route('clubs.index')"
                        :active="request()->routeIs('clubs.*')">

                        🏛 Clubs

                    </x-nav-link>

                    <x-nav-link
                        :href="route('events.index')"
                        :active="request()->routeIs('events.*')">

                        📅 Événements

                    </x-nav-link>

                    <x-nav-link
                        :href="route('clubs.create')"
                        :active="request()->routeIs('clubs.create')">

                        ➕ Créer un club

                    </x-nav-link>

                </div>

            </div>

            <!-- User Menu -->
            <div class="hidden sm:flex sm:items-center">

                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button class="inline-flex items-center px-3 py-2 text-sm rounded-md">

                            {{ auth()->user()->name }}

                        </button>

                    </x-slot>

                    <x-slot name="content">

                        <x-dropdown-link
                            :href="route('profile.edit')">

                            👤 Mon profil

                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                🚪 Déconnexion
                            </x-dropdown-link>
                        </form>

                    </x-slot>

                </x-dropdown>

            </div>

            <!-- Hamburger -->
            <div class="flex items-center sm:hidden">

                <button
                    @click="open=!open"
                    class="p-2">

                    ☰

                </button>

            </div>

        </div>

    </div>

    <!-- Mobile -->

    <div
        x-show="open"
        class="sm:hidden border-t">

        <x-responsive-nav-link
            :href="route('home')">

            🏠 Accueil

        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('clubs.index')">

            🏛 Clubs

        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('events.index')">

            📅 Événements

        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('clubs.create')">

            ➕ Créer un club

        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('profile.edit')">

            👤 Mon profil

        </x-responsive-nav-link>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')"
                onclick="event.preventDefault(); this.closest('form').submit();">
                🚪 Déconnexion
            </x-responsive-nav-link>
        </form>

    </div>

</nav>