{{--
    Card de ranking genérico.
    Parâmetros: $titulo, $icone (bootstrap-icons), $itens, $unidade, $descricao (opcional),
                $filtroAtivo (mostra o seletor Todos/Em atividade), $padraoAtivo (começa em "Em atividade"),
                $sufixo (ex.: '%'), $limite (itens visíveis antes do "ver mais"), $largo (ocupa 2 colunas)
--}}
@php
    $limite = $limite ?? 8;
    $sufixo = $sufixo ?? '';
    $filtroAtivo = $filtroAtivo ?? false;
    $padraoAtivo = $padraoAtivo ?? false;
    $maximo = collect($itens)->max('valor') ?: 1;
    $formatar = fn ($v) => (is_float($v) && floor($v) != $v) ? number_format($v, 1, ',', '.') : number_format($v, 0, ',', '.');
@endphp
<article class="cur-card {{ !empty($largo) ? 'cur-largo' : '' }}" data-so-ativos="{{ $filtroAtivo && $padraoAtivo ? '1' : '0' }}">
    <header class="cur-card-header">
        <div>
            <h3><i class="bi {{ $icone }}"></i> {{ $titulo }}</h3>
            @if(!empty($descricao))<p>{{ $descricao }}</p>@endif
        </div>
        @if($filtroAtivo)
            <div class="cur-chips" role="group" aria-label="Filtrar">
                <button type="button" data-filtro="0" class="{{ $padraoAtivo ? '' : 'ativo' }}">Todos</button>
                <button type="button" data-filtro="1" class="{{ $padraoAtivo ? 'ativo' : '' }}">Em atividade</button>
            </div>
        @endif
    </header>

    @if(count($itens) === 0)
        <p class="cur-vazio">Nenhum registro encontrado.</p>
    @else
        <ol class="cur-list" data-cur-lista data-limite="{{ $limite }}">
            @foreach($itens as $i => $item)
                @php $q = $item['quem']; @endphp
                <li class="cur-item {{ $i >= $limite ? 'oculto' : '' }}" data-item
                    data-ativo="{{ !empty($q['ativo']) ? '1' : '0' }}"
                    data-piloto="{{ ($q['tipo'] ?? '') === 'piloto' ? $q['id'] : '' }}">
                    <span class="cur-pos"></span>
                    @include('site.curiosidades._avatar', ['q' => $q])
                    <div class="cur-info">
                        <div class="cur-nome-linha">
                            @if(!empty($q['bandeira']))<img class="cur-bandeira" src="{{ $q['bandeira'] }}" alt="{{ $q['pais'] }}" title="{{ $q['pais'] }}" loading="lazy">@endif
                            <a href="{{ $q['url'] }}" class="cur-nome">{{ $q['nome'] }}</a>
                            @if(!empty($item['tag']))<span class="cur-tag">{{ $item['tag'] }}</span>@endif
                            @if(empty($q['ativo']) && $filtroAtivo)<span class="cur-tag cur-tag-cinza">inativo</span>@endif
                        </div>
                        @if(!empty($item['detalhe']))
                            <small class="cur-detalhe">
                                @if(!empty($item['link']))<a href="{{ $item['link'] }}">{{ $item['detalhe'] }}</a>@else{{ $item['detalhe'] }}@endif
                            </small>
                        @endif
                        @if(!empty($item['extra']))<small class="cur-detalhe cur-extra">{{ $item['extra'] }}</small>@endif
                        @if(!empty($item['logos']))
                            <div class="cur-logos">
                                @foreach($item['logos'] as $logo)
                                    <a href="{{ $logo['url'] }}" title="{{ $logo['nome'] }}{{ isset($logo['n']) ? ' · '.$logo['n'].' vitória(s)' : '' }}">
                                        @include('site.curiosidades._avatar', ['q' => $logo, 'classe' => 'mini'])
                                        @isset($logo['n'])<b>{{ $logo['n'] }}</b>@endisset
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <span class="cur-barra" aria-hidden="true"><span style="width: {{ max(3, round($item['valor'] / $maximo * 100)) }}%"></span></span>
                    </div>
                    <div class="cur-valor">
                        <strong>{{ $formatar($item['valor']) }}{{ $item['sufixo'] ?? $sufixo }}</strong>
                        @if(!empty($unidade))<small>{{ $unidade }}</small>@endif
                    </div>
                </li>
            @endforeach
        </ol>
        <p class="cur-vazio" hidden>Nenhum registro com esse filtro.</p>
        @if(count($itens) > $limite)
            <button type="button" class="cur-mais">Ver mais</button>
        @endif
    @endif
</article>
