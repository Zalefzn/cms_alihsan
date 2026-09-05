@php
    // Purely illustrative mockups — plain inline styles so they never depend on
    // Filament's compiled Tailwind CSS purge list. When the current type has
    // variants, every variant gets its own small card side by side (the active
    // one highlighted) so admins can compare before picking.
    //
    // With 23 block types × 5 variants (115 combinations) a fully bespoke mockup
    // per combination isn't maintainable, so closely-related variants across
    // different types intentionally share the same illustration via switch
    // fallthrough (multiple @case lines stacked before one shared block) —
    // the variant's label underneath is what actually distinguishes them.
    $card = 'border-radius:6px;overflow:hidden;';
    $muted = '#e5e7eb';
    $mutedDark = '#cbd5e1';
    $indigo = '#4f46e5';
    $text = '#374151';

    $variants = $variants ?? [];
    $activeVariant = $variant ?? ($variants !== [] ? array_key_first($variants) : null);

    // --- Real live preview: render the admin's actual typed text / uploaded images / list
    // items (not just an abstract mockup) for whichever variant card is currently active, so
    // they can judge the real content before committing to a layout. Works generically across
    // all 23 block types by pulling from whichever of these commonly-named fields exist.
    // Media resolution (saved path / fresh upload / already-absolute URL) is shared with
    // Block::resolvedData() and the page builder's live canvas via App\Support\MediaResolver.
    $resolveMediaUrl = fn ($value) => \App\Support\MediaResolver::resolveValue($value);

    $liveData = $liveData ?? [];
    $pick = fn (array $keys) => collect($keys)->map(fn ($k) => $liveData[$k] ?? null)->first(fn ($v) => filled($v));

    $previewTitle = $pick(['heading', 'quote', 'name']);
    $previewSubtitle = $pick(['subheading', 'role', 'vision_heading']);
    $previewBody = $pick(['body', 'address', 'vision_text']);
    $previewImage = $resolveMediaUrl($liveData['image'] ?? $liveData['photo'] ?? null)
        ?: \App\Support\MediaResolver::youtubeThumbnail($liveData['embed_url'] ?? null);
    $rawItems = $liveData['items'] ?? $liveData['logos'] ?? $liveData['mission_items'] ?? [];

    $previewItems = collect(is_array($rawItems) ? $rawItems : [])
        ->filter(fn ($item) => is_array($item))
        ->map(function ($item) use ($resolveMediaUrl) {
            return [
                'title' => $item['title'] ?? $item['name'] ?? $item['question'] ?? $item['label'] ?? $item['text'] ?? '',
                'subtitle' => $item['description'] ?? $item['role'] ?? $item['answer'] ?? $item['body']
                    ?? $item['quote'] ?? $item['price'] ?? (isset($item['value']) ? ($item['value'] . ($item['suffix'] ?? '')) : null),
                'image' => $resolveMediaUrl($item['photo'] ?? $item['image'] ?? null),
            ];
        })
        ->filter(fn ($item) => filled($item['title']) || filled($item['image']))
        ->values()
        ->all();

    $hasRealContent = filled($previewTitle) || filled($previewBody) || filled($previewImage) || $previewItems !== [];
@endphp

