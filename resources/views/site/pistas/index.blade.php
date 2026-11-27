{{-- MELHORIA 20261117: grid de pistas no visual dos resumos (hero + KPIs + tabela ordenável), sem a coluna "Tipo",
     com filtro por ano de referência ("já recebeu" / "nunca recebeu"). Versão anterior: index.blade.php_20261117 --}}
@extends('layouts.main')

@section('section')
@php
    $totalCorridas = collect($estatisticas)->sum('corridas');
    $totalSprints = collect($estatisticas)->sum('sprints');
    $usadas = $pistas->filter(fn ($p) => isset($estatisticas[$p->id]))->count();
@endphp

@include('site.partials.resumo-estilos')
<style>
    .pis-filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 14px 16px; margin-bottom: 14px; }
    .pis-filtro { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .pis-filtro label { font-size: .68rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-3); margin: 0; }
    .pis-filtro input, .pis-filtro select { height: 36px; border: 1px solid #dcdcdc; border-radius: 8px; padding: 0 10px; font-size: .9rem; background: #fff; color: var(--ink-1); }
    .pis-filtro input { width: 260px; max-width: 100%; }
    .pis-filtro input:focus, .pis-filtro select:focus { outline: none; border-color: var(--f1-red); box-shadow: 0 0 0 3px rgba(225,6,0,.12); }
    .pis-contador { margin-left: auto; font-size: .82rem; color: var(--ink-2); font-weight: 700; }
    .pis-resposta { display: none; width: 100%; padding: 10px 12px; border-radius: 8px; font-size: .88rem; background: #f6f7f9; border-left: 4px solid var(--f1-dark); }
    .pis-resposta.on { display: block; }
    #tabelaPistas td { font-size: .88rem; }
    #tabelaPistas .pis-nome { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; }
    #tabelaPistas .pis-nome a { color: var(--ink-1); }
    #tabelaPistas .pis-nome a:hover { color: var(--f1-red); }
    .pis-tag { display: inline-block; font-size: .62rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; }
    .pis-tag.inativa { background: #eef0f3; color: #6c757d; }
    .pis-tag.inedita { background: #e0f2fe; color: #075985; }
    .pis-tag.sim { background: #dcfce7; color: #166534; }
    .pis-tag.nao { background: #fde2e1; color: #b42318; }
    .pis-num-zero { color: #c9c9c9; }
    .res-tabela th.ordenavel.asc::after { content: ' ▲'; opacity: .9; }
    .res-tabela th.ordenavel.desc::after { content: ' ▼'; opacity: .9; }
    .pis-ref-cel { cursor: help; }
    .pis-rodape { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
    @media (max-width: 700px) { .pis-filtro, .pis-filtro input, .pis-filtro select { width: 100%; } .pis-contador { margin-left: 0; } }
</style>

<div class="res-page">
    @if (session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listagem de Pistas</li>
        </ol>
    </nav>

    <header class="res-hero">
        <div>
            <small class="res-kicker">Calendário · circuitos</small>
            <h1>Pistas</h1>
            <div style="color: #c3c2b7; font-size: .9rem;">Histórico de uso de cada pista e anos de referência já disputados.</div>
        </div>
        <div class="res-hero-acoes">
            <div class="res-links">
                <a href="{{ route('pistas.create') }}"><i class="bi bi-plus-lg"></i> Adicionar pista</a>
                <a href="#" class="adicionar_autor"><i class="bi bi-person-plus"></i> Adicionar autor</a>
                <a href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </header>

    <div class="res-kpis">
        <div class="res-kpi"><span><i class="bi bi-geo-alt-fill"></i> Pistas</span><strong>{{ $pistas->count() }}</strong><small>{{ $pistas->where('flg_ativo', 'S')->count() }} ativas</small></div>
        <div class="res-kpi"><span><i class="bi bi-check2-circle"></i> Já utilizadas</span><strong>{{ $usadas }}</strong><small>{{ $pistas->count() - $usadas }} inéditas</small></div>
        <div class="res-kpi"><span><i class="bi bi-flag-fill"></i> Corridas</span><strong>{{ $totalCorridas }}</strong><small>corridas principais</small></div>
        <div class="res-kpi"><span><i class="bi bi-lightning-charge-fill"></i> Sprints</span><strong>{{ $totalSprints }}</strong><small>contadas à parte</small></div>
        <div class="res-kpi"><span><i class="bi bi-globe2"></i> Países</span><strong>{{ $pistas->pluck('pais_id')->filter()->unique()->count() }}</strong><small>{{ $pistas->map(fn ($p) => optional(optional($p->pais)->continente)->nome)->filter()->unique()->count() }} continentes</small></div>
    </div>

    <div class="res-card pis-filtros">
        <div class="pis-filtro">
            <label for="caixaBusca">Pesquisar</label>
            <input type="text" id="caixaBusca" placeholder="Nome, país, continente..." autocomplete="off">
        </div>
        <div class="pis-filtro">
            {{-- ids na lista de exceções do select2 global (layouts/main) --}}
            <label for="filtroRefPista">Ano de referência</label>
            <select id="filtroRefPista">
                <option value="">Todos</option>
                @foreach($referencias as $ref)
                    <option value="{{ $ref }}">{{ $ref }}</option>
                @endforeach
            </select>
        </div>
        <div class="pis-filtro">
            <label for="filtroSituacaoPista">Situação</label>
            <select id="filtroSituacaoPista">
                <option value="">Todas as pistas</option>
                <option value="sim">Já recebeu (o ano selecionado)</option>
                <option value="nao">Nunca recebeu (o ano selecionado)</option>
                <option value="inedita">Nunca utilizadas</option>
                <option value="ativa">Somente ativas</option>
            </select>
        </div>
        <span class="pis-contador" id="contadorPistas"></span>
        <div class="pis-resposta" id="respostaRef"></div>
    </div>

    @if(count($pistas) > 0)
    <div class="res-card">
        <div class="res-tabela-wrap">
            <table class="res-tabela" id="tabelaPistas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="esq ordenavel" data-tipo="texto">Nome</th>
                        <th class="esq ordenavel" data-tipo="texto">País</th>
                        <th class="esq ordenavel" data-tipo="texto">Continente</th>
                        <th class="esq ordenavel" data-tipo="num" title="Ordena pela temporada mais recente">Última corrida</th>
                        <th class="ordenavel" data-tipo="num">Qtd Carros</th>
                        <th class="ordenavel" data-tipo="num">Corridas</th>
                        <th class="ordenavel" data-tipo="num">Sprints</th>
                        <th class="ordenavel" data-tipo="num" title="Quantidade de anos de referência diferentes já disputados na pista">Anos ref.</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pistas as $key => $pista)
                        @php
                            $e = $estatisticas[$pista->id] ?? null;
                            $refs = $e['referencias'] ?? [];
                            $ultima = $e['ultima'] ?? null;
                        @endphp
                        <tr data-refs="|{{ implode('|', $refs) }}|" data-usada="{{ $e ? 1 : 0 }}" data-ativa="{{ $pista->flg_ativo === 'S' ? 1 : 0 }}">
                            <td>{{ $key + 1 }}</td>
                            <td class="esq" data-valor="{{ $pista->nome }}">
                                <span class="pis-nome">
                                    @if(optional($pista->pais)->imagem)
                                        <img class="res-bandeira" src="{{ asset('images/'.$pista->pais->imagem) }}" alt="">
                                    @endif
                                    <a href="{{ route('pistas.historico', [$pista->id]) }}">{{ $pista->nome }}</a>
                                    @if($pista->flg_ativo !== 'S')<span class="pis-tag inativa">inativa</span>@endif
                                    @if(!$e)<span class="pis-tag inedita">inédita</span>@endif
                                    <span class="pis-tag pis-tag-ref" hidden></span>
                                </span>
                            </td>
                            <td class="esq">{{ optional($pista->pais)->des_nome ?? '-' }}</td>
                            <td class="esq">{{ optional(optional($pista->pais)->continente)->nome ?? '-' }}</td>
                            <td class="esq" data-valor="{{ $ultima['ordem'] ?? 0 }}">
                                @if($ultima)
                                    {{ $ultima['temporada'] }}@if($ultima['referencia']) <span class="res-muted">· {{ $ultima['referencia'] }}</span>@endif
                                @else
                                    <span class="res-muted">-</span>
                                @endif
                            </td>
                            <td data-valor="{{ $pista->qtd_carros ?? 30 }}">{{ $pista->qtd_carros ?? '30' }}</td>
                            <td data-valor="{{ $e['corridas'] ?? 0 }}" class="{{ ($e['corridas'] ?? 0) ? 'forte' : 'pis-num-zero' }}">{{ ($e['corridas'] ?? 0) ?: '-' }}</td>
                            <td data-valor="{{ $e['sprints'] ?? 0 }}" class="{{ ($e['sprints'] ?? 0) ? '' : 'pis-num-zero' }}">{{ ($e['sprints'] ?? 0) ?: '-' }}</td>
                            <td data-valor="{{ count($refs) }}" class="pis-ref-cel {{ count($refs) ? '' : 'pis-num-zero' }}" title="{{ $refs ? implode(', ', $refs) : 'Nenhum ano de referência' }}">{{ count($refs) ?: '-' }}</td>
                            <td class="text-nowrap">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-dark" data-toggle="tooltip" data-placement="top" title="Histórico e estatísticas da pista" href="{{ route('pistas.historico', [$pista->id]) }}"><i class="bi bi-newspaper"></i></a>
                                    <a class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" data-placement="top" title="Visualizar resultados na pista" href="{{ route('pistas.show', [$pista->id]) }}"><i class="bi bi-eye"></i></a>
                                    <a class="btn btn-sm btn-outline-primary" data-toggle="tooltip" data-placement="top" title="Editar pista" href="{{ route('pistas.edit', [$pista->id]) }}"><i class="bi bi-pencil"></i></a>
                                    <a class="btn btn-sm btn-outline-danger" data-toggle="tooltip" data-placement="top" title="Excluir pista" href="{{ route('pistas.delete', [$pista->id]) }}" onclick="return confirm('Excluir a pista {{ addslashes($pista->nome) }}?');"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="res-card res-vazio">Nenhuma pista cadastrada</div>
    @endif

    <div class="pis-rodape">
        <a href="{{ route('pistas.create') }}" class="btn btn-dark">Adicionar pista</a>
        <a href="{{ route('dashboard') }}" class="btn btn-danger">Voltar</a>
    </div>
</div>

<!-- Modal adicionar autor -->
<form id="deleteForm" method="get" action="{{ route('pistas.adicionarAutor') }}">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Adicionar Autor</h5>
                </div>
                <div class="modal-body">
                    <input type="text" name="autor" id="autor" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    const tabela = document.getElementById('tabelaPistas');
    if (!tabela) return;
    const tbody = tabela.tBodies[0];
    const linhas = Array.from(tbody.rows);
    const busca = document.getElementById('caixaBusca');
    const selRef = document.getElementById('filtroRefPista');
    const selSituacao = document.getElementById('filtroSituacaoPista');
    const contador = document.getElementById('contadorPistas');
    const resposta = document.getElementById('respostaRef');
    const norm = t => (t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase();

    function filtrar() {
        const termo = norm(busca.value.trim());
        const ref = selRef.value;
        const situacao = selSituacao.value;
        let visiveis = 0, recebeu = 0;
        linhas.forEach(tr => {
            const temRef = ref !== '' && tr.dataset.refs.includes('|' + ref + '|');
            if (temRef) recebeu++;
            let ok = !termo || norm(tr.textContent).includes(termo);
            if (ok && situacao === 'sim') ok = ref === '' ? tr.dataset.usada === '1' : temRef;
            if (ok && situacao === 'nao') ok = ref === '' ? tr.dataset.usada === '0' : !temRef;
            if (ok && situacao === 'inedita') ok = tr.dataset.usada === '0';
            if (ok && situacao === 'ativa') ok = tr.dataset.ativa === '1';
            tr.style.display = ok ? '' : 'none';
            if (ok) visiveis++;

            // selo "recebeu 2023 / nunca recebeu 2023" ao lado do nome
            const selo = tr.querySelector('.pis-tag-ref');
            selo.hidden = ref === '';
            selo.className = 'pis-tag pis-tag-ref ' + (temRef ? 'sim' : 'nao');
            selo.textContent = temRef ? 'recebeu ' + ref : 'nunca ' + ref;
        });
        contador.textContent = visiveis + ' de ' + linhas.length + ' pistas';
        resposta.classList.toggle('on', ref !== '');
        if (ref !== '') {
            resposta.innerHTML = '<b>Referência ' + ref + ':</b> ' + recebeu + ' pista(s) já receberam corridas desse ano, '
                + (linhas.length - recebeu) + ' nunca receberam.';
        }
    }

    // ordenação por coluna (usa data-valor quando existir, senão o texto da célula)
    tabela.querySelectorAll('th.ordenavel').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.cellIndex;
            const num = th.dataset.tipo === 'num';
            const asc = !th.classList.contains('asc');
            tabela.querySelectorAll('th.ordenavel').forEach(o => o.classList.remove('asc', 'desc'));
            th.classList.add(asc ? 'asc' : 'desc');
            const valor = tr => {
                const td = tr.cells[col];
                const v = td.dataset.valor !== undefined ? td.dataset.valor : td.textContent.trim();
                return num ? (parseFloat(v) || 0) : norm(v === '-' ? '' : v);
            };
            linhas.sort((a, b) => {
                const va = valor(a), vb = valor(b);
                const c = num ? va - vb : va.localeCompare(vb, 'pt-BR');
                return asc ? c : -c;
            });
            linhas.forEach((tr, i) => { tr.cells[0].textContent = i + 1; tbody.appendChild(tr); });
        });
    });

    busca.addEventListener('input', filtrar);
    selRef.addEventListener('change', filtrar);
    selSituacao.addEventListener('change', filtrar);
    filtrar();

    $(document).ready(function () {
        $('.adicionar_autor').click(function (e) {
            e.preventDefault();
            $('#exampleModal').modal('show');
        });
    });
})();
</script>
@endsection
