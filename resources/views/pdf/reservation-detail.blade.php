<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <style>

        /* 通常フォント */
        @font-face {
            font-family: 'NotoSansJP';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/NotoSansJP-Regular.ttf') }}');
        }

        /* 太字フォント */
        @font-face {
            font-family: 'NotoSansJP';
            font-style: normal;
            font-weight: bold;
            src: url('{{ storage_path('fonts/NotoSansJP-Bold.ttf') }}');
        }
        * {
            font-family: NotoSansJP, sans-serif !important;
        }

        body {
            font-size: 12px;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center; /* 横中央寄せ */
        }

        .container {
            width: 90%; 
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 4px;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        p {
            font-size: 14px;
        }
    </style>

</head>
<body>

<div class="container"> <!-- 横幅制限用ラッパー -->

<h2>募集情報</h2>
<table>
    {{-- <tr><th>ID</th><td>{{ $reservation->id }}</td></tr> --}}
    <tr><th>タイトル</th><td>{{ $reservation->title }}</td></tr>
    <tr><th>担当者</th><td>{{ $reservation->staff_name }}</td></tr>
    {{-- <tr><th>ステータス</th><td>{{ $reservation->status }}</td></tr>     --}}
    <tr><th>公開開始</th><td>{{ optional($reservation->publish_at)?->format('Y/m/d H:i') }}</td></tr>
    <tr><th>締切</th><td>{{ optional($reservation->deadline_at)?->format('Y/m/d H:i') }}</td></tr>
    <tr><th>公開終了</th><td>{{ optional($reservation->close_at)?->format('Y/m/d H:i') }}</td></tr>

</table>

<h2>予約ユーザー</h2>
@foreach($reservation->slots as $i => $slot)
    <h3>日程 {{ $i + 1 }}</h3>
    <p>
        {{ optional($slot->start_at)?->format('Y/m/d H:i') }}
        -
        {{ optional($slot->end_at)?->format('Y/m/d H:i') }}
        （定員 {{ $slot->capacity }}名）
    </p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>予約日時</th>
            </tr>
        </thead>
        <tbody>
            @foreach($slot->reservationUsers->where('status', 'reserved') as $ru)
                <tr>
                    <td>{{ $ru->user_id }}</td>
                    <td>{{ optional($ru->user)->name }}</td>
                    <td>{{ optional($ru->user)->email }}</td>
                    <td>{{ optional($ru->updated_at)?->format('Y/m/d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

</div> <!-- /.container -->

</body>
</html>
