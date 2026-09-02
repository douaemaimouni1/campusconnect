<nav x-data="{ open: false }" class="bg-paper border-b border-ink/10">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">

                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-8 w-auto fill-current text-pine-600" />
                        <span class="font-serif font-semibold text-lg text-ink hidden sm:block">CampusConnect</span>
                    </a>

                </div>

                <!-- Navigation Desktop -->
                <div class="hidden sm:flex space-x-8 ms-10">

                    <x-nav-link
                        :href="route('home')"
                        :active="request()->routeIs('home')"
                        class="flex items-center gap-1.5">

                        <x-lucide-home class="w-4 h-4" />
                        Accueil

                    </x-nav-link>

                    <x-nav-link
                        :href="route('clubs.index')"
                        :active="request()->routeIs('clubs.*')"
                        class="flex items-center gap-1.5">

                        <x-lucide-landmark class="w-4 h-4" />
                        Clubs

                    </x-nav-link>

                    <x-nav-link
                        :href="route('events.index')"
                        :active="request()->routeIs('events.*')"
                        class="flex items-center gap-1.5">

                        <x-lucide-calendar class="w-4 h-4" />
                        Événements

                    </x-nav-link>

                    <x-nav-link
                        :href="route('clubs.create')"
                        :active="request()->routeIs('clubs.create')"
                        class="flex items-center gap-1.5">

                        <x-lucide-plus-circle class="w-4 h-4" />
                        Créer un club

                    </x-nav-link>

                    @if(auth()->user()->isSuperAdmin())
                        <x-nav-link
                            :href="route('admin.dashboard')"
                            :active="request()->routeIs('admin.*')"
                            class="flex items-center gap-1.5">

                            <x-lucide-wrench class="w-4 h-4" />
                            Admin

                        </x-nav-link>
                    @endif

                </div>

            </div>

            <!-- Notifications + User Menu -->
            <div class="flex items-center gap-2">

                <livewire:notifications.bell />

                <div class="hidden sm:flex sm:items-center">

                    <x-dropdown align="right" width="48">

                        <x-slot name="trigger">

                            <button class="inline-flex items-center gap-1.5 px-3 py-2 text-sm rounded-md text-ink hover:bg-pine-50 transition">

                                <x-lucide-user-circle class="w-4 h-4" />
                                {{ auth()->user()->name }}

                            </button>

                        </x-slot>

                        <x-slot name="content">

                            <x-dropdown-link
                                :href="route('profile.edit')">

                                <span class="flex items-center gap-2">
                                    <x-lucide-user class="w-4 h-4" />
                                    Mon profil
                                </span>

                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <span class="flex items-center gap-2">
                                        <x-lucide-log-out class="w-4 h-4" />
                                        Déconnexion
                                    </span>
                                </x-dropdown-link>
                            </form>

                        </x-slot>

                    </x-dropdown>

                </div>

            </div>

            <!-- Hamburger -->
            <div class="flex items-center sm:hidden">

                <button
                    @click="open=!open"
                    class="p-2 text-ink">

                    <x-lucide-menu x-show="!open" class="w-6 h-6" />
                    <x-lucide-x x-show="open" class="w-6 h-6" x-cloak />

                </button>

            </div>

        </div>

    </div>

    <!-- Mobile -->

    <div
        x-show="open"
        x-cloak
        class="sm:hidden border-t border-ink/10">

        <x-responsive-nav-link
            :href="route('home')"
            class="flex items-center gap-2">
            <x-lucide-home class="w-4 h-4" />
            Accueil
        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('clubs.index')"
            class="flex items-center gap-2">
            <x-lucide-landmark class="w-4 h-4" />
            Clubs
        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('events.index')"
            class="flex items-center gap-2">
            <x-lucide-calendar class="w-4 h-4" />
            Événements
        </x-responsive-nav-link>

        <x-responsive-nav-link
            :href="route('clubs.create')"
            class="flex items-center gap-2">
            <x-lucide-plus-circle class="w-4 h-4" />
            Créer un club
        </x-responsive-nav-link>

        @if(auth()->user()->isSuperAdmin())
            <x-responsive-nav-link
                :href="route('admin.dashboard')"
                class="flex items-center gap-2">
                <x-lucide-wrench class="w-4 h-4" />
                Admin
            </x-responsive-nav-link>
        @endif

        <x-responsive-nav-link
            :href="route('profile.edit')"
            class="flex items-center gap-2">
            <x-lucide-user class="w-4 h-4" />
            Mon profil
        </x-responsive-nav-link>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')"
                onclick="event.preventDefault(); this.closest('form').submit();"
                class="flex items-center gap-2">
                <x-lucide-log-out class="w-4 h-4" />
                Déconnexion
            </x-responsive-nav-link>
        </form>

    </div>

</nav>