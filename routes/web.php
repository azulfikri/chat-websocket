<?php

use App\Events\ChatEvent;
use App\Events\TypingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return view('chat');
});

Route::post('/kirim', function (Request $request) {
    event(new ChatEvent($request->nama, $request->pesan));
    return response()->json(['status' => 'Terkirim']);
});
Route::post('/typing', function (Request $request) {
    event(new TypingEvent($request->nama));
    return response()->json(['status' => 'mengetik...']);
});
