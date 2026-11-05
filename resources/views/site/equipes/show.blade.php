@php
 use App\Models\Site\Equipe;
 // MELHORIA 20261105: tela refeita no padrão de pilotos/show. Versão anterior: show.blade.php_20261105
 $corFundoLogo = Equipe::corFundoIcone($modelEquipe->des_cor, $modelEquipe->imagem);
 $corEquipe = preg_match('/^#[0-9a-f]{6}$/i', (string) $modelEquipe->des_cor) ? $modelEquipe->des_cor : '#e10600';
@endphp
@extends('layouts.main')

@section('section')
@include('site.partials.perfil-f1-estilos')
<style>
    /* específicos da equipe */
    .equipe-res { display: flex; flex-direction: column; gap: 4px; }
    .equipe-res-linha { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .equipe-res-linha .nome { min-width: 140px; }
    .equipe-res-linha small { color: #888; }
    .profile-header { border-bottom-color: {{ $corEquipe }}; }
</style>

<div class="container main-content-padding">

    <!-- CARD DE PERFIL -->
    <div class="driver-profile-card" id="topo-perfil">
        <div class="profile-header">
            <div class="profile-title">
                <h2>{{ $modelEquipe->nome }}</h2>
                <span class="badge-f1 badge-country">
                    @if(isset($modelEquipe->pais->imagem) && $modelEquipe->pais->imagem != '')
                        <img src="{{ asset('images/' . $modelEquipe->pais->imagem) }}" class="flag-img" alt="{{ $modelEquipe->pais->des_nome }}">
                    @else
                        <i class="bi bi-globe"></i>
                    @endif
                    {{ $modelEquipe->pais->des_nome ?? '-' }}
                </span>
                @if($modelEquipe->flg_ativo == 'S')
                    <span class="badge-f1 badge-active"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Em Atividade</span>
                @else
                    <span class="badge-f1 badge-retired"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Inativa</span>
                @endif
            </div>

            <div class="season-selector">
                <select id="filtroTemporadaEquipe" class="f1-select">
                    <option value="">Todas as Temporadas (Geral)</option>
                    @foreach($dadosTemporadas->reverse() as $temporada)
                        <option value="{{ $temporada['id'] }}">{{ $temporada['completo'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="profile-body">
            <div class="profile-image-container">
                <div class="team-logo-box" style="background-color: {{ $corFundoLogo }};">
                    <img src="{{ \App\Support\ImageUrl::for($modelEquipe->imagem) }}" alt="Logo da equipe">
                </div>
                <button id="show-other-stats">Exibir Mais Estatísticas</button>
            </div>

            <div class="profile-stats-grid" id="equipeStats"></div>
        </div>
    </div>

    <div class="perfil-toolbar">
        <div class="filtro-ativo"><i class="bi bi-funnel"></i> Exibindo: <strong id="filtroAtivoTexto">Todas as temporadas</strong></div>
        <div class="d-flex gap-2">
            <button type="button" id="expandirTudo"><i class="bi bi-arrows-expand"></i> Expandir tudo</button>
            <button type="button" id="recolherTudo"><i class="bi bi-arrows-collapse"></i> Recolher tudo</button>
        </div>
    </div>

    <!-- ACORDEÕES -->
    <details class="f1-accordion" id="pilotos-equipe">
        <summary><span>Pilotos da equipe <span class="secao-contador" data-contador="pilotos">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Piloto <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Temporadas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Corridas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Vitórias <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Poles <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pódios <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Top 10 <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pontos <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Títulos <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody id="tbPilotos"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="campeonatos-equipe">
        <summary><span>Histórico nos campeonatos <span class="secao-contador" data-contador="temporadas">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="grafico-pontos"><canvas id="graficoPontosEquipe"></canvas></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pilotos</th>
                            <th>Posição Final <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pontos <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Vitórias <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Poles <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pódios <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbCampeonatos"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="corridas-equipe">
        <summary><span>Corridas por temporada <span class="secao-contador" data-contador="corridas">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="sprint-subtitulo">Grandes Prêmios <small class="text-muted text-lowercase fw-normal">(clique na temporada para ver as corridas)</small></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr><th>Temporada</th><th>Corridas</th><th>Vitórias</th><th>Poles</th><th>Pódios</th><th>Dobradinhas</th><th>Pontos</th></tr>
                    </thead>
                    <tbody id="tbCorridas" data-tipo="gp"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="sprints-equipe">
        <summary><span>Sprints <span class="sprint-contador" data-contador="sprints">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="sprint-painel">
                <div class="profile-stats-grid" id="sprintStatsEquipe"></div>
            </div>
            <div class="sprint-subtitulo">Por temporada <small class="text-muted text-lowercase fw-normal">(clique na temporada para ver as sprints)</small></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr><th>Temporada</th><th>Sprints</th><th>Vitórias</th><th>Poles</th><th>Pódios</th><th>Dobradinhas</th><th>Pontos</th></tr>
                    </thead>
                    <tbody id="tbSprints" data-tipo="sprint"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="vitorias-equipe">
        <summary><span>Histórico de Vitórias <span class="secao-contador" data-contador="vitorias">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="alerta-sequencia"><i class="bi bi-info-circle-fill me-2 text-danger"></i> Corridas seguidas sem vitória atualmente: <strong id="seqSemVitoria">0</strong></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Etapa <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Evento / Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Piloto <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Largada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbVitorias"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="poles-equipe">
        <summary><span>Histórico de Pole Positions <span class="secao-contador" data-contador="poles">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="alerta-sequencia"><i class="bi bi-stopwatch-fill me-2 text-danger"></i> Corridas seguidas sem pole position atualmente: <strong id="seqSemPole">0</strong></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Etapa <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Evento / Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Piloto <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Chegada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbPoles"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="dobradinhas-equipe">
        <summary><span>Dobradinhas <span class="secao-contador" data-contador="dobradinhas">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Temporada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Etapa <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Evento / Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>1º <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>2º <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbDobradinhas"></tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="f1-accordion" id="pistas-equipe">
        <summary><span>Desempenho por pista <span class="secao-contador" data-contador="pistas">0</span></span> <i class="bi bi-chevron-down toggle-icon"></i></summary>
        <div class="accordion-body">
            <div class="alerta-sequencia" id="resumoPistas"></div>
            <div class="table-responsive">
                <table class="f1-table">
                    <thead>
                        <tr>
                            <th>Pista <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Corridas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Vitórias <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Poles <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pódios <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Dobradinhas <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Melhor Chegada <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            <th>Pontos <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody id="tbPistas"></tbody>
                </table>
            </div>
        </div>
    </details>

    <div class="d-flex justify-content-center gap-4 mb-5">
        <a href="{{ route('equipes.index') }}" class="btn btn-outline-dark fw-bold px-4 py-2 text-uppercase"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</div>

<div class="footer-show-pilotos shadow">
    <a href="#topo-perfil"><i class="bi bi-house-door"></i> Início</a>
    <a href="#pilotos-equipe"><i class="bi bi-people"></i> Pilotos</a>
    <a href="#campeonatos-equipe"><i class="bi bi-list-ol"></i> Campeonatos</a>
    <a href="#corridas-equipe"><i class="bi bi-flag"></i> Corridas</a>
    <a href="#sprints-equipe"><i class="bi bi-lightning-charge"></i> Sprints</a>
    <a href="#vitorias-equipe"><i class="bi bi-trophy"></i> Vitórias</a>
    <a href="#poles-equipe"><i class="bi bi-stopwatch"></i> Poles</a>
    <a href="#dobradinhas-equipe"><i class="bi bi-award"></i> Dobradinhas</a>
    <a href="#pistas-equipe"><i class="bi bi-geo-alt"></i> Pistas</a>
</div>

<script>window.DADOS_EQUIPE = @json($dadosEquipe); window.COR_EQUIPE = @json($corEquipe);</script>
@include('site.partials.perfil-f1-ordenacao')

<script>
(function () {
    const D = window.DADOS_EQUIPE;
    // o layout carrega um segundo jQuery no fim da página; guarda o atual (o mesmo do select2)
    const $j = window.jQuery;
    const esc = t => String(t === null || t === undefined ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const num = v => Number.isInteger(v) ? v : String(Math.round(v * 10) / 10).replace('.', ',');
    const media = v => v.length ? (v.reduce((a, b) => a + b, 0) / v.length).toFixed(1).replace('.', ',') : '-';

    // ---------- dados normalizados ----------
    const temporadas = D.temporadas;                                   // ordem cronológica
    const temporadaPorId = {}; temporadas.forEach((t, i) => { t.indice = i; temporadaPorId[t.id] = t; });
    const corridas = {};
    Object.keys(D.corridas).forEach(id => {
        const c = D.corridas[id];
        corridas[id] = { id: Number(id), temporada: c[0], ordem: c[1], sprint: c[2] === 1, pista: c[3], evento: c[4] };
    });
    const resultados = D.resultados.map(r => ({
        corrida: corridas[r[0]], piloto: r[1], largada: r[2], chegada: r[3], pontos: r[4], abandono: r[5] === 1, vr: r[6] === 1,
    }));
    const titulosConstrutores = D.titulosConstrutores;
    const titulosPilotos = D.titulosPilotos.map(t => ({ temporada: t[0], piloto: t[1] }));

    // ---------- helpers de exibição ----------
    const piloto = id => D.pilotos[id] || { nome: 'Piloto #' + id, img: null, bandeira: null, link: '#' };
    const pista = id => D.pistas[id] || { nome: '-', bandeira: null };
    const linkCorrida = id => D.linkCorrida.replace('__ID__', id);
    const nomeTemporada = id => temporadaPorId[id] ? temporadaPorId[id].nome : '-';

    function htmlPiloto(id, comFoto) {
        const p = piloto(id);
        return (comFoto ? '<img src="' + esc(p.img || '') + '" class="mini-avatar" alt="">' : '') +
            '<a class="link-perfil" href="' + esc(p.link) + '" title="Ver perfil do piloto">' + esc(p.nome) + '</a>';
    }
    function htmlLocal(c) {
        const p = pista(c.pista), ev = c.evento ? D.eventos[c.evento] : null;
        const bandeira = p.bandeira ? '<img src="' + esc(p.bandeira) + '" class="flag-img" alt=""> ' : '';
        return bandeira + (ev ? '<span class="fw-bold">' + esc(ev) + '</span><br><small class="text-muted">' + esc(p.nome) + '</small>' : esc(p.nome));
    }
    function classePos(p, pontos) {
        if (p === 1) return 'p1'; if (p === 2) return 'p2'; if (p === 3) return 'p3';
        return pontos > 0 ? 'pontua' : '';
    }
    const vazio = (colunas, texto) => '<tr><td colspan="' + colunas + '" class="sprint-vazio">' + texto + '</td></tr>';
    const acao = id => '<a title="Visualizar corrida" href="' + esc(linkCorrida(id)) + '"><i class="bi bi-play-circle-fill"></i></a>';

    // corrida -> resultados da equipe (para dobradinhas e listagens por corrida)
    function agruparPorCorrida(lista) {
        const mapa = new Map();
        lista.forEach(r => { if (!mapa.has(r.corrida.id)) mapa.set(r.corrida.id, []); mapa.get(r.corrida.id).push(r); });
        return mapa;
    }
    const ehDobradinha = rs => rs.some(r => r.chegada === 1) && rs.some(r => r.chegada === 2);

    // ---------- resumo (mesmas regras da tela anterior: estatísticas de GP; pontos incluem sprints) ----------
    function resumo(lista) {
        const gp = lista.filter(r => !r.corrida.sprint);
        const largadas = gp.filter(r => r.largada > 0).map(r => r.largada);
        const chegadas = gp.filter(r => r.chegada > 0).map(r => r.chegada);
        const porCorrida = agruparPorCorrida(gp);
        let dobradinhas = 0; porCorrida.forEach(rs => { if (ehDobradinha(rs)) dobradinhas++; });
        return {
            largadas: gp.length,
            corridas: porCorrida.size,
            vitorias: gp.filter(r => r.chegada === 1).length,
            poles: gp.filter(r => r.largada === 1).length,
            podios: gp.filter(r => r.chegada >= 1 && r.chegada <= 3).length,
            topTen: gp.filter(r => r.chegada >= 1 && r.chegada <= 10).length,
            abandonos: gp.filter(r => r.abandono).length,
            voltasRapidas: gp.filter(r => r.vr).length,
            pontos: lista.reduce((a, r) => a + r.pontos, 0),
            dobradinhas: dobradinhas,
            melhorLargada: largadas.length ? Math.min(...largadas) + 'º' : '-',
            piorLargada: largadas.length ? Math.max(...largadas) + 'º' : '-',
            melhorChegada: chegadas.length ? Math.min(...chegadas) + 'º' : '-',
            piorChegada: chegadas.length ? Math.max(...chegadas) + 'º' : '-',
            // tela anterior: soma / total de largadas, arredondado
            gridMedio: gp.length ? Math.round(gp.reduce((a, r) => a + (r.largada || 0), 0) / gp.length) : 0,
            mediaChegada: gp.length ? Math.round(gp.reduce((a, r) => a + (r.chegada || 0), 0) / gp.length) : 0,
            pilotos: new Set(lista.map(r => r.piloto)).size,
        };
    }

    // ---------- card principal ----------
    function renderCard(lista, temporadaId) {
        const r = resumo(lista);
        const filtraTitulo = t => !temporadaId || String(t) === String(temporadaId);
        const tc = titulosConstrutores.filter(filtraTitulo).length;
        const tp = titulosPilotos.filter(t => filtraTitulo(t.temporada)).length;
        const sprints = new Set(lista.filter(x => x.corrida.sprint).map(x => x.corrida.id)).size;
        const principais = [
            ['Corridas', r.corridas, r.largadas + ' largadas'], ['Títulos Construtores', tc, null, tc > 0], ['Títulos de Pilotos', tp, null, tp > 0],
            ['Vitórias', r.vitorias], ['Poles', r.poles], ['Pódios', r.podios], ['Pontos', num(r.pontos)],
            ['Voltas Rápidas', r.voltasRapidas], ['Dobradinhas', r.dobradinhas],
        ];
        const outras = [
            ['Top 10', r.topTen], ['Melhor Largada', r.melhorLargada], ['Pior Largada', r.piorLargada],
            ['Melhor Chegada', r.melhorChegada], ['Pior Chegada', r.piorChegada], ['Abandonos', r.abandonos],
            ['Grid Médio', r.gridMedio], ['Média Chegada', r.mediaChegada], ['Pilotos Utilizados', r.pilotos], ['Sprints', sprints],
        ];
        const caixa = (t, extra) => '<div class="stat-box' + (t[3] ? ' destaque' : '') + extra + '"><span>' + t[0] + '</span><strong>' + esc(t[1]) + '</strong>' + (t[2] ? '<small>' + esc(t[2]) + '</small>' : '') + '</div>';
        const abertas = document.getElementById('show-other-stats').dataset.aberto === '1';
        document.getElementById('equipeStats').innerHTML = principais.map(t => caixa(t, '')).join('') +
            outras.map(t => caixa(t, ' other-stats')).join('');
        if (abertas) document.querySelectorAll('#equipeStats .other-stats').forEach(el => el.style.display = 'block');
    }

    // ---------- pilotos ----------
    function renderPilotos(lista, temporadaId) {
        const mapa = new Map();
        lista.forEach(r => {
            if (!mapa.has(r.piloto)) mapa.set(r.piloto, { id: r.piloto, itens: [], temporadas: new Set() });
            const p = mapa.get(r.piloto); p.itens.push(r); p.temporadas.add(r.corrida.temporada);
        });
        // pilotos inscritos na temporada que ainda não correram
        temporadas.forEach(t => {
            if (temporadaId && String(t.id) !== String(temporadaId)) return;
            t.pilotos.forEach(pid => {
                if (!mapa.has(pid)) mapa.set(pid, { id: pid, itens: [], temporadas: new Set() });
                mapa.get(pid).temporadas.add(t.id);
            });
        });
        const linhas = Array.from(mapa.values()).map(p => {
            const r = resumo(p.itens);
            const titulos = titulosPilotos.filter(t => t.piloto === p.id && (!temporadaId || String(t.temporada) === String(temporadaId))).length;
            return { p, r, titulos };
        }).sort((a, b) => b.r.largadas - a.r.largadas || b.r.pontos - a.r.pontos);
        document.querySelector('[data-contador="pilotos"]').textContent = linhas.length;
        document.getElementById('tbPilotos').innerHTML = linhas.length ? linhas.map(({ p, r, titulos }) => {
            const anos = Array.from(p.temporadas).sort((a, b) => a - b).map(nomeTemporada);
            return '<tr>' +
                '<td data-valor="' + esc(piloto(p.id).nome) + '">' + htmlPiloto(p.id, true) + '</td>' +
                '<td data-valor="' + anos.length + '" title="' + esc(anos.join(', ')) + '"><span class="badge bg-secondary">' + anos.length + '</span> <small class="text-muted">' + esc(anos.length > 4 ? anos.slice(0, 2).join(', ') + ' … ' + anos[anos.length - 1] : anos.join(', ')) + '</small></td>' +
                '<td>' + r.largadas + '</td>' +
                '<td class="text-success fw-bold">' + r.vitorias + '</td>' +
                '<td class="fw-bold">' + r.poles + '</td>' +
                '<td class="text-primary fw-bold">' + r.podios + '</td>' +
                '<td>' + r.topTen + '</td>' +
                '<td class="fw-bold text-danger" data-valor="' + r.pontos + '">' + num(r.pontos) + '</td>' +
                '<td data-valor="' + titulos + '">' + (titulos ? '<span class="tag-titulo"><i class="bi bi-trophy-fill"></i> ' + titulos + '</span>' : '-') + '</td>' +
                '</tr>';
        }).join('') : vazio(9, 'Nenhum piloto nesta seleção.');
    }

    // ---------- campeonatos + gráfico ----------
    let grafico = null;
    function renderCampeonatos(temporadaId) {
        const linhas = temporadas.slice().reverse();
        document.querySelector('[data-contador="temporadas"]').textContent = temporadas.length;
        document.getElementById('tbCampeonatos').innerHTML = linhas.length ? linhas.map(t => {
            const r = resumo(resultados.filter(x => x.corrida.temporada === t.id));
            const campea = titulosConstrutores.some(x => x === t.id);
            const pilotos = t.pilotos.length ? t.pilotos.map(pid => htmlPiloto(pid, false)).join(' / ') : '-';
            const selecionada = temporadaId && String(t.id) === String(temporadaId);
            return '<tr class="' + (selecionada ? 'linha-filtrada' : '') + '">' +
                '<td class="fw-bold" data-valor="' + t.indice + '">' + esc(t.nome) + (t.ano ? ' <small class="text-muted">(' + esc(t.ano) + ')</small>' : '') + (campea ? '<span class="tag-titulo"><i class="bi bi-trophy-fill"></i> Campeã</span>' : '') + '</td>' +
                '<td>' + pilotos + '</td>' +
                '<td data-valor="' + (t.posicao === '-' ? '' : t.posicao) + '"><span class="badge bg-dark rounded-pill px-3 py-2">' + esc(t.posicao) + (t.posicao === '-' ? '' : 'º') + '</span></td>' +
                '<td class="fw-bold fs-5 text-danger">' + esc(t.pontos) + '</td>' +
                '<td class="text-success fw-bold">' + r.vitorias + '</td>' +
                '<td class="fw-bold">' + r.poles + '</td>' +
                '<td class="text-primary fw-bold">' + r.podios + '</td>' +
                '<td class="text-nowrap">' +
                    '<a title="Ver Classificação Completa" href="' + esc(t.classificacao) + '"><i class="bi bi-list-ol"></i></a> ' +
                    '<a href="#" class="filtrar-temporada ms-2" data-temporada="' + t.id + '" title="Filtrar a tela por esta temporada"><i class="bi bi-funnel"></i></a>' +
                '</td></tr>';
        }).join('') : vazio(8, 'Equipe ainda não disputou temporadas.');

        // gráfico de pontos por temporada (inclui sprints, como a tela anterior)
        const canvas = document.getElementById('graficoPontosEquipe');
        if (!window.Chart || !canvas) return;
        const pontos = temporadas.map(t => Math.round(resultados.filter(x => x.corrida.temporada === t.id).reduce((a, x) => a + x.pontos, 0) * 10) / 10);
        const cores = temporadas.map(t => !temporadaId || String(t.id) === String(temporadaId) ? window.COR_EQUIPE : 'rgba(150,150,150,0.4)');
        if (grafico) {
            grafico.data.datasets[0].backgroundColor = cores;
            grafico.update();
            return;
        }
        grafico = new Chart(canvas, {
            type: 'bar',
            data: { labels: temporadas.map(t => t.nome), datasets: [{ label: 'Pontos', data: pontos, backgroundColor: cores, maxBarThickness: 60 }] },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    }

    // ---------- corridas e sprints agrupadas por temporada ----------
    function renderPorTemporada(tbodyId, lista, sprint, temporadaId) {
        const doTipo = lista.filter(r => r.corrida.sprint === sprint);
        const porCorrida = agruparPorCorrida(doTipo);
        const grupos = [];
        porCorrida.forEach((rs, corridaId) => {
            const c = corridas[corridaId];
            let g = grupos.find(x => x.id === c.temporada);
            if (!g) { g = { id: c.temporada, corridas: [] }; grupos.push(g); }
            g.corridas.push({ c, rs });
        });
        grupos.reverse(); // mais recente primeiro
        const abrir = grupos.length === 1;
        const tbody = document.getElementById(tbodyId);
        tbody.innerHTML = grupos.length ? grupos.map(g => {
            const todos = g.corridas.flatMap(x => x.rs);
            const vit = todos.filter(r => r.chegada === 1).length, pol = todos.filter(r => r.largada === 1).length;
            const pod = todos.filter(r => r.chegada >= 1 && r.chegada <= 3).length;
            const dob = g.corridas.filter(x => ehDobradinha(x.rs)).length;
            const pts = todos.reduce((a, r) => a + r.pontos, 0);
            const t = temporadaPorId[g.id] || { nome: '-', ano: '' };
            return '<tr class="sprint-temporada' + (abrir ? ' aberta' : '') + '" data-temporada="' + g.id + '" title="Clique para ver as corridas">' +
                '<td class="fw-bold"><i class="bi bi-chevron-right sprint-seta"></i>' + esc(t.nome) + (t.ano ? ' (' + esc(t.ano) + ')' : '') + '</td>' +
                '<td><span class="badge bg-secondary">' + g.corridas.length + '</span></td>' +
                '<td class="text-success fw-bold">' + vit + '</td><td class="fw-bold">' + pol + '</td><td class="text-primary fw-bold">' + pod + '</td>' +
                '<td>' + dob + '</td><td class="fw-bold text-danger">' + num(pts) + '</td></tr>' +
                '<tr class="sprint-detalhe' + (abrir ? '' : ' oculto') + '" data-detalhe="' + g.id + '"><td colspan="7">' +
                    '<div class="table-responsive"><table class="sprint-corridas"><thead><tr>' +
                    '<th>Etapa</th><th>Evento / Pista</th><th>Pilotos (largada → chegada)</th><th>Pontos</th><th>Status</th><th></th>' +
                    '</tr></thead><tbody>' + g.corridas.slice().reverse().map(linhaCorrida).join('') + '</tbody></table></div>' +
                '</td></tr>';
        }).join('') : vazio(7, sprint ? 'Nenhuma sprint disputada nesta seleção.' : 'Nenhuma corrida disputada nesta seleção.');
        return porCorrida.size;
    }

    function linhaCorrida({ c, rs }) {
        const ordenados = rs.slice().sort((a, b) => (a.chegada || 99) - (b.chegada || 99));
        const pilotos = ordenados.map(r => {
            let delta = '';
            if (r.largada > 0 && r.chegada > 0) {
                const d = r.largada - r.chegada;
                delta = d > 0 ? ' <span class="sprint-delta sobe"><i class="bi bi-caret-up-fill"></i>' + d + '</span>'
                      : d < 0 ? ' <span class="sprint-delta desce"><i class="bi bi-caret-down-fill"></i>' + (-d) + '</span>' : '';
            }
            const tags = (r.abandono ? '<span class="sprint-tag dnf">DNF</span>' : '') + (r.vr ? '<span class="sprint-tag vmr">VMR</span>' : '');
            return '<div class="equipe-res-linha"><span class="nome">' + htmlPiloto(r.piloto, false) + '</span>' +
                '<small>' + (r.largada ? r.largada + 'º' : '-') + ' →</small> ' +
                (r.chegada ? '<span class="sprint-pos ' + classePos(r.chegada, r.pontos) + '">' + r.chegada + 'º</span>' : '<span class="sprint-pos">-</span>') + delta + ' ' + tags + '</div>';
        }).join('');
        const pts = rs.reduce((a, r) => a + r.pontos, 0);
        const status = ehDobradinha(rs) ? '<span class="sprint-tag" style="background:#198754">DOBRADINHA</span>' : '-';
        return '<tr><td>' + esc(c.ordem) + 'ª</td><td>' + htmlLocal(c) + '</td><td><div class="equipe-res">' + pilotos + '</div></td>' +
            '<td class="fw-bold">' + num(pts) + '</td><td>' + status + '</td><td>' + acao(c.id) + '</td></tr>';
    }

    function renderSprintStats(lista) {
        const sp = lista.filter(r => r.corrida.sprint);
        const chegadas = sp.filter(r => r.chegada > 0).map(r => r.chegada);
        const porCorrida = agruparPorCorrida(sp);
        let dob = 0; porCorrida.forEach(rs => { if (ehDobradinha(rs)) dob++; });
        const tiles = [
            ['Sprints', porCorrida.size], ['Vitórias', sp.filter(r => r.chegada === 1).length], ['Poles', sp.filter(r => r.largada === 1).length],
            ['Pódios', sp.filter(r => r.chegada >= 1 && r.chegada <= 3).length], ['Dobradinhas', dob],
            ['Pontos', num(sp.reduce((a, r) => a + r.pontos, 0))], ['Abandonos', sp.filter(r => r.abandono).length],
            ['Melhor Chegada', chegadas.length ? Math.min(...chegadas) + 'º' : '-'], ['Média Chegada', media(chegadas)],
        ];
        document.getElementById('sprintStatsEquipe').innerHTML = tiles.map(t => '<div class="stat-box"><span>' + t[0] + '</span><strong>' + esc(t[1]) + '</strong></div>').join('');
    }

    // ---------- vitórias / poles / dobradinhas ----------
    function linhaLista(r, colunaExtra) {
        const c = r.corrida;
        return '<tr><td class="fw-bold" data-valor="' + (temporadaPorId[c.temporada] ? temporadaPorId[c.temporada].indice : 0) + '">' + esc(nomeTemporada(c.temporada)) + '</td>' +
            '<td data-valor="' + c.ordem + '">' + c.ordem + 'ª</td>' +
            '<td data-valor="' + esc(pista(c.pista).nome) + '">' + htmlLocal(c) + '</td>' +
            '<td data-valor="' + esc(piloto(r.piloto).nome) + '">' + htmlPiloto(r.piloto, true) + '</td>' +
            '<td>' + colunaExtra + '</td><td>' + acao(c.id) + '</td></tr>';
    }

    function renderListas(lista) {
        const gp = lista.filter(r => !r.corrida.sprint).slice().reverse(); // mais recente primeiro
        const vitorias = gp.filter(r => r.chegada === 1);
        const poles = gp.filter(r => r.largada === 1);
        document.querySelector('[data-contador="vitorias"]').textContent = vitorias.length;
        document.querySelector('[data-contador="poles"]').textContent = poles.length;
        document.getElementById('tbVitorias').innerHTML = vitorias.length
            ? vitorias.map(r => linhaLista(r, r.largada ? r.largada + 'º' : '-')).join('') : vazio(6, 'Nenhuma vitória registrada.');
        document.getElementById('tbPoles').innerHTML = poles.length
            ? poles.map(r => linhaLista(r, r.chegada ? '<span class="sprint-pos ' + classePos(r.chegada, r.pontos) + '">' + r.chegada + 'º</span>' : '-')).join('') : vazio(6, 'Nenhuma pole position registrada.');

        const dobradinhas = [];
        agruparPorCorrida(gp).forEach(rs => { if (ehDobradinha(rs)) dobradinhas.push(rs); });
        document.querySelector('[data-contador="dobradinhas"]').textContent = dobradinhas.length;
        document.getElementById('tbDobradinhas').innerHTML = dobradinhas.length ? dobradinhas.map(rs => {
            const c = rs[0].corrida, p1 = rs.find(r => r.chegada === 1), p2 = rs.find(r => r.chegada === 2);
            return '<tr><td class="fw-bold" data-valor="' + (temporadaPorId[c.temporada] ? temporadaPorId[c.temporada].indice : 0) + '">' + esc(nomeTemporada(c.temporada)) + '</td>' +
                '<td data-valor="' + c.ordem + '">' + c.ordem + 'ª</td><td data-valor="' + esc(pista(c.pista).nome) + '">' + htmlLocal(c) + '</td>' +
                '<td data-valor="' + esc(piloto(p1.piloto).nome) + '">' + htmlPiloto(p1.piloto, true) + '</td>' +
                '<td data-valor="' + esc(piloto(p2.piloto).nome) + '">' + htmlPiloto(p2.piloto, true) + '</td><td>' + acao(c.id) + '</td></tr>';
        }).join('') : vazio(6, 'Nenhuma dobradinha registrada.');
    }

    // sequência atual sem vitória/pole: Grandes Prêmios desde a última vez (sempre no geral)
    function renderSequencias() {
        const gps = Array.from(agruparPorCorrida(resultados.filter(r => !r.corrida.sprint)).values()); // ordem cronológica
        let semVitoria = 0, semPole = 0, achouV = false, achouP = false;
        for (let i = gps.length - 1; i >= 0; i--) {
            if (!achouV) { if (gps[i].some(r => r.chegada === 1)) achouV = true; else semVitoria++; }
            if (!achouP) { if (gps[i].some(r => r.largada === 1)) achouP = true; else semPole++; }
            if (achouV && achouP) break;
        }
        document.getElementById('seqSemVitoria').textContent = semVitoria;
        document.getElementById('seqSemPole').textContent = semPole;
    }

    // ---------- pistas ----------
    function renderPistas(lista) {
        const porCorrida = agruparPorCorrida(lista.filter(r => !r.corrida.sprint));
        const mapa = new Map();
        porCorrida.forEach((rs, corridaId) => {
            const c = corridas[corridaId];
            if (!mapa.has(c.pista)) mapa.set(c.pista, { id: c.pista, corridas: 0, vit: 0, pol: 0, pod: 0, dob: 0, melhor: null, pts: 0 });
            const p = mapa.get(c.pista);
            p.corridas++;
            if (ehDobradinha(rs)) p.dob++;
            rs.forEach(r => {
                if (r.chegada === 1) p.vit++;
                if (r.largada === 1) p.pol++;
                if (r.chegada >= 1 && r.chegada <= 3) p.pod++;
                if (r.chegada > 0 && (p.melhor === null || r.chegada < p.melhor)) p.melhor = r.chegada;
                p.pts += r.pontos;
            });
        });
        const linhas = Array.from(mapa.values()).sort((a, b) => b.vit - a.vit || b.pol - a.pol || b.corridas - a.corridas);
        const semVitoria = linhas.filter(p => !p.vit).length, semPole = linhas.filter(p => !p.pol).length;
        document.querySelector('[data-contador="pistas"]').textContent = linhas.length;
        document.getElementById('resumoPistas').innerHTML = '<i class="bi bi-geo-alt-fill me-2 text-danger"></i> ' + linhas.length + ' pistas disputadas · ' +
            '<strong>' + semVitoria + '</strong> sem vitória · <strong>' + semPole + '</strong> sem pole <small class="ms-2">(clique nos cabeçalhos para ordenar)</small>';
        document.getElementById('tbPistas').innerHTML = linhas.length ? linhas.map(p => {
            const info = pista(p.id);
            return '<tr><td class="fw-bold" data-valor="' + esc(info.nome) + '">' + (info.bandeira ? '<img src="' + esc(info.bandeira) + '" class="flag-img" alt=""> ' : '') + esc(info.nome) + '</td>' +
                '<td>' + p.corridas + '</td>' +
                '<td class="' + (p.vit ? 'text-success fw-bold' : 'text-muted') + '">' + p.vit + '</td>' +
                '<td class="' + (p.pol ? 'fw-bold' : 'text-muted') + '">' + p.pol + '</td>' +
                '<td class="text-primary fw-bold">' + p.pod + '</td>' +
                '<td>' + p.dob + '</td>' +
                '<td data-valor="' + (p.melhor || '') + '">' + (p.melhor ? '<span class="sprint-pos ' + classePos(p.melhor, 1) + '">' + p.melhor + 'º</span>' : '-') + '</td>' +
                '<td class="fw-bold text-danger" data-valor="' + p.pts + '">' + num(p.pts) + '</td></tr>';
        }).join('') : vazio(8, 'Nenhuma corrida nesta seleção.');
    }

    // ---------- orquestração ----------
    function renderizar(temporadaId) {
        const lista = temporadaId ? resultados.filter(r => String(r.corrida.temporada) === String(temporadaId)) : resultados;
        document.getElementById('filtroAtivoTexto').textContent = temporadaId && temporadaPorId[temporadaId] ? temporadaPorId[temporadaId].completo : 'Todas as temporadas';
        renderCard(lista, temporadaId);
        renderPilotos(lista, temporadaId);
        renderCampeonatos(temporadaId);
        document.querySelector('[data-contador="corridas"]').textContent = renderPorTemporada('tbCorridas', lista, false, temporadaId);
        document.querySelector('[data-contador="sprints"]').textContent = renderPorTemporada('tbSprints', lista, true, temporadaId);
        renderSprintStats(lista);
        renderListas(lista);
        renderPistas(lista);
    }

    // expandir/recolher as corridas de uma temporada
    ['tbCorridas', 'tbSprints'].forEach(id => document.getElementById(id).addEventListener('click', e => {
        const linha = e.target.closest('tr.sprint-temporada');
        if (!linha || e.target.closest('a')) return;
        const detalhe = document.querySelector('#' + id + ' tr[data-detalhe="' + linha.dataset.temporada + '"]');
        if (!detalhe) return;
        const abrir = detalhe.classList.contains('oculto');
        detalhe.classList.toggle('oculto', !abrir);
        linha.classList.toggle('aberta', abrir);
    }));

    // seletor de temporada (select2 do layout dispara o change pelo jQuery)
    const seletor = document.getElementById('filtroTemporadaEquipe');
    const aoMudar = () => renderizar(seletor.value);
    if ($j) $j(seletor).on('change', aoMudar); else seletor.addEventListener('change', aoMudar);

    function definirTemporada(id) {
        seletor.value = id;
        if ($j) $j(seletor).trigger('change'); else aoMudar();
        document.getElementById('topo-perfil').scrollIntoView({ behavior: 'smooth' });
    }
    document.getElementById('tbCampeonatos').addEventListener('click', e => {
        const link = e.target.closest('a.filtrar-temporada');
        if (!link) return;
        e.preventDefault();
        definirTemporada(seletor.value === link.dataset.temporada ? '' : link.dataset.temporada);
    });

    // mais estatísticas
    document.getElementById('show-other-stats').addEventListener('click', function (e) {
        e.preventDefault();
        const abrir = this.dataset.aberto !== '1';
        this.dataset.aberto = abrir ? '1' : '0';
        this.textContent = abrir ? 'Ocultar Estatísticas' : 'Exibir Mais Estatísticas';
        document.querySelectorAll('#equipeStats .other-stats').forEach(el => el.style.display = abrir ? 'block' : 'none');
    });

    // navegação: rodapé abre o acordeão de destino; expandir/recolher tudo
    document.querySelectorAll('.footer-show-pilotos a').forEach(a => a.addEventListener('click', () => {
        const alvo = document.querySelector(a.getAttribute('href'));
        if (alvo && alvo.tagName === 'DETAILS') alvo.open = true;
    }));
    document.getElementById('expandirTudo').addEventListener('click', () => document.querySelectorAll('details.f1-accordion').forEach(d => d.open = true));
    document.getElementById('recolherTudo').addEventListener('click', () => document.querySelectorAll('details.f1-accordion').forEach(d => d.open = false));

    renderSequencias();
    renderizar('');
})();
</script>
@endsection
