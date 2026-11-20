{{--
    20261120 - Card de ranking de lugares (pistas/países), mesmo visual do _ranking.
    Parâmetros: $titulo, $icone, $itens (nome, bandeira, url?, valor, de?, detalhe, lider?), $unidade, $descricao (opcional), $limite
--}}
@php
    $limite = $limite ?? 8;
    $maximo = collect($itens)->max('valor') ?: 1;
@endphp
<article class="cur-card">
    <header class="cur-card-header"><div><h3><i class="bi {{ $icone }}"></i> {{ $titulo }}</h3>@if(!empty($descricao))<p>{{ $descricao }}</p>@endif</div></header>
    @if(count($itens) === 0)
        <p class="cur-vazio">Nenhum registro encontrado.</p>
    @else
        <ol class="cur-list" data-cur-lista data-limite="{{ $limite }}">
            @foreach($itens as $item)
                <li class="cur-item {{ $loop->index >= $limite ? 'oculto' : '' }}" data-item data-ativo="1" data-piloto="">
                    <span class="cur-pos"></span>
                    <span class="cur-bandeira-box">@if($item['bandeira'])<img src="{{ $item['bandeira'] }}" alt="" loading="lazy">@else<i class="bi bi-flag-fill"></i>@endif</span>
                    <div class="cur-info">
                        <div class="cur-nome-linha">
                            @if(!empty($item['url']))<a href="{{ $item['url'] }}" class="cur-nome">{{ $item['nome'] }}</a>@else<span class="cur-nome">{{ $item['nome'] }}</span>@endif
                        </div>
                        @if(!empty($item['detalhe']))<small class="cur-detalhe">{{ $item['detalhe'] }}</small>@endif
                        @if(!empty($item['lideres']))
                            <small class="cur-detalhe cur-extra">
                                <span data-cur-primeiro>
                                    @foreach($item['lideres'] as $l)
                                        <a class="cur-equipe-mini {{ $loop->first ? '' : 'oculto' }}" data-item data-piloto="{{ $l['id'] }}" href="{{ $l['url'] }}">@include('site.curiosidades._avatar', ['q' => $l, 'classe' => 'mini']) Quem mais venceu: {{ $l['nome'] }} ({{ $l['vitorias'] }})</a>
                                    @endforeach
                                </span>
                            </small>
                        @endif
                        <span class="cur-barra" aria-hidden="true"><span style="width: {{ max(3, round($item['valor'] / $maximo * 100)) }}%"></span></span>
                    </div>
                    <div class="cur-valor">
                        <strong>{{ $item['valor'] }}@isset($item['de'])<span class="cur-de">/{{ $item['de'] }}</span>@endisset</strong>
                        <small>{{ $unidade }}</small>
                    </div>
                </li>
            @endforeach
        </ol>
        <p class="cur-vazio" hidden>Nenhum registro.</p>
        @if(count($itens) > $limite)<button type="button" class="cur-mais">Ver mais</button>@endif
    @endif
</article>
