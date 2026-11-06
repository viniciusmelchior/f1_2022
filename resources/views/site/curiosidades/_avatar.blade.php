{{-- Foto do piloto (redonda) ou logo da equipe sobre a cor dela. Espera $q (piloto/equipe vindo do CuriosidadesService) --}}
@if(($q['tipo'] ?? '') === 'equipe')
    <span class="cur-logo {{ $classe ?? '' }}" style="background: {{ $q['cor'] }}" title="{{ $q['nome'] }}">
        <img src="{{ $q['img'] }}" alt="{{ $q['nome'] }}" loading="lazy" decoding="async">
    </span>
@else
    <img class="cur-foto {{ $classe ?? '' }}" src="{{ $q['img'] }}" alt="{{ $q['nome'] }}" title="{{ $q['nome'] }}" loading="lazy" decoding="async">
@endif
