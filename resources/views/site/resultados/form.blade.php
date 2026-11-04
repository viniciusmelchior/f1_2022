@extends('layouts.main')

@php
    use App\Models\Site\Resultado;
    use App\Models\Site\ImagensCorrida;
    use App\Models\Site\CorridaSessao;
    use Illuminate\Support\Facades\Artisan;
    $route = route('resultados.update', [$corrida->id]);
    $method = method_field('POST');
    if(isset($model)){
        $route = route('resultados.update', [$corrida->id]);
        $method = method_field('POST');
    }
    Artisan::call('view:clear'); //teste

    $tituloCorrida = isset($corrida->evento->des_nome) ? $corrida->evento->des_nome : 'GP de '.$corrida->pista->nome;
    $possuiArquivos = count(ImagensCorrida::getImagensCorrida($corrida->id)) > 0;

    // MELHORIA 20261103: JSONs das sessões já gravados para esta corrida
    $sessoesSalvas = CorridaSessao::where('corrida_id', $corrida->id)->where('user_id', Auth::user()->id)->get()->keyBy('tipo');
    // MELHORIA 20261103 (etapa 3): equipe, logo, cor e foto de cada piloto do grid
    $infoPilotos = $model->map(fn ($pe) => [
        'nome' => $pe->piloto->nome . ' ' . $pe->piloto->sobrenome,
        'equipe' => $pe->equipe->nome ?? null,
        'logo' => isset($pe->equipe->imagem) ? asset('images/' . $pe->equipe->imagem) : null,
        'cor' => $pe->equipe->des_cor ?? null,
        'foto' => $pe->piloto->imagem ? asset('images/' . $pe->piloto->imagem) : null,
        'super' => $pe->flg_super_corrida == 'S',
    ])->values();
    $metaSessoes = $sessoesSalvas->map(fn ($s) => ['nome' => $s->nome_arquivo, 'data' => $s->updated_at ? $s->updated_at->format('d/m/Y H:i') : ''])->toArray();
@endphp

