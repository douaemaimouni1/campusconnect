<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\Onboarding\CompleteProfile;
use App\Livewire\Clubs\Index as ClubsIndex;
use App\Livewire\Clubs\Show as ClubsShow;
use App\Livewire\Clubs\Requests as ClubsRequests;
use App\Livewire\Clubs\ClubForm;
use App\Livewire\Events\Index as EventsIndex;
use App\Livewire\Events\Show as EventsShow;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Livewire\Events\Form as EventForm;
use App\Livewire\Actions\Logout;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Route::post('/logout', function (Logout $logout) {
    $logout();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'profile.completed'])->group(function ()  {

    Route::get('/home', Home::class)->name('home');

    Route::get('/clubs', ClubsIndex::class)->name('clubs.index');

    Route::get('/clubs/create', ClubForm::class)->name('clubs.create');
    Route::get('/clubs/{club}/edit', ClubForm::class)->name('clubs.edit');
    Route::get('/clubs/{club}/requests', ClubsRequests::class)->name('clubs.requests');
    Route::get('/events/{event}', EventsShow::class)->name('events.show');
    Route::get('/clubs/{club}', ClubsShow::class)->name('clubs.show');

    Route::get('/events', EventsIndex::class)->name('events.index');
    Route::get('/clubs/{club}/events/create', EventForm::class)->name('events.create');
    Route::get('/events/{event}/edit', EventForm::class)->name('events.edit');
    Route::get('/profile', ProfileEdit::class)->name('profile.edit');

    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

    Route::get('/onboarding', CompleteProfile::class)->name('onboarding');
    
});

require __DIR__.'/auth.php';