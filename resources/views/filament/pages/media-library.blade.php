<x-filament-panels::page>
    <p style="font-size:13px;color:#6b7280;margin:0 0 16px;">
        Semua file yang pernah diunggah lewat form blok/pengaturan ({{ count($files) }} file). Hapus di sini hanya kalau
        Anda yakin file itu sudah tidak dipakai di halaman/pengaturan mana pun — halaman ini tidak tahu di mana sebuah
        file sedang dipakai.
    </p>

    @if (count($files) === 0)
        <div style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
            Belum ada file yang diunggah.
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
            @foreach ($files as $file)
                <div
                    wire:key="media-{{ $file['path'] }}"
                    style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fff;"
                >
                    <div style="height:110px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        @if ($file['isImage'])
                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" style="width:100%;height:100%;object-fit:cover;display:block;">
                        @else
                            <svg style="width:32px;height:32px;color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-1.519-3.75L12 15.75l-1.481-1.5M8.25 21h7.5a2.25 2.25 0 002.25-2.25V11.25l-7.5-7.5H6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 006 21z" /></svg>
                        @endif
                    </div>
                    <div style="padding:8px 10px;">
                        <div style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $file['name'] }}">
                            {{ $file['name'] }}
                        </div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px;">{{ $file['size'] }} &middot; {{ $file['modified'] }}</div>
                        <div style="display:flex;gap:8px;margin-top:6px;">
                            <a href="{{ $file['url'] }}" target="_blank" style="font-size:11px;font-weight:600;color:#4f46e5;text-decoration:none;">Lihat</a>
                            <button
                                type="button"
                                wire:click="deleteFile('{{ $file['path'] }}')"
                                wire:confirm="Hapus file ini? Aksi ini tidak bisa dibatalkan."
                                style="font-size:11px;font-weight:600;color:#dc2626;background:none;border:0;padding:0;cursor:pointer;"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
