<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});


// Route::get('/login', function(){
//      return view('login');
// });


// Admin dashboard (only UI, auth handled via JS token)
Route::view('/admin/dashboard', 'admin_dashboard');

// Admin sellers page (tab content)
Route::view('/admin/sellers', 'admin_sellers');

Route::view('/seller/dashboard', 'seller_dashboard');
