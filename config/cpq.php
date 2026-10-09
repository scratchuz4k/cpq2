<?php

return [
    'superuser' => [
        'email' => env('SUPERUSER_EMAIL', 'super@example.com'),
        'password' => env('SUPERUSER_PASSWORD', 'werwerwer'),
    ],

    'pdf' => [
        // base URL headless Chrome uses to load the quote PDF page
        'render_url' => env('PDF_RENDER_URL', env('APP_URL')),
        // defaults to the Chrome installed in the Sail image, otherwise puppeteer uses the Chrome it downloaded
        'chrome_path' => env('CHROMIUM_PATH', is_file('/usr/bin/google-chrome-stable') ? '/usr/bin/google-chrome-stable' : null),
        // host Chrome uses to reach the Vite dev server (`npm run dev` outside the Sail container)
        'vite_host' => env('PDF_VITE_HOST', 'host.docker.internal'),
        // minutes the signed PDF page URL stays valid
        'link_ttl' => (int) env('PDF_LINK_TTL', 5),
    ],
];