@section('section')
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
    input[type=number] { -moz-appearance: textfield; }

    /* =================== Race Control - tela de resultados =================== */
    .rc {
        --rc-plano: #0d0d0d;
        --rc-superficie: #1a1a19;
        --rc-superficie-2: #232321;
        --rc-borda: rgba(255,255,255,0.10);
        --rc-grade: #2c2c2a;
        --rc-eixo: #383835;
        --rc-tinta: #f0efec;
        --rc-tinta-2: #c3c2b7;
        --rc-tinta-3: #898781;
        --rc-destaque: #e10600;
        --rc-bom: #0ca30c;
        --rc-alerta: #fab219;
        --rc-critico: #d03b3b;
        --rc-roxo: #9085e9;
        font-family: 'Titillium Web', 'Poppins', sans-serif;
        background: var(--rc-plano);
        color: var(--rc-tinta);
        min-height: 100vh;
        padding: 0 0 90px 0;
    }
    .rc * { font-family: inherit; }
    .rc .bi { font-family: "bootstrap-icons" !important; }
    .rc-topo {
        position: relative;
        background: linear-gradient(90deg, #15151e 0%, #1f1f27 60%, #15151e 100%);
        border-bottom: 1px solid var(--rc-borda);
        padding: 22px 28px 18px 28px;
    }
    .rc-topo::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--rc-destaque); }
    .rc-topo .rc-sobretitulo { font-size: .75rem; letter-spacing: .18em; text-transform: uppercase; color: var(--rc-tinta-3); font-weight: 600; }
    .rc-topo h2 { font-weight: 900; font-size: 2rem; margin: 0; line-height: 1.1; text-transform: uppercase; letter-spacing: .01em; }
    .rc-topo .rc-temporada { color: var(--rc-destaque); }
    .rc-chip { display: inline-flex; align-items: center; gap: 6px; padding: 2px 10px; border-radius: 999px; background: var(--rc-superficie-2); border: 1px solid var(--rc-borda); font-size: .78rem; color: var(--rc-tinta-2); }
    .rc-corpo { padding: 22px 28px; }

    /* Grades próprias: o classificacao.css global redefine ".row" como grid de 4 colunas, por isso não usamos row/col aqui */
    .rc-grade-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .rc-grade-principal { display: grid; grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr); gap: 20px; align-items: start; }
    .rc-grade-analise { gap: 20px; margin-bottom: 20px; }
    .rc-grade-analise > div > .rc-card { height: 100%; margin-bottom: 0; }
    .rc-coluna { min-width: 0; }
    @media (max-width: 1199px) { .rc-grade-principal { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 899px) { .rc-grade-3 { grid-template-columns: minmax(0, 1fr); } }

    .rc-card { background: var(--rc-superficie); border: 1px solid var(--rc-borda); border-radius: 12px; margin-bottom: 20px; }
    .rc-card-cabecalho { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 18px; border-bottom: 1px solid var(--rc-borda); flex-wrap: wrap; }
    .rc-card-titulo { margin: 0; font-size: .85rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--rc-tinta); display: flex; align-items: center; gap: 8px; }
    .rc-card-titulo .bi { color: var(--rc-destaque); }
    .rc-card-subtitulo { font-size: .78rem; color: var(--rc-tinta-3); }
    .rc-card-corpo { padding: 16px 18px; }

    .rc label.rc-rotulo, .rc .rc-rotulo { font-size: .72rem; letter-spacing: .1em; text-transform: uppercase; color: var(--rc-tinta-3); font-weight: 600; margin-bottom: 4px; display: block; }
    .rc .form-control, .rc .form-select { background: var(--rc-superficie-2); border: 1px solid var(--rc-eixo); color: var(--rc-tinta); border-radius: 8px; }
    .rc .form-control:focus, .rc .form-select:focus { background: var(--rc-superficie-2); color: var(--rc-tinta); border-color: var(--rc-destaque); box-shadow: 0 0 0 3px rgba(225,6,0,.2); }
    .rc select.form-control option { background: var(--rc-superficie-2); color: var(--rc-tinta); }
    .rc textarea.form-control::placeholder { color: var(--rc-tinta-3); }

    /* Upload tiles */
    .rc-upload { position: relative; display: block; height: 100%; padding: 14px 16px; border: 1px dashed var(--rc-eixo); border-radius: 10px; background: var(--rc-superficie-2); cursor: pointer; transition: border-color .15s, background .15s; margin: 0; }
    .rc-upload:hover { border-color: var(--rc-tinta-3); background: #2a2a28; }
    .rc-upload.carregado { border-style: solid; border-color: var(--rc-bom); }
    .rc-upload.erro { border-style: solid; border-color: var(--rc-critico); }
    .rc-upload input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; }
    .rc-upload .rc-upload-icone { font-size: 1.5rem; color: var(--rc-tinta-2); line-height: 1; }
    .rc-upload .rc-upload-titulo { font-weight: 700; font-size: .95rem; margin-top: 6px; }
    .rc-upload .rc-upload-status { font-size: .78rem; color: var(--rc-tinta-3); margin-top: 2px; word-break: break-all; }
    .rc-upload.carregado .rc-upload-status { color: var(--rc-tinta-2); }
    .rc-upload.carregado .rc-upload-icone { color: var(--rc-bom); }
    .rc-upload.erro .rc-upload-icone { color: var(--rc-critico); }

    /* Tabela de resultado (formulário) */
    .rc-tabela { width: 100%; border-collapse: separate; border-spacing: 0; color: var(--rc-tinta); margin: 0; }
    .rc-tabela thead th { position: sticky; top: 0; z-index: 2; background: var(--rc-superficie); font-size: .7rem; letter-spacing: .1em; text-transform: uppercase; color: var(--rc-tinta-3); font-weight: 600; padding: 10px 8px; border-bottom: 1px solid var(--rc-eixo); text-align: center; white-space: nowrap; }
    .rc-tabela thead th.text-start { text-align: left; }
    .rc-tabela td { padding: 6px 8px; border-bottom: 1px solid var(--rc-grade); vertical-align: middle; text-align: center; }
    .rc-tabela tbody tr { transition: background .1s; }
    .rc-tabela tbody tr:hover td { background: rgba(255,255,255,.03); }
    .rc-tabela td.rc-col-equipe { width: 44px; padding-left: 14px; }
    .rc-tabela td.rc-col-equipe img { width: 28px; height: 28px; object-fit: contain; border-radius: 6px; background: #fff; padding: 2px; }
    .rc-tabela td.rc-col-piloto { text-align: left; font-weight: 600; font-size: .95rem; white-space: nowrap; border-left: 3px solid transparent; }
    .rc-tabela .rc-equipe-nome { display: block; font-size: .7rem; color: var(--rc-tinta-3); font-weight: 400; line-height: 1; margin-top: 2px; white-space: nowrap; max-width: 90px; overflow: hidden; text-overflow: ellipsis; }
    .rc-tabela input[type=number] { width: 52px; height: 34px; text-align: center; font-weight: 700; font-size: 1rem; border-radius: 8px; border: 1px solid var(--rc-eixo); background: var(--rc-superficie-2); color: var(--rc-tinta); }
    .rc-tabela input[type=number]:focus { outline: none; border-color: var(--rc-destaque); box-shadow: 0 0 0 3px rgba(225,6,0,.2); }
    .rc-tabela tr.linha-preenchida input.chegada { border-color: var(--rc-bom); }
    .rc-dnf { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--rc-eixo); font-size: .75rem; font-weight: 700; color: var(--rc-tinta-3); user-select: none; margin: 0; }
    .rc-dnf input { width: 16px; height: 16px; accent-color: var(--rc-critico); cursor: pointer; margin: 0; }
    .rc-dnf:has(input:checked) { border-color: var(--rc-critico); color: var(--rc-tinta); background: rgba(208,59,59,.18); }
    .rc-tabela tr.linha-abandono td.rc-col-piloto { border-left-color: var(--rc-critico); }
    .rc-tabela tr.linha-volta-rapida td.rc-col-piloto { border-left-color: var(--rc-roxo); }
    .rc-pontos { display: inline-block; min-width: 38px; padding: 3px 8px; border-radius: 6px; background: var(--rc-superficie-2); font-weight: 700; color: var(--rc-tinta-2); }
    .rc-pontos.tem-pontos { color: var(--rc-tinta); background: #2f2f2c; }
    .rc-delta { font-weight: 700; font-size: .85rem; white-space: nowrap; }
    .rc-delta.sobe { color: var(--rc-bom); }
    .rc-delta.desce { color: #ec835a; }
    .rc-delta.igual { color: var(--rc-tinta-3); }
    .rc-detalhe { font-size: .75rem; color: var(--rc-tinta-2); white-space: nowrap; text-align: left !important; }
    .rc-detalhe .rc-detalhe-volta { color: var(--rc-tinta); font-weight: 600; }
    .col-pontuacao-extra { display: none; }
    .rc-tabela-wrap { max-height: 75vh; overflow: auto; border-radius: 0 0 12px 12px; }

    /* KPIs */
    .rc-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .rc-kpi { background: var(--rc-superficie); border: 1px solid var(--rc-borda); border-radius: 12px; padding: 14px 16px; }
    .rc-kpi-rotulo { font-size: .7rem; letter-spacing: .12em; text-transform: uppercase; color: var(--rc-tinta-3); font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .rc-kpi-valor { font-size: 1.6rem; font-weight: 900; line-height: 1.15; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .rc-kpi-valor.menor { font-size: 1.15rem; font-weight: 700; }
    .rc-kpi-extra { font-size: .78rem; color: var(--rc-tinta-2); margin-top: 2px; }

    /* Pódio */
    .rc-podio { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; align-items: end; }
    .rc-podio-item { text-align: center; }
    .rc-podio-nome { font-weight: 700; font-size: .95rem; line-height: 1.1; }
    .rc-podio-dif { font-size: .78rem; color: var(--rc-tinta-2); font-variant-numeric: tabular-nums; }
    .rc-podio-degrau { margin-top: 8px; border-radius: 8px 8px 0 0; background: var(--rc-superficie-2); border: 1px solid var(--rc-borda); border-bottom: 0; display: flex; align-items: flex-start; justify-content: center; padding-top: 8px; font-weight: 900; font-size: 1.6rem; color: var(--rc-tinta-2); }
    .rc-podio-item.p1 .rc-podio-degrau { height: 96px; color: var(--rc-tinta); border-top: 3px solid var(--rc-destaque); }
    .rc-podio-item.p2 .rc-podio-degrau { height: 72px; }
    .rc-podio-item.p3 .rc-podio-degrau { height: 54px; }

    /* Listas de informação */
    .rc-lista { display: grid; grid-template-columns: 1fr 1fr; gap: 0; margin: 0; }
    .rc-lista > div { padding: 8px 0; border-bottom: 1px solid var(--rc-grade); }
    .rc-lista > div:nth-child(odd) { padding-right: 12px; }
    .rc-lista dt { font-size: .68rem; letter-spacing: .1em; text-transform: uppercase; color: var(--rc-tinta-3); font-weight: 600; }
    .rc-lista dd { margin: 0; font-weight: 600; font-variant-numeric: tabular-nums; }
    .rc-lista dd small { color: var(--rc-tinta-3); font-weight: 400; }

    /* Destaques */
    .rc-destaques { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
    .rc-destaque-item { background: var(--rc-superficie-2); border-radius: 10px; padding: 12px 14px; border: 1px solid var(--rc-borda); }
    .rc-destaque-item .rc-kpi-rotulo .bi { font-size: .95rem; }
    .rc-destaque-valor { font-weight: 700; font-size: 1.05rem; margin-top: 4px; }
    .rc-destaque-extra { font-size: .78rem; color: var(--rc-tinta-2); }

    /* Badges de status (sempre com ícone + texto) */
    .rc-status { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .rc-status.ok { background: rgba(12,163,12,.16); color: var(--rc-tinta); border: 1px solid rgba(12,163,12,.55); }
    .rc-status.ok .bi { color: var(--rc-bom); }
    .rc-status.dnf { background: rgba(208,59,59,.16); color: var(--rc-tinta); border: 1px solid rgba(208,59,59,.6); }
    .rc-status.dnf .bi { color: var(--rc-critico); }
    .rc-voce { display: inline-block; font-size: .62rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; background: var(--rc-destaque); color: #fff; margin-left: 6px; vertical-align: middle; }
    .rc-roxo { color: var(--rc-roxo) !important; font-weight: 700; }
    .rc-verde { color: var(--rc-bom) !important; font-weight: 700; }

    /* Tabelas de análise */
    .rc-analise-tabela { width: 100%; font-size: .82rem; color: var(--rc-tinta); border-collapse: separate; border-spacing: 0; }
    .rc-analise-tabela th { position: sticky; top: 0; background: var(--rc-superficie); z-index: 1; font-size: .66rem; letter-spacing: .1em; text-transform: uppercase; color: var(--rc-tinta-3); font-weight: 600; padding: 8px 8px; border-bottom: 1px solid var(--rc-eixo); white-space: nowrap; text-align: right; }
    .rc-analise-tabela td { padding: 6px 8px; border-bottom: 1px solid var(--rc-grade); white-space: nowrap; text-align: right; font-variant-numeric: tabular-nums; }
    .rc-analise-tabela th.text-start, .rc-analise-tabela td.text-start { text-align: left; }
    .rc-analise-tabela th.text-center, .rc-analise-tabela td.text-center { text-align: center; }
    .rc-analise-tabela tbody tr { cursor: pointer; }
    .rc-analise-tabela tbody tr:hover td { background: rgba(255,255,255,.03); }
    .rc-analise-tabela tr.dnf td { color: var(--rc-tinta-2); }
    .rc-analise-tabela tr.destacado td:first-child { box-shadow: inset 3px 0 0 var(--cor-destaque, transparent); }
    .rc-analise-tabela .rc-nome-piloto { font-weight: 700; }
    .rc-analise-tabela .rc-sub { display: block; font-size: .7rem; color: var(--rc-tinta-3); font-weight: 400; }
    .rc-analise-tabela .fora-form { color: var(--rc-tinta-3); }
    .rc-wrap-rolagem { max-height: 520px; overflow: auto; }

    .rc-pneu { display: inline-block; min-width: 14px; padding: 0 1px 1px; margin-right: 5px; font-size: .82rem; font-weight: 700; line-height: 1.1; text-align: center; color: var(--rc-tinta); border-bottom: 3px solid var(--rc-eixo); }
    .rc-pneu.S { border-bottom-color: #d03b3b; } .rc-pneu.M { border-bottom-color: #fab219; } .rc-pneu.H { border-bottom-color: #f0efec; }
    .rc-pneu.I { border-bottom-color: #0ca30c; } .rc-pneu.W { border-bottom-color: #3987e5; }

    /* Estratégia de pneus */
    .rc-estrategia-linha { display: grid; grid-template-columns: 34px 210px 1fr; align-items: center; gap: 10px; padding: 3px 0; }
    .rc-estrategia-pos { color: var(--rc-tinta-3); font-weight: 700; text-align: right; font-variant-numeric: tabular-nums; }
    .rc-estrategia-nome { font-weight: 600; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rc-estrategia-barra { position: relative; height: 20px; }
    .rc-estrategia-barra .rc-stint { position: absolute; top: 0; bottom: 0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: .66rem; font-weight: 700; overflow: hidden; white-space: nowrap; }
    .rc-stint.S { background: #d03b3b; color: #fff; }
    .rc-stint.M { background: #fab219; color: #0b0b0b; }
    .rc-stint.H { background: #f0efec; color: #0b0b0b; }
    .rc-stint.I { background: #0ca30c; color: #fff; }
    .rc-stint.W { background: #3987e5; color: #fff; }
    .rc-stint.X { background: #898781; color: #0b0b0b; }
    .rc-estrategia-fim { position: absolute; top: 2px; font-size: .72rem; color: var(--rc-critico); }
    .rc-estrategia-eixo { display: grid; grid-template-columns: 34px 210px 1fr; gap: 10px; font-size: .66rem; color: var(--rc-tinta-3); margin-top: 6px; }
    .rc-estrategia-eixo .marcas { position: relative; height: 14px; border-top: 1px solid var(--rc-eixo); }
    .rc-estrategia-eixo .marcas span { position: absolute; transform: translateX(-50%); top: 2px; }
    .rc-legenda { display: flex; flex-wrap: wrap; gap: 14px; font-size: .75rem; color: var(--rc-tinta-2); }
    .rc-legenda span { display: inline-flex; align-items: center; gap: 6px; }
    .rc-legenda i.amostra { display: inline-block; width: 14px; height: 10px; border-radius: 3px; }
    .rc-legenda i.amostra-linha { display: inline-block; width: 18px; height: 3px; border-radius: 2px; }

    /* Volta a volta */
    .rc-voltas td.volta-pit { box-shadow: inset 0 -2px 0 #fab219; }
    .rc-voltas th.neutralizada, .rc-voltas td.neutralizada { background: rgba(250,178,25,.06); }

    .rc-acoes { position: sticky; bottom: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: rgba(13,13,13,.94); backdrop-filter: blur(6px); border-top: 1px solid var(--rc-borda); padding: 12px 28px; margin-top: 10px; }
    .rc .btn-rc { background: var(--rc-destaque); border: 0; color: #fff; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; padding: 8px 22px; border-radius: 8px; }
    .rc .btn-rc:hover { background: #ff2a1f; color: #fff; }
    .rc .btn-rc-sec { background: transparent; border: 1px solid var(--rc-eixo); color: var(--rc-tinta-2); font-weight: 600; padding: 7px 16px; border-radius: 8px; }
    .rc .btn-rc-sec:hover { border-color: var(--rc-tinta-3); color: var(--rc-tinta); }
    .rc .btn-rc-mini { background: var(--rc-superficie-2); border: 1px solid var(--rc-eixo); color: var(--rc-tinta-2); font-size: .75rem; padding: 3px 10px; border-radius: 6px; }
    .rc .btn-rc-mini:hover, .rc .btn-rc-mini.ativo { color: var(--rc-tinta); border-color: var(--rc-tinta-3); }
    .rc-aviso { font-size: .8rem; padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; display: flex; gap: 10px; align-items: flex-start; line-height: 1.4; }
    .rc-aviso > .bi { font-size: 1rem; margin-top: 1px; }
    .rc-aviso strong { display: block; color: var(--rc-tinta); font-weight: 700; }
    .rc-aviso-detalhe { display: block; color: var(--rc-tinta-2); }
    .rc-aviso-lista { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .rc-aviso-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 999px; background: rgba(255,255,255,.06); border: 1px solid var(--rc-borda); color: var(--rc-tinta); font-size: .78rem; white-space: nowrap; }
    .rc-aviso-chip b { color: var(--rc-tinta-3); font-weight: 700; }
    /* bloco de upload + linha "Salvo" ficam empilhados dentro da coluna (sem sobrepor o aviso abaixo) */
    .rc-grade-3 > div:has(> .rc-upload) { display: flex; flex-direction: column; }
    .rc-grade-3 > div > .rc-upload { height: auto; flex: 1 1 auto; }
    .rc-aviso.alerta { background: rgba(250,178,25,.1); border: 1px solid rgba(250,178,25,.45); color: var(--rc-tinta); }
    .rc-aviso.alerta .bi { color: var(--rc-alerta); }
    .rc-aviso.info { background: var(--rc-superficie-2); border: 1px solid var(--rc-borda); color: var(--rc-tinta-2); }
    .rc-vazio { text-align: center; padding: 34px 20px; color: var(--rc-tinta-3); }
    .rc-vazio .bi { font-size: 2.2rem; display: block; margin-bottom: 8px; color: var(--rc-eixo); }
    .rc-grafico-wrap { position: relative; height: 420px; }
    .rc .form-check-input { background-color: var(--rc-superficie-2); border-color: var(--rc-eixo); }
    .rc .form-check-input:checked { background-color: var(--rc-destaque); border-color: var(--rc-destaque); }
    .rc .select2-container { width: 100% !important; }
    .rc .select2-container .select2-selection--single { background: var(--rc-superficie-2) !important; background-image: none !important; border: 1px solid var(--rc-eixo); border-radius: 8px; height: 38px; }
    .rc .select2-container .select2-selection--single .select2-selection__rendered { color: var(--rc-tinta); line-height: 36px; padding-left: 12px; }
    .rc .select2-container .select2-selection--single .select2-selection__arrow { height: 36px; background: transparent !important; background-image: none !important; border-left: 0 !important; }
    .rc .select2-container .select2-selection--single .select2-selection__arrow b { border-color: var(--rc-tinta-3) transparent transparent transparent; }
    .rc-banco { font-size: .76rem; margin-top: 6px; min-height: 1.4em; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; color: var(--rc-tinta-3); }
    .rc-banco .btn-rc-mini { padding: 0 6px; font-size: .7rem; }
    .rc-banco-salvo { color: var(--rc-tinta-2); }
    .rc-banco-salvo .bi { color: var(--rc-bom); }
    .rc-banco-pendente { color: var(--rc-alerta); }
    .rc-banco-remover { color: #ec835a; }
    /* equipes */
    .rc-equipe { display: inline-flex; align-items: center; gap: 5px; font-size: .72rem; color: var(--rc-tinta-2); font-weight: 600; white-space: nowrap; vertical-align: middle; }
    .rc-equipe img { width: 16px; height: 16px; object-fit: contain; background: #fff; border-radius: 3px; padding: 1px; }
    .rc-equipe.sem-cadastro { color: var(--rc-tinta-3); font-weight: 400; font-style: italic; }
    .rc-cor-equipe { display: inline-block; width: 4px; height: 30px; border-radius: 2px; margin-right: 8px; vertical-align: middle; float: left; }
    .rc-sub .rc-equipe { font-size: .7rem; }
    .rc-destaque-equipe { margin-top: 2px; }
    .rc-voltas th:nth-child(2), .rc-voltas td:nth-child(2) { position: sticky; left: 0; background: var(--rc-superficie); z-index: 1; }
    .rc-voltas thead th:nth-child(2) { z-index: 3; }
    /* pódio */
    .rc-podio-foto { position: relative; width: 74px; height: 74px; margin: 0 auto 8px; border-radius: 50%; border: 3px solid var(--cor-equipe, #5a5a57); background: #2a2a28; display: flex; align-items: center; justify-content: center; }
    .rc-podio-foto > img:first-child { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .rc-podio-foto > .bi { font-size: 2rem; color: var(--rc-tinta-3); }
    .rc-podio-item.p1 .rc-podio-foto { width: 92px; height: 92px; }
    .rc-podio-logo { position: absolute; right: -6px; bottom: -4px; width: 30px !important; height: 30px !important; border-radius: 7px !important; background: #fff; padding: 2px; object-fit: contain !important; border: 2px solid var(--rc-superficie); }
    .rc-podio-equipe { font-size: .75rem; color: var(--rc-tinta-2); margin-top: 1px; }
    .rc-podio-item .rc-podio-degrau { border-top: 3px solid var(--cor-equipe, var(--rc-borda)) !important; }
    /* treino */
    .rc-resumo-texto { font-size: .92rem; color: var(--rc-tinta-2); margin-bottom: 14px; line-height: 1.5; }
    .rc-resumo-texto strong { color: var(--rc-tinta); }
    .rc-kpis-treino .rc-kpi { background: var(--rc-superficie-2); }
    .rc-barra-dif { display: inline-block; vertical-align: middle; height: 6px; border-radius: 3px; background: #6f6e69; margin-right: 6px; min-width: 2px; }
    .rc-como-ler { font-size: .75rem; color: var(--rc-tinta-3); display: flex; flex-wrap: wrap; gap: 6px 16px; border-top: 1px solid var(--rc-borda); }
    .rc-como-ler b { color: var(--rc-tinta-2); }
    .rc-validas { display: inline-block; width: 46px; height: 6px; border-radius: 3px; background: #3a3a37; vertical-align: middle; margin-left: 6px; overflow: hidden; }
    .rc-validas > i { display: block; height: 100%; background: var(--rc-bom); }
    .rc-volta-rapida-info { font-size: .78rem; color: var(--rc-tinta-2); margin-top: 6px; min-height: 1.2em; }
</style>

<div class="rc">
    <form method="POST" action="{{ $route }}" enctype="multipart/form-data" id="formResultado">
        {{ $method }}
        @csrf

        {{-- ===================== TOPO ===================== --}}
        <div class="rc-topo">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <div class="rc-sobretitulo">Resultado da corrida &middot; {{ $corrida->pista->nome }}</div>
                    <h2>{{ $tituloCorrida }} <span class="rc-temporada">{{ $corrida->temporada->ano->ano }}</span></h2>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="rc-chip"><i class="bi bi-people"></i> {{ $model->where('flg_super_corrida', '!=', 'S')->count() }} pilotos</span>
                        @if($corrida->flg_sprint == 'S')
                            <span class="rc-chip"><i class="bi bi-lightning"></i> {{ $corrida->flg_super_corrida == 'S' ? 'Super Sprint' : 'Sprint' }}</span>
                        @endif
                        @if(isset($corrida->updated_at))
                            <span class="rc-chip"><i class="bi bi-clock-history"></i> Atualizado em {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:i', strtotime($corrida->updated_at))}}</span>
                        @endif
                        @if($possuiArquivos)
                            <span class="rc-chip"><i class="bi bi-images"></i> Possui arquivos salvos</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{route('temporadas.index')}}" class="btn btn-rc-sec"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <button type="submit" class="btn btn-rc"><i class="bi bi-check2"></i> Salvar</button>
                </div>
            </div>
        </div>

        <div class="rc-corpo">

            {{-- ===================== IMPORTAÇÃO ===================== --}}
            <div class="rc-card">
                <div class="rc-card-cabecalho">
                    <h5 class="rc-card-titulo"><i class="bi bi-cloud-arrow-up"></i> Importar sessões (JSON do Content Manager)</h5>
                    <span class="rc-card-subtitulo">Os dados são lidos no navegador e preenchem o formulário &mdash; nada é salvo até clicar em Salvar</span>
                </div>
                <div class="rc-card-corpo">
                    <div class="rc-grade-3">
                        <div>
                            <label class="rc-upload" id="tileLargada">
                                <input type="file" id="inputFileLargada" accept=".json,application/json">
                                <div class="rc-upload-icone"><i class="bi bi-flag"></i></div>
                                <div class="rc-upload-titulo">Largada</div>
                                <div class="rc-upload-status">Classificação (Qualifying) &rarr; preenche o grid</div>
                            </label>
                            <div class="rc-banco" id="banco_largada"></div>
                        </div>
                        <div>
                            <label class="rc-upload" id="tileChegada">
                                <input type="file" id="inputFileChegada" accept=".json,application/json">
                                <div class="rc-upload-icone"><i class="bi bi-trophy"></i></div>
                                <div class="rc-upload-titulo">Chegada</div>
                                <div class="rc-upload-status" id="statusChegada">Corrida (Quick Race) &rarr; chegada, abandonos, volta mais rápida e análise</div>
                            </label>
                            <div class="rc-banco" id="banco_chegada"></div>
                        </div>
                        <div>
                            <label class="rc-upload" id="tileGridInvertido">
                                <input type="file" id="inputFileLargadaGridInvertido" accept=".json,application/json">
                                <div class="rc-upload-icone"><i class="bi bi-arrow-down-up"></i></div>
                                <div class="rc-upload-titulo">Grid invertido</div>
                                <div class="rc-upload-status">Classificação &rarr; inverte o top 10 no grid</div>
                            </label>
                            <div class="rc-banco" id="banco_grid_invertido"></div>
                        </div>
                    </div>
                    <div id="avisosChegada" class="mt-3 pt-1"></div>
                    @if(session('erro_sessoes_json'))
                        <div class="rc-aviso alerta mt-2"><i class="bi bi-exclamation-triangle-fill"></i><div>{{ session('erro_sessoes_json') }}</div></div>
                    @endif

                    {{-- MELHORIA 20261103: os JSONs carregados vão para o banco junto com o Salvar --}}
                    <div id="camposSessoes" class="d-none">
                        @foreach(CorridaSessao::TIPOS as $tipoSessao)
                            <textarea name="sessao_json[{{ $tipoSessao }}]" id="sessaoJson_{{ $tipoSessao }}"></textarea>
                            <input type="hidden" name="sessao_nome[{{ $tipoSessao }}]" id="sessaoNome_{{ $tipoSessao }}" value="">
                        @endforeach
                    </div>
                    @foreach($sessoesSalvas as $tipoSessao => $sessaoSalva)
                        <script type="application/json" id="sessaoSalva_{{ $tipoSessao }}">{!! str_replace('</', '<\/', $sessaoSalva->conteudo) !!}</script>
                    @endforeach
                    <script>window.SESSOES_SALVAS = @json((object) $metaSessoes);</script>
                    <script>
                        window.CORRIDA_SUPER = {{ $corrida->flg_super_corrida == 'S' ? 'true' : 'false' }};
                        window.INFO_PILOTOS = @json($infoPilotos);
                    </script>
                </div>
            </div>

            {{-- ===================== KPIs (preenchidos pelo JSON de chegada) ===================== --}}
            <div class="rc-kpis d-none" id="kpisCorrida"></div>

            <div class="rc-grade-principal">
                {{-- ===================== RESULTADO (formulário) ===================== --}}
                <div class="rc-coluna">
                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-list-ol"></i> Resultado</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-rc-mini" data-ordenar="chegada"><i class="bi bi-sort-numeric-down"></i> Chegada</button>
                                <button type="button" class="btn btn-rc-mini" data-ordenar="largada"><i class="bi bi-flag"></i> Largada</button>
                                <button type="button" class="btn btn-rc-mini" data-ordenar="equipe"><i class="bi bi-people"></i> Equipe</button>
                            </div>
                        </div>
                        <div class="rc-tabela-wrap">
                            <table class="rc-tabela">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-start" style="padding-left: 14px;">Piloto</th>
                                        <th>Largada</th>
                                        <th>Chegada</th>
                                        <th>Abandono</th>
                                        <th>Pontos</th>
                                        <th title="Posições ganhas/perdidas (largada - chegada)">+/-</th>
                                        <th class="text-start">Corrida (arquivo)</th>
                                        <th class="col-pontuacao-extra">Clássica</th>
                                        <th class="col-pontuacao-extra">Personalizada</th>
                                        <th class="col-pontuacao-extra">Invertida</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela">
                                    @foreach($model as $pilotoEquipe)
                                    @php
                                        $infoPiloto =  Resultado::where('user_id', Auth::user()->id)->where('pilotoEquipe_id', $pilotoEquipe->id)->where('corrida_id', $corrida->id)->first();
                                        $abandonou = isset($infoPiloto->flg_abandono) && $infoPiloto->flg_abandono == 'S';
                                    @endphp
                                        {{-- cells[1] precisa conter apenas o nome do piloto (usado na leitura dos JSONs) --}}
                                        <tr class="linha_resultados {{$pilotoEquipe->flg_super_corrida == 'S' ? 'd-none': ''}} {{ $abandonou ? 'linha-abandono' : '' }}" data-piloto-equipe-id="{{$pilotoEquipe->id}}" data-ordem-original="{{ $loop->index }}">
                                            <td class="rc-col-equipe" title="{{ $pilotoEquipe->equipe->nome }}"><img src="{{asset('images/'.$pilotoEquipe->equipe->imagem)}}" alt="{{ $pilotoEquipe->equipe->nome }}"></td>
                                            <td class="rc-col-piloto">{{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}</td>
                                            <td>
                                                <span> <input type="hidden" name="pilotoEquipe_id[]" value="{{$pilotoEquipe->id}}"></span>
                                                <input type="number" class="largada" name="largada[]" min="1" value="{{ $infoPiloto->largada ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" class="chegada" name="chegada[]" min="1" value="{{ $infoPiloto->chegada ?? '' }}">
                                            </td>
                                            <td>
                                                <label class="rc-dnf">
                                                    <input type="checkbox" class="flg_abandono" name="flg_abandono[]" value="{{ $pilotoEquipe->id }}" {{ $abandonou ? 'checked' : '' }}>
                                                    DNF
                                                </label>
                                            </td>
                                            <td>
                                                <span class="rc-pontos {{ (isset($infoPiloto->pontuacao) && $infoPiloto->pontuacao > 0) ? 'tem-pontos' : '' }}">{{ $infoPiloto->pontuacao ?? '-' }}</span>
                                            </td>
                                            <td class="rc-col-delta"><span class="rc-delta igual">&ndash;</span></td>
                                            <td class="rc-detalhe rc-col-detalhe"><span class="text-secondary">&ndash;</span></td>
                                            <td class="col-pontuacao-extra">{{ $infoPiloto->pontuacao_classica ?? '' }}</td>
                                            <td class="col-pontuacao-extra">{{ $infoPiloto->pontuacao_personalizada ?? '' }}</td>
                                            <td class="col-pontuacao-extra">{{ $infoPiloto->pontuacao_invertida ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ===================== LATERAL ===================== --}}
                <div class="rc-coluna">
                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-sliders"></i> Configuração</h5>
                        </div>
                        <div class="rc-card-corpo">
                            <div class="mb-3">
                                <label for="categoria" class="rc-rotulo">Categoria / pontuação</label>
                                <select name="categoria" id="categoria" class="form-control">
                                    <option value="F1" {{$corrida->categoria == 'F1' ? 'selected' : ''}}>Formula 1</option>
                                    <option value="F1_12_pontuam" {{$corrida->categoria == 'F1_12_pontuam' ? 'selected' : ''}}>Formula 1 (12 Pontuam)</option>
                                    <option value="F1_12_pontuam_incompleto" {{$corrida->categoria == 'F1_12_pontuam_incompleto' ? 'selected' : ''}}>Formula 1 (12 Pontuam) Incompleto</option>
                                    <option value="F1_todos_pontuam" {{$corrida->categoria == 'F1_todos_pontuam' ? 'selected' : ''}}>F1 (Todos Pontuam)</option>
                                    <option value="F1_todos_pontuam_incompleto" {{$corrida->categoria == 'F1_todos_pontuam_incompleto' ? 'selected' : ''}}>F1 (Todos Pontuam) Incompleto</option>
                                    <option value="F1Sprint" {{ $corrida->flg_sprint == 'S' ? 'selected': '' }} >Formula 1 (Sprint)</option>
                                    <option value="SC" {{ $corrida->flg_super_corrida == 'S' ? 'selected': '' }}>Super Sprint</option>
                                    <option value="Indy">Indy</option>
                                    <option value="FormulaE">Formula E</option>
                                    <option value="Stock1">Stock Car (Corrida 1)</option>
                                    <option value="Stock2">Stock Car (Corrida 2)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="condicao_id" class="rc-rotulo">Condição climática</label>
                                <select name="condicao_id" id="condicao_id" class="form-control">
                                    @foreach($condicoesClimaticas as $condicaoClimatica)
                                        <option value="{{$condicaoClimatica->id}}" @if(isset($corrida) && $corrida->condicao_id == $condicaoClimatica->id) selected @endif>{{$condicaoClimatica->descricao}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="volta_rapida" class="rc-rotulo"><i class="bi bi-stopwatch"></i> Volta mais rápida</label>
                                <select name="volta_rapida" id="volta_rapida" class="form-control">
                                    <option value="">selecione</option>
                                    @foreach($model as $pilotoEquipe)
                                       <option value="{{$pilotoEquipe->id}}" @if(isset($model) && $pilotoEquipe->id == $corrida->volta_rapida) selected @endif>{{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}</option>
                                    @endforeach
                                </select>
                                <div class="rc-volta-rapida-info" id="infoVoltaRapida"></div>
                            </div>
                            <div class="form-check mb-0 d-none">
                                <input type="checkbox" class="form-check-input" id="gerarGridLargada" name="gerarGridLargada" value="S">
                                <label class="form-check-label" for="gerarGridLargada">Gerar grid de largada</label>
                            </div>
                        </div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-journal-text"></i> Observações</h5>
                        </div>
                        <div class="rc-card-corpo">
                            <textarea class="form-control" name="observacoes" id="observacoes" rows="5" placeholder="Anotações sobre a corrida...">{{ trim($corrida->observacoes ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-images"></i> Arquivos da corrida</h5>
                        </div>
                        <div class="rc-card-corpo">
                            <input type="file" id="imagens_corrida" name="imagens_corrida[]" class="form-control form-control-sm" multiple>
                            @if($possuiArquivos)
                                <div class="d-flex align-items-center justify-content-between mt-2 gap-2">
                                    <span class="rc-card-subtitulo"><i class="bi bi-info-circle"></i> Corrida já possui arquivos salvos</span>
                                    <button type="button" class="btn btn-rc-mini" data-toggle="modal" data-target="#photoModal">
                                        <i class="bi bi-eye"></i> Visualizar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== ANÁLISE DA CORRIDA (JSON de chegada) ===================== --}}
            {{-- ===================== CLASSIFICAÇÃO (JSON de largada / grid invertido) ===================== --}}
            <div class="rc-card d-none" id="cardClassificacao">
                <div class="rc-card-cabecalho">
                    <h5 class="rc-card-titulo"><i class="bi bi-stopwatch"></i> Treino e classificação</h5>
                    <div class="d-flex gap-2 flex-wrap" id="abasTreino"></div>
                </div>
                <div class="rc-card-corpo">
                    <div class="rc-card-subtitulo mb-2" id="poleInfo"></div>
                    <p class="rc-resumo-texto" id="resumoTreino"></p>
                    <div class="rc-kpis rc-kpis-treino" id="kpisTreino"></div>
                    <div class="rc-destaques mb-3" id="destaquesTreino"></div>
                </div>
                <div class="rc-wrap-rolagem">
                    <table class="rc-analise-tabela" id="tabelaQualy"></table>
                </div>
                <div class="rc-card-corpo rc-como-ler">
                    <strong><i class="bi bi-info-circle"></i> Como ler:</strong>
                    <span><b>Dif. 1º</b> = quanto ficou atrás do mais rápido.</span>
                    <span><b>Volta ideal</b> = soma dos melhores setores do piloto; a diferença para a melhor volta é o tempo que ele <b>deixou na pista</b>.</span>
                    <span><b>Voltas anuladas</b> = corte de pista ou volta de saída/entrada dos boxes (não valem tempo).</span>
                    <span><b>Ritmo</b> = média das voltas válidas até 7% mais lentas que a melhor.</span>
                </div>
            </div>

            <div id="analiseCorrida">
                <div class="rc-card" id="analiseVazia">
                    <div class="rc-vazio">
                        <i class="bi bi-bar-chart-line"></i>
                        Carregue o <strong>JSON de chegada</strong> para ver a análise completa da corrida:<br>
                        pódio, condições, ritmo, setores, estratégia de pneus, evolução das posições e volta a volta.
                    </div>
                </div>

                <div class="d-none" id="analiseConteudo">
                    <div class="rc-grade-3 rc-grade-analise">
                        <div>
                            <div class="rc-card h-100 mb-0">
                                <div class="rc-card-cabecalho"><h5 class="rc-card-titulo"><i class="bi bi-trophy"></i> Pódio</h5></div>
                                <div class="rc-card-corpo"><div class="rc-podio" id="podio"></div></div>
                            </div>
                        </div>
                        <div>
                            <div class="rc-card h-100 mb-0">
                                <div class="rc-card-cabecalho"><h5 class="rc-card-titulo"><i class="bi bi-cloud-sun"></i> Condições</h5></div>
                                <div class="rc-card-corpo"><dl class="rc-lista" id="listaCondicoes"></dl></div>
                            </div>
                        </div>
                        <div>
                            <div class="rc-card h-100 mb-0">
                                <div class="rc-card-cabecalho"><h5 class="rc-card-titulo"><i class="bi bi-gear"></i> Sessão</h5></div>
                                <div class="rc-card-corpo"><dl class="rc-lista" id="listaSessao"></dl></div>
                            </div>
                        </div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho"><h5 class="rc-card-titulo"><i class="bi bi-stars"></i> Destaques</h5></div>
                        <div class="rc-card-corpo"><div class="rc-destaques" id="destaques"></div></div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-table"></i> Classificação completa</h5>
                            <span class="rc-card-subtitulo">Clique em um piloto para destacá-lo no gráfico (até 4)</span>
                        </div>
                        <div class="rc-wrap-rolagem">
                            <table class="rc-analise-tabela" id="tabelaClassificacao"></table>
                        </div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-graph-down"></i> Evolução das posições</h5>
                            <div class="rc-legenda" id="legendaPosicoes"></div>
                        </div>
                        <div class="rc-card-corpo">
                            <div class="rc-grafico-wrap"><canvas id="graficoPosicoes" aria-label="Posição de cada piloto ao final de cada volta" role="img"></canvas></div>
                        </div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-circle-half"></i> Estratégia de pneus</h5>
                            <div class="rc-legenda">
                                <span><i class="amostra" style="background:#d03b3b"></i> S Macio</span>
                                <span><i class="amostra" style="background:#fab219"></i> M Médio</span>
                                <span><i class="amostra" style="background:#f0efec"></i> H Duro</span>
                                <span><i class="bi bi-x-octagon-fill" style="color:#d03b3b"></i> Abandono</span>
                            </div>
                        </div>
                        <div class="rc-card-corpo" id="estrategiaPneus"></div>
                    </div>

                    <div class="rc-card">
                        <div class="rc-card-cabecalho">
                            <h5 class="rc-card-titulo"><i class="bi bi-grid-3x3"></i> Volta a volta</h5>
                            <div class="rc-legenda">
                                <span class="rc-roxo"><i class="bi bi-lightning-charge-fill"></i> Mais rápida da corrida</span>
                                <span class="rc-verde">Melhor do piloto</span>
                                <span><i class="amostra-linha" style="background:#fab219"></i> Troca de pneus</span>
                                <span><i class="bi bi-flag-fill" style="color:#fab219"></i> Volta lenta do pelotão</span>
                            </div>
                        </div>
                        <div class="rc-wrap-rolagem">
                            <table class="rc-analise-tabela rc-voltas" id="tabelaVoltas"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rc-acoes">
            <span class="rc-card-subtitulo"><span id="resumoAcoes">Revise os dados antes de salvar.</span><span id="resumoSessoes" class="rc-banco-pendente"></span></span>
            <div class="d-flex gap-2">
                <a href="{{route('temporadas.index')}}" class="btn btn-rc-sec"><i class="bi bi-arrow-left"></i> Voltar</a>
                <button type="submit" class="btn btn-rc"><i class="bi bi-check2"></i> Salvar</button>
            </div>
        </div>
    </form>
</div>

   @include('site.resultados._modal')
@endsection
<script type="module">
document.getElementById('inputFileLargada').addEventListener('change', function(event) {
        // console.log('funcionou largada')
        const arquivo = event.target.files[0];
        const leitor = new FileReader();

        leitor.onload = function(e) {
            const conteudo = e.target.result;
            let objetoJSON = JSON.parse(conteudo);
            gerarResultadoLargada(objetoJSON)
        };

        leitor.readAsText(arquivo);
    });

    document.getElementById('inputFileChegada').addEventListener('change', function(event) {
        // console.log('funcionou chegada')
        const arquivo = event.target.files[0];
        const leitor = new FileReader();

        leitor.onload = function(e) {
            const conteudo = e.target.result;
            let objetoJSON = JSON.parse(conteudo);
            gerarResultadoChegada(objetoJSON)
        };

        leitor.readAsText(arquivo);
    });

    document.getElementById('inputFileLargadaGridInvertido').addEventListener('change', function(event) {
        // console.log('funcionou chegada')
        const arquivo = event.target.files[0];
        const leitor = new FileReader();

        leitor.onload = function(e) {
            const conteudo = e.target.result;
            let objetoJSON = JSON.parse(conteudo);
            gerarResultadoGridInvertido(objetoJSON)
        };

        leitor.readAsText(arquivo);
    });

    /* =====================================================================
     * CHEGADA - leitura do JSON de resultado (Assetto Corsa / Content Manager)
     * ---------------------------------------------------------------------
     * Classificacao por tempo de pista:
     *   1) quem completou mais voltas fica na frente;
     *   2) com o mesmo numero de voltas, menor tempo total (soma das voltas) vence.
     * Abandono: o piloto que nao completou todas as voltas E cujo tempo total
     * e menor que o tempo do vencedor parou antes da bandeirada (abandonou).
     * Retardatario (tomou volta) cruza a linha depois do vencedor, entao o
     * tempo total dele e maior que o do vencedor -> terminou a corrida.
     * Quem abandonou recebe a posicao real (por voltas/tempo) e nao "NC".
     * ===================================================================== */

    // INICIO-ANALISE-CORRIDA (funcoes puras, sem acesso ao DOM)
    function normalizarNome(nome) {
        return String(nome || '')
            .normalize('NFD').replace(/[̀-ͯ]/g, '') // retira acentos
            .replace(/\s+/g, ' ')
            .trim()
            .toUpperCase();
    }

    function formatarTempo(ms) {
        if (ms === null || ms === undefined || isNaN(ms)) return '-';
        ms = Math.abs(Math.round(ms));
        const horas = Math.floor(ms / 3600000);
        const minutos = Math.floor((ms % 3600000) / 60000);
        const segundos = Math.floor((ms % 60000) / 1000);
        const milesimos = String(ms % 1000).padStart(3, '0');
        if (horas > 0) return horas + ':' + String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0') + '.' + milesimos;
        if (minutos > 0) return minutos + ':' + String(segundos).padStart(2, '0') + '.' + milesimos;
        return segundos + '.' + milesimos;
    }

    function lerIni(textoIni) {
        const ini = {};
        let secao = null;
        String(textoIni || '').split(/\r?\n/).forEach(linha => {
            linha = linha.trim();
            if (!linha || linha.startsWith(';')) return;
            const matchSecao = linha.match(/^\[(.+)\]$/);
            if (matchSecao) { secao = matchSecao[1]; ini[secao] = ini[secao] || {}; return; }
            const pos = linha.indexOf('=');
            if (secao && pos > -1) ini[secao][linha.substring(0, pos).trim()] = linha.substring(pos + 1).trim();
        });
        return ini;
    }

    function descreverClima(nomeClima) {
        const nome = String(nomeClima || '').toLowerCase();
        const mapa = [
            ['thunder', 'Tempestade'], ['storm', 'Tempestade'], ['heavy_rain', 'Chuva forte'],
            ['light_rain', 'Chuva fraca'], ['drizzle', 'Garoa'], ['rain', 'Chuva'],
            ['snow', 'Neve'], ['sleet', 'Granizo'], ['heavy_fog', 'Neblina forte'], ['fog', 'Neblina'],
            ['mist', 'Névoa'], ['haze', 'Névoa seca'], ['overcast', 'Encoberto'],
            ['heavy_clouds', 'Muito nublado'], ['mid_clouds', 'Parcialmente nublado'],
            ['light_clouds', 'Poucas nuvens'], ['few_clouds', 'Poucas nuvens'], ['cloud', 'Nublado'],
            ['windy', 'Ventania'], ['clear', 'Céu limpo'], ['sunny', 'Ensolarado'],
        ];
        const item = mapa.find(m => nome.includes(m[0]));
        return item ? item[1] : (nomeClima || 'Não informado');
    }

    function climaMolhado(nomeClima) {
        return /rain|drizzle|storm|thunder|snow|sleet/i.test(String(nomeClima || ''));
    }

    function direcaoVento(graus) {
        if (graus === undefined || graus === null || isNaN(graus)) return '';
        const direcoes = ['N', 'NE', 'L', 'SE', 'S', 'SO', 'O', 'NO'];
        return direcoes[Math.round((Number(graus) % 360) / 45) % 8];
    }

    function numeroOuNulo(valor) {
        return (valor === undefined || valor === null || valor === '' || isNaN(valor)) ? null : Number(valor);
    }

    function mediana(valores) {
        const v = valores.filter(x => x > 0).sort((a, b) => a - b);
        if (!v.length) return null;
        const m = Math.floor(v.length / 2);
        return v.length % 2 ? v[m] : (v[m - 1] + v[m]) / 2;
    }

    function desvioPadrao(valores) {
        if (valores.length < 2) return null;
        const media = valores.reduce((s, v) => s + v, 0) / valores.length;
        return Math.sqrt(valores.reduce((s, v) => s + Math.pow(v - media, 2), 0) / (valores.length - 1));
    }

    function nomeCarro(codigo) {
        return String(codigo || '')
            .replace(/^[a-z]{2,4}_(?=\d{4}_)/i, '')   // prefixo do mod (ex.: cim_)
            .replace(/_v\d+$/i, '')
            .split('_')
            .map(p => p.length <= 3 && !/^\d+$/.test(p) ? p.toUpperCase() : p.charAt(0).toUpperCase() + p.slice(1))
            .join(' ');
    }

    function analisarCorrida(json) {
        const sessoes = json['sessions'] || [];
        const jogadores = json['players'] || [];

        // sessao de corrida: "Quick Race" / type 3 / qualquer uma com raceResult
        const sessao = sessoes.find(s => s['name'] == 'Quick Race' && (s['laps'] || []).length)
            || sessoes.find(s => s['type'] == 3 && (s['laps'] || []).length);
        if (!sessao) throw new Error('Nenhuma sessão de corrida encontrada no arquivo.');

        const ini = lerIni(json['__raceIni']);
        let quickDrive = {};
        try { quickDrive = json['__quickDrive'] ? JSON.parse(json['__quickDrive']) : {}; } catch (e) { quickDrive = {}; }
        let assistencias = {};
        try { assistencias = quickDrive['AssistsData'] ? JSON.parse(quickDrive['AssistsData']) : {}; } catch (e) { assistencias = {}; }
        let pistaPropriedades = {};
        try { pistaPropriedades = quickDrive['TrackPropertiesData'] ? JSON.parse(quickDrive['TrackPropertiesData']) : {}; } catch (e) { pistaPropriedades = {}; }

        const voltas = sessao['laps'] || [];
        const totalVoltasCorrida = Number(sessao['lapsCount']) || Math.max(0, ...(sessao['lapstotal'] || [0]));

        // voltas "lentas do pelotao" (safety car / bandeira amarela / acidente): mediana da volta muito acima do normal
        const maxVoltaIndice = voltas.reduce((m, v) => Math.max(m, Number(v['lap'])), -1);
        const medianaPorVolta = [];
        for (let k = 0; k <= maxVoltaIndice; k++) medianaPorVolta[k] = mediana(voltas.filter(v => v['lap'] == k).map(v => Number(v['time'])));
        const medianaGeral = mediana(medianaPorVolta.filter((m, k) => k > 0 && m));
        const voltasNeutralizadas = [];
        medianaPorVolta.forEach((m, k) => { if (k > 0 && m && medianaGeral && m > medianaGeral * 1.5) voltasNeutralizadas.push(k + 1); });

        const carros = jogadores.map((jogador, indice) => {
            const voltasCarro = voltas.filter(v => v['car'] == indice).sort((a, b) => a['lap'] - b['lap']);
            const tempoTotal = voltasCarro.reduce((soma, v) => soma + (Number(v['time']) || 0), 0);
            const voltasCompletadas = (sessao['lapstotal'] && sessao['lapstotal'][indice] !== undefined)
                ? Number(sessao['lapstotal'][indice])
                : voltasCarro.length;

            // tempo acumulado ao fim de cada volta
            const acumulado = [];
            voltasCarro.reduce((soma, v, i) => { acumulado[i] = soma + (Number(v['time']) || 0); return acumulado[i]; }, 0);

            // melhor volta: bestLaps do arquivo ou calculada pelas voltas
            let melhorVolta = (sessao['bestLaps'] || []).find(b => b['car'] == indice && b['time'] > 0);
            if (!melhorVolta) {
                const v = voltasCarro.filter(v => v['time'] > 0).sort((a, b) => a['time'] - b['time'])[0];
                if (v) melhorVolta = { car: indice, time: v['time'], lap: v['lap'] };
            }

            // melhores setores
            const melhoresSetores = [];
            voltasCarro.forEach(v => (v['sectors'] || []).forEach((s, i) => {
                if (s > 0 && (melhoresSetores[i] === undefined || s < melhoresSetores[i])) melhoresSetores[i] = s;
            }));

            // stints de pneus e paradas (a volta de entrada nos boxes e a ultima com o composto anterior)
            const stints = [];
            voltasCarro.forEach((v, i) => {
                const pneu = v['tyre'] ? siglaPneu(v['tyre']) : '?';
                const atual = stints[stints.length - 1];
                if (atual && atual.pneu === pneu) { atual.voltas++; }
                else stints.push({ pneu: pneu, inicio: i + 1, voltas: 1 });
            });
            const paradas = stints.slice(1).map(s => ({ volta: s.inicio - 1, de: null, para: s.pneu }));
            paradas.forEach((p, i) => { p.de = stints[i].pneu; });
            const voltasBox = new Set();
            paradas.forEach(p => { voltasBox.add(p.volta); voltasBox.add(p.volta + 1); });

            // ritmo: voltas limpas (sem largada, sem box, sem volta neutralizada, dentro de 107% da melhor)
            const melhor = melhorVolta ? Number(melhorVolta['time']) : null;
            const voltasLimpas = voltasCarro
                .filter((v, i) => i > 0 && !voltasBox.has(i + 1) && !voltasNeutralizadas.includes(i + 1) && melhor && v['time'] <= melhor * 1.07)
                .map(v => Number(v['time']));
            const ritmoMedio = voltasLimpas.length ? voltasLimpas.reduce((s, v) => s + v, 0) / voltasLimpas.length : null;

            const configIni = ini['CAR_' + indice] || {};

            return {
                indice: indice,
                nome: String(jogador['name'] || '').replace(/\s+/g, ' ').trim(),
                nomeNormalizado: normalizarNome(jogador['name']),
                carro: jogador['car'],
                carroNome: nomeCarro(jogador['car']),
                skin: jogador['skin'] || null,
                nacionalidade: configIni['NATION_CODE'] || null,
                agressividade: numeroOuNulo(configIni['AI_AGGRESSION']),
                nivelIA: numeroOuNulo(configIni['AI_LEVEL']),
                lastro: numeroOuNulo(configIni['BALLAST']),
                restritor: numeroOuNulo(configIni['RESTRICTOR']),
                ehJogador: indice === 0 && configIni['AI_LEVEL'] === undefined,
                voltas: voltasCarro.map((v, i) => ({
                    numero: i + 1,
                    tempo: Number(v['time']) || 0,
                    setores: v['sectors'] || [],
                    cortes: Number(v['cuts']) || 0,
                    pneu: v['tyre'] || null,
                })),
                acumulado: acumulado,
                voltasCompletadas: voltasCompletadas,
                tempoTotal: tempoTotal,
                melhorVolta: melhor,
                numeroMelhorVolta: melhorVolta ? Number(melhorVolta['lap']) + 1 : null, // lap no arquivo comeca em 0
                melhoresSetores: melhoresSetores,
                stints: stints,
                pneus: stints.map(s => s.pneu),
                paradas: paradas,
                ritmoMedio: ritmoMedio,
                consistencia: voltasLimpas.length >= 5 ? desvioPadrao(voltasLimpas) : null,
                voltasLimpas: voltasLimpas.length,
                cortes: voltasCarro.reduce((s, v) => s + (Number(v['cuts']) || 0), 0),
            };
        });

        // ordenacao por tempo de pista
        carros.sort((a, b) => {
            if (b.voltasCompletadas !== a.voltasCompletadas) return b.voltasCompletadas - a.voltasCompletadas;
            if (a.tempoTotal !== b.tempoTotal) return a.tempoTotal - b.tempoTotal;
            return a.indice - b.indice;
        });

        const vencedor = carros[0] || null;
        const tempoVencedor = vencedor ? vencedor.tempoTotal : 0;

        carros.forEach((carro, i) => {
            carro.posicao = i + 1;
            carro.terminou = carro.voltasCompletadas >= totalVoltasCorrida
                || (carro.voltasCompletadas > 0 && carro.tempoTotal >= tempoVencedor);
            carro.abandonou = !carro.terminou;
            carro.voltaAbandono = carro.abandonou ? carro.voltasCompletadas + 1 : null;
            carro.voltasAtras = vencedor ? vencedor.voltasCompletadas - carro.voltasCompletadas : 0;
            carro.difLider = (i > 0 && carro.voltasAtras === 0) ? carro.tempoTotal - tempoVencedor : null;
            const anterior = carros[i - 1];
            carro.intervalo = (anterior && anterior.voltasCompletadas === carro.voltasCompletadas) ? carro.tempoTotal - anterior.tempoTotal : null;
            if (i === 0) carro.diferenca = formatarTempo(carro.tempoTotal);
            else if (carro.abandonou) carro.diferenca = 'DNF';
            else if (carro.voltasAtras > 0) carro.diferenca = '+' + carro.voltasAtras + (carro.voltasAtras > 1 ? ' voltas' : ' volta');
            else carro.diferenca = '+' + formatarTempo(carro.difLider);
        });

        // posicao ao final de cada volta (entre quem completou aquela volta, pelo tempo acumulado)
        const totalColunas = Math.max(0, ...carros.map(c => c.voltas.length));
        carros.forEach(c => { c.posicaoPorVolta = new Array(totalColunas).fill(null); });
        for (let k = 0; k < totalColunas; k++) {
            carros.filter(c => c.acumulado[k] !== undefined)
                .sort((a, b) => a.acumulado[k] - b.acumulado[k])
                .forEach((c, p) => { c.posicaoPorVolta[k] = p + 1; });
        }
        carros.forEach(c => { c.posicaoVolta1 = c.posicaoPorVolta[0]; });

        // volta mais rapida da corrida e melhores setores gerais
        const comVolta = carros.filter(c => c.melhorVolta);
        const voltaMaisRapida = comVolta.length ? comVolta.reduce((m, c) => (c.melhorVolta < m.melhorVolta ? c : m)) : null;
        const numeroSetores = Math.max(0, ...carros.map(c => c.melhoresSetores.length));
        const melhoresSetores = [];
        for (let s = 0; s < numeroSetores; s++) {
            let melhor = null;
            carros.forEach(c => { if (c.melhoresSetores[s] && (!melhor || c.melhoresSetores[s] < melhor.tempo)) melhor = { tempo: c.melhoresSetores[s], carro: c }; });
            melhoresSetores.push(melhor);
        }
        const voltaIdeal = melhoresSetores.length && melhoresSetores.every(Boolean) ? melhoresSetores.reduce((s, m) => s + m.tempo, 0) : null;

        // informacoes da sessao (race.ini e quickDrive do Content Manager)
        const temperatura = ini['TEMPERATURE'] || {};
        const vento = ini['WIND'] || {};
        const corrida = ini['RACE'] || {};
        const sessaoIni = Object.keys(ini).filter(k => /^SESSION_\d+$/.test(k)).map(k => ini[k]).find(s => s['TYPE'] == '3' || s['NAME'] == sessao['name']) || {};
        const pistaDinamica = ini['DYNAMIC_TRACK'] || {};
        const iluminacao = ini['LIGHTING'] || {};
        const nomeClima = (ini['WEATHER'] || {})['NAME'] || '';

        let horario = null;
        if (numeroOuNulo(quickDrive['Time']) !== null) {
            const seg = Number(quickDrive['Time']);
            horario = String(Math.floor(seg / 3600)).padStart(2, '0') + ':' + String(Math.floor((seg % 3600) / 60)).padStart(2, '0');
        }
        let temperaturaAr = numeroOuNulo(temperatura['AMBIENT']);
        if (temperaturaAr === null && numeroOuNulo(quickDrive['Temperature']) !== null) temperaturaAr = Math.round(quickDrive['Temperature']);

        const agressividades = carros.map(c => c.agressividade).filter(a => a !== null);
        const jogador = carros.find(c => c.ehJogador) || null;

        return {
            pista: json['track'] || null,
            pistaCodigo: corrida['TRACK'] || null,
            pistaLayout: corrida['CONFIG_TRACK'] || null,
            pistaCoordenadas: (iluminacao['__TRACK_GEOTAG_LAT'] && iluminacao['__TRACK_GEOTAG_LONG']) ? iluminacao['__TRACK_GEOTAG_LAT'] + ', ' + iluminacao['__TRACK_GEOTAG_LONG'] : null,
            sessaoNome: sessao['name'] || null,
            totalVoltas: totalVoltasCorrida,
            tempoCorrida: tempoVencedor,
            mediaVoltaVencedor: vencedor && vencedor.voltasCompletadas ? tempoVencedor / vencedor.voltasCompletadas : null,
            vencedor: vencedor,
            jogador: jogador,
            voltaMaisRapida: voltaMaisRapida,
            melhoresSetores: melhoresSetores,
            voltaIdeal: voltaIdeal,
            voltasNeutralizadas: voltasNeutralizadas,
            classificacao: carros,
            abandonos: carros.filter(c => c.abandonou),
            totalCarros: carros.length,
            totalParadas: carros.reduce((s, c) => s + c.paradas.length, 0),
            clima: {
                codigo: nomeClima,
                descricao: descreverClima(nomeClima),
                molhado: climaMolhado(nomeClima),
                temperaturaAr: temperaturaAr,
                temperaturaPista: numeroOuNulo(temperatura['ROAD']),
                ventoMin: numeroOuNulo(vento['SPEED_KMH_MIN']),
                ventoMax: numeroOuNulo(vento['SPEED_KMH_MAX']),
                ventoDirecao: numeroOuNulo(vento['DIRECTION_DEG']),
                condicoesReais: quickDrive['RealConditions'] === undefined ? null : !!quickDrive['RealConditions'],
            },
            horario: horario,
            multiplicadorTempo: numeroOuNulo(iluminacao['TIME_MULT']),
            dataCorrida: quickDrive['dtv'] ? String(quickDrive['dtv']).substring(0, 10) : null,
            aderenciaInicial: numeroOuNulo(pistaDinamica['SESSION_START']),
            ganhoAderencia: numeroOuNulo(pistaDinamica['LAP_GAIN']),
            estadoPista: pistaPropriedades['d'] || null,
            nivelIA: numeroOuNulo(corrida['AI_LEVEL']),
            agressividadeMedia: agressividades.length ? agressividades.reduce((s, a) => s + a, 0) / agressividades.length : null,
            agressividadeMin: agressividades.length ? Math.min(...agressividades) : null,
            agressividadeMax: agressividades.length ? Math.max(...agressividades) : null,
            penalidades: corrida['PENALTIES'] !== undefined ? corrida['PENALTIES'] == '1' : null,
            penalidadeLargada: numeroOuNulo(corrida['JUMP_START_PENALTY']),
            setupFixo: corrida['FIXED_SETUP'] !== undefined ? corrida['FIXED_SETUP'] == '1' : null,
            largadaJogador: numeroOuNulo(sessaoIni['STARTING_POSITION']),
            assistencias: assistencias,
            extras: json['extras'] || [],
        };
    }
    // FIM-ANALISE-CORRIDA

    /* ============================ RENDERIZACAO ============================ */
    const CORES_DESTAQUE = ['#3987e5', '#d95926', '#199e70', '#c98500']; // paleta categorica validada (tema escuro)
    const estadoAnalise = { dados: null, destacados: [], grafico: null };

    /* ---------- Equipe / piloto cadastrados no sistema (logo, cor, foto) ---------- */
    const MAPA_PILOTOS = (() => {
        const mapa = {};
        (window.INFO_PILOTOS || []).forEach(p => {
            const chave = normalizarNome(p.nome);
            const preferido = !!p.super === !!window.CORRIDA_SUPER;
            if (!mapa[chave] || (preferido && !!mapa[chave].super !== !!window.CORRIDA_SUPER)) mapa[chave] = p;
        });
        return mapa;
    })();
    function infoPiloto(nomeNormalizado) { return MAPA_PILOTOS[nomeNormalizado] || null; }
    function corDaEquipe(info) {
        const cor = info ? String(info.cor || '').trim() : '';
        return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(cor) ? cor : '#5a5a57';
    }
    // nome da equipe cadastrada; sem cadastro, o carro do arquivo
    function nomeEquipe(c) { const i = infoPiloto(c.nomeNormalizado); return i && i.equipe ? i.equipe : (c.carroNome || ''); }
    // logo + nome da equipe
    function htmlEquipe(c, soLogo) {
        const i = infoPiloto(c.nomeNormalizado);
        if (!i) return soLogo ? '' : '<span class="rc-equipe sem-cadastro" title="Piloto sem cadastro nesta temporada">' + escaparHtml(c.carroNome || '') + '</span>';
        return '<span class="rc-equipe" title="' + escaparHtml(i.equipe) + '">' + (i.logo ? '<img src="' + escaparHtml(i.logo) + '" alt="">' : '') + (soLogo ? '' : escaparHtml(i.equipe)) + '</span>';
    }
    // marcador de cor da equipe (tabelas com muito conteúdo)
    function htmlCorEquipe(c) {
        return '<span class="rc-cor-equipe" style="background:' + corDaEquipe(infoPiloto(c.nomeNormalizado)) + '" title="' + escaparHtml(nomeEquipe(c)) + '"></span>';
    }

    // composto -> primeira letra (Medium -> M, Hard -> H, Soft -> S, Intermediate -> I, Wet -> W)
    function siglaPneu(pneu) {
        const texto = String(pneu || '').trim();
        const mapa = { soft: 'S', supersoft: 'S', ultrasoft: 'S', hypersoft: 'S', medium: 'M', hard: 'H', intermediate: 'I', inter: 'I', wet: 'W', rain: 'W' };
        return mapa[texto.toLowerCase().replace(/[^a-z]/g, '')] || texto.charAt(0).toUpperCase() || '?';
    }
    function htmlPneu(pneu) {
        const sigla = siglaPneu(pneu);
        return '<span class="rc-pneu ' + escaparHtml(sigla) + '" title="' + escaparHtml(pneu) + '">' + escaparHtml(sigla) + '</span>';
    }

    function escaparHtml(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function simNao(valor) {
        if (valor === null || valor === undefined) return '-';
        return valor ? 'Sim' : 'Não';
    }

    function textoVento(clima) {
        if (clima.ventoMax === null) return '-';
        if (clima.ventoMax === 0) return 'Sem vento';
        const faixa = (clima.ventoMin !== null && clima.ventoMin !== clima.ventoMax) ? clima.ventoMin + '-' + clima.ventoMax : clima.ventoMax;
        return faixa + ' km/h <small>' + direcaoVento(clima.ventoDirecao) + (clima.ventoDirecao !== null ? ' (' + clima.ventoDirecao + '°)' : '') + '</small>';
    }

    function linhasFormulario() {
        return Array.from(document.getElementById('tabela').rows);
    }

    // grid de largada informado no formulario (nome normalizado -> posicao)
    function gridDoFormulario() {
        const grid = {};
        linhasFormulario().forEach(l => {
            const v = parseInt(l.cells[2].querySelector('input[type="number"]').value);
            if (v > 0) grid[normalizarNome(l.cells[1].textContent)] = v;
        });
        return grid;
    }

    function gridDoPiloto(c, grid) {
        if (grid[c.nomeNormalizado]) return grid[c.nomeNormalizado];
        if (c.ehJogador && estadoAnalise.dados && estadoAnalise.dados.largadaJogador) return estadoAnalise.dados.largadaJogador;
        return null;
    }

    function htmlDelta(largada, chegada) {
        if (!(largada > 0) || !(chegada > 0)) return '<span class="rc-delta igual">&ndash;</span>';
        const d = largada - chegada;
        if (d > 0) return '<span class="rc-delta sobe" title="Ganhou ' + d + ' posições"><i class="bi bi-caret-up-fill"></i>' + d + '</span>';
        if (d < 0) return '<span class="rc-delta desce" title="Perdeu ' + (-d) + ' posições"><i class="bi bi-caret-down-fill"></i>' + (-d) + '</span>';
        return '<span class="rc-delta igual" title="Manteve a posição">=</span>';
    }

    function atualizarDeltasFormulario() {
        linhasFormulario().forEach(l => {
            const largada = parseInt(l.cells[2].querySelector('input[type="number"]').value);
            const chegada = parseInt(l.cells[3].querySelector('input[type="number"]').value);
            l.cells[6].innerHTML = htmlDelta(largada, chegada);
            const dnf = l.cells[4].querySelector('input[type="checkbox"]').checked;
            l.classList.toggle('linha-abandono', dnf);
        });
        const preenchidos = linhasFormulario().filter(l => !l.classList.contains('d-none') && l.cells[3].querySelector('input').value !== '').length;
        const visiveis = linhasFormulario().filter(l => !l.classList.contains('d-none')).length;
        const dnfs = linhasFormulario().filter(l => !l.classList.contains('d-none') && l.cells[4].querySelector('input[type="checkbox"]').checked).length;
        document.getElementById('resumoAcoes').textContent = preenchidos + '/' + visiveis + ' chegadas preenchidas · ' + dnfs + ' abandono(s)';
    }

    function ordenarFormulario(criterio) {
        const tbody = document.getElementById('tabela');
        const linhas = linhasFormulario();
        const valor = (l, i) => {
            const v = parseInt(l.cells[i].querySelector('input[type="number"]').value);
            return v > 0 ? v : 9999;
        };
        linhas.sort((a, b) => {
            if (criterio === 'chegada') return valor(a, 3) - valor(b, 3) || a.dataset.ordemOriginal - b.dataset.ordemOriginal;
            if (criterio === 'largada') return valor(a, 2) - valor(b, 2) || a.dataset.ordemOriginal - b.dataset.ordemOriginal;
            return a.dataset.ordemOriginal - b.dataset.ordemOriginal;
        });
        linhas.forEach(l => tbody.appendChild(l)); // mesma linha leva todos os inputs juntos: arrays do POST continuam alinhados
        document.querySelectorAll('[data-ordenar]').forEach(b => b.classList.toggle('ativo', b.dataset.ordenar === criterio));
    }

    function atualizarSelect2(select) {
        if (window.jQuery && jQuery(select).data("select2")) jQuery(select).trigger("change");
    }

    function marcarUpload(idTile, sucesso, texto) {
        const tile = document.getElementById(idTile);
        tile.classList.remove('carregado', 'erro');
        tile.classList.add(sucesso ? 'carregado' : 'erro');
        tile.querySelector('.rc-upload-status').innerHTML = texto;
    }

    // aplicarNoFormulario = false: apenas exibe a análise (JSON já salvo no banco), sem alterar os valores do formulário
    function gerarResultadoChegada(resultados, aplicarNoFormulario = true) {
        let dados;
        try {
            dados = analisarCorrida(resultados);
        } catch (erro) {
            console.error(erro);
            marcarUpload('tileChegada', false, '<i class="bi bi-x-circle"></i> ' + escaparHtml(erro.message));
            return;
        }
        estadoAnalise.dados = dados;

        const porNome = {};
        dados.classificacao.forEach(c => { porNome[c.nomeNormalizado] = c; });
        const encontrados = new Set();
        const semCorrespondencia = [];

        linhasFormulario().forEach(linha => {
            const nomeTabela = linha.cells[1].textContent.trim();
            const piloto = porNome[normalizarNome(nomeTabela)];
            linha.classList.remove('linha-preenchida', 'linha-volta-rapida');
            if (!piloto) {
                if (!linha.classList.contains('d-none')) semCorrespondencia.push(nomeTabela);
                linha.cells[7].innerHTML = '<span class="text-secondary">não está no arquivo</span>';
                return;
            }
            encontrados.add(piloto.nomeNormalizado);
            if (aplicarNoFormulario) {
                linha.cells[3].querySelector('input[type="number"]').value = piloto.posicao; // posicao real, inclusive de quem abandonou
                linha.cells[4].querySelector('input[type="checkbox"]').checked = piloto.abandonou;
            }
            linha.classList.add('linha-preenchida');
            const ehMaisRapida = dados.voltaMaisRapida && dados.voltaMaisRapida.indice === piloto.indice;
            if (ehMaisRapida) linha.classList.add('linha-volta-rapida');
            linha.cells[7].innerHTML =
                '<span class="rc-detalhe-volta">' + piloto.voltasCompletadas + ' V</span> · ' +
                (piloto.melhorVolta ? '<span class="' + (ehMaisRapida ? 'rc-roxo' : '') + '">' + (ehMaisRapida ? '<i class="bi bi-lightning-charge-fill"></i> ' : '') + formatarTempo(piloto.melhorVolta) + '</span> ' : '') +
                '· ' + piloto.pneus.map(p => htmlPneu(p)).join('') +
                (piloto.abandonou ? ' · <span class="rc-status dnf"><i class="bi bi-x-octagon-fill"></i> V' + piloto.voltaAbandono + '</span>' : '');
        });

        // volta mais rapida: preenche sozinho (se o mais rapido nao estiver no formulario, usa o mais rapido entre os que estao)
        const selectVoltaRapida = document.getElementById('volta_rapida');
        const opcoes = Array.from(selectVoltaRapida.options);
        const candidatos = dados.classificacao.filter(c => c.melhorVolta && encontrados.has(c.nomeNormalizado)).sort((a, b) => a.melhorVolta - b.melhorVolta);
        const avisos = [];
        let escolhido = null;
        for (const c of candidatos) {
            const opcao = opcoes.find(o => normalizarNome(o.textContent) === c.nomeNormalizado);
            if (opcao) {
                if (aplicarNoFormulario) selectVoltaRapida.value = opcao.value;
                escolhido = c;
                break;
            }
        }
        if (aplicarNoFormulario) atualizarSelect2(selectVoltaRapida); // o layout aplica select2 em todos os selects
        const infoVR = document.getElementById('infoVoltaRapida');
        if (escolhido) {
            infoVR.innerHTML = '<i class="bi bi-check-circle-fill rc-verde"></i> ' + (aplicarNoFormulario ? 'Preenchido pelo arquivo' : 'Arquivo salvo: ' + escaparHtml(escolhido.nome)) + ': <strong>' + formatarTempo(escolhido.melhorVolta) + '</strong> na volta ' + escolhido.numeroMelhorVolta;
            if (dados.voltaMaisRapida && escolhido.indice !== dados.voltaMaisRapida.indice) {
                avisos.push(['alerta', 'A volta mais rápida do arquivo foi de ' + escaparHtml(dados.voltaMaisRapida.nome) + ' (' + formatarTempo(dados.voltaMaisRapida.melhorVolta) + '), que não está no formulário. Selecionado o mais rápido entre os pilotos do formulário: ' + escaparHtml(escolhido.nome) + '.']);
            }
        } else {
            infoVR.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Nenhum piloto do arquivo encontrado na lista';
        }

        // condicao climatica: so altera se houver opcao compativel cadastrada
        const selectCondicao = document.getElementById('condicao_id');
        const termos = dados.clima.molhado ? ['CHUV', 'MOLHAD', 'WET', 'RAIN'] : ['SECO', 'DRY'];
        const opcaoClima = Array.from(selectCondicao.options).find(o => termos.some(t => normalizarNome(o.textContent).includes(t)));
        if (opcaoClima && aplicarNoFormulario) { selectCondicao.value = opcaoClima.value; atualizarSelect2(selectCondicao); }

        const foraDoForm = dados.classificacao.filter(c => !encontrados.has(c.nomeNormalizado))
            .map(c => '<span class="rc-aviso-chip"><b>' + c.posicao + 'º</b> ' + escaparHtml(c.nome) + '</span>');
        if (foraDoForm.length) {
            avisos.push(['alerta',
                '<strong>' + (foraDoForm.length === 1 ? '1 piloto do arquivo não está' : foraDoForm.length + ' pilotos do arquivo não estão') + ' no grid desta temporada</strong>' +
                '<span class="rc-aviso-detalhe">A chegada dele(s) não foi preenchida. Confira se o nome no arquivo é igual ao do cadastro.</span>' +
                '<div class="rc-aviso-lista">' + foraDoForm.join('') + '</div>']);
        }
        if (semCorrespondencia.length) {
            avisos.push(['info',
                '<strong>' + (semCorrespondencia.length === 1 ? '1 piloto do formulário não aparece' : semCorrespondencia.length + ' pilotos do formulário não aparecem') + ' no arquivo</strong>' +
                '<span class="rc-aviso-detalhe">Os valores dele(s) foram mantidos como estavam.</span>' +
                '<div class="rc-aviso-lista">' + semCorrespondencia.map(n => '<span class="rc-aviso-chip">' + escaparHtml(n) + '</span>').join('') + '</div>']);
        }
        document.getElementById('avisosChegada').innerHTML = avisos.map(a =>
            '<div class="rc-aviso ' + a[0] + '"><i class="bi ' + (a[0] === 'alerta' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle') + '"></i><div>' + a[1] + '</div></div>').join('');

        if (aplicarNoFormulario) {
            marcarUpload('tileChegada', true, '<i class="bi bi-check-circle"></i> ' + encontrados.size + ' pilotos preenchidos · ' + dados.abandonos.length + ' abandono(s)');
        }

        // destaques do grafico: podio + voce
        estadoAnalise.destacados = dados.classificacao.slice(0, 3).map(c => c.indice);
        if (dados.jogador && !estadoAnalise.destacados.includes(dados.jogador.indice)) estadoAnalise.destacados.push(dados.jogador.indice);
        estadoAnalise.destacados = estadoAnalise.destacados.slice(0, 4);

        ordenarFormulario('chegada');
        atualizarDeltasFormulario();
        renderizarAnalise();
    }

    function renderizarAnalise() {
        const dados = estadoAnalise.dados;
        if (!dados) return;
        const grid = gridDoFormulario();
        const vmr = dados.voltaMaisRapida;
        const v = dados.vencedor;
        const segundo = dados.classificacao[1];

        document.getElementById('analiseVazia').classList.add('d-none');
        document.getElementById('analiseConteudo').classList.remove('d-none');

        /* ---------- KPIs ---------- */
        const kpis = [
            ['bi-arrow-repeat', 'Voltas', dados.totalVoltas, dados.voltasNeutralizadas.length ? dados.voltasNeutralizadas.length + ' volta(s) lenta(s) do pelotão' : 'sem neutralizações', false],
            ['bi-stopwatch', 'Tempo de corrida', formatarTempo(dados.tempoCorrida), 'média ' + formatarTempo(dados.mediaVoltaVencedor) + ' por volta', false],
            ['bi-trophy', 'Vencedor', v ? escaparHtml(v.nome) : '-', v ? escaparHtml(nomeEquipe(v)) + (segundo && segundo.difLider !== null ? ' · ' + formatarTempo(segundo.difLider) + ' de vantagem' : '') : '', true],
            ['bi-lightning-charge', 'Volta mais rápida', vmr ? formatarTempo(vmr.melhorVolta) : '-', vmr ? escaparHtml(vmr.nome) + ' (' + escaparHtml(nomeEquipe(vmr)) + ') · volta ' + vmr.numeroMelhorVolta : '', false],
            ['bi-x-octagon', 'Abandonos', dados.abandonos.length, (dados.totalCarros - dados.abandonos.length) + ' de ' + dados.totalCarros + ' terminaram', false],
            ['bi-tools', 'Paradas nos boxes', dados.totalParadas, (dados.totalCarros ? (dados.totalParadas / dados.totalCarros).toFixed(1) : 0) + ' por carro', false],
        ];
        const kpiEl = document.getElementById('kpisCorrida');
        kpiEl.classList.remove('d-none');
        kpiEl.innerHTML = kpis.map(k =>
            '<div class="rc-kpi"><div class="rc-kpi-rotulo"><i class="bi ' + k[0] + '"></i> ' + k[1] + '</div>' +
            '<div class="rc-kpi-valor' + (k[4] ? ' menor' : '') + '">' + k[2] + '</div>' +
            '<div class="rc-kpi-extra">' + k[3] + '</div></div>').join('');

        /* ---------- Podio ---------- */
        const p = dados.classificacao;
        const ordemPodio = [[p[1], 'p2', 2], [p[0], 'p1', 1], [p[2], 'p3', 3]];
        document.getElementById('podio').innerHTML = ordemPodio.map(([c, cls, n]) => {
            if (!c) return '<div></div>';
            const info = infoPiloto(c.nomeNormalizado);
            return '<div class="rc-podio-item ' + cls + '" style="--cor-equipe:' + corDaEquipe(info) + '">' +
                '<div class="rc-podio-foto">' +
                    (info && info.foto ? '<img src="' + escaparHtml(info.foto) + '" alt="' + escaparHtml(c.nome) + '">' : '<i class="bi bi-person-fill"></i>') +
                    (info && info.logo ? '<img class="rc-podio-logo" src="' + escaparHtml(info.logo) + '" alt="' + escaparHtml(info.equipe) + '">' : '') +
                '</div>' +
                '<div class="rc-podio-nome">' + escaparHtml(c.nome) + (c.ehJogador ? '<span class="rc-voce">Você</span>' : '') + '</div>' +
                '<div class="rc-podio-equipe">' + escaparHtml(nomeEquipe(c)) + '</div>' +
                '<div class="rc-podio-dif">' + (n === 1 ? formatarTempo(c.tempoTotal) : c.diferenca) + '</div>' +
                '<div class="rc-podio-degrau">' + n + '</div></div>';
        }).join('');

        /* ---------- Condicoes / Sessao ---------- */
        const item = (rotulo, valor) => '<div><dt>' + rotulo + '</dt><dd>' + (valor === null || valor === undefined || valor === '' ? '-' : valor) + '</dd></div>';
        const c = dados.clima;
        document.getElementById('listaCondicoes').innerHTML =
            item('Clima', escaparHtml(c.descricao) + (c.codigo ? ' <small>' + escaparHtml(c.codigo) + '</small>' : '')) +
            item('Pista', c.molhado ? 'Molhada' : 'Seca') +
            item('Temp. do ar', c.temperaturaAr !== null ? c.temperaturaAr + '°C' : null) +
            item('Temp. da pista', c.temperaturaPista !== null ? c.temperaturaPista + '°C' : null) +
            item('Vento', textoVento(c)) +
            item('Horário', dados.horario ? dados.horario + (dados.multiplicadorTempo && dados.multiplicadorTempo !== 1 ? ' <small>x' + dados.multiplicadorTempo + '</small>' : '') : null) +
            item('Aderência inicial', dados.aderenciaInicial !== null ? dados.aderenciaInicial + '%' : null) +
            item('Ganho de aderência', dados.ganhoAderencia !== null ? dados.ganhoAderencia + ' <small>voltas p/ emborrachar</small>' : null) +
            item('Data da sessão', dados.dataCorrida ? dados.dataCorrida.split('-').reverse().join('/') : null) +
            item('Condições reais', simNao(c.condicoesReais));

        const a = dados.assistencias || {};
        const assist = [];
        if (a.Abs !== undefined) assist.push('ABS ' + (a.Abs == 0 ? 'off' : a.Abs == 1 ? 'fábrica' : 'on'));
        if (a.TractionControl !== undefined) assist.push('TC ' + (a.TractionControl == 0 ? 'off' : a.TractionControl == 1 ? 'fábrica' : 'on'));
        if (a.StabilityControl !== undefined) assist.push('ESC ' + a.StabilityControl + '%');
        if (a.AutoShifter !== undefined) assist.push(a.AutoShifter ? 'Câmbio auto' : 'Câmbio manual');
        if (a.AutoClutch) assist.push('Embreagem auto');
        if (a.IdealLine) assist.push('Linha ideal');
        if (a.TyreBlankets) assist.push('Aquecedor de pneus');
        document.getElementById('listaSessao').innerHTML =
            item('Pista', escaparHtml(dados.pistaCodigo || dados.pista) + (dados.pistaLayout ? ' <small>' + escaparHtml(dados.pistaLayout) + '</small>' : '')) +
            item('Sessão', escaparHtml(dados.sessaoNome) + ' <small>' + dados.totalCarros + ' carros</small>') +
            item('Nível da IA', dados.nivelIA !== null ? dados.nivelIA + '%' : null) +
            item('Agressividade IA', dados.agressividadeMedia !== null ? dados.agressividadeMedia.toFixed(0) + '% <small>' + dados.agressividadeMin + '-' + dados.agressividadeMax + '%</small>' : null) +
            item('Desgaste pneus', a.TyreWear !== undefined ? a.TyreWear + 'x' : null) +
            item('Consumo', a.FuelConsumption !== undefined ? a.FuelConsumption + 'x' : null) +
            item('Dano', a.Damage !== undefined ? a.Damage + '%' : null) +
            item('Penalidades', simNao(dados.penalidades) + (dados.penalidadeLargada ? ' <small>largada queimada: ' + dados.penalidadeLargada + '</small>' : '')) +
            item('Setup fixo', simNao(dados.setupFixo)) +
            item('Sua largada', dados.largadaJogador ? dados.largadaJogador + 'º' : null) +
            (assist.length ? '<div style="grid-column: 1 / -1;"><dt>Assistências (você)</dt><dd>' + assist.join(' · ') + '</dd></div>' : '');

        /* ---------- Destaques ---------- */
        const destaques = [];
        if (vmr) destaques.push(['bi-lightning-charge-fill', 'Volta mais rápida', escaparHtml(vmr.nome), formatarTempo(vmr.melhorVolta) + ' na volta ' + vmr.numeroMelhorVolta, 'rc-roxo', vmr]);
        if (dados.voltaIdeal) {
            destaques.push(['bi-bullseye', 'Volta ideal (melhores setores)', formatarTempo(dados.voltaIdeal),
                dados.melhoresSetores.map((m, i) => 'S' + (i + 1) + ' ' + formatarTempo(m.tempo) + ' ' + escaparHtml(m.carro.nome.split(' ').slice(-1)[0])).join(' · ')]);
        }
        const ritmo = dados.classificacao.filter(x => x.ritmoMedio && x.voltasLimpas >= 5).sort((x, y) => x.ritmoMedio - y.ritmoMedio)[0];
        if (ritmo) destaques.push(['bi-speedometer2', 'Melhor ritmo médio', escaparHtml(ritmo.nome), formatarTempo(ritmo.ritmoMedio) + ' em ' + ritmo.voltasLimpas + ' voltas limpas', null, ritmo]);
        const consistente = dados.classificacao.filter(x => x.consistencia !== null).sort((x, y) => x.consistencia - y.consistencia)[0];
        if (consistente) destaques.push(['bi-activity', 'Mais consistente', escaparHtml(consistente.nome), 'variação de ±' + (consistente.consistencia / 1000).toFixed(3) + 's por volta', null, consistente]);
        const comGrid = dados.classificacao.filter(x => gridDoPiloto(x, grid) && !x.abandonou).map(x => ({ c: x, ganho: gridDoPiloto(x, grid) - x.posicao }));
        if (comGrid.length) {
            const melhor = comGrid.sort((x, y) => y.ganho - x.ganho)[0];
            if (melhor.ganho > 0) destaques.push(['bi-graph-up-arrow', 'Maior recuperação', escaparHtml(melhor.c.nome), 'largou em ' + gridDoPiloto(melhor.c, grid) + 'º e terminou em ' + melhor.c.posicao + 'º (+' + melhor.ganho + ')', null, melhor.c]);
            const pior = comGrid.sort((x, y) => x.ganho - y.ganho)[0];
            if (pior.ganho < 0) destaques.push(['bi-graph-down-arrow', 'Mais posições perdidas', escaparHtml(pior.c.nome), 'largou em ' + gridDoPiloto(pior.c, grid) + 'º e terminou em ' + pior.c.posicao + 'º (' + pior.ganho + ')', null, pior.c]);
        } else {
            const largadaBoa = dados.classificacao.filter(x => x.posicaoVolta1).map(x => x).sort((x, y) => x.posicaoVolta1 - y.posicaoVolta1);
            if (largadaBoa.length) destaques.push(['bi-flag', 'Líder na volta 1', escaparHtml(largadaBoa[0].nome), 'preencha a largada para ver posições ganhas', null, largadaBoa[0]]);
        }
        const estrategias = {};
        dados.classificacao.forEach(x => { const k = x.pneus.join('→'); estrategias[k] = (estrategias[k] || 0) + 1; });
        const estrategiaComum = Object.entries(estrategias).sort((x, y) => y[1] - x[1])[0];
        if (estrategiaComum) destaques.push(['bi-circle-half', 'Estratégia mais usada', estrategiaComum[0], estrategiaComum[1] + ' de ' + dados.totalCarros + ' carros']);
        const cortes = dados.classificacao.slice().sort((x, y) => y.cortes - x.cortes)[0];
        if (cortes && cortes.cortes > 0) destaques.push(['bi-exclamation-diamond', 'Mais cortes de pista', escaparHtml(cortes.nome), cortes.cortes + ' corte(s) · total da corrida: ' + dados.classificacao.reduce((s, x) => s + x.cortes, 0), null, cortes]);
        if (dados.voltasNeutralizadas.length) destaques.push(['bi-flag-fill', 'Voltas lentas do pelotão', 'Volta ' + dados.voltasNeutralizadas.join(', '), 'possível safety car / bandeira amarela']);
        if (dados.abandonos.length) {
            const primeiro = dados.abandonos.slice().sort((x, y) => x.voltaAbandono - y.voltaAbandono)[0];
            destaques.push(['bi-x-octagon', 'Primeiro abandono', escaparHtml(primeiro.nome), 'na volta ' + primeiro.voltaAbandono, null, primeiro]);
        }
        if (dados.jogador) {
            const j = dados.jogador;
            const gj = gridDoPiloto(j, grid);
            destaques.push(['bi-person-fill', 'Seu resultado', j.posicao + 'º · ' + escaparHtml(j.nome),
                (gj ? 'largou ' + gj + 'º · ' : '') + 'melhor volta ' + formatarTempo(j.melhorVolta) + ' · ' + j.diferenca, null, j]);
        }
        document.getElementById('destaques').innerHTML = destaques.map(d =>
            '<div class="rc-destaque-item"><div class="rc-kpi-rotulo"><i class="bi ' + d[0] + ' ' + (d[4] || '') + '"></i> ' + d[1] + '</div>' +
            '<div class="rc-destaque-valor">' + d[2] + '</div>' + (d[5] ? '<div class="rc-destaque-equipe">' + htmlEquipe(d[5]) + '</div>' : '') +
            '<div class="rc-destaque-extra">' + d[3] + '</div></div>').join('');

        renderizarClassificacao(grid);
        renderizarGrafico();
        renderizarEstrategia();
        renderizarVoltaAVolta();
    }

    function renderizarClassificacao(grid) {
        const dados = estadoAnalise.dados;
        const nomesForm = new Set(linhasFormulario().map(l => normalizarNome(l.cells[1].textContent)));
        const melhorSetor = dados.melhoresSetores.map(m => m ? m.tempo : null);
        const melhorRitmo = Math.min(...dados.classificacao.map(x => x.ritmoMedio || Infinity));
        const cab = '<thead><tr>' +
            '<th class="text-center">Pos</th><th class="text-start">Piloto</th><th class="text-center">Grid</th><th class="text-center">+/-</th>' +
            '<th>Voltas</th><th>Tempo</th><th>Dif. líder</th><th>Intervalo</th><th>Melhor volta</th>' +
            dados.melhoresSetores.map((m, i) => '<th>S' + (i + 1) + '</th>').join('') +
            '<th>Ritmo médio</th><th title="Desvio padrão das voltas limpas">Consist.</th><th class="text-start">Pneus</th><th class="text-start">Paradas</th>' +
            '<th>Cortes</th><th title="Lastro / restritor">Lastro / Restr.</th><th title="Agressividade da IA">Agress.</th><th class="text-start">Status</th>' +
            '</tr></thead>';
        const linhas = dados.classificacao.map(x => {
            const g = gridDoPiloto(x, grid);
            const idx = estadoAnalise.destacados.indexOf(x.indice);
            const cor = idx > -1 ? CORES_DESTAQUE[idx] : null;
            const ehVmr = dados.voltaMaisRapida && dados.voltaMaisRapida.indice === x.indice;
            return '<tr data-indice="' + x.indice + '" class="' + (x.abandonou ? 'dnf ' : '') + (cor ? 'destacado' : '') + '"' + (cor ? ' style="--cor-destaque:' + cor + '"' : '') + '>' +
                '<td class="text-center"><strong>' + x.posicao + '</strong></td>' +
                '<td class="text-start">' + htmlCorEquipe(x) + '<span class="rc-nome-piloto' + (nomesForm.has(x.nomeNormalizado) ? '' : ' fora-form') + '">' + escaparHtml(x.nome) + '</span>' +
                    (x.ehJogador ? '<span class="rc-voce">Você</span>' : '') +
                    '<span class="rc-sub" title="' + escaparHtml(x.carroNome) + '">' + htmlEquipe(x) + (x.nacionalidade ? ' · ' + escaparHtml(x.nacionalidade) : '') + (nomesForm.has(x.nomeNormalizado) ? '' : ' · fora do formulário') + '</span></td>' +
                '<td class="text-center">' + (g || '-') + '</td>' +
                '<td class="text-center">' + (x.abandonou ? '<span class="rc-delta igual">&ndash;</span>' : htmlDelta(g, x.posicao)) + '</td>' +
                '<td>' + x.voltasCompletadas + '</td>' +
                '<td>' + formatarTempo(x.tempoTotal) + '</td>' +
                '<td>' + (x.posicao === 1 ? '-' : (x.abandonou ? '-' : (x.difLider !== null ? '+' + formatarTempo(x.difLider) : x.diferenca))) + '</td>' +
                '<td>' + (x.intervalo !== null ? '+' + formatarTempo(x.intervalo) : '-') + '</td>' +
                '<td class="' + (ehVmr ? 'rc-roxo' : '') + '">' + (ehVmr ? '<i class="bi bi-lightning-charge-fill"></i> ' : '') + formatarTempo(x.melhorVolta) + ' <span class="rc-sub" style="display:inline">V' + (x.numeroMelhorVolta || '-') + '</span></td>' +
                dados.melhoresSetores.map((m, i) => '<td class="' + (x.melhoresSetores[i] && x.melhoresSetores[i] === melhorSetor[i] ? 'rc-roxo' : '') + '">' + formatarTempo(x.melhoresSetores[i]) + '</td>').join('') +
                '<td class="' + (x.ritmoMedio === melhorRitmo ? 'rc-verde' : '') + '">' + formatarTempo(x.ritmoMedio) + '</td>' +
                '<td>' + (x.consistencia !== null ? '±' + (x.consistencia / 1000).toFixed(3) : '-') + '</td>' +
                '<td class="text-start">' + x.pneus.map(pn => htmlPneu(pn)).join('') + '</td>' +
                '<td class="text-start">' + (x.paradas.length ? x.paradas.length + ' <span class="rc-sub" style="display:inline">(V' + x.paradas.map(pp => pp.volta).join(', V') + ')</span>' : '0') + '</td>' +
                '<td>' + x.cortes + '</td>' +
                '<td>' + (x.lastro !== null ? x.lastro + 'kg' : '-') + ' / ' + (x.restritor !== null ? x.restritor + '%' : '-') + '</td>' +
                '<td>' + (x.agressividade !== null ? x.agressividade + '%' : (x.ehJogador ? '-' : '-')) + '</td>' +
                '<td class="text-start">' + (x.abandonou
                    ? '<span class="rc-status dnf"><i class="bi bi-x-octagon-fill"></i> Abandonou V' + x.voltaAbandono + '</span>'
                    : '<span class="rc-status ok"><i class="bi bi-check-circle-fill"></i> ' + (x.voltasAtras > 0 ? '+' + x.voltasAtras + ' volta' + (x.voltasAtras > 1 ? 's' : '') : 'Terminou') + '</span>') + '</td>' +
                '</tr>';
        }).join('');
        const tabela = document.getElementById('tabelaClassificacao');
        tabela.innerHTML = cab + '<tbody>' + linhas + '</tbody>';
        tabela.querySelectorAll('tbody tr').forEach(tr => tr.addEventListener('click', () => alternarDestaque(Number(tr.dataset.indice))));
    }

    function alternarDestaque(indice) {
        const lista = estadoAnalise.destacados;
        const pos = lista.indexOf(indice);
        if (pos > -1) lista[pos] = null;            // libera o slot sem repintar os outros (cor segue o piloto)
        else {
            const livre = lista.indexOf(null);
            if (livre > -1) lista[livre] = indice;
            else if (lista.length < 4) lista.push(indice);
            else return;
        }
        while (lista.length && lista[lista.length - 1] === null) lista.pop();
        renderizarClassificacao(gridDoFormulario());
        renderizarGrafico();
    }

    function renderizarGrafico() {
        const dados = estadoAnalise.dados;
        const canvas = document.getElementById('graficoPosicoes');
        if (!window.Chart || !canvas) return;
        const totalColunas = Math.max(0, ...dados.classificacao.map(x => x.posicaoPorVolta.length));
        const rotulos = Array.from({ length: totalColunas }, (_, i) => 'V' + (i + 1));
        const destacados = estadoAnalise.destacados;

        const datasets = dados.classificacao.map(x => {
            const idx = destacados.indexOf(x.indice);
            const cor = idx > -1 ? CORES_DESTAQUE[idx] : '#4a4a47';
            return {
                label: x.nome,
                data: x.posicaoPorVolta,
                borderColor: cor,
                backgroundColor: cor,
                borderWidth: idx > -1 ? 2.5 : 1,
                pointRadius: 0,
                pointHoverRadius: idx > -1 ? 5 : 0,
                pointHoverBorderColor: '#1a1a19',
                pointHoverBorderWidth: 2,
                tension: 0,
                spanGaps: false,
                order: idx > -1 ? 0 : 1,
                destacado: idx > -1,
                indicePiloto: x.indice,
            };
        });

        // rotulo direto no fim da linha dos pilotos destacados (texto na cor da tinta, nao da serie)
        const rotuloFinal = {
            id: 'rotuloFinal',
            afterDatasetsDraw(chart) {
                const ctx = chart.ctx;
                ctx.save();
                ctx.font = '600 11px "Titillium Web", sans-serif';
                ctx.textBaseline = 'middle';
                chart.data.datasets.forEach((ds, i) => {
                    if (!ds.destacado) return;
                    const meta = chart.getDatasetMeta(i);
                    let ultimo = -1;
                    ds.data.forEach((v, k) => { if (v !== null) ultimo = k; });
                    if (ultimo < 0 || !meta.data[ultimo]) return;
                    const ponto = meta.data[ultimo];
                    ctx.fillStyle = ds.borderColor;
                    ctx.beginPath(); ctx.arc(ponto.x, ponto.y, 4, 0, Math.PI * 2); ctx.fill();
                    ctx.fillStyle = '#f0efec';
                    ctx.fillText(ds.label.split(' ').slice(-1)[0], ponto.x + 8, ponto.y);
                });
                ctx.restore();
            }
        };

        if (estadoAnalise.grafico) estadoAnalise.grafico.destroy();
        estadoAnalise.grafico = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels: rotulos, datasets: datasets },
            plugins: [rotuloFinal],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                layout: { padding: { right: 80, top: 8 } },
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        reverse: true, min: 1, max: dados.totalCarros,
                        ticks: { stepSize: 1, color: '#898781', font: { size: 11 } },
                        grid: { color: '#2c2c2a', borderColor: '#383835' },
                        title: { display: true, text: 'Posição', color: '#898781' },
                    },
                    x: {
                        ticks: { color: '#898781', font: { size: 11 }, autoSkip: true, maxRotation: 0 },
                        grid: { display: false },
                        title: { display: true, text: 'Volta', color: '#898781' },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#232321', borderColor: 'rgba(255,255,255,.12)', borderWidth: 1,
                        titleColor: '#f0efec', bodyColor: '#c3c2b7', padding: 10, usePointStyle: true,
                        filter: (item) => destacados.filter(d => d !== null).length ? item.dataset.destacado && item.raw !== null : item.raw !== null && item.raw <= 5,
                        itemSort: (a, b) => a.raw - b.raw,
                        callbacks: {
                            title: (itens) => itens.length ? 'Volta ' + (itens[0].dataIndex + 1) : '',
                            label: (item) => ' P' + item.raw + '  ' + item.dataset.label,
                        },
                    },
                },
            },
        });

        // legenda: pilotos destacados (sempre presente para mais de uma serie)
        const nomes = dados.classificacao.reduce((m, x) => { m[x.indice] = x; return m; }, {});
        const itens = destacados.map((d, i) => d === null ? '' :
            '<span><i class="amostra-linha" style="background:' + CORES_DESTAQUE[i] + '"></i>' + escaparHtml(nomes[d].nome) + ' <small>(P' + nomes[d].posicao + ')</small></span>').join('');
        document.getElementById('legendaPosicoes').innerHTML = itens + '<span><i class="amostra-linha" style="background:#4a4a47"></i> Demais pilotos</span>';
    }

    function renderizarEstrategia() {
        const dados = estadoAnalise.dados;
        const total = Math.max(dados.totalVoltas, ...dados.classificacao.map(x => x.voltas.length)) || 1;
        const linhas = dados.classificacao.map(x => {
            const stints = x.stints.map(s => {
                const esquerda = ((s.inicio - 1) / total) * 100;
                const largura = (s.voltas / total) * 100;
                const sigla = siglaPneu(s.pneu);
                const classe = ['S', 'M', 'H', 'I', 'W'].includes(sigla) ? sigla : 'X';
                return '<div class="rc-stint ' + classe + '" style="left:calc(' + esquerda + '% + 1px); width:calc(' + largura + '% - 2px);" title="' + escaparHtml(s.pneu) + ' · voltas ' + s.inicio + '-' + (s.inicio + s.voltas - 1) + ' (' + s.voltas + ')">' +
                    (largura > 2.5 ? escaparHtml(sigla) : '') + '</div>';
            }).join('');
            const fim = x.abandonou ? '<i class="bi bi-x-octagon-fill rc-estrategia-fim" style="left:calc(' + ((x.voltas.length / total) * 100) + '% + 4px)" title="Abandonou na volta ' + x.voltaAbandono + '"></i>' : '';
            return '<div class="rc-estrategia-linha"><div class="rc-estrategia-pos">' + x.posicao + '</div>' +
                '<div class="rc-estrategia-nome" title="' + escaparHtml(x.nome + ' · ' + nomeEquipe(x)) + '">' + htmlEquipe(x, true) + ' ' + escaparHtml(x.nome) + (x.ehJogador ? '<span class="rc-voce">Você</span>' : '') + '</div>' +
                '<div class="rc-estrategia-barra">' + stints + fim + '</div></div>';
        }).join('');
        const passo = total > 40 ? 10 : 5;
        let marcas = '';
        for (let v = 0; v <= total; v += passo) marcas += '<span style="left:' + (v / total * 100) + '%">' + v + '</span>';
        document.getElementById('estrategiaPneus').innerHTML = linhas +
            '<div class="rc-estrategia-eixo"><div></div><div>Volta</div><div class="marcas">' + marcas + '</div></div>';
    }

    function renderizarVoltaAVolta() {
        const dados = estadoAnalise.dados;
        const total = Math.max(0, ...dados.classificacao.map(x => x.voltas.length));
        const vmr = dados.voltaMaisRapida;
        let cab = '<thead><tr><th class="text-center">Pos</th><th class="text-start">Piloto</th>';
        for (let k = 1; k <= total; k++) {
            const neutra = dados.voltasNeutralizadas.includes(k);
            cab += '<th class="' + (neutra ? 'neutralizada' : '') + '">' + (neutra ? '<i class="bi bi-flag-fill" style="color:#fab219"></i> ' : '') + 'V' + k + '</th>';
        }
        cab += '</tr></thead>';
        const corpo = dados.classificacao.map(x => {
            let tr = '<tr data-indice="' + x.indice + '"' + (x.abandonou ? ' class="dnf"' : '') + '><td class="text-center"><strong>' + x.posicao + '</strong></td><td class="text-start">' + htmlCorEquipe(x) + '<span class="rc-nome-piloto">' + escaparHtml(x.nome) + '</span><span class="rc-sub">' + escaparHtml(nomeEquipe(x)) + '</span></td>';
            const voltasPit = new Set(x.paradas.map(p => p.volta));
            for (let k = 1; k <= total; k++) {
                const volta = x.voltas[k - 1];
                const neutra = dados.voltasNeutralizadas.includes(k);
                if (!volta) { tr += '<td class="' + (neutra ? 'neutralizada' : '') + '">' + (x.abandonou && k === x.voltaAbandono ? '<i class="bi bi-x-octagon-fill" style="color:#d03b3b" title="Abandono"></i>' : '') + '</td>'; continue; }
                const ehMelhorGeral = vmr && vmr.indice === x.indice && volta.numero === vmr.numeroMelhorVolta;
                const ehMelhorPiloto = volta.numero === x.numeroMelhorVolta;
                const classes = [ehMelhorGeral ? 'rc-roxo' : (ehMelhorPiloto ? 'rc-verde' : ''), voltasPit.has(k) ? 'volta-pit' : '', neutra ? 'neutralizada' : ''].join(' ');
                const titulo = 'Volta ' + k + ' · ' + formatarTempo(volta.tempo) + ' · setores ' + volta.setores.map(formatarTempo).join(' / ') + (volta.pneu ? ' · pneu ' + volta.pneu : '') + (volta.cortes ? ' · ' + volta.cortes + ' corte(s)' : '') + (voltasPit.has(k) ? ' · entrada nos boxes' : '') + ' · P' + x.posicaoPorVolta[k - 1];
                tr += '<td class="' + classes + '" title="' + escaparHtml(titulo) + '">' + (ehMelhorGeral ? '<i class="bi bi-lightning-charge-fill"></i>' : '') + formatarTempo(volta.tempo) + (volta.cortes ? '<sup style="color:#fab219">!</sup>' : '') + '</td>';
            }
            return tr + '</tr>';
        }).join('');
        document.getElementById('tabelaVoltas').innerHTML = cab + '<tbody>' + corpo + '</tbody>';
    }

    /* ============ TREINO E CLASSIFICACAO - JSON de largada / grid invertido (ou chegada de fim de semana) ============ */
    // sessoes de treino livre (type 1 / Practice) e classificacao (type 2 / Qualifying) que tenham voltas
    function sessoesDeTreino(json) {
        return (json['sessions'] || []).filter(s =>
            (s['type'] == 1 || s['type'] == 2 || /^(practice|qualify)/i.test(String(s['name'] || ''))) && (s['laps'] || []).length);
    }

    function ehClassificacao(sessao) {
        return sessao['type'] == 2 || /^qualify/i.test(String(sessao['name'] || ''));
    }

    function nomeSessao(sessao) {
        const nome = String(sessao['name'] || '');
        const numero = nome.match(/\d+/);
        if (ehClassificacao(sessao)) return 'Classificação';
        if (/^practice/i.test(nome) || sessao['type'] == 1) return 'Treino livre' + (numero ? ' ' + numero[0] : '');
        return nome;
    }

    // regra igual a da largada: posicao pela melhor volta valida (bestLaps do arquivo)
    function analisarSessaoTreino(json, sessao) {
        const ini = lerIni(json['__raceIni']);
        const melhoresArquivo = (sessao['bestLaps'] || []).filter(b => b['time'] > 0);
        const pilotos = (json['players'] || []).map((jogador, indice) => {
            const voltas = (sessao['laps'] || []).filter(v => v['car'] == indice).sort((a, b) => a['lap'] - b['lap'])
                .map((v, i) => ({
                    numero: i + 1,
                    lap: Number(v['lap']),
                    tempo: v['time'] > 0 ? Number(v['time']) : null,             // -1 = volta sem tempo
                    valida: v['time'] > 0 && !(Number(v['cuts']) > 0),
                    setores: v['sectors'] || [],
                    pneu: v['tyre'] || null,
                }));
            const validas = voltas.filter(v => v.valida);

            const doArquivo = melhoresArquivo.find(b => b['car'] == indice);
            let melhor = null;
            if (doArquivo) {
                const volta = voltas.find(v => v.lap == doArquivo['lap']);
                melhor = { tempo: Number(doArquivo['time']), numero: Number(doArquivo['lap']) + 1, setores: volta ? volta.setores : [], pneu: volta ? volta.pneu : null };
            } else if (validas.length) {
                const v = validas.reduce((m, x) => (x.tempo < m.tempo ? x : m));
                melhor = { tempo: v.tempo, numero: v.numero, setores: v.setores, pneu: v.pneu };
            }

            const setoresIdeais = [];
            validas.forEach(v => v.setores.forEach((t, i) => { if (t > 0 && (setoresIdeais[i] === undefined || t < setoresIdeais[i])) setoresIdeais[i] = t; }));
            const voltaIdeal = setoresIdeais.length >= 3 && setoresIdeais.every(Boolean) ? setoresIdeais.reduce((a, b) => a + b, 0) : null;
            const ritmo = melhor ? validas.filter(v => v.tempo <= melhor.tempo * 1.07).map(v => v.tempo) : [];
            const pneus = [];
            voltas.forEach(v => { const sigla = v.pneu ? siglaPneu(v.pneu) : null; if (sigla && !pneus.includes(sigla)) pneus.push(sigla); });

            return {
                indice: indice,
                nome: String(jogador['name'] || '').replace(/\s+/g, ' ').trim(),
                nomeNormalizado: normalizarNome(jogador['name']),
                carroNome: nomeCarro(jogador['car']),
                ehJogador: indice === 0 && (ini['CAR_0'] || {})['AI_LEVEL'] === undefined,
                melhor: melhor ? melhor.tempo : null,
                numeroMelhor: melhor ? melhor.numero : null,
                setoresMelhor: melhor ? melhor.setores : [],
                pneuMelhor: melhor ? melhor.pneu : null,
                voltaIdeal: voltaIdeal,
                deixouNaPista: voltaIdeal && melhor ? Math.max(0, melhor.tempo - voltaIdeal) : null,
                voltas: voltas.length,
                validas: validas.length,
                anuladas: voltas.length - validas.length,
                ritmo: ritmo.length >= 2 ? ritmo.reduce((a, b) => a + b, 0) / ritmo.length : null,
                consistencia: ritmo.length >= 3 ? desvioPadrao(ritmo) : null,
                pneus: pneus,
                setoresIdeais: setoresIdeais,
            };
        });

        const comTempo = pilotos.filter(p => p.melhor).sort((a, b) => a.melhor - b.melhor);
        comTempo.forEach((p, i) => {
            p.posicao = i + 1;
            p.difPrimeiro = p.melhor - comTempo[0].melhor;
            p.difFrente = i ? p.melhor - comTempo[i - 1].melhor : null;
        });
        pilotos.filter(p => !p.melhor).forEach(p => { p.posicao = null; p.difPrimeiro = null; p.difFrente = null; });

        const setoresSessao = [0, 1, 2].map(i => {
            let m = null;
            pilotos.forEach(p => { const t = p.setoresIdeais[i]; if (t && (!m || t < m.tempo)) m = { tempo: t, piloto: p }; });
            return m;
        });
        return {
            classificacao: comTempo.concat(pilotos.filter(p => !p.melhor)),
            comTempo: comTempo,
            setoresSessao: setoresSessao,
            voltaIdealSessao: setoresSessao.every(Boolean) ? setoresSessao.reduce((a, m) => a + m.tempo, 0) : null,
            totalVoltas: pilotos.reduce((a, p) => a + p.voltas, 0),
            totalValidas: pilotos.reduce((a, p) => a + p.validas, 0),
            duracao: Number(sessao['duration']) || null,
            jogador: pilotos.find(p => p.ehJogador) || null,
        };
    }

    const estadoTreino = { json: null, origem: '', indiceSessao: 0 };

    // mantem o nome antigo: chamado no upload (largada / grid invertido / chegada) e ao abrir a tela
    function renderizarClassificacaoQualy(json, origem) {
        const sessoes = sessoesDeTreino(json);
        const card = document.getElementById('cardClassificacao');
        if (!sessoes.length) { card.classList.add('d-none'); return false; }
        estadoTreino.json = json;
        estadoTreino.origem = origem;
        // abre na classificacao; se nao houver, no ultimo treino
        estadoTreino.indiceSessao = sessoes.length - 1;
        sessoes.forEach((s, i) => { if (ehClassificacao(s)) estadoTreino.indiceSessao = i; });
        desenharTreino();
        card.classList.remove('d-none');
        return true;
    }

    function desenharTreino() {
        const sessoes = sessoesDeTreino(estadoTreino.json);
        const sessao = sessoes[estadoTreino.indiceSessao];
        const d = analisarSessaoTreino(estadoTreino.json, sessao);
        const qualy = ehClassificacao(sessao);
        const nomesForm = new Set(linhasFormulario().map(l => normalizarNome(l.cells[1].textContent)));
        const p1 = d.comTempo[0] || null;
        const p2 = d.comTempo[1] || null;
        const ultimo = d.comTempo[d.comTempo.length - 1] || null;
        const decimo = d.comTempo[9] || null;
        const j = d.jogador;
        const segundos = ms => (ms / 1000).toFixed(3) + 's';

        document.getElementById('abasTreino').innerHTML = sessoes.map((s, i) =>
            '<button type="button" class="btn btn-rc-mini' + (i === estadoTreino.indiceSessao ? ' ativo' : '') + '" data-sessao-treino="' + i + '">' +
            '<i class="bi ' + (ehClassificacao(s) ? 'bi-stopwatch' : 'bi-tools') + '"></i> ' + escaparHtml(nomeSessao(s)) + '</button>').join('');
        document.getElementById('poleInfo').innerHTML = '<i class="bi bi-file-earmark-code"></i> ' + escaparHtml(estadoTreino.origem) +
            (d.duracao ? ' · sessão de ' + d.duracao + ' min' : '');

        // resumo em linguagem simples
        const frases = [];
        if (p1) {
            frases.push('<strong>' + escaparHtml(p1.nome) + '</strong> (' + escaparHtml(nomeEquipe(p1)) + ') ' + (qualy ? 'fez a pole position' : 'foi o mais rápido do treino') +
                ' com <strong>' + formatarTempo(p1.melhor) + '</strong>' + (p2 ? ', ' + segundos(p2.difPrimeiro) + ' à frente de ' + escaparHtml(p2.nome) + ' (' + escaparHtml(nomeEquipe(p2)) + ')' : '') + '.');
        }
        if (decimo) frases.push('Os 10 primeiros ficaram separados por apenas <strong>' + segundos(decimo.difPrimeiro) + '</strong>.');
        frases.push('Foram <strong>' + d.totalVoltas + ' voltas</strong> na sessão, ' + d.totalValidas + ' válidas.');
        if (j && j.posicao) {
            frases.push('Você ficou em <strong>' + j.posicao + 'º</strong>' + (j.posicao > 1 ? ', a ' + segundos(j.difPrimeiro) + ' do primeiro' : '') +
                (j.deixouNaPista ? ', e juntando seus melhores setores daria para ganhar mais <strong>' + segundos(j.deixouNaPista) + '</strong>' : '') + '.');
        }
        document.getElementById('resumoTreino').innerHTML = frases.join(' ');

        // KPIs
        const pct = d.totalVoltas ? Math.round(d.totalValidas / d.totalVoltas * 100) : 0;
        const kpis = [
            ['bi-stopwatch', qualy ? 'Pole position' : 'Mais rápido', p1 ? formatarTempo(p1.melhor) : '-', p1 ? escaparHtml(p1.nome) + ' · ' + htmlEquipe(p1) : ''],
            ['bi-bullseye', 'Volta ideal da sessão', d.voltaIdealSessao ? formatarTempo(d.voltaIdealSessao) : '-',
                d.setoresSessao.filter(Boolean).map((m, i) => 'S' + (i + 1) + ' ' + escaparHtml(m.piloto.nome.split(' ').slice(-1)[0])).join(' · ')],
            ['bi-arrow-repeat', 'Voltas', d.totalVoltas, pct + '% válidas · ' + (d.totalVoltas - d.totalValidas) + ' anuladas'],
            ['bi-distribute-horizontal', 'Do 1º ao último', ultimo && p1 && ultimo !== p1 ? '+' + segundos(ultimo.difPrimeiro) : '-', decimo ? 'top 10 em ' + segundos(decimo.difPrimeiro) : ''],
        ];
        if (j) kpis.push(['bi-person-fill', 'Você', j.posicao ? j.posicao + 'º' : 'sem tempo', j.posicao ? formatarTempo(j.melhor) + (j.posicao > 1 ? ' · +' + segundos(j.difPrimeiro) : '') : '']);
        document.getElementById('kpisTreino').innerHTML = kpis.map(k =>
            '<div class="rc-kpi"><div class="rc-kpi-rotulo"><i class="bi ' + k[0] + '"></i> ' + k[1] + '</div>' +
            '<div class="rc-kpi-valor">' + k[2] + '</div><div class="rc-kpi-extra">' + k[3] + '</div></div>').join('');

        // destaques
        const destaques = [];
        const maisVoltas = d.classificacao.slice().sort((a, b) => b.voltas - a.voltas)[0];
        if (maisVoltas && maisVoltas.voltas) destaques.push(['bi-arrow-repeat', 'Quem mais andou', maisVoltas, maisVoltas.voltas + ' voltas (' + maisVoltas.validas + ' válidas)']);
        const consistente = d.classificacao.filter(p => p.consistencia !== null).sort((a, b) => a.consistencia - b.consistencia)[0];
        if (consistente) destaques.push(['bi-activity', 'Mais constante', consistente, 'variação de ±' + segundos(consistente.consistencia) + ' entre as voltas']);
        const ritmo = d.classificacao.filter(p => p.ritmo).sort((a, b) => a.ritmo - b.ritmo)[0];
        if (ritmo && !qualy) destaques.push(['bi-speedometer2', 'Melhor ritmo', ritmo, 'média de ' + formatarTempo(ritmo.ritmo) + ' por volta']);
        const deixou = d.classificacao.filter(p => p.deixouNaPista > 0).sort((a, b) => b.deixouNaPista - a.deixouNaPista)[0];
        if (deixou) destaques.push(['bi-hourglass-split', 'Mais tempo deixado na pista', deixou, 'volta ideal ' + formatarTempo(deixou.voltaIdeal) + ' (−' + segundos(deixou.deixouNaPista) + ')']);
        const anuladas = d.classificacao.slice().sort((a, b) => b.anuladas - a.anuladas)[0];
        if (anuladas && anuladas.anuladas) destaques.push(['bi-x-circle', 'Mais voltas anuladas', anuladas, anuladas.anuladas + ' de ' + anuladas.voltas + ' voltas']);
        document.getElementById('destaquesTreino').innerHTML = destaques.map(x =>
            '<div class="rc-destaque-item"><div class="rc-kpi-rotulo"><i class="bi ' + x[0] + '"></i> ' + x[1] + '</div>' +
            '<div class="rc-destaque-valor">' + escaparHtml(x[2].nome) + (x[2].ehJogador ? '<span class="rc-voce">Você</span>' : '') + '</div>' +
            '<div class="rc-destaque-equipe">' + htmlEquipe(x[2]) + '</div><div class="rc-destaque-extra">' + x[3] + '</div></div>').join('');

        // tabela
        const maiorDif = Math.max(1, ...d.comTempo.map(p => p.difPrimeiro));
        const melhorSetor = [0, 1, 2].map(i => d.setoresSessao[i] ? d.setoresSessao[i].tempo : null);
        const cab = '<thead><tr><th class="text-center">Pos</th><th class="text-start">Piloto</th>' +
            '<th>Melhor volta</th><th class="text-start" title="Diferença para o mais rápido">Dif. 1º</th><th title="Diferença para o piloto à frente">Dif. frente</th>' +
            '<th title="Setores da melhor volta">S1</th><th>S2</th><th>S3</th>' +
            '<th title="Soma dos melhores setores do piloto">Volta ideal</th><th title="Volta ideal comparada à melhor volta">Deixou na pista</th>' +
            (qualy ? '' : '<th title="Média das voltas válidas">Ritmo</th>') +
            '<th class="text-start" title="Válidas / total">Voltas</th><th class="text-center">Pneus</th></tr></thead>';
        const corpo = d.classificacao.map(p => {
            const naForm = nomesForm.has(p.nomeNormalizado);
            return '<tr' + (p.posicao ? '' : ' class="dnf"') + '><td class="text-center"><strong>' + (p.posicao || '-') + '</strong></td>' +
                '<td class="text-start">' + htmlCorEquipe(p) + '<span class="rc-nome-piloto' + (naForm ? '' : ' fora-form') + '">' + escaparHtml(p.nome) + '</span>' +
                    (p.ehJogador ? '<span class="rc-voce">Você</span>' : '') +
                    '<span class="rc-sub" title="' + escaparHtml(p.carroNome) + '">' + htmlEquipe(p) + (naForm ? '' : ' · fora do formulário') + '</span></td>' +
                '<td class="' + (p.posicao === 1 ? 'rc-roxo' : '') + '">' + (p.melhor ? (p.posicao === 1 ? '<i class="bi bi-stopwatch-fill"></i> ' : '') + formatarTempo(p.melhor) + ' <span class="rc-sub" style="display:inline">V' + p.numeroMelhor + '</span>' : 'sem tempo válido') + '</td>' +
                '<td class="text-start">' + (p.posicao > 1 ? '<span class="rc-barra-dif" style="width:' + Math.round(8 + (p.difPrimeiro / maiorDif) * 70) + 'px"></span>+' + segundos(p.difPrimeiro) : (p.posicao === 1 ? '<span class="rc-card-subtitulo">referência</span>' : '-')) + '</td>' +
                '<td>' + (p.difFrente !== null && p.difFrente !== undefined ? '+' + segundos(p.difFrente) : '-') + '</td>' +
                [0, 1, 2].map(i => { const t = p.setoresMelhor[i]; return '<td class="' + (t && t === melhorSetor[i] ? 'rc-roxo' : '') + '">' + (t ? formatarTempo(t) : '-') + '</td>'; }).join('') +
                '<td>' + (p.voltaIdeal ? formatarTempo(p.voltaIdeal) : '-') + '</td>' +
                '<td>' + (p.deixouNaPista ? '<span class="rc-delta desce">−' + segundos(p.deixouNaPista) + '</span>' : (p.voltaIdeal ? '<span class="rc-verde">volta perfeita</span>' : '-')) + '</td>' +
                (qualy ? '' : '<td>' + (p.ritmo ? formatarTempo(p.ritmo) : '-') + '</td>') +
                '<td class="text-start">' + p.validas + '/' + p.voltas + '<span class="rc-validas" title="' + p.anuladas + ' anulada(s)"><i style="width:' + (p.voltas ? Math.round(p.validas / p.voltas * 100) : 0) + '%"></i></span></td>' +
                '<td class="text-center">' + p.pneus.map(pn => htmlPneu(pn)).join('') + '</td></tr>';
        }).join('');
        document.getElementById('tabelaQualy').innerHTML = cab + '<tbody>' + corpo + '</tbody>';
    }

    document.getElementById('cardClassificacao').addEventListener('click', (e) => {
        const aba = e.target.closest('[data-sessao-treino]');
        if (!aba) return;
        estadoTreino.indiceSessao = Number(aba.dataset.sessaoTreino);
        desenharTreino();
    });

    /* ============ PERSISTENCIA - JSONs vao para o banco ao clicar em Salvar ============ */
    const TIPOS_SESSAO = { inputFileLargada: 'largada', inputFileChegada: 'chegada', inputFileLargadaGridInvertido: 'grid_invertido' };
    const ROTULO_SESSAO = { largada: 'largada', chegada: 'chegada', grid_invertido: 'grid invertido' };

    function atualizarRodapeSessoes() {
        const pendentes = Object.values(TIPOS_SESSAO).filter(t => document.getElementById('sessaoJson_' + t).value !== '');
        const remover = Array.from(document.querySelectorAll('input[name="sessao_remover[]"]')).map(i => i.value);
        const partes = [];
        if (pendentes.length) partes.push('arquivo(s) a gravar: ' + pendentes.map(t => ROTULO_SESSAO[t]).join(', '));
        if (remover.length) partes.push('a remover: ' + remover.map(t => ROTULO_SESSAO[t]).join(', '));
        document.getElementById('resumoSessoes').textContent = partes.length ? ' · ' + partes.join(' · ') : '';
    }

    function renderizarBanco(tipo) {
        const el = document.getElementById('banco_' + tipo);
        const pendente = document.getElementById('sessaoJson_' + tipo).value !== '';
        const marcadoRemover = !!document.querySelector('input[name="sessao_remover[]"][value="' + tipo + '"]');
        const salvo = SESSOES_SALVAS[tipo];
        if (pendente) {
            el.innerHTML = '<span class="rc-banco-pendente"><i class="bi bi-hourglass-split"></i> ' + escaparHtml(document.getElementById('sessaoNome_' + tipo).value) + ' — será gravado ao clicar em Salvar</span>' +
                (salvo ? ' <span class="rc-card-subtitulo">(substitui o arquivo salvo)</span>' : '');
        } else if (salvo && marcadoRemover) {
            el.innerHTML = '<span class="rc-banco-remover"><i class="bi bi-trash"></i> ' + escaparHtml(salvo.nome || 'arquivo') + ' será removido ao salvar</span> <button type="button" class="btn btn-rc-mini" data-desfazer-remocao="' + tipo + '">desfazer</button>';
        } else if (salvo) {
            el.innerHTML = '<span class="rc-banco-salvo"><i class="bi bi-database-check"></i> Salvo: ' + escaparHtml(salvo.nome || 'arquivo') + ' · ' + escaparHtml(salvo.data) + '</span> <button type="button" class="btn btn-rc-mini" data-remover-sessao="' + tipo + '" title="Remover este arquivo do banco ao salvar"><i class="bi bi-trash"></i></button>';
        } else {
            el.innerHTML = '<span class="rc-card-subtitulo"><i class="bi bi-database"></i> Nenhum arquivo salvo</span>';
        }
        atualizarRodapeSessoes();
    }

    document.getElementById('formResultado').addEventListener('click', (e) => {
        const remover = e.target.closest('[data-remover-sessao]');
        const desfazer = e.target.closest('[data-desfazer-remocao]');
        if (remover) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'sessao_remover[]'; input.value = remover.dataset.removerSessao;
            document.getElementById('camposSessoes').appendChild(input);
            renderizarBanco(remover.dataset.removerSessao);
        }
        if (desfazer) {
            const input = document.querySelector('input[name="sessao_remover[]"][value="' + desfazer.dataset.desfazerRemocao + '"]');
            if (input) input.remove();
            renderizarBanco(desfazer.dataset.desfazerRemocao);
        }
    });

    /* ---------- Eventos adicionais (nao alteram a logica de largada/grid invertido) ---------- */
    ['inputFileLargada', 'inputFileLargadaGridInvertido', 'inputFileChegada'].forEach(id => {
        document.getElementById(id).addEventListener('change', function (event) {
            const arquivo = event.target.files[0];
            if (!arquivo) return;
            const tipo = TIPOS_SESSAO[id];
            const tile = { inputFileLargada: 'tileLargada', inputFileLargadaGridInvertido: 'tileGridInvertido', inputFileChegada: 'tileChegada' }[id];
            if (id !== 'inputFileChegada') marcarUpload(tile, true, '<i class="bi bi-check-circle"></i> ' + escaparHtml(arquivo.name));

            // guarda o conteudo nos campos ocultos: vai para o banco junto com o Salvar
            const leitor = new FileReader();
            leitor.onload = function (e) {
                let json = null;
                try { json = JSON.parse(e.target.result); } catch (erro) { json = null; }
                if (!json || !json['sessions'] || !json['players']) {
                    marcarUpload(tile, false, '<i class="bi bi-x-circle"></i> Arquivo inválido (não é um JSON do Content Manager)');
                    return;
                }
                document.getElementById('sessaoJson_' + tipo).value = e.target.result;
                document.getElementById('sessaoNome_' + tipo).value = arquivo.name;
                const remocao = document.querySelector('input[name="sessao_remover[]"][value="' + tipo + '"]');
                if (remocao) remocao.remove();
                renderizarBanco(tipo);
                const temLargada = document.getElementById('sessaoJson_largada').value || SESSOES_SALVAS['largada'];
                const temGrid = document.getElementById('sessaoJson_grid_invertido').value || SESSOES_SALVAS['grid_invertido'];
                if (tipo === 'largada' || (tipo === 'grid_invertido' && !temLargada) || (tipo === 'chegada' && !temLargada && !temGrid)) {
                    renderizarClassificacaoQualy(json, 'Arquivo ' + arquivo.name + ' (ainda não salvo)');
                }
            };
            leitor.readAsText(arquivo);

            // a leitura dos arquivos e assincrona: atualiza deltas/analise depois do preenchimento
            setTimeout(() => { atualizarDeltasFormulario(); if (estadoAnalise.dados) renderizarAnalise(); }, 400);
        });
    });

    document.getElementById('tabela').addEventListener('input', () => {
        atualizarDeltasFormulario();
        clearTimeout(estadoAnalise.timerAnalise);
        estadoAnalise.timerAnalise = setTimeout(() => { if (estadoAnalise.dados) renderizarAnalise(); }, 500);
    });
    document.getElementById('tabela').addEventListener('change', atualizarDeltasFormulario);
    document.querySelectorAll('[data-ordenar]').forEach(b => b.addEventListener('click', () => ordenarFormulario(b.dataset.ordenar)));
    atualizarDeltasFormulario();

    /* ---------- Ao abrir a tela: monta tudo a partir dos JSONs salvos no banco ---------- */
    function lerSessaoSalva(tipo) {
        const el = document.getElementById('sessaoSalva_' + tipo);
        if (!el) return null;
        try { return JSON.parse(el.textContent); } catch (erro) { console.error('JSON salvo inválido: ' + tipo, erro); return null; }
    }
    Object.values(TIPOS_SESSAO).forEach(renderizarBanco);
    const qualySalva = lerSessaoSalva('largada') ? ['largada', lerSessaoSalva('largada')] : (lerSessaoSalva('grid_invertido') ? ['grid_invertido', lerSessaoSalva('grid_invertido')] : null);
    if (qualySalva) {
        renderizarClassificacaoQualy(qualySalva[1], 'Arquivo salvo: ' + (SESSOES_SALVAS[qualySalva[0]].nome || ROTULO_SESSAO[qualySalva[0]]));
        marcarUpload(qualySalva[0] === 'largada' ? 'tileLargada' : 'tileGridInvertido', true, '<i class="bi bi-database-check"></i> Treino/classificação carregados do arquivo salvo');
    } else if (lerSessaoSalva('chegada')) {
        // fim de semana completo: o JSON de chegada pode conter treino e classificação
        renderizarClassificacaoQualy(lerSessaoSalva('chegada'), 'Arquivo salvo: ' + (SESSOES_SALVAS['chegada'].nome || 'chegada'));
    }
    const chegadaSalva = lerSessaoSalva('chegada');
    if (chegadaSalva) {
        gerarResultadoChegada(chegadaSalva, false);
        marcarUpload('tileChegada', true, '<i class="bi bi-database-check"></i> Análise carregada do arquivo salvo');
    }

    //testes grid invertido
//     function gerarResultadoGridInvertido(resultados) {

//         console.log('grid invertido');
       
//        var resultados = resultados
//        let sessoesDisponiveis = resultados['sessions'];
//        var pilotos = resultados['players'];

//        sessoesDisponiveis.forEach((sessao,key) => {
//            if(sessao['name'] == 'Quick Race'){
//                var resultadoFinal = resultados['sessions'][key]['raceResult']; //pega o resultado que está na posição em que a chave Quick Race foi encontrada

//                let ordemChegada = [];

//                for (let index = 0; index < resultadoFinal.length; index++) {
//                    ordemChegada.push(pilotos[resultadoFinal[index]]['name']);
//                }

//                const tabela = document.getElementById('tabela');

//                // Obtendo todas as linhas da tabela
//                const linhas = tabela.rows;

//                //deixar o código dinâmico
//                const arrayDeLinhas = Array.from(linhas);

//                //transformar o array em UPPERCASE
//                ordemChegada = ordemChegada.map(item => item.toUpperCase())

//                arrayDeLinhas.forEach((linha, indice) => {
//                    let nomePiloto = linha.cells[1].innerHTML.toUpperCase().trim() //limpa os espaços do inicio e fim da string
//                    nomePiloto = nomePiloto.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); //retira os ascentos
//                    let valorChegada = parseInt(ordemChegada.indexOf(nomePiloto))+1
//                    if(valorChegada > 0){

//                     switch (valorChegada) {
//                         case 1:
//                             valorChegada = 10
//                             break;
//                         case 2:
//                             valorChegada = 9
//                             break;
//                         case 3:
//                             valorChegada = 8
//                             break;
//                         case 4:     
//                             valorChegada = 7
//                             break;
//                         case 5:
//                             valorChegada = 6
//                             break;
//                         case 6:
//                             valorChegada = 5
//                             break;
//                         case 7:
//                             valorChegada = 4
//                             break;
//                         case 8:
//                             valorChegada = 3
//                             break;
//                         case 9:
//                             valorChegada = 2
//                             break;
//                         case 10:
//                             valorChegada = 1
//                             break;
//                     }

//                        linha.cells[2].querySelector('input[type="number"]').value = valorChegada //posição da coluna em que sera adicionada a largada
//                    }
//                });
//            }
//        });
//    }

    function gerarResultadoGridInvertido(resultados){
        var resultados = resultados;
        let sessoesDisponiveis = resultados['sessions'];
        var pilotos = resultados['players'];

        sessoesDisponiveis.forEach((sessao,key) => {
            if(sessao['name'] == 'Qualifying'){

                var resultadoFinal = resultados['sessions'][key]['bestLaps'].sort((a, b) => a.time - b.time); //pega o resultado que está na posição em que a chave Qualyfying foi encontrada
                
                let ordemLargada = [];

                for (let index = 0; index < resultadoFinal.length; index++) {
                    ordemLargada.push(pilotos[resultadoFinal[index]['car']]['name']);
                }

                const tabela = document.getElementById('tabela');

                // Obtendo todas as linhas da tabela
                const linhas = tabela.rows;
                
                //deixar o código dinâmico
                const arrayDeLinhas = Array.from(linhas);

                //transformar o array em UPPERCASE
                ordemLargada = ordemLargada.map(item => item.toUpperCase())

                arrayDeLinhas.forEach((linha, indice) => {
                    let nomePiloto = linha.cells[1].innerHTML.toUpperCase().trim() //limpa os espaços do inicio e fim da string
                    nomePiloto = nomePiloto.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); ///retira os ascentos
                    let valorLargada = parseInt(ordemLargada.indexOf(nomePiloto))+1

                    if(valorLargada > 0){

                        switch (valorLargada) {
                        case 1:
                            valorLargada = 10
                            break;
                        case 2:
                            valorLargada = 9
                            break;
                        case 3:
                            valorLargada = 8
                            break;
                        case 4:     
                            valorLargada = 7
                            break;
                        case 5:
                            valorLargada = 6
                            break;
                        case 6:
                            valorLargada = 5
                            break;
                        case 7:
                            valorLargada = 4
                            break;
                        case 8:
                            valorLargada = 3
                            break;
                        case 9:
                            valorLargada = 2
                            break;
                        case 10:
                            valorLargada = 1
                            break;
                    }

                        linha.cells[2].querySelector('input[type="number"]').value = valorLargada
                    }
                });

            }
        });
    }

    function gerarResultadoLargada(resultados){
        var resultados = resultados;
        let sessoesDisponiveis = resultados['sessions'];
        var pilotos = resultados['players'];

        sessoesDisponiveis.forEach((sessao,key) => {
            if(sessao['name'] == 'Qualifying'){

                var resultadoFinal = resultados['sessions'][key]['bestLaps'].sort((a, b) => a.time - b.time); //pega o resultado que está na posição em que a chave Qualyfying foi encontrada
                
                let ordemLargada = [];

                for (let index = 0; index < resultadoFinal.length; index++) {
                    ordemLargada.push(pilotos[resultadoFinal[index]['car']]['name']);
                }

                const tabela = document.getElementById('tabela');

                // Obtendo todas as linhas da tabela
                const linhas = tabela.rows;
                
                //deixar o código dinâmico
                const arrayDeLinhas = Array.from(linhas);

                //transformar o array em UPPERCASE
                ordemLargada = ordemLargada.map(item => item.toUpperCase())

                arrayDeLinhas.forEach((linha, indice) => {
                    let nomePiloto = linha.cells[1].innerHTML.toUpperCase().trim() //limpa os espaços do inicio e fim da string
                    // nomePiloto = nomePiloto.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); ///retira os ascentos
                    const valorLargada = parseInt(ordemLargada.indexOf(nomePiloto))+1

                    if(valorLargada > 0){
                        linha.cells[2].querySelector('input[type="number"]').value = valorLargada
                    }
                });

            }
        });
    }

    var checkbox = $("#flg_super_corrida");

    // Adiciona um ouvinte de eventos para o evento 'change' (mudança) do checkbox
    checkbox.change(function() {
    // Verifica se o checkbox está marcado
        if (checkbox.is(":checked")) {
            // Esconde todas as linhas quando o checkbox está marcado
            $(".linha_resultados").removeClass("d-none");
        } else {
            // Exibe todas as linhas quando o checkbox está desmarcado
            // $(".linha_resultados").addClass("d-none");
            location.reload()
        }
    });

    var swiper = new Swiper('.swiper-container', {
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>