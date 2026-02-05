<?php

return [
    'required' => ':attribute は必須です。',
    'email' => ':attribute は正しい形式で入力してください。',
    'unique' => ':attribute はすでに使用されています。',
    'confirmed' => ':attribute が一致しません。',
    'min' => [
        'string' => ':attribute は :min 文字以上で入力してください。',
    ],
    'max' => [
        'string' => ':attribute は :max 文字以下で入力してください。',
    ],

    'attributes' => [
        'name' => 'ユーザー名',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'current_password' => '現在のパスワード',
    ],
];