<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HandlerController;
use App\Http\Controllers\ConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\User;
use Illuminate\Support\Facades\Session;

Route::middleware(['set.locale', 'check.cookie'])->group(function () {
    
    Route::fallback(function () {
        return view('fallback');
    });

    Route::get('/', function () {
        return view('index');
    })->name('index');

});

Route::get('legal', function () {
    return redirect()->away('https://cantagalo.it/legal');
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
    if(session('avoid_monitor')) { session()->forget('avoid_monitor'); }
    if (!session('show_cookie')) { return view('fallback'); }
    else {
        session()->forget('show_cookie');
        return view('popups.cookies')->with('lang', $lang);
    }
});

Route::get('robots.txt', function (ConfigController $config, Request $request) {
    $config->monitor_route($request);
    $isProduction = app()->environment('production');
    $content = $isProduction
        ? <<<TXT
User-agent: *
Disallow: /supercontrols/
Disallow: /handler/
Allow: /

Sitemap: https://{$_SERVER['HTTP_HOST']}/sitemap.xml
TXT
        : <<<TXT
User-agent: *
Disallow: /
TXT;
    return response($content, 200)
        ->header('Content-Type', 'text/plain');
});

Route::post('handler', [HandlerController::class, 'handle']);