<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HandlerController;
use App\Http\Controllers\ConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\User;
use Illuminate\Support\Facades\Session;

Route::fallback(function (ConfigController $config, Request $request) {
    return view('fallback');
});

Route::get('/', function (ConfigController $config, Request $request) {
    return view('index');
})->name('index');

Route::get('legal', function (ConfigController $config, Request $request) {
    return view('legal');
})->name('legal');

Route::get('cookies', function(){
    if(session('templang', false)) { 
        $lang = session('templang');
        App::setLocale($lang);
        session()->forget('templang');
    }
    else { 
        $lang = session('lang');
        App::setLocale($lang);
    }
    if(session('reload')) { session()->forget('reload'); }
    if (!session('show_cookie')) { return view('fallback'); }
    else {
        session()->forget('show_cookie');
        return view('popups.cookies')->with('lang', $lang);
    }
});

Route::post('handler', [HandlerController::class, 'handle']);