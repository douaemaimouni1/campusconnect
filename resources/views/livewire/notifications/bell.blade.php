<div class="relative" x-data="{ open: false }" @click.outside="open = false" wire:poll.15s>

    <button
        @click="open = ! open"
        wire:click="markInformationalAsRead"
        class="relative p-2 rounded-md hover:bg-muted-100 focus:outline-none"
        aria-label="Notifications"
    >
        <x-lucide-bell class="w-5 h-5 text-ink-700" />

        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 w-80 right-0 rounded-md shadow-lg"
        style="display: none;"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white max-h-96 overflow-y-auto">

            @forelse ($notifications as $notification)
                <div wire:key="notification-{{ $notification->id }}" class="p-4 border-b border-muted-200 last:border-b-0 {{ $notification->read_at ? 'bg-white' : 'bg-pine-50' }}">

                    {{-- ===== Proposition de présidence (reçue par le successeur) ===== --}}
                    @if ($notification->type === \App\Notifications\ClubPresidencyTransferProposed::class)
                        @php $transfer = $transfers[$notification->data['transfer_id']] ?? null; @endphp

                        <p class="text-sm text-ink-700">
                            @if ($notification->data['current_president_name'])
                                <strong>{{ $notification->data['current_president_name'] }}</strong>
                                {{ __('te propose de devenir président(e) du club') }}
                            @else
                                {{ __('L\'administration te propose de devenir président(e) du club') }}
                            @endif
                            <strong>{{ $notification->data['club_name'] }}</strong>.
                        </p>

                        <a href="{{ route('clubs.show', $notification->data['club_id']) }}" class="text-sm text-pine-600 hover:text-pine-700 underline mt-1 inline-block">
                            {{ __('Voir le club') }}
                        </a>

                        @if ($transfer && $transfer->status === 'pending')
                            <div class="flex gap-2 mt-3">
                                <button wire:click="acceptTransfer('{{ $notification->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-green-600 text-white hover:bg-green-700">
                                    <x-lucide-check class="w-3.5 h-3.5" /> {{ __('Accepter') }}
                                </button>
                                <button wire:click="declineTransfer('{{ $notification->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700">
                                    <x-lucide-x class="w-3.5 h-3.5" /> {{ __('Refuser') }}
                                </button>
                            </div>
                        @elseif ($transfer && $transfer->status === 'accepted')
                            <p class="flex items-center gap-1 text-xs text-green-700 font-medium mt-2">
                                <x-lucide-check class="w-3.5 h-3.5" /> {{ __('Tu as accepté la présidence.') }}
                            </p>
                        @elseif ($transfer && $transfer->status === 'declined')
                            <p class="flex items-center gap-1 text-xs text-muted-500 font-medium mt-2">
                                <x-lucide-x class="w-3.5 h-3.5" /> {{ __('Tu as refusé la présidence.') }}
                            </p>
                        @endif

                    {{-- ===== Réponse à une proposition de présidence (reçue par l'admin) ===== --}}
                    @elseif ($notification->type === \App\Notifications\ClubPresidencyTransferResponded::class)
                        <p class="text-sm text-ink-700">
                            <strong>{{ $notification->data['proposed_president_name'] }}</strong>
                            {{ $notification->data['status'] === 'accepted' ? __('a accepté de devenir président(e) du club') : __('a refusé de devenir président(e) du club') }}
                            <strong>{{ $notification->data['club_name'] }}</strong>.
                        </p>

                        <a href="{{ route('clubs.show', $notification->data['club_id']) }}" class="text-sm text-pine-600 hover:text-pine-700 underline mt-1 inline-block">
                            {{ __('Voir le club') }}
                        </a>

                    {{-- ===== Nouvelle demande d'adhésion (reçue par le président) ===== --}}
                    @elseif ($notification->type === \App\Notifications\ClubMembershipRequested::class)
                        @php $membership = $memberships[$notification->data['membership_id']] ?? null; @endphp

                        <p class="text-sm text-ink-700">
                            <strong>{{ $notification->data['requester_name'] }}</strong>
                            {{ __('souhaite rejoindre le club') }}
                            <strong>{{ $notification->data['club_name'] }}</strong>.
                        </p>

                        @if ($membership && $membership->status === 'pending')
                            <div class="flex gap-2 mt-3">
                                <button wire:click="acceptMembershipRequest('{{ $notification->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-green-600 text-white hover:bg-green-700">
                                    <x-lucide-check class="w-3.5 h-3.5" /> {{ __('Accepter') }}
                                </button>
                                <button wire:click="rejectMembershipRequest('{{ $notification->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700">
                                    <x-lucide-x class="w-3.5 h-3.5" /> {{ __('Refuser') }}
                                </button>
                            </div>
                        @elseif ($membership && $membership->status === 'accepted')
                            <p class="flex items-center gap-1 text-xs text-green-700 font-medium mt-2">
                                <x-lucide-check class="w-3.5 h-3.5" /> {{ __('Demande déjà acceptée.') }}
                            </p>
                        @else
                            <p class="text-xs text-muted-500 font-medium mt-2">{{ __('Demande déjà traitée.') }}</p>
                        @endif

                    {{-- ===== Réponse à une demande d'adhésion (reçue par le demandeur) ===== --}}
                    @elseif ($notification->type === \App\Notifications\ClubMembershipResponded::class)
                        <p class="text-sm text-ink-700">
                            {{ __('Ta demande d\'adhésion au club') }}
                            <strong>{{ $notification->data['club_name'] }}</strong>
                            {{ $notification->data['status'] === 'accepted' ? __('a été acceptée. 🎉') : __('a été refusée.') }}
                        </p>

                    {{-- ===== Nouvelle demande de participation (reçue par le président) ===== --}}
                    @elseif ($notification->type === \App\Notifications\EventRegistrationRequested::class)
                        @php $registration = $registrations[$notification->data['registration_id']] ?? null; @endphp

                        <p class="text-sm text-ink-700">
                            <strong>{{ $notification->data['requester_name'] }}</strong>
                            {{ __('souhaite participer à l\'événement') }}
                            <strong>{{ $notification->data['event_title'] }}</strong>
                            ({{ $notification->data['club_name'] }}).
                        </p>

                        @if ($registration && $registration->status === 'pending')
                            <div class="flex gap-2 mt-3">
                                <button wire:click="acceptEventRegistrationRequest('{{ $notification->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-green-600 text-white hover:bg-green-700">
                                    <x-lucide-check class="w-3.5 h-3.5" /> {{ __('Accepter') }}
                                </button>
                                <button wire:click="rejectEventRegistrationRequest('{{ $notification->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700">
                                    <x-lucide-x class="w-3.5 h-3.5" /> {{ __('Refuser') }}
                                </button>
                            </div>
                        @elseif ($registration && $registration->status === 'confirmed')
                            <p class="flex items-center gap-1 text-xs text-green-700 font-medium mt-2">
                                <x-lucide-check class="w-3.5 h-3.5" /> {{ __('Demande déjà acceptée.') }}
                            </p>
                        @else
                            <p class="text-xs text-muted-500 font-medium mt-2">{{ __('Demande déjà traitée.') }}</p>
                        @endif

                    {{-- ===== Réponse à une demande de participation (reçue par le demandeur) ===== --}}
                    @elseif ($notification->type === \App\Notifications\EventRegistrationResponded::class)
                        <p class="text-sm text-ink-700">
                            {{ __('Ta demande de participation à') }}
                            <strong>{{ $notification->data['event_title'] }}</strong>
                            {{ $notification->data['status'] === 'confirmed' ? __('a été confirmée. 🎉') : __('a été refusée.') }}
                        </p>

                    {{-- ===== Nouveau post publié dans un club dont je suis membre ===== --}}
                    @elseif ($notification->type === \App\Notifications\ClubPostCreated::class)
                        <p class="text-sm text-ink-700">
                            <strong>{{ $notification->data['club_name'] }}</strong>
                            {{ __('a publié :') }}
                            <span class="text-muted-600">{{ $notification->data['content_preview'] }}</span>
                        </p>

                        @if ($notification->data['event_id'])
                            <a href="{{ route('events.show', $notification->data['event_id']) }}" class="text-sm text-pine-600 hover:text-pine-700 underline mt-1 inline-block">
                                {{ __('Voir l\'événement') }}
                            </a>
                        @else
                            <a href="{{ route('clubs.show', $notification->data['club_id']) }}" class="text-sm text-pine-600 hover:text-pine-700 underline mt-1 inline-block">
                                {{ __('Voir le club') }}
                            </a>
                        @endif

                    {{-- ===== Un président de club a supprimé son compte (reçue par les Super Admins) ===== --}}
                    @elseif ($notification->type === \App\Notifications\ClubPresidentAccountDeleted::class)
                        <p class="text-sm text-ink-700">
                            <strong>{{ $notification->data['former_president_name'] }}</strong>
                            {{ __('a supprimé son compte.') }}
                            @if ($notification->data['successor_proposed'])
                                {{ __('Un transfert de présidence pour le club') }}
                                <strong>{{ $notification->data['club_name'] }}</strong>
                                {{ __('a été proposé à') }}
                                <strong>{{ $notification->data['successor_name'] }}</strong>.
                            @else
                                {{ __('Le club') }}
                                <strong>{{ $notification->data['club_name'] }}</strong>
                                {{ __('n\'a plus de président et nécessite une intervention.') }}
                            @endif
                        </p>

                        <a href="{{ route('clubs.show', $notification->data['club_id']) }}" class="text-sm text-pine-600 hover:text-pine-700 underline mt-1 inline-block">
                            {{ __('Voir le club') }}
                        </a>
                    @endif

                    <p class="text-xs text-muted-400 mt-2">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>
            @empty
                <p class="p-4 text-sm text-muted-500 text-center">
                    {{ __('Aucune notification.') }}
                </p>
            @endforelse

        </div>
    </div>

</div>