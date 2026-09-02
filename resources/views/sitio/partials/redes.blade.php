@php
    $redes = $sitio->redesPublicas();
@endphp
@if($redes->isNotEmpty())
    <div class="sw-redes">
        @foreach($redes as $red)
            <a class="sw-red sw-red-{{ $red['key'] }}" href="{{ $red['url'] }}" target="_blank" rel="noopener noreferrer">
                <b aria-hidden="true">{{ ['instagram' => '◎', 'facebook' => 'f', 'tiktok' => '♪', 'youtube' => '▶', 'linkedin' => 'in', 'whatsapp' => '◉', 'web' => '↗'][$red['key']] ?? '•' }}</b>
                {{ $red['label'] }}
            </a>
        @endforeach
    </div>
@endif
