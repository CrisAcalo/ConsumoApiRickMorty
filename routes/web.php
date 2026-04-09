<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\RickAndMorty\Index::class)->name('home');
