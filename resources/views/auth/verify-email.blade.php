<x-app title="メールアドレスの確認">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto pt-10">

            <h1 class="text-2xl font-semibold pb-3 text-left">
                メールアドレスの確認
            </h1>

            <form method="POST" action="{{ route('verification.send') }}" class="px-5">

                @csrf
                <p class="text-md py-2">
                    登録したメールアドレス宛に、確認用のメールをお送りしています。
                    メール内のリンクをクリックして、メールアドレスの確認を完了してください。
                </p>
                @if (session('status') == 'verification-link-sent')
                    <p class="text-md text-indigo-700">
                        *確認メールを再送信しました。
                    </p>
                @endif

                <x-button_unit>
                    確認メールを再送信
                </x-button_unit>

                
            </form>
            
        </div>
    </x-header_sidebar>
</x-app>

{{-- 3秒おきにメール認証の確認 --}}
<script>
setInterval(async () => {
    const res = await fetch('/email/verification-status', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    const data = await res.json();

    if (data.verified) {
        location.reload();
    }
}, 3000);
</script>
