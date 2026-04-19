@php 
    use App\Models\Site\ImagensCorrida;
    use App\Models\Site\PilotoEquipe;
@endphp

@extends('layouts.main')

@section('section')
  <style>
        :root {
            --f1-black: #15151e;
            --f1-red: #e10600;
            --mclaren: #ff8700;
            --mercedes: #00d2be;
            --ferrari: #dc0000;
            --placeholder-gold: #ccff00;
            --text-white: #ffffff;
            --text-gray: #aeaeae;
            --row-bg: #1f1f27;
        }

        .bodyDark {
            background-color: var(--f1-black);
            color: var(--text-white);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 10px;
            border-radius: 4px;
        }

        /* --- LAYOUT GERAL --- */
        .f1-container {
            width: 100%;
            max-width: 750px;
            /* Aumentado levemente para telas de PC */
            /* border-top: 6px double var(--f1-red); */
            padding-top: 10px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            padding: 20px 0;
            text-transform: uppercase;
            font-weight: 900;
        }

        .header h1 {
            font-size: 1.3rem;
            margin: 0;
        }

        .header span {
            color: var(--text-gray);
        }

        /* --- NAVEGAÇÃO --- */
        .tabs-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 2px solid #222;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--text-gray);
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 900;
            text-transform: uppercase;
            transition: 0.3s;
        }

        .tab-btn.active {
            color: var(--f1-red);
            border-bottom: 3px solid var(--f1-red);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* --- PÓDIO (CHEGADA) --- */
        .podium {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 35px;
            height: 400px;
        }

        .driver-card {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            overflow: hidden;
            clip-path: polygon(0 7%, 100% 0, 100% 100%, 0% 100%);
            border-radius: 4px;
        }

        .p1 {
            height: 100%;
            background: linear-gradient(180deg, var(--mercedes) 0%, #000 140%);
        }

        .p2 {
            height: 85%;
            /* background: linear-gradient(180deg, var(--mclaren) 0%, #000 140%); */
        }

        .p3 {
            height: 75%;
            background: linear-gradient(180deg, var(--ferrari) 0%, #000 140%);
        }

        .pos-number {
            position: absolute;
            top: 15px;
            left: 10px;
            font-size: 4.5rem;
            font-weight: 900;
            opacity: 0.8;
            z-index: 2;
        }

        .driver-photo-main {
            position: absolute;
            top: 0px;
            left: 0;
            width: 100%;
            height: auto;
            object-fit: cover;
            z-index: 1;
        }

        .driver-info {
            position: relative;
            z-index: 3;
            width: 100%;
        }

        .points-badge {
            background: rgba(0, 0, 0, 0.8);
            padding: 5px 12px;
            border-radius: 4px 0 0 0;
            align-self: flex-end;
            text-align: center;
            border-bottom: 2px solid var(--placeholder-gold);
            width: fit-content;
            margin-left: auto;
        }

        .points-badge b {
            font-size: 1.1rem;
            color: var(--placeholder-gold);
        }

        .points-badge span {
            font-size: 0.6rem;
            display: block;
        }

        .name-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            background-color: var(--mercedes);
            /* Default para o pódio */
        }

        .driver-name-dynamic {
            font-weight: 900;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        /* --- LISTA DE RESULTADOS --- */
        .result-row {
            display: grid;
            grid-template-columns: 35px 30px 1.2fr 30px 1fr 70px;
            align-items: center;
            background: var(--row-bg);
            padding: 12px 15px;
            border-radius: 4px;
            gap: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .row-pos {
            font-size: 1.1rem;
            font-weight: 900;
            font-style: italic;
        }

        .row-driver-name {
            font-size: 0.85rem;
            font-weight: 400;
        }

        .row-driver-name b {
            font-weight: 900;
        }

        .row-team-name {
            color: var(--text-gray);
            font-size: 0.75rem;
        }

        .row-points {
            text-align: right;
            color: var(--placeholder-gold);
            font-weight: 900;
        }

        /* --- IMAGENS GERAIS --- */
        .img-icon {
            object-fit: contain;
        }

        .flag-img {
            width: 22px;
            height: 14px;
            object-fit: cover;
        }

        .team-logo-img {
            width: 24px;
            height: 24px;
        }

        /* --- GRID DE LARGADA --- */
        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 25px;
            row-gap: 35px;
            padding: 20px 0;
        }

        .grid-item {
            display: flex;
            align-items: center;
            background: var(--row-bg);
            height: 45px;
            border-radius: 3px;
            border-left: 4px solid var(--f1-red);
        }

        .grid-pos {
            width: 35px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-style: italic;
            background: rgba(255, 255, 255, 0.05);
        }

        .grid-driver-content {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 10px;
        }

        .grid-mini-face-img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
        }

        .grid-item:nth-child(even) {
            transform: translateY(25px);
        }

        /* --- RESPONSIVIDADE --- */
        @media (max-width: 600px) {
            .podium {
                height: 300px;
                gap: 5px;
            }

            .pos-number {
                font-size: 3rem;
            }

            .driver-name-dynamic {
                font-size: 0.7rem;
            }

            .result-row {
                grid-template-columns: 30px 25px 1fr 25px 60px;
                font-size: 0.75rem;
            }

            .row-team-name {
                display: none;
            }

            /* Esconde nome da equipe no mobile */
            .grid-layout {
                grid-template-columns: 1fr;
                row-gap: 10px;
            }

            .grid-item:nth-child(even) {
                transform: none;
            }
        }

        /* Estilo para a área de texto informativa */
        .info-textarea {
            width: 100%;
            background-color: #1f1f27;
            color: var(--text-gray);
            border: 1px solid #333;
            border-left: 4px solid var(--f1-red);
            padding: 15px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            line-height: 1.5;
            resize: none;
            /* Impede o usuário de redimensionar */
            border-radius: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .update-timestamp {
            font-size: 0.75rem;
            color: var(--text-gray);
            font-style: italic;
            margin-bottom: 30px;
            display: block;
        }

        /* Botão Voltar no final do layout */
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            padding-bottom: 40px;
        }

        .btn-back {
            background-color: transparent;
            color: var(--text-white);
            border: 2px solid var(--text-white);
            padding: 10px 30px;
            text-transform: uppercase;
            font-weight: 900;
            cursor: pointer;
            transition: 0.3s;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: var(--text-white);
            color: var(--f1-black);
        }

        /* Container do Header mais robusto */
        .header {
            text-align: center;
            padding: 30px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .header-top-row {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.75rem;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .header-main-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 5px;
        }

        .header-main-row h1 {
            font-size: 1.8rem;
            margin: 0;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: -1px;
        }

        /* Badge de Pista/Circuito */
        .track-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #2a2a32;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: var(--placeholder-gold);
            border: 1px solid #444;
            text-transform: uppercase;
            font-weight: 700;
        }

        /* Estilo para os ícones (usando emojis ou SVGs simples) */
        .f1-icon {
            font-style: normal;
            opacity: 0.8;
        }

        .report-title-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #333;
        }

        .report-title-container h2 {
            margin: 0;
            font-size: 1.2rem;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 1px;
            color: var(--text-white);
        }

        /* Ícone estilizado com fundo vermelho F1 */
        .report-icon {
            background-color: var(--f1-red);
            color: white;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            font-size: 0.9rem;
            font-style: normal;
        }

        /* Detalhe estético: uma pequena linha vermelha abaixo do título */
        .report-title-container::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--f1-red);
        }

        .report-title-container {
            position: relative;
            /* Para a linha ::after funcionar */
        }
    </style>
    <div class="div bodyDark">
    <nav class="tabs-nav">
        <button class="tab-btn active" onclick="openTab(event, 'chegada')">Chegada</button>
        <button class="tab-btn" onclick="openTab(event, 'largada')">Largada</button>
        <button class="tab-btn" onclick="openTab(event, 'mais_infos')">Outras informações</button>
    </nav>

    <header class="header">
        <div class="header-top-row">
            {{-- <span><i class="f1-icon">📅</i> 1ª Temporada (2023)</span> --}}
            <span><i class="f1-icon">📅</i>{{$corrida->temporada->des_temporada}} ({{$corrida->temporada->referencia}})</span>
            <span>•</span>
            <span><i class="f1-icon">🏁</i> Etapa {{$corrida->ordem}}</span>
        </div>

        <div class="header-main-row">
            <img src="{{asset('images/'.$corrida->pista->pais->imagem)}}" class="flag-img" style="width: 35px; height: 20px;" alt="Japão">
            <h1>{{$corrida->evento->des_nome}}</h1>
        </div>

        <div class="track-badge">
            <i class="f1-icon">📍</i>
            <span>{{$corrida->pista->nome}}</span>
        </div>
    </header>

    <div id="chegada" class="tab-content active">
    <div class="f1-container">
        
        <div class="podium">
    @php
        // Criamos uma coleção chaveada pela posição para facilitar o acesso
        $posicoes = $model->sortBy('largada')->keyBy('chegada');
    @endphp

    {{-- Renderiza o 2º LUGAR (Esquerda) --}}
    @if(isset($posicoes[2]))
        @php $p2 = $posicoes[2]; @endphp
        <div class="driver-card p2" style="background: linear-gradient(180deg, {{$p2->pilotoEquipe->equipe->des_cor}} 0%, #000 140%)">
            <div class="pos-number">{{$p2->chegada}}</div>
            <img src="{{asset('images/'.$p2->pilotoEquipe->piloto->imagem)}}" class="driver-photo-main" alt="Driver">
            <div class="driver-info">
                <div class="points-badge"><b>{{$p2->pontuacao}}</b><span>PTS</span></div>
                <div class="name-container" style="background-color: {{$p2->pilotoEquipe->equipe->des_cor}}">
                    <img src="{{asset('images/'.$p2->pilotoEquipe->piloto->pais->imagem)}}" class="flag-img" alt="Flag">
                    <span class="driver-name-dynamic" style="color: #000;">{{$p2->pilotoEquipe->piloto->sobrenome}}</span>
                    <img src="{{asset('images/'.$p2->pilotoEquipe->equipe->imagem)}}" class="team-logo-img" alt="Logo">
                </div>
            </div>
        </div>
    @endif

    {{-- Renderiza o 1º LUGAR (Centro) --}}
   @if(isset($posicoes[1]))
        @php $p1 = $posicoes[1]; @endphp
        <div class="driver-card p1" style="background: linear-gradient(180deg, {{$p1->pilotoEquipe->equipe->des_cor}} 0%, #000 140%)">
            <div class="pos-number">{{$p1->chegada}}</div>
            <img src="{{asset('images/'.$p1->pilotoEquipe->piloto->imagem)}}" class="driver-photo-main" alt="Driver">
            <div class="driver-info">
                <div class="points-badge"><b>{{$p1->pontuacao}}</b><span>PTS</span></div>
                <div class="name-container" style="background-color: {{$p1->pilotoEquipe->equipe->des_cor}}">
                    <img src="{{asset('images/'.$p1->pilotoEquipe->piloto->pais->imagem)}}" class="flag-img" alt="Flag">
                    <span class="driver-name-dynamic" style="color: {{ PilotoEquipe::getContrastColor($p1->pilotoEquipe->equipe->des_cor) }}">{{$p1->pilotoEquipe->piloto->sobrenome}}</span>
                    <img src="{{asset('images/'.$p1->pilotoEquipe->equipe->imagem)}}" class="team-logo-img" alt="Logo">
                </div>
            </div>
        </div>
    @endif

    {{-- Renderiza o 3º LUGAR (Direita) --}}
    @if(isset($posicoes[3]))
        @php $p3 = $posicoes[3]; @endphp
        <div class="driver-card p3" style="background: linear-gradient(180deg, {{$p3->pilotoEquipe->equipe->des_cor}} 0%, #000 140%)">
            <div class="pos-number">{{$p3->chegada}}</div>
            <img src="{{asset('images/'.$p3->pilotoEquipe->piloto->imagem)}}" class="driver-photo-main" alt="Driver">
            <div class="driver-info">
                <div class="points-badge"><b>{{$p3->pontuacao}}</b><span>PTS</span></div>
                <div class="name-container" style="background-color: {{$p3->pilotoEquipe->equipe->des_cor}}">
                    <img src="{{asset('images/'.$p3->pilotoEquipe->piloto->pais->imagem)}}" class="flag-img" alt="Flag">
                    <span class="driver-name-dynamic" style="color: #000;">{{$p3->pilotoEquipe->piloto->sobrenome}}</span>
                    <img src="{{asset('images/'.$p3->pilotoEquipe->equipe->imagem)}}" class="team-logo-img" alt="Logo">
                </div>
            </div>
        </div>
    @endif
