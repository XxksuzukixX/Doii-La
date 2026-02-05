<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // バリデーション
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:30'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'theme' => ['nullable', 'string', 'max:50'],
            'icon_cropped'  => ['nullable', 'string'], // Base64 を受け取る
        ]);

        if ($request->filled('icon_cropped')) {

            $data = $request->input('icon_cropped');
            // Base64 から生データを取得
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $data = substr($data, strpos($data, ',') + 1);
            }
            $decoded = base64_decode($data);
            $manager = new ImageManager(new Driver());

            $image = $manager->read($decoded);

            // 幅と高さを取得
            $width = $image->width();
            $height = $image->height();
            
            // 1:1 かどうかで処理を変える
            if ($width === $height) {
                // すでに正方形ならリサイズのみ
                $image->resize(256, 256);
            } else {
                // 正方形でない場合は中央をトリミングしてからリサイズ
                $image->cover(256, 256);
            }

            // PNG 形式でエンコード
            $path = 'icons/' . uniqid('', true) . '.png';
            Storage::disk('public')->put($path, $image->toPng());
            $validated['icon_path'] = $path;

            // 既存のアイコンがあれば削除
            if ($user->icon_path) {
                Storage::disk('public')->delete($user->icon_path);
            }
        }
        // フォーム入力値から icon_cropped を除外
        unset($validated['icon_cropped']);

        // データベース更新
        $user->update($validated);

        //メールアドレスが変更された場合
        if ($user->wasChanged('email')) {
            // メール認証を破棄
            $user->email_verified_at = null;
            $user->save();

            // 認証メールを再送信
            $user->sendEmailVerificationNotification();

            // return redirect()
            //     ->route('verification.notice')
            //     ->with('success', 'メールアドレスを変更しました。確認メールを送信しました。');
        }

        return redirect()
            ->route('user.profile')
            ->with('success', 'ユーザー情報を更新しました。');
        
    }
}

