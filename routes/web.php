<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;



//ログイン前
Route::middleware(['guest'])->group(function () {
    
    Route::redirect('/', '/login');

    //ログイン画面
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    //新規登録画面
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register.show');

    //パスワード忘れた画面
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('forgot.show');

    //パスワード再設定画面
    Route::get('/reset-password', function () {
        return view('auth.reset');
    })->name('reset.show');
});

//メール認証待機画面
Route::middleware('auth')->get('/email/verification-status', function () {
    return response()->json([
        'verified' => auth()->user()->hasVerifiedEmail(),
    ]);
});
//ログイン後（メール認証前に可能な操作）
Route::middleware(['auth'])->group(function () {
    //ユーザー情報画面
    Route::get('/user/profile', function () {
        return view('user.profile');
    })->name('user.profile');

    //ユーザー情報編集画面
    Route::get('/user/edit', function () {
        return view('user.edit');
    })->name('user.edit.show');

        //ユーザー情報編集処理
    Route::post('/user/edit', 
        [UserController::class, 'update'])
    ->name('user.edit');
});


//メール認証後
Route::middleware(['auth', 'verified'])->group(function () {
    //ダッシュボード
    Route::get('/home', [DashboardController::class, 'index'])
    ->name('dashboard');

    //募集一覧画面
    Route::get('/reservation/list', 
        [ReservationController::class, 'list'])
    ->name('reservation.list');

    //予約
    Route::post('/reservation/slots/{slot}/reserve',
        [ReservationController::class, 'reserve']
    )->name('reservation.reserve');

    //予約キャンセル
    Route::delete('/reservation/slots/{slot}/cancel',
        [ReservationController::class, 'cancel']
    )->name('reservation.cancel');

});

Route::middleware(['auth', 'verified' , 'reservation.published'])->group(function () {
    //募集詳細画面
    Route::get('/reservation/detail/{reservation}', 
        [ReservationController::class, 'detail'])
    ->name('reservation.detail');
});

use App\Models\Reservation;
Route::get('/debug/reservation/{reservation}/pdf-preview', function (Reservation $reservation) {
    $reservation->load([
        'creator',
        'slots.reservationUsers.user',
    ]);

    return view('pdf.reservation-detail', [
        'reservation' => $reservation,
    ]);
});
//チャット
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat/list', [ChatController::class, 'list'])->name('chat.list');
    
});
// //チャットルーム
// Route::middleware(['auth', 'verified', 'chat.permission'])->group(function () {
//     Route::get('/chat/room/{partnerId}', [ChatController::class, 'room'])->name('chat.room');
//     Route::post('/chat/room/send', [ChatController::class, 'send'])->name('chat.send');
// });

// チャットルーム
Route::middleware(['auth', 'verified', 'chat.permission'])->group(function () {
    Route::get('/chat/room/{room_id}', [ChatController::class, 'room'])
        ->name('chat.room');

    Route::post('/chat/room/{room_id}/send', [ChatController::class, 'send'])
        ->name('chat.send');
});
//管理者操作
Route::middleware(['auth', 'admin'])->group(function () {

    //募集新規作成
    Route::get('/reservation/create', function () {
        return view('reservation.create');
    })->name('reservation.create');
    
    //募集編集
    Route::get('/reservation/{reservation}/edit', [ReservationController::class, 'edit'])
    ->name('reservation.edit');

    //募集削除
    Route::delete('/reservation/{reservation}', [ReservationController::class, 'destroy'])
    ->name('reservation.destroy');
    //募集公開
    Route::post('/reservation/{reservation}/publish', [ReservationController::class, 'publish'])
    ->name('reservation.publish');
    //募集非公開
    Route::post('/reservation/{reservation}/unpublish', [ReservationController::class, 'unpublish'])
    ->name('reservation.unpublish');
});