</div>
        <div class="results-list">
            @foreach ($model->where('chegada', '>=', 4)->sortBy('chegada') as $item)
                <div class="result-row">
                    <span class="row-pos" @if($item->flg_abandono == 'S') style="color: var(--f1-red)" @endif>{{ $item->chegada }}</span>
                    <img src="{{asset('images/'.$item->pilotoEquipe->piloto->pais->imagem)}}" class="flag-img">
                    <span class="row-driver-name">{{ $item->pilotoEquipe->piloto->nome }}<b> {{ $item->pilotoEquipe->piloto->sobrenome }}</b></span>
                    <img src="{{asset('images/'.$item->pilotoEquipe->equipe->imagem)}}" class="team-logo-img">
                    <span class="row-team-name">{{ $item->pilotoEquipe->equipe->nome }}</span>
                    <span class="row-points">{{ $item->pontuacao }} PTS</span>
                </div>
            @endforeach
        </div> </div>
</div>

@php 
    $largada = $model->sortBy('largada')->keyBy('largada');
    // dd($largada);
@endphp

    <div id="largada" class="tab-content">
        <div class="f1-container">
            <div class="grid-layout">
                @foreach( $largada as $item )
                <div class="grid-item" style="border-left-color: {{$item->pilotoEquipe->equipe->des_cor}};">
                    <div class="grid-pos">{{$item->largada}}</div>
                    <div class="grid-driver-content">
                        <img src="{{asset('images/'.$item->pilotoEquipe->equipe->imagem)}}" class="team-logo-img">
                        <img src="{{asset('images/'.$item->pilotoEquipe->piloto->imagem)}}" class="grid-mini-face-img">
                        <span class="row-driver-name">{{$item->pilotoEquipe->piloto->nome}} <b>{{$item->pilotoEquipe->piloto->sobrenome}}</b></span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="mais_infos" class="tab-content">
        <div class="f1-container">

            <div class="report-title-container">
                <i class="report-icon">📊</i>
                <h2>Relatório Técnico do GP</h2>
            </div>

        <textarea class="info-textarea" rows="10" readonly>
           {{$corrida->observacoes}}
        </textarea>

            <span class="update-timestamp">
                <i class="f1-icon">🕒</i> Atualizado em {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:i:s', strtotime($corrida->updated_at))}}
            </span>

        </div>
    </div>

    <div class="btn-container">
        <a href="{{route('temporadas.index')}}" class="btn-back">Voltar</a>
    </div>
    </div>
    <script>
         function openTab(evt, tabName) {
            const contents = document.querySelectorAll(".tab-content");
            const btns = document.querySelectorAll(".tab-btn");
            contents.forEach(c => c.classList.remove("active"));
            btns.forEach(b => b.classList.remove("active"));
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
@endsection