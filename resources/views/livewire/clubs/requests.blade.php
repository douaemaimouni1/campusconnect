<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-ink">
            Gérer les demandes — {{ $club->name }}
        </h2>
    </div>

    <div class="max-w-3xl mx-auto py-8 px-6">

        <a href="{{ route('clubs.show', $club) }}"
           class="inline-flex items-center gap-1 text-sm text-muted hover:text-pine-600 transition mb-6">
            ← Retour au club
        </a>

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- ================= DEMANDES D'ADHÉSION ================= --}}
        <div class="mb-10">

            <h3 class="text-lg font-bold text-ink mb-4">
                Demandes d'adhésion
                @if ($membershipRequests->isNotEmpty())
                    <span class="inline-block bg-pine-100 text-pine-600 text-xs font-semibold px-2 py-0.5 rounded-full align-middle ml-1">
                        {{ $membershipRequests->count() }}
                    </span>
                @endif
            </h3>

            @if ($membershipRequests->isEmpty())

                <div class="bg-white rounded-2xl shadow p-8 text-center">
                    <p class="text-3xl mb-2">📭</p>
                    <p class="text-muted text-sm">Aucune demande d'adhésion en attente.</p>
                </div>

            @else

                <div class="space-y-3">

                    @foreach ($membershipRequests as $request)

                        <div wire:key="membership-{{ $request->id }}"
                             class="bg-white rounded-2xl shadow p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            <div class="flex items-center gap-3">

                                @if ($request->user->avatar)
                                        <img src="{{ str_starts_with($request->user->avatar, 'http') ? $request->user->avatar : asset('storage/' . $request->user->avatar) }}"
                                         class="w-11 h-11 rounded-full object-cover">
                                @else
                                    <div class="w-11 h-11 rounded-full bg-pine-100 flex items-center justify-center font-semibold text-pine-600">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <a href="{{ route('profile.show', $request->user) }}" class="font-semibold text-ink hover:text-pine-600 hover:underline">
                                        {{ $request->user->name }}
                                    </a>
                                    <p class="text-xs text-muted">
                                        {{ $request->user->department ?? 'Département non renseigné' }}
                                        · demandé {{ $request->requested_at?->diffForHumans() ?? $request->created_at->diffForHumans() }}
                                    </p>
                                </div>

                            </div>

                            <div class="flex items-center gap-2">

                                <button
                                    wire:click="acceptMembership({{ $request->id }})"
                                    wire:loading.attr="disabled"
                                    class="bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">
                                    ✓ Accepter
                                </button>

                                <button
                                    wire:click="rejectMembership({{ $request->id }})"
                                    wire:confirm="Refuser cette demande d'adhésion ? Cette action est définitive."
                                    wire:loading.attr="disabled"
                                    class="bg-gray-100 hover:bg-red-50 text-muted hover:text-red-600 disabled:opacity-50 text-sm px-4 py-2 rounded-lg font-semibold transition">
                                    ✗ Refuser
                                </button>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

        {{-- ================= DEMANDES DE PARTICIPATION AUX ÉVÉNEMENTS ================= --}}
        <div>

            <h3 class="text-lg font-bold text-ink mb-4">
                Demandes de participation aux événements
                @if ($eventRequests->isNotEmpty())
                    <span class="inline-block bg-pine-100 text-pine-600 text-xs font-semibold px-2 py-0.5 rounded-full align-middle ml-1">
                        {{ $eventRequests->count() }}
                    </span>
                @endif
            </h3>

            @if ($eventRequests->isEmpty())

                <div class="bg-white rounded-2xl shadow p-8 text-center">
                    <p class="text-3xl mb-2">📭</p>
                    <p class="text-muted text-sm">Aucune demande de participation en attente.</p>
                </div>

            @else

                <div class="space-y-3">

                    @foreach ($eventRequests as $request)

                        <div wire:key="event-registration-{{ $request->id }}"
                             class="bg-white rounded-2xl shadow p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            <div class="flex items-center gap-3">

                                @if ($request->user->avatar)
                                   <img src="{{ str_starts_with($request->user->avatar, 'http') ? $request->user->avatar : asset('storage/' . $request->user->avatar) }}"
                                         class="w-11 h-11 rounded-full object-cover">
                                @else
                                    <div class="w-11 h-11 rounded-full bg-pine-100 flex items-center justify-center font-semibold text-pine-600">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <a href="{{ route('profile.show', $request->user) }}" class="font-semibold text-ink hover:text-pine-600 hover:underline">
                                        {{ $request->user->name }}
                                    </a>
                                    <p class="text-xs text-muted">
                                        veut participer à
                                        <strong class="text-ink">{{ $request->event->title }}</strong>
                                        · demandé {{ $request->registered_at?->diffForHumans() ?? $request->created_at->diffForHumans() }}
                                    </p>
                                </div>

                            </div>

                            <div class="flex items-center gap-2">

                                @if ($request->event_is_full)
                                    <span class="bg-gray-100 text-muted text-sm px-4 py-2 rounded-lg font-semibold">
                                        Complet
                                    </span>
                                @else
                                    <button
                                        wire:click="acceptEventRegistration({{ $request->id }})"
                                        wire:loading.attr="disabled"
                                        class="bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">
                                        ✓ Accepter
                                    </button>
                                @endif

                                <button
                                    wire:click="rejectEventRegistration({{ $request->id }})"
                                    wire:confirm="Refuser cette demande de participation ? Cette action est définitive."
                                    wire:loading.attr="disabled"
                                    class="bg-gray-100 hover:bg-red-50 text-muted hover:text-red-600 disabled:opacity-50 text-sm px-4 py-2 rounded-lg font-semibold transition">
                                    ✗ Refuser
                                </button>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>

</div>