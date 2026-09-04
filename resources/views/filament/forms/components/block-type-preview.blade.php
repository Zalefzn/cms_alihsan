@php
    // Purely illustrative mockups — plain inline styles so they never depend on
    // Filament's compiled Tailwind CSS purge list. When the current type has
    // variants, every variant gets its own small card side by side (the active
    // one highlighted) so admins can compare before picking.
    $card = 'border-radius:6px;overflow:hidden;';
    $muted = '#e5e7eb';
    $mutedDark = '#cbd5e1';
    $indigo = '#4f46e5';
    $text = '#374151';

    $variants = $variants ?? [];
    $activeVariant = $variant ?? ($variants !== [] ? array_key_first($variants) : null);
@endphp

<div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fff;">
    <div style="padding:14px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
        Pratinjau tampilan — tata letak &amp; warna asli bisa sedikit berbeda.
    </div>

    <div style="padding:20px;">
        @if ($variants !== [])
            <div style="display:grid;grid-template-columns:repeat({{ count($variants) }},1fr);gap:12px;">
                @foreach ($variants as $variantKey => $variantLabel)
                    @php $isActive = $activeVariant === $variantKey; @endphp
                    <div style="border-radius:8px;padding:6px;{{ $isActive ? 'box-shadow:0 0 0 2px '.$indigo.';' : 'box-shadow:0 0 0 1px '.$muted.';' }}">
                        @switch($type.':'.$variantKey)
                            {{-- hero --}}
                            @case('hero:split')
                                <div style="{{ $card }} background:linear-gradient(135deg,#e0e7ff,#dbeafe);padding:14px 12px;">
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;align-items:center;">
                                        <div>
                                            <div style="height:8px;width:85%;background:{{ $text }};border-radius:3px;margin-bottom:6px;"></div>
                                            <div style="height:6px;width:60%;background:{{ $mutedDark }};border-radius:3px;"></div>
                                        </div>
                                        <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;">🖼</div>
                                    </div>
                                </div>
                                @break
                            @case('hero:minimal')
                                <div style="{{ $card }} background:linear-gradient(135deg,{{ $indigo }},#312e81);padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 6px;"></div>
                                    <div style="height:5px;width:38%;background:rgba(255,255,255,.6);border-radius:3px;margin:0 auto;"></div>
                                </div>
                                @break
                            @case('hero:center')
                                <div style="{{ $card }} background:linear-gradient(135deg,{{ $indigo }},#312e81);padding:16px 10px;text-align:center;position:relative;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 6px;"></div>
                                    <div style="height:5px;width:38%;background:rgba(255,255,255,.6);border-radius:3px;margin:0 auto 10px;"></div>
                                    <div style="position:absolute;bottom:0;left:0;right:0;height:10px;background:#fff;clip-path:polygon(0 60%,10% 40%,25% 70%,40% 30%,55% 65%,70% 35%,85% 60%,100% 45%,100% 100%,0 100%);"></div>
                                </div>
                                @break

                            {{-- rich_text --}}
                            @case('rich_text:left')
                                <div style="{{ $card }} background:#fff;text-align:left;">
                                    <div style="height:9px;width:40%;background:{{ $text }};border-radius:3px;margin-bottom:8px;"></div>
                                    <div style="height:6px;width:95%;background:{{ $muted }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:6px;width:80%;background:{{ $muted }};border-radius:3px;"></div>
                                </div>
                                @break
                            @case('rich_text:standard')
                                <div style="{{ $card }} background:#fff;text-align:center;">
                                    <div style="height:9px;width:45%;background:{{ $text }};border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="height:6px;width:90%;background:{{ $muted }};border-radius:3px;margin:0 auto 5px;"></div>
                                    <div style="height:6px;width:70%;background:{{ $muted }};border-radius:3px;margin:0 auto;"></div>
                                </div>
                                @break

                            {{-- image_gallery --}}
                            @case('image_gallery:carousel')
                                <div style="display:flex;gap:6px;overflow:hidden;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} min-width:32%;aspect-ratio:4/3;background:{{ $mutedDark }};flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;">🖼</div>
                                    @endfor
                                </div>
                                @break
                            @case('image_gallery:grid')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;">🖼</div>
                                    @endfor
                                </div>
                                @break

                            {{-- video --}}
                            @case('video:compact')
                                <div style="{{ $card }} aspect-ratio:16/9;background:#1f2937;display:flex;align-items:center;justify-content:center;">
                                    <div style="width:0;height:0;border-top:8px solid transparent;border-bottom:8px solid transparent;border-left:12px solid #fff;"></div>
                                </div>
                                @break
                            @case('video:standard')
                                <div>
                                    <div style="height:7px;width:40%;background:{{ $text }};border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="{{ $card }} aspect-ratio:16/9;background:#1f2937;display:flex;align-items:center;justify-content:center;">
                                        <div style="width:0;height:0;border-top:8px solid transparent;border-bottom:8px solid transparent;border-left:12px solid #fff;"></div>
                                    </div>
                                </div>
                                @break

                            {{-- cta --}}
                            @case('cta:banner')
                                <div style="{{ $card }} background:{{ $indigo }};padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="height:14px;width:70px;background:#fff;border-radius:999px;margin:0 auto;"></div>
                                </div>
                                @break
                            @case('cta:plain')
                                <div style="{{ $card }} background:#fff;padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:{{ $text }};border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="height:14px;width:70px;background:{{ $indigo }};border-radius:999px;margin:0 auto;"></div>
                                </div>
                                @break

                            {{-- faq --}}
                            @case('faq:grid')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;">
                                            <div style="height:6px;width:80%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                            <div style="height:5px;width:60%;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break
                            @case('faq:accordion')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:7px 10px;display:flex;align-items:center;justify-content:space-between;">
                                            <div style="height:6px;width:{{ 60 - $i * 8 }}%;background:{{ $text }};border-radius:3px;"></div>
                                            <div style="color:#9ca3af;font-size:12px;">+</div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- team --}}
                            @case('team:list')
                                <div style="display:flex;flex-direction:column;gap:8px;">
                                    @for ($i = 0; $i < 2; $i++)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="width:28px;height:28px;border-radius:999px;background:{{ $mutedDark }};flex-shrink:0;"></div>
                                            <div style="flex:1;">
                                                <div style="height:6px;width:70%;background:{{ $text }};border-radius:3px;margin-bottom:4px;"></div>
                                                <div style="height:5px;width:45%;background:{{ $muted }};border-radius:3px;"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                @break
                            @case('team:grid')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="text-align:center;">
                                            <div style="width:32px;height:32px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 5px;"></div>
                                            <div style="height:5px;width:80%;background:{{ $text }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- testimonials --}}
                            @case('testimonials:grid')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;">
                                            <div style="height:5px;width:90%;background:{{ $muted }};border-radius:3px;margin-bottom:4px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $muted }};border-radius:3px;margin-bottom:8px;"></div>
                                            <div style="width:16px;height:16px;border-radius:999px;background:{{ $mutedDark }};"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break
                            @case('testimonials:carousel')
                                <div style="{{ $card }} border:1px solid {{ $muted }};padding:12px;">
                                    <div style="height:6px;width:90%;background:{{ $muted }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:6px;width:60%;background:{{ $muted }};border-radius:3px;margin-bottom:10px;"></div>
                                    <div style="display:flex;justify-content:center;gap:4px;">
                                        <span style="width:6px;height:6px;border-radius:999px;background:{{ $indigo }};display:inline-block;"></span>
                                        <span style="width:6px;height:6px;border-radius:999px;background:{{ $muted }};display:inline-block;"></span>
                                        <span style="width:6px;height:6px;border-radius:999px;background:{{ $muted }};display:inline-block;"></span>
                                    </div>
                                </div>
                                @break

                            {{-- contact_info --}}
                            @case('contact_info:stacked')
                                <div>
                                    <div style="display:flex;gap:10px;justify-content:center;margin-bottom:8px;">
                                        @foreach (['📞','📍','✉️'] as $icon)
                                            <span style="font-size:13px;">{{ $icon }}</span>
                                        @endforeach
                                    </div>
                                    <div style="{{ $card }} background:{{ $mutedDark }};height:36px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px;">🗺️</div>
                                </div>
                                @break
                            @case('contact_info:standard')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                    <div>
                                        @foreach (['📞','📍','✉️'] as $icon)
                                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                                <span style="font-size:12px;">{{ $icon }}</span>
                                                <div style="height:5px;width:60px;background:{{ $muted }};border-radius:3px;"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div style="{{ $card }} background:{{ $mutedDark }};min-height:50px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px;">🗺️</div>
                                </div>
                                @break

                            {{-- stats --}}
                            @case('stats:cards')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;text-align:center;">
                                            <div style="height:10px;width:50%;background:{{ $indigo }};border-radius:3px;margin:0 auto 4px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $muted }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break
                            @case('stats:inline')
                                <div style="display:flex;justify-content:space-around;text-align:center;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div>
                                            <div style="height:12px;width:36px;background:{{ $indigo }};border-radius:3px;margin:0 auto 4px;"></div>
                                            <div style="height:5px;width:36px;background:{{ $muted }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- feature_list --}}
                            @case('feature_list:list')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="width:18px;height:18px;border-radius:999px;background:{{ $indigo }};color:#fff;font-size:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $i + 1 }}</div>
                                            <div style="height:6px;flex:1;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break
                            @case('feature_list:grid')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;text-align:center;">
                                            <div style="width:20px;height:20px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 5px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $text }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- photo_feature --}}
                            @case('photo_feature:overlay')
                                <div style="{{ $card }} aspect-ratio:16/9;background:{{ $mutedDark }};position:relative;display:flex;align-items:flex-end;">
                                    <div style="width:100%;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:10px;">
                                        <div style="height:7px;width:60%;background:#fff;border-radius:3px;"></div>
                                    </div>
                                </div>
                                @break
                            @case('photo_feature:standard')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;align-items:center;">
                                    <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;">🖼</div>
                                    <div>
                                        <div style="height:7px;width:80%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                        <div style="height:5px;width:100%;background:{{ $muted }};border-radius:3px;"></div>
                                    </div>
                                </div>
                                @break

                            {{-- about_split --}}
                            @case('about_split:stacked')
                                <div>
                                    <div style="height:7px;width:50%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:5px;width:90%;background:{{ $muted }};border-radius:3px;margin-bottom:8px;"></div>
                                    <div style="height:6px;width:40%;background:{{ $indigo }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:5px;width:80%;background:{{ $muted }};border-radius:3px;"></div>
                                </div>
                                @break
                            @case('about_split:columns')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                    <div>
                                        <div style="height:7px;width:70%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                        <div style="height:5px;width:100%;background:{{ $muted }};border-radius:3px;"></div>
                                    </div>
                                    <div>
                                        <div style="height:6px;width:50%;background:{{ $indigo }};border-radius:3px;margin-bottom:5px;"></div>
                                        <div style="height:5px;width:90%;background:{{ $muted }};border-radius:3px;"></div>
                                    </div>
                                </div>
                                @break

                            {{-- program_cards --}}
                            @case('program_cards:minimal')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @foreach (['#14b8a6','#6366f1','#ec4899'] as $color)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:10px 6px;text-align:center;">
                                            <div style="width:16px;height:16px;border-radius:999px;background:{{ $color }};margin:0 auto 5px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $text }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endforeach
                                </div>
                                @break
                            @case('program_cards:colorful')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @foreach (['#14b8a6','#6366f1','#ec4899'] as $color)
                                        <div style="{{ $card }} background:{{ $color }};padding:10px 6px;text-align:center;">
                                            <div style="height:5px;width:70%;background:#fff;border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            {{-- news_list --}}
                            @case('news_list:list')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @for ($i = 0; $i < 2; $i++)
                                        <div style="display:flex;gap:6px;align-items:center;">
                                            <div style="{{ $card }} width:36px;height:26px;background:{{ $mutedDark }};flex-shrink:0;"></div>
                                            <div style="flex:1;">
                                                <div style="height:5px;width:80%;background:{{ $text }};border-radius:3px;margin-bottom:4px;"></div>
                                                <div style="height:4px;width:50%;background:{{ $muted }};border-radius:3px;"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                @break
                            @case('news_list:grid')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};overflow:hidden;">
                                            <div style="aspect-ratio:16/10;background:{{ $mutedDark }};"></div>
                                            <div style="padding:6px;">
                                                <div style="height:5px;width:80%;background:{{ $text }};border-radius:3px;"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            @default
                                <div style="color:#9ca3af;font-size:11px;text-align:center;">{{ $variantLabel }}</div>
                        @endswitch
                        <div style="text-align:center;font-size:11px;margin-top:6px;color:{{ $isActive ? $indigo : '#6b7280' }};font-weight:{{ $isActive ? '600' : '400' }};">
                            {{ $variantLabel }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Types without variants: one static illustrative mockup. --}}
            @switch($type)
                @case('image_gallery')
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                        @for ($i = 0; $i < 6; $i++)
                            <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:18px;">🖼</div>
                        @endfor
                    </div>
                    @break

                @default
                    <div style="color:#9ca3af;font-size:13px;">Pilih tipe blok untuk melihat pratinjau.</div>
            @endswitch
        @endif
    </div>
</div>
