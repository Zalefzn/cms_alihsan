@include('errors.minimal', [
    'code' => 419,
    'emoji' => '⏳',
    'title' => 'Sesi Berakhir',
    'message' => 'Sesi Anda sudah tidak berlaku, biasanya karena halaman dibiarkan terbuka terlalu lama. Silakan masuk kembali.',
    'buttonUrl' => url('/admin/login'),
    'buttonLabel' => 'Masuk Kembali',
])
