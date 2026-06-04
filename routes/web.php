<?php

use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Auth\SelfRegister;
use App\Livewire\Computers\Form as ComputerForm;
use App\Livewire\Computers\Index as ComputersIndex;
use App\Livewire\Dashboard;
use App\Livewire\Distributions\Create as DistributionsCreate;
use App\Livewire\Distributions\Index as DistributionsIndex;
use App\Livewire\Statistics\Matrix as StatisticsMatrix;
use App\Livewire\Users\Edit as UserEdit;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Users\InviteForm as UserInviteForm;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['guest', 'signed', 'throttle:6,1'])
    ->get('invitation/{token}', AcceptInvitation::class)
    ->name('invitation.accept');

Route::middleware(['guest', 'throttle:10,1'])
    ->get('register/{token}', SelfRegister::class)
    ->name('self-register');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('computers', ComputersIndex::class)->name('computers.index');
    Route::get('computers/create', ComputerForm::class)->name('computers.create');
    Route::get('computers/{computer}/edit', ComputerForm::class)->name('computers.edit');

    Route::get('distributions', DistributionsIndex::class)->name('distributions.index');
    Route::get('distributions/create', DistributionsCreate::class)->name('distributions.create');

    Route::get('statistics', StatisticsMatrix::class)->name('statistics.index');

    Route::view('profile', 'profile')->name('profile');

    Route::middleware('can:admin')->group(function () {
        Route::get('users', UsersIndex::class)->name('users.index');
        Route::get('users/invite', UserInviteForm::class)->name('users.invite');
        Route::get('users/{user}/edit', UserEdit::class)->name('users.edit');
    });
});

require __DIR__.'/auth.php';
