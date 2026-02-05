// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
//     server: {
//         watch: {
//             ignored: ['**/storage/framework/views/**'],
//         },
//     },
// });



// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
//     server: {
//         host: '0.0.0.0',
//         port: 5173,
//     },
// });

// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: [
//                 'resources/css/app.css',
//                 'resources/js/app.js',
//             ],
//             refresh: true,
//         }),
//     ],
//     server: {
//         host: true,        // 外部アクセス許可
//         port: Number(env.VITE_DEV_SERVER_PORT),
//         strictPort: true,
//         hmr: {
//             host: env.VITE_DEV_SERVER_HOST, // ← サーバーのIP
//         },
//     },
// });



import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    // .env読み込み
    const env = loadEnv(mode, process.cwd());

    return {
        // css,jsの読み込み
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],
        server: {
            host: env.VITE_DEV_SERVER_HOST,
            port: Number(env.VITE_DEV_SERVER_PORT),
            strictPort: true,
            hmr: {
                host: env.VITE_DEV_SERVER_HOST,
            },
        },
    };
});
