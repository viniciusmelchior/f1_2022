@php 
   
@endphp

@extends('layouts.main')

@section('section')

<div class="container-fluid mt-3 mb-3">
  <header class="topbar">
        {{-- <div class="header-left">
            <div class="f1-logo"><span class="f1-mark">F</span><span class="f1-num">1</span></div>
        </div> --}}
        
        <nav class="tabs">
            <button class="tab active" data-view="drivers">PILOTOS</button>
            <button class="tab" data-view="teams">EQUIPES</button>
        </nav>

        <div class="season-badge">
            <div class="season-label">{{$temporada->des_temporada}}</div>
            <div class="season-year">{{$temporada->referencia}}</div>
        </div>
    </header>
    <div class="container-escuro">
        <main class="container main-custom">
            <div class="race-nav">
                <button id="prev-race" class="nav-btn"><span class="nav-text">← Anterior</span><span class="nav-icon">←</span></button>
                <div class="location-header" id="location-data">
                    <div class="circuit-icon">
                        <svg viewBox="0 0 24 24" width="32" height="32"><path fill="#e10600" d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z"/></svg>
                    </div>
                    <div class="location-info">
                        <h1 class="page-title" id="gp-title">...</h1>
                        <p class="subtitle">
                            <img src="" id="gp-flag" class="flag-img" alt=""> 
                            <span class="gp-details">
                                <strong id="gp-name">...</strong> (<span id="gp-city">...</span>) • Etapa <span id="gp-stage">...</span>
                            </span>
                        </p>
                    </div>
                </div>
                <button id="next-race" class="nav-btn"><span class="nav-text">Próxima →</span><span class="nav-icon">→</span></button>
            </div>

            <div class="table-head">
                <span class="pos-col">POS</span><span id="col-name">PILOTO</span><span class="points-col">PTS</span><span class="diff-col">DIF</span>
            </div>

            <div id="standings-content"></div>

            <div class="actions">
                <button class="back-btn" onclick="window.history.back()">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/></svg>
                    VOLTAR
                </button>
            </div>
        </main>
    </div>

    
</div>

<script>
    const INITIAL_STATE = <?= json_encode($initialData) ?>
</script>

@endsection
