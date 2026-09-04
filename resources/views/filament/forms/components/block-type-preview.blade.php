@php
    // Purely illustrative mockup per block type — plain inline styles so it never
    // depends on Filament's compiled Tailwind CSS purge list.
    $card = 'border-radius:6px;overflow:hidden;';
    $muted = '#e5e7eb';
    $mutedDark = '#cbd5e1';
    $indigo = '#4f46e5';
    $text = '#374151';
@endphp

<div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fff;">
    <div style="padding:14px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
        Pratinjau tampilan — tata letak &amp; warna asli bisa sedikit berbeda.
    </div>

    <div style="padding:20px;">
        @switch($type)
            @case('hero')
                @php $activeVariant = $variant ?? array_key_first($variants ?? ['center' => '']); @endphp
                <div style="display:grid;grid-template-columns:repeat({{ max(count($variants ?? []), 1) }},1fr);gap:12px;">
                    @foreach (($variants ?? ['center' => 'Standar']) as $variantKey => $variantLabel)
                        @php $isActive = $activeVariant === $variantKey; @endphp
                        <div style="border-radius:8px;padding:6px;{{ $isActive ? 'box-shadow:0 0 0 2px '.$indigo.';' : 'box-shadow:0 0 0 1px '.$muted.';' }}">
                            @if ($variantKey === 'split')
                                <div style="{{ $card }} background:linear-gradient(135deg,#e0e7ff,#dbeafe);padding:14px 12px;">
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;align-items:center;">
                                        <div>
                                            <div style="height:8px;width:85%;background:{{ $text }};border-radius:3px;margin-bottom:6px;"></div>
                                            <div style="height:6px;width:60%;background:{{ $mutedDark }};border-radius:3px;"></div>
                                        </div>
                                        <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;">🖼</div>
                                    </div>
                                </div>
                            @elseif ($variantKey === 'minimal')
                                <div style="{{ $card }} background:linear-gradient(135deg,{{ $indigo }},#312e81);padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 6px;"></div>
                                    <div style="height:5px;width:38%;background:rgba(255,255,255,.6);border-radius:3px;margin:0 auto;"></div>
                                </div>
                            @else
                                <div style="{{ $card }} background:linear-gradient(135deg,{{ $indigo }},#312e81);padding:16px 10px;text-align:center;position:relative;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 6px;"></div>
                                    <div style="height:5px;width:38%;background:rgba(255,255,255,.6);border-radius:3px;margin:0 auto 10px;"></div>
                                    <div style="position:absolute;bottom:0;left:0;right:0;height:10px;background:#fff;clip-path:polygon(0 60%,10% 40%,25% 70%,40% 30%,55% 65%,70% 35%,85% 60%,100% 45%,100% 100%,0 100%);"></div>
                                </div>
                            @endif
                            <div style="text-align:center;font-size:11px;margin-top:6px;color:{{ $isActive ? $indigo : '#6b7280' }};font-weight:{{ $isActive ? '600' : '400' }};">
                                {{ $variantLabel }}
                            </div>
                        </div>
                    @endforeach
                </div>
                @break

            @case('cta')
                <div style="{{ $card }} background:{{ $indigo }};padding:26px 20px;text-align:center;">
                    <div style="height:12px;width:50%;background:#fff;border-radius:4px;margin:0 auto 10px;"></div>
                    <div style="height:8px;width:70%;background:rgba(255,255,255,.6);border-radius:4px;margin:0 auto 14px;"></div>
                    <div style="height:20px;width:100px;background:#fff;border-radius:999px;margin:0 auto;"></div>
                </div>
                @break

            @case('rich_text')
                <div style="{{ $card }} background:#fff;">
                    <div style="height:12px;width:45%;background:{{ $text }};border-radius:4px;margin:0 auto 14px;"></div>
                    <div style="height:7px;width:95%;background:{{ $muted }};border-radius:4px;margin-bottom:8px;"></div>
                    <div style="height:7px;width:88%;background:{{ $muted }};border-radius:4px;margin-bottom:8px;"></div>
                    <div style="height:7px;width:92%;background:{{ $muted }};border-radius:4px;margin-bottom:8px;"></div>
                    <div style="height:7px;width:60%;background:{{ $muted }};border-radius:4px;"></div>
                </div>
                @break

            @case('image_gallery')
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                    @for ($i = 0; $i < 6; $i++)
                        <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:18px;">🖼</div>
                    @endfor
                </div>
                @break

            @case('video')
                <div style="{{ $card }} aspect-ratio:16/9;background:#1f2937;display:flex;align-items:center;justify-content:center;">
                    <div style="width:0;height:0;border-top:12px solid transparent;border-bottom:12px solid transparent;border-left:20px solid #fff;margin-left:4px;"></div>
                </div>
                @break

            @case('faq')
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @for ($i = 0; $i < 3; $i++)
                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:10px 12px;display:flex;align-items:center;justify-content:space-between;">
                            <div style="height:8px;width:{{ 70 - $i * 10 }}%;background:{{ $text }};border-radius:4px;"></div>
                            <div style="color:#9ca3af;">+</div>
                        </div>
                    @endfor
                </div>
                @break

            @case('team')
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                    @for ($i = 0; $i < 3; $i++)
                        <div style="text-align:center;">
                            <div style="width:56px;height:56px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 8px;"></div>
                            <div style="height:7px;width:80%;background:{{ $text }};border-radius:4px;margin:0 auto 6px;"></div>
                            <div style="height:6px;width:60%;background:{{ $muted }};border-radius:4px;margin:0 auto;"></div>
                        </div>
                    @endfor
                </div>
                @break

            @case('testimonials')
                <div style="{{ $card }} border:1px solid {{ $muted }};padding:16px;position:relative;">
                    <div style="font-size:22px;color:{{ $indigo }};line-height:1;margin-bottom:6px;">&ldquo;</div>
                    <div style="height:7px;width:95%;background:{{ $muted }};border-radius:4px;margin-bottom:6px;"></div>
                    <div style="height:7px;width:80%;background:{{ $muted }};border-radius:4px;margin-bottom:14px;"></div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:32px;height:32px;border-radius:999px;background:{{ $mutedDark }};"></div>
                        <div style="height:7px;width:90px;background:{{ $text }};border-radius:4px;"></div>
                    </div>
                </div>
                @break

            @case('contact_info')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        @foreach (['📞','📍','✉️'] as $icon)
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                <div style="width:22px;">{{ $icon }}</div>
                                <div style="height:7px;width:100px;background:{{ $muted }};border-radius:4px;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div style="{{ $card }} background:{{ $mutedDark }};min-height:70px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:16px;">🗺️</div>
                </div>
                @break

            @case('stats')
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center;">
                    @for ($i = 0; $i < 3; $i++)
                        <div>
                            <div style="height:16px;width:50%;background:{{ $indigo }};border-radius:4px;margin:0 auto 8px;"></div>
                            <div style="height:6px;width:70%;background:{{ $muted }};border-radius:4px;margin:0 auto;"></div>
                        </div>
                    @endfor
                </div>
                @break

            @case('feature_list')
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    @for ($i = 0; $i < 3; $i++)
                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:12px;text-align:center;">
                            <div style="width:32px;height:32px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 8px;"></div>
                            <div style="height:7px;width:80%;background:{{ $text }};border-radius:4px;margin:0 auto 6px;"></div>
                            <div style="height:6px;width:60%;background:{{ $muted }};border-radius:4px;margin:0 auto;"></div>
                        </div>
                    @endfor
                </div>
                @break

            @case('photo_feature')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center;">
                    <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:20px;">🖼</div>
                    <div>
                        <div style="height:10px;width:80%;background:{{ $text }};border-radius:4px;margin-bottom:8px;"></div>
                        <div style="height:6px;width:100%;background:{{ $muted }};border-radius:4px;margin-bottom:6px;"></div>
                        <div style="height:6px;width:90%;background:{{ $muted }};border-radius:4px;margin-bottom:12px;"></div>
                        <div style="height:16px;width:90px;background:{{ $indigo }};border-radius:999px;"></div>
                    </div>
                </div>
                @break

            @case('about_split')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <div style="height:10px;width:70%;background:{{ $text }};border-radius:4px;margin-bottom:8px;"></div>
                        <div style="height:6px;width:100%;background:{{ $muted }};border-radius:4px;margin-bottom:6px;"></div>
                        <div style="height:6px;width:85%;background:{{ $muted }};border-radius:4px;"></div>
                    </div>
                    <div>
                        <div style="height:8px;width:50%;background:{{ $indigo }};border-radius:4px;margin-bottom:8px;"></div>
                        <div style="height:6px;width:90%;background:{{ $muted }};border-radius:4px;margin-bottom:10px;"></div>
                        <div style="height:8px;width:50%;background:{{ $indigo }};border-radius:4px;margin-bottom:8px;"></div>
                        <div style="height:6px;width:90%;background:{{ $muted }};border-radius:4px;"></div>
                    </div>
                </div>
                @break

            @case('program_cards')
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    @foreach (['#14b8a6','#6366f1','#ec4899'] as $color)
                        <div style="text-align:center;padding-top:20px;position:relative;">
                            <div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:36px;height:36px;border-radius:999px;background:#fff;border:2px solid {{ $muted }};"></div>
                            <div style="{{ $card }} background:{{ $color }};padding:22px 8px 12px;">
                                <div style="height:7px;width:70%;background:#fff;border-radius:4px;margin:0 auto 8px;"></div>
                                <div style="height:14px;width:80%;background:rgba(255,255,255,.9);border-radius:999px;margin:0 auto;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @break

            @case('news_list')
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                    @for ($i = 0; $i < 3; $i++)
                        <div style="{{ $card }} border:1px solid {{ $muted }};overflow:hidden;">
                            <div style="aspect-ratio:16/10;background:{{ $mutedDark }};"></div>
                            <div style="padding:10px;">
                                <div style="height:6px;width:40%;background:{{ $indigo }};border-radius:4px;margin-bottom:6px;"></div>
                                <div style="height:7px;width:90%;background:{{ $text }};border-radius:4px;margin-bottom:6px;"></div>
                                <div style="height:6px;width:70%;background:{{ $muted }};border-radius:4px;"></div>
                            </div>
                        </div>
                    @endfor
                </div>
                @break

            @default
                <div style="color:#9ca3af;font-size:13px;">Pilih tipe blok untuk melihat pratinjau.</div>
        @endswitch
    </div>
</div>
