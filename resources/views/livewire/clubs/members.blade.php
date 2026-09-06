<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-ink">
            Membres — {{ $club->name }}
        </h2>
    </div>

    <div class="max-w-3xl mx-auto py-8 px-6">

        <a href="{{ route('clubs.show', $club) }}"
           class="inline-flex items-center gap-1 text-sm text-muted hover:text-pine-600 transition mb-6">
            ← Retour au club
        </a>

        @if (session()->has('success'))
            <div class="bg-pine-50 text-pine-700 p-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($members->isEmpty())

            <div class="bg-white rounded-2xl shadow p-8 text-center">
                <p class="text-3xl mb-2">📭</p>
                <p class="text-muted text-sm">Aucun membre pour le moment.</p>
            </div>

        @else

            <div class="bg-white rounded-2xl shadow divide-y divide-ink/10">

                @foreach ($members as $membership)

                    <div wire:key="member-{{ $membership->id }}" class="flex items-center justify-between gap-3 p-5">

                        <a href="{{ route('profile.show', $membership->user) }}"
                           class="flex items-center gap-3 hover:opacity-80 transition">

                            @if ($membership->user->avatar)
                                <img src="{{ asset('storage/' . $membership->user->avatar) }}"
                                     class="w-11 h-11 rounded-full object-cover">
                            @else
                                <div class="w-11 h-11 rounded-full bg-pine-100 flex items-center justify-center font-semibold text-pine-600">
                                    {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <p class="font-semibold text-ink">{{ $membership->user->name }}</p>
                                <p class="text-xs text-muted">
                                    {{ $membership->user->department ?? 'Département non renseigné' }}
                                    · membre depuis {{ $membership->responded_at?->translatedFormat('d F Y') ?? $membership->created_at->translatedFormat('d F Y') }}
                                </p>
                            </div>

                        </a>

                        @if ($club->president_id === auth()->id())
                            <button
                                wire:click="removeMember({{ $membership->id }})"
                                wire:confirm="Retirer {{ $membership->user->name }} du club ?"
                                wire:loading.attr="disabled"
                                class="text-xs text-muted hover:text-red-600 font-semibold shrink-0 transition">
                                Retirer
                            </button>
                        @endif

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>