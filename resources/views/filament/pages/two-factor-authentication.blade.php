<x-filament-panels::page>
    <div style="max-width:520px;">
        @if ($showRecoveryCodes)
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:16px;">
                <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 4px;">Kode Pemulihan</h2>
                <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">
                    Simpan kode-kode ini di tempat yang aman. Setiap kode hanya bisa dipakai sekali sebagai pengganti
                    kode dari aplikasi authenticator jika HP Anda hilang/tidak bisa diakses. Kode ini tidak akan
                    ditampilkan lagi setelah Anda menutup halaman ini.
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;background:#f9fafb;border-radius:8px;padding:12px;font-family:monospace;font-size:13px;color:#111827;">
                    @foreach ($recoveryCodesToShow as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
                <button
                    type="button"
                    wire:click="dismissRecoveryCodes"
                    style="margin-top:14px;border-radius:8px;background:#4f46e5;border:0;padding:8px 14px;font-size:12px;font-weight:600;color:#fff;cursor:pointer;"
                >
                    Sudah Saya Simpan
                </button>
            </div>
        @elseif ($confirming)
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 4px;">Pindai Kode QR</h2>
                <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">
                    Buka aplikasi authenticator (Google Authenticator, Authy, dll), pindai kode di bawah, lalu masukkan
                    6 digit kode yang muncul untuk mengaktifkan.
                </p>

                @if ($svg = $this->qrSvg())
                    <div style="text-align:center;margin-bottom:12px;">
                        <img src="{{ $svg }}" alt="Kode QR 2FA" style="display:inline-block;width:200px;height:200px;">
                    </div>
                @endif

                <p style="font-size:11px;color:#9ca3af;text-align:center;margin:0 0 16px;">
                    Tidak bisa memindai? Masukkan manual: <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;">{{ $this->secretForManualEntry() }}</code>
                </p>

                <form wire:submit.prevent="confirmSetup" style="display:flex;gap:8px;align-items:flex-start;">
                    <input
                        type="text"
                        wire:model="confirmationCode"
                        placeholder="123456"
                        maxlength="6"
                        inputmode="numeric"
                        style="flex:1;border-radius:8px;border:1px solid #d1d5db;padding:8px 12px;font-size:14px;letter-spacing:0.2em;text-align:center;"
                    >
                    <button
                        type="submit"
                        style="border-radius:8px;background:#4f46e5;border:0;padding:9px 16px;font-size:12px;font-weight:600;color:#fff;cursor:pointer;white-space:nowrap;"
                    >
                        Konfirmasi
                    </button>
                </form>
                <button
                    type="button"
                    wire:click="cancelSetup"
                    style="margin-top:10px;border:0;background:none;color:#9ca3af;font-size:12px;cursor:pointer;text-decoration:underline;"
                >
                    Batalkan
                </button>
            </div>
        @elseif ($enabled)
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#166534;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">
                        Aktif
                    </span>
                    <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0;">Autentikasi Dua Faktor</h2>
                </div>
                <p style="font-size:12px;color:#6b7280;margin:8px 0 12px;">
                    Login sekarang meminta kode dari aplikasi authenticator Anda setelah kata sandi.
                </p>
                <button
                    type="button"
                    wire:click="regenerateRecoveryCodes"
                    style="border-radius:8px;border:1px solid #e5e7eb;background:#fff;padding:8px 14px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;"
                >
                    Buat Ulang Kode Pemulihan
                </button>
            </div>

            <div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:20px;">
                <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 4px;">Nonaktifkan 2FA</h2>
                <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">
                    Masukkan kata sandi Anda untuk menonaktifkan autentikasi dua faktor.
                </p>
                <form wire:submit.prevent="disable" style="display:flex;gap:8px;">
                    <input
                        type="password"
                        wire:model="disablePassword"
                        placeholder="Kata sandi"
                        style="flex:1;border-radius:8px;border:1px solid #d1d5db;padding:8px 12px;font-size:13px;"
                    >
                    <button
                        type="submit"
                        style="border-radius:8px;background:#dc2626;border:0;padding:9px 16px;font-size:12px;font-weight:600;color:#fff;cursor:pointer;white-space:nowrap;"
                    >
                        Nonaktifkan
                    </button>
                </form>
            </div>
        @else
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 4px;">Autentikasi Dua Faktor Belum Aktif</h2>
                <p style="font-size:12px;color:#6b7280;margin:0 0 14px;">
                    Tambahkan lapisan keamanan ekstra: setelah aktif, login membutuhkan kode dari aplikasi
                    authenticator (Google Authenticator, Authy, dll) selain kata sandi.
                </p>
                <button
                    type="button"
                    wire:click="startSetup"
                    style="border-radius:8px;background:#4f46e5;border:0;padding:9px 16px;font-size:12px;font-weight:600;color:#fff;cursor:pointer;"
                >
                    Aktifkan Autentikasi Dua Faktor
                </button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