<div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fff;">
    <div style="padding:14px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
        Pratinjau tampilan — tata letak &amp; warna asli bisa sedikit berbeda.
    </div>

    <div style="padding:20px;">
        @if ($variants !== [])
            {{-- auto-fill + minmax instead of a fixed count($variants) columns — in the
                 page builder's narrow ~420px sidebar, forcing e.g. 5 columns squeezes each
                 card to a sliver; this wraps to as many columns as actually fit (2-3 in the
                 sidebar, more in the wider table-editor modal) at a readable minimum width. --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;">
                @foreach ($variants as $variantKey => $variantLabel)
                    @php $isActive = $activeVariant === $variantKey; @endphp
                    <div style="border-radius:8px;padding:6px;{{ $isActive ? 'box-shadow:0 0 0 2px '.$indigo.';' : 'box-shadow:0 0 0 1px '.$muted.';' }}">
                        @if ($isActive && $hasRealContent)
                            <div style="{{ $card }} background:#fff;border:1px solid {{ $muted }};">
                                @if ($previewImage)
                                    <div style="aspect-ratio:16/9;overflow:hidden;background:{{ $mutedDark }};">
                                        <img src="{{ $previewImage }}" style="width:100%;height:100%;object-fit:cover;display:block;">
                                    </div>
                                @endif
                                <div style="padding:10px;">
                                    @if ($previewTitle)
                                        <div style="font-weight:700;font-size:13px;color:{{ $text }};margin-bottom:3px;line-height:1.3;">
                                            {{ \Illuminate\Support\Str::limit($previewTitle, 60) }}
                                        </div>
                                    @endif
                                    @if ($previewSubtitle)
                                        <div style="font-size:11px;color:{{ $indigo }};margin-bottom:4px;">
                                            {{ \Illuminate\Support\Str::limit($previewSubtitle, 50) }}
                                        </div>
                                    @endif
                                    @if ($previewBody)
                                        <div style="font-size:11px;color:#6b7280;line-height:1.5;">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($previewBody), 110) }}
                                        </div>
                                    @endif
                                    @if ($previewItems !== [])
                                        <div style="display:flex;gap:6px;overflow:hidden;margin-top:{{ ($previewTitle || $previewBody) ? '8px' : '0' }};">
                                            @foreach (array_slice($previewItems, 0, 4) as $item)
                                                <div style="flex:1;min-width:0;text-align:center;">
                                                    @if ($item['image'])
                                                        <img src="{{ $item['image'] }}" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;margin-bottom:3px;">
                                                    @elseif (!$previewImage && $item['title'])
                                                        <div style="width:22px;height:22px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 3px;"></div>
                                                    @endif
                                                    <div style="font-size:9px;font-weight:600;color:{{ $text }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        {{ \Illuminate\Support\Str::limit($item['title'], 14) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                        @switch($type.':'.$variantKey)
                            {{-- hero:split family — 2-col text + image --}}
                            @case('hero:split')
                            @case('rich_text:two_column')
                            @case('video:side_by_side')
                            @case('map:side_info')
                            @case('quote:side_photo')
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

                            {{-- hero:minimal family — solid colour centered box --}}
                            @case('hero:minimal')
                            @case('hero:banner')
                            @case('rich_text:highlight')
                            @case('cta:gradient')
                            @case('quote:large_type')
                            @case('pricing_table:featured_center')
                                <div style="{{ $card }} background:linear-gradient(135deg,{{ $indigo }},#312e81);padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 6px;"></div>
                                    <div style="height:5px;width:38%;background:rgba(255,255,255,.6);border-radius:3px;margin:0 auto;"></div>
                                </div>
                                @break

                            {{-- hero:center — same as above plus a bottom wave divider --}}
                            @case('hero:center')
                                <div style="{{ $card }} background:linear-gradient(135deg,{{ $indigo }},#312e81);padding:16px 10px;text-align:center;position:relative;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 6px;"></div>
                                    <div style="height:5px;width:38%;background:rgba(255,255,255,.6);border-radius:3px;margin:0 auto 10px;"></div>
                                    <div style="position:absolute;bottom:0;left:0;right:0;height:10px;background:#fff;clip-path:polygon(0 60%,10% 40%,25% 70%,40% 30%,55% 65%,70% 35%,85% 60%,100% 45%,100% 100%,0 100%);"></div>
                                </div>
                                @break

                            {{-- rich_text:standard family — centered text lines --}}
                            @case('rich_text:standard')
                            @case('quote:centered')
                                <div style="{{ $card }} background:#fff;text-align:center;">
                                    <div style="height:9px;width:45%;background:{{ $text }};border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="height:6px;width:90%;background:{{ $muted }};border-radius:3px;margin:0 auto 5px;"></div>
                                    <div style="height:6px;width:70%;background:{{ $muted }};border-radius:3px;margin:0 auto;"></div>
                                </div>
                                @break

                            {{-- rich_text:left family — left-aligned plain lines --}}
                            @case('rich_text:left')
                            @case('rich_text:boxed')
                            @case('quote:minimal')
                            @case('contact_info:minimal')
                            @case('photo_feature:minimal')
                            @case('video_feature:minimal')
                            @case('news_list:minimal_list')
                            @case('downloads:minimal')
                            @case('map:minimal')
                            @case('pricing_table:minimal')
                            @case('testimonials:minimal_quote')
                                <div style="{{ $card }} background:#fff;text-align:left;">
                                    <div style="height:9px;width:40%;background:{{ $text }};border-radius:3px;margin-bottom:8px;"></div>
                                    <div style="height:6px;width:95%;background:{{ $muted }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:6px;width:80%;background:{{ $muted }};border-radius:3px;"></div>
                                </div>
                                @break

                            {{-- image_gallery:carousel family — horizontal scroll strip --}}
                            @case('image_gallery:carousel')
                            @case('image_gallery:strip')
                            @case('image_gallery:masonry')
                            @case('team:carousel')
                            @case('logo_cloud:carousel')
                                <div style="display:flex;gap:6px;overflow:hidden;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} min-width:32%;aspect-ratio:4/3;background:{{ $mutedDark }};flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;">🖼</div>
                                    @endfor
                                </div>
                                @break

                            {{-- image_gallery:grid family — 3-tile grid --}}
                            @case('image_gallery:grid')
                            @case('image_gallery:columns_2')
                            @case('logo_cloud:grid')
                            @case('downloads:grid')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;">🖼</div>
                                    @endfor
                                </div>
                                @break

                            {{-- video:compact family — dark play button box --}}
                            @case('video:compact')
                            @case('video:framed')
                            @case('video_feature:framed')
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

                            {{-- cta:banner family — colour box, centered text + pill --}}
                            @case('cta:banner')
                            @case('cta:split')
                            @case('cta:boxed_card')
                            @case('countdown:banner')
                            @case('countdown:boxed')
                                <div style="{{ $card }} background:{{ $indigo }};padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:#fff;border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="height:14px;width:70px;background:#fff;border-radius:999px;margin:0 auto;"></div>
                                </div>
                                @break

                            {{-- cta:plain family — white bordered box, centered text + pill --}}
                            @case('cta:plain')
                            @case('quote:boxed')
                            @case('map:boxed')
                                <div style="{{ $card }} background:#fff;padding:16px 10px;text-align:center;">
                                    <div style="height:8px;width:60%;background:{{ $text }};border-radius:3px;margin:0 auto 8px;"></div>
                                    <div style="height:14px;width:70px;background:{{ $indigo }};border-radius:999px;margin:0 auto;"></div>
                                </div>
                                @break

                            {{-- faq:grid family — 2-col bordered boxes --}}
                            @case('faq:grid')
                            @case('faq:two_column')
                            @case('accordion_tabs:two_column')
                            @case('stats:bordered_grid')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;">
                                            <div style="height:6px;width:80%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                            <div style="height:5px;width:60%;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- faq:accordion family — stacked rows with +/- --}}
                            @case('faq:accordion')
                            @case('faq:minimal_list')
                            @case('faq:boxed')
                            @case('accordion_tabs:accordion')
                            @case('accordion_tabs:boxed_accordion')
                            @case('accordion_tabs:numbered_list')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:7px 10px;display:flex;align-items:center;justify-content:space-between;">
                                            <div style="height:6px;width:{{ 60 - $i * 8 }}%;background:{{ $text }};border-radius:3px;"></div>
                                            <div style="color:#9ca3af;font-size:12px;">+</div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- team:list family — avatar + lines rows --}}
                            @case('team:list')
                            @case('team:minimal')
                            @case('downloads:list')
                            @case('pricing_table:horizontal')
                            @case('program_cards:horizontal')
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

                            {{-- team:grid family — circle + label grid --}}
                            @case('team:grid')
                            @case('team:compact_grid')
                            @case('logo_cloud:inline_row')
                            @case('logo_cloud:bordered_grid')
                            @case('logo_cloud:grayscale')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="text-align:center;">
                                            <div style="width:32px;height:32px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 5px;"></div>
                                            <div style="height:5px;width:80%;background:{{ $text }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- testimonials:grid family — bordered quote cards --}}
                            @case('testimonials:grid')
                            @case('testimonials:masonry')
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

                            {{-- contact_info:stacked --}}
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

                            {{-- contact_info:standard family — icon list + map box --}}
                            @case('contact_info:standard')
                            @case('contact_info:cards')
                            @case('contact_info:sidebar')
                            @case('map:standard')
                            @case('map:fullwidth')
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

                            {{-- stats:cards family — bordered number cards --}}
                            @case('stats:cards')
                            @case('counter:cards')
                            @case('pricing_table:cards')
                            @case('downloads:cards')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;text-align:center;">
                                            <div style="height:10px;width:50%;background:{{ $indigo }};border-radius:3px;margin:0 auto 4px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $muted }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- stats:inline family --}}
                            @case('stats:inline')
                            @case('counter:inline')
                                <div style="display:flex;justify-content:space-around;text-align:center;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div>
                                            <div style="height:12px;width:36px;background:{{ $indigo }};border-radius:3px;margin:0 auto 4px;"></div>
                                            <div style="height:5px;width:36px;background:{{ $muted }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- feature_list:list family — numbered rows --}}
                            @case('feature_list:list')
                            @case('feature_list:icons_row')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="width:18px;height:18px;border-radius:999px;background:{{ $indigo }};color:#fff;font-size:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $i + 1 }}</div>
                                            <div style="height:6px;flex:1;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- feature_list:grid family — icon + label cards --}}
                            @case('feature_list:grid')
                            @case('counter:icons_top')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;text-align:center;">
                                            <div style="width:20px;height:20px;border-radius:999px;background:{{ $mutedDark }};margin:0 auto 5px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $text }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- photo_feature:overlay family — full bg photo + gradient text --}}
                            @case('photo_feature:overlay')
                            @case('photo_feature:side_card')
                            @case('video_feature:side_card')
                            @case('video:background')
                            @case('hero:fullscreen')
                                <div style="{{ $card }} aspect-ratio:16/9;background:{{ $mutedDark }};position:relative;display:flex;align-items:flex-end;">
                                    <div style="width:100%;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:10px;">
                                        <div style="height:7px;width:60%;background:#fff;border-radius:3px;"></div>
                                    </div>
                                </div>
                                @break

                            {{-- photo_feature:standard family --}}
                            @case('photo_feature:standard')
                            @case('photo_feature:stacked')
                            @case('video_feature:standard')
                            @case('video_feature:stacked')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;align-items:center;">
                                    <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;">🖼</div>
                                    <div>
                                        <div style="height:7px;width:80%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                        <div style="height:5px;width:100%;background:{{ $muted }};border-radius:3px;"></div>
                                    </div>
                                </div>
                                @break

                            {{-- about_split:stacked --}}
                            @case('about_split:stacked')
                                <div>
                                    <div style="height:7px;width:50%;background:{{ $text }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:5px;width:90%;background:{{ $muted }};border-radius:3px;margin-bottom:8px;"></div>
                                    <div style="height:6px;width:40%;background:{{ $indigo }};border-radius:3px;margin-bottom:5px;"></div>
                                    <div style="height:5px;width:80%;background:{{ $muted }};border-radius:3px;"></div>
                                </div>
                                @break

                            {{-- about_split:columns family --}}
                            @case('about_split:columns')
                            @case('about_split:cards')
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

                            {{-- program_cards:minimal family --}}
                            @case('program_cards:minimal')
                            @case('program_cards:bordered')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @foreach (['#14b8a6','#6366f1','#ec4899'] as $color)
                                        <div style="{{ $card }} border:1px solid {{ $muted }};padding:10px 6px;text-align:center;">
                                            <div style="width:16px;height:16px;border-radius:999px;background:{{ $color }};margin:0 auto 5px;"></div>
                                            <div style="height:5px;width:70%;background:{{ $text }};border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            {{-- program_cards:colorful family --}}
                            @case('program_cards:colorful')
                            @case('program_cards:stacked_image')
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                    @foreach (['#14b8a6','#6366f1','#ec4899'] as $color)
                                        <div style="{{ $card }} background:{{ $color }};padding:10px 6px;text-align:center;">
                                            <div style="height:5px;width:70%;background:#fff;border-radius:3px;margin:0 auto;"></div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            {{-- news_list:list --}}
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

                            {{-- news_list:grid --}}
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

                            {{-- NEW: one big featured tile + small row below --}}
                            @case('testimonials:single_featured')
                            @case('news_list:featured')
                            @case('news_list:magazine')
                                <div>
                                    <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;margin-bottom:6px;">
                                        <div style="height:6px;width:60%;background:{{ $text }};border-radius:3px;margin-bottom:4px;"></div>
                                        <div style="height:5px;width:90%;background:{{ $muted }};border-radius:3px;"></div>
                                    </div>
                                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;">
                                        @for ($i = 0; $i < 3; $i++)
                                            <div style="{{ $card }} aspect-ratio:4/3;background:{{ $mutedDark }};"></div>
                                        @endfor
                                    </div>
                                </div>
                                @break

                            {{-- NEW: vertical connected timeline --}}
                            @case('feature_list:timeline')
                            @case('about_split:timeline')
                                <div style="position:relative;padding-left:18px;">
                                    <div style="position:absolute;left:5px;top:2px;bottom:2px;width:2px;background:{{ $muted }};"></div>
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="position:relative;margin-bottom:8px;">
                                            <div style="position:absolute;left:-18px;top:1px;width:10px;height:10px;border-radius:999px;background:{{ $indigo }};"></div>
                                            <div style="height:6px;width:{{ 80 - $i * 10 }}%;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- NEW: horizontal tab bar + content box --}}
                            @case('about_split:tabs')
                            @case('accordion_tabs:tabs')
                                <div>
                                    <div style="display:flex;gap:5px;margin-bottom:6px;">
                                        <div style="{{ $card }} background:{{ $indigo }};padding:4px 8px;font-size:9px;color:#fff;">Tab 1</div>
                                        <div style="{{ $card }} background:{{ $muted }};padding:4px 8px;font-size:9px;color:#9ca3af;">Tab 2</div>
                                    </div>
                                    <div style="{{ $card }} border:1px solid {{ $muted }};padding:8px;">
                                        <div style="height:5px;width:90%;background:{{ $muted }};border-radius:3px;margin-bottom:4px;"></div>
                                        <div style="height:5px;width:70%;background:{{ $muted }};border-radius:3px;"></div>
                                    </div>
                                </div>
                                @break

                            {{-- NEW: circular progress badges --}}
                            @case('stats:circular')
                            @case('counter:circular')
                                <div style="display:flex;justify-content:space-around;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="width:34px;height:34px;border-radius:999px;border:3px solid {{ $indigo }};display:flex;align-items:center;justify-content:center;font-size:9px;color:{{ $indigo }};font-weight:600;">{{ ($i + 1) * 25 }}%</div>
                                    @endfor
                                </div>
                                @break

                            {{-- NEW: full-width gradient band with big numbers --}}
                            @case('stats:gradient_band')
                            @case('counter:gradient_band')
                                <div style="{{ $card }} background:linear-gradient(90deg,{{ $indigo }},#0ea5e9);padding:12px;display:flex;justify-content:space-around;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="height:10px;width:22%;background:rgba(255,255,255,.85);border-radius:3px;"></div>
                                    @endfor
                                </div>
                                @break

                            {{-- NEW: table-style rows with a header --}}
                            @case('pricing_table:table')
                            @case('downloads:table')
                                <div style="{{ $card }} border:1px solid {{ $muted }};">
                                    <div style="background:#f3f4f6;padding:5px 8px;display:flex;justify-content:space-between;">
                                        <div style="height:5px;width:30%;background:{{ $mutedDark }};border-radius:3px;"></div>
                                        <div style="height:5px;width:15%;background:{{ $mutedDark }};border-radius:3px;"></div>
                                    </div>
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="padding:5px 8px;display:flex;justify-content:space-between;border-top:1px solid {{ $muted }};">
                                            <div style="height:5px;width:50%;background:{{ $muted }};border-radius:3px;"></div>
                                            <div style="height:5px;width:15%;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- NEW: zig-zag alternating rows --}}
                            @case('feature_list:alternating')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div style="display:flex;{{ $i % 2 === 1 ? 'flex-direction:row-reverse;' : '' }}align-items:center;gap:6px;">
                                            <div style="width:20px;height:20px;border-radius:999px;background:{{ $mutedDark }};flex-shrink:0;"></div>
                                            <div style="height:5px;flex:1;background:{{ $muted }};border-radius:3px;"></div>
                                        </div>
                                    @endfor
                                </div>
                                @break

                            {{-- NEW: countdown digit boxes --}}
                            @case('countdown:standard')
                            @case('countdown:minimal')
                            @case('countdown:dark')
                                <div style="display:flex;justify-content:center;gap:5px;">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div style="{{ $card }} background:{{ $variantKey === 'dark' ? '#111827' : $indigo }};color:#fff;font-size:11px;font-weight:700;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">{{ str_pad((string) (9 - $i * 2), 2, '0', STR_PAD_LEFT) }}</div>
                                    @endfor
                                </div>
                                @break

                            @default
                                <div style="color:#9ca3af;font-size:11px;text-align:center;">{{ $variantLabel }}</div>
                        @endswitch
                        @endif
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
