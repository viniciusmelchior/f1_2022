{{-- MELHORIA 20261105: estilos compartilhados pelas telas de perfil (pilotos/show e equipes/show). Extraído de pilotos/show.blade.php --}}
<style>
    /* Variáveis de Cores Estilo F1 */
    :root {
        --f1-red: #e10600;
        --f1-dark: #15151e;
        --f1-darker: #11101d;
        --f1-gray: #38383f;
        --f1-light: #f4f4f4;
        --f1-text: #ffffff;
    }

    /* === 1. CARD DE PERFIL (HERO CARD) === */
    .driver-profile-card {
        background-color: var(--f1-dark);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        margin-top: 30px;
        margin-bottom: 40px;
        overflow: hidden;
    }

    .profile-header {
        background-color: var(--f1-darker);
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-bottom: 4px solid var(--f1-red);
    }

    .profile-title {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .profile-title h2 {
        color: white;
        margin: 0;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 2rem;
        letter-spacing: -1px;
    }

    .badge-f1 {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .badge-country { background: rgba(255,255,255,0.1); color: white; }
    .badge-active { background: rgba(40, 167, 69, 0.15); color: #28a745; }
    .badge-retired { background: rgba(220, 53, 69, 0.15); color: #dc3545; }

    .season-selector {
        flex-grow: 1;
        max-width: 350px;
        min-width: 250px;
    }

    .f1-select {
        width: 100%;
        background-color: var(--f1-dark);
        color: white;
        border: 2px solid var(--f1-gray);
        border-radius: 6px;
        padding: 10px 15px;
        font-weight: bold;
        text-transform: uppercase;
        transition: 0.3s;
        cursor: pointer;
    }

    .f1-select:focus {
        outline: none;
        border-color: var(--f1-red);
        box-shadow: 0 0 0 3px rgba(225, 6, 0, 0.2);
    }

    .profile-body {
        display: flex;
        padding: 30px;
        gap: 30px;
    }

    .profile-image-container {
        flex: 0 0 300px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .profile-image-container img {
        width: 100%;
        border-radius: 8px;
        border: 2px solid var(--f1-gray);
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }

    .profile-stats-grid {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 15px;
        align-content: start;
    }

    .stat-box {
        background: rgba(255,255,255,0.03);
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid var(--f1-red);
        transition: transform 0.2s, background 0.2s;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        background: rgba(255,255,255,0.08);
    }

    .stat-box span {
        display: block;
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .stat-box strong {
        display: block;
        font-size: 24px;
        color: white;
        font-weight: 900;
        line-height: 1;
    }

    .other-stats { display: none; }
    
    #show-other-stats {
        width: 100%;
        background: transparent;
        border: 2px solid var(--f1-red);
        color: white;
        text-transform: uppercase;
        font-weight: bold;
        border-radius: 6px;
        padding: 10px;
        transition: 0.3s;
    }
    
    #show-other-stats:hover {
        background: var(--f1-red);
    }

    @media (max-width: 992px) {
        .profile-body { flex-direction: column; align-items: center; }
        .profile-image-container { width: 100%; max-width: 400px; }
        .profile-header { justify-content: center; }
    }

    /* === 2. ACCORDIONS DAS TABELAS === */
    details.f1-accordion {
        background: white;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #eee;
        overflow: hidden;
    }

    details.f1-accordion summary {
        background: var(--f1-darker);
        color: white;
        padding: 18px 25px;
        font-size: 1.1rem;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        list-style: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 6px solid var(--f1-red);
        transition: background 0.3s;
    }
    
    details.f1-accordion summary::-webkit-details-marker {
        display: none;
    }

    details.f1-accordion summary:hover {
        background: var(--f1-dark);
    }

    details.f1-accordion summary .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 1.2rem;
    }

    details[open].f1-accordion summary .toggle-icon {
        transform: rotate(180deg);
        color: var(--f1-red);
    }

    .accordion-body {
        padding: 20px;
        background: #fafafa;
    }

    /* === 3. TABELAS === */
    .table-responsive { width: 100%; overflow-x: auto; }
    .f1-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    
    .f1-table th { 
        background: #eaeaea; 
        color: var(--f1-darker); 
        text-transform: uppercase; 
        font-size: 12px; 
        padding: 12px 15px; 
        text-align: left;
        cursor: pointer;
        user-select: none;
    }

    .f1-table th .sort-icon {
        font-size: 11px;
        opacity: 0.4;
        transition: opacity 0.2s, color 0.2s;
        margin-left: 4px;
    }

    .f1-table th:hover .sort-icon {
        opacity: 1;
        color: var(--f1-red);
    }

    .f1-table td { padding: 15px; border-bottom: 1px solid #e0e0e0; vertical-align: middle; color: var(--f1-darker); }
    .f1-table tr:hover { background-color: white; }
    .f1-table a { color: var(--f1-darker); font-size: 1.2rem; transition: 0.2s; }
    .f1-table a:hover { color: var(--f1-red); }

    /* Estilo para Bandeiras */
    .flag-img {
        width: 22px;
        height: 15px;
        object-fit: cover;
        border-radius: 2px;
        margin-right: 6px;
        vertical-align: middle;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    /* Footer Fixo Estilo F1 */
    .footer-show-pilotos {
        position: fixed;
        bottom: 0; left: 0; width: 100%;
        background-color: var(--f1-darker);
        border-top: 3px solid var(--f1-red);
        padding: 15px 10px;
        display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;
        z-index: 1000;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.3);
    }

    .footer-show-pilotos a {
        color: white; text-decoration: none; font-size: 13px; text-transform: uppercase;
        font-weight: 600; padding: 5px 15px; border-radius: 20px;
        background: rgba(255, 255, 255, 0.1); transition: 0.3s; white-space: nowrap;
    }

    .footer-show-pilotos a:hover { background: var(--f1-red); color: white; }
    .main-content-padding { padding-bottom: 100px; }

    /* === MELHORIA 20261105: SPRINTS === */
    .sprint-contador { background: var(--f1-red); color: #fff; border-radius: 20px; padding: 2px 10px; font-size: 0.8rem; margin-left: 10px; vertical-align: middle; }
    .sprint-filtro-info { font-size: 0.85rem; color: #666; margin-bottom: 12px; }
    .sprint-painel { background: var(--f1-dark); border-radius: 8px; padding: 15px; margin-bottom: 20px; }
    .sprint-painel .profile-stats-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
    .sprint-subtitulo { font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: var(--f1-darker); border-left: 4px solid var(--f1-red); padding-left: 8px; margin: 20px 0 10px; }
    .sprint-pos { display: inline-block; min-width: 34px; text-align: center; font-weight: 800; border-radius: 4px; padding: 2px 6px; background: #eaeaea; }
    .sprint-pos.p1 { background: #f5c518; } .sprint-pos.p2 { background: #c9ccd1; } .sprint-pos.p3 { background: #d4925b; }
    .sprint-pos.pontua { background: #d8f0e0; }
    .sprint-delta.sobe { color: #198754; font-weight: 700; } .sprint-delta.desce { color: #dc3545; font-weight: 700; } .sprint-delta.igual { color: #888; }
    .sprint-tag { display: inline-block; font-size: 10px; font-weight: 800; border-radius: 3px; padding: 2px 6px; margin-right: 4px; color: #fff; }
    .sprint-tag.dnf { background: #b81414; } .sprint-tag.vmr { background: #8a2be2; }
    .sprint-vazio { text-align: center; color: #888; font-style: italic; }
    /* temporada que expande as sprints */
    .sprint-temporada { cursor: pointer; }
    .sprint-temporada:hover { background-color: #fff; }
    .sprint-temporada .sprint-seta { display: inline-block; margin-right: 8px; color: var(--f1-red); transition: transform 0.2s; }
    .sprint-temporada.aberta .sprint-seta { transform: rotate(90deg); }
    .sprint-temporada.aberta td { border-bottom-color: transparent; }
    .sprint-detalhe.oculto { display: none; }
    .sprint-detalhe > td { padding: 0 0 15px 0 !important; background: #fafafa; }
    .sprint-detalhe .sprint-corridas { width: 100%; border-collapse: collapse; border-left: 4px solid var(--f1-red); background: #fff; }
    .sprint-detalhe .sprint-corridas th { background: #f2f2f2; font-size: 11px; text-transform: uppercase; padding: 8px 12px; color: #555; cursor: default; }
    .sprint-detalhe .sprint-corridas td { padding: 10px 12px; border-bottom: 1px solid #eee; }
    @media (max-width: 768px) { .main-content-padding { padding-bottom: 140px; } }

    /* === MELHORIA 20261105: padronização pilotos/equipes === */
    /* logo da equipe sobre a cor dela */
    .team-logo-box { width: 100%; aspect-ratio: 4 / 3; border-radius: 8px; border: 2px solid var(--f1-gray); display: flex; align-items: center; justify-content: center; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
    .profile-image-container .team-logo-box img { width: auto; max-width: 100%; max-height: 100%; border: 0; box-shadow: none; border-radius: 0; object-fit: contain; }
    .stat-box.destaque { border-left-color: #f5c518; }
    .stat-box small { display: block; color: #888; font-size: 11px; margin-top: 6px; }
    /* links para outros perfis (piloto <-> equipe) */
    .f1-table a.link-perfil, a.link-perfil { font-size: inherit; color: var(--f1-darker); text-decoration: none; font-weight: 700; }
    .f1-table a.link-perfil:hover, a.link-perfil:hover { color: var(--f1-red); text-decoration: underline; }
    .mini-img { width: 25px; height: 25px; border-radius: 3px; margin-right: 8px; object-fit: cover; vertical-align: middle; }
    .mini-avatar { width: 28px; height: 28px; border-radius: 50%; margin-right: 8px; object-fit: cover; vertical-align: middle; background: #ddd; }
    /* barra de ações acima dos acordeões */
    .perfil-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
    .perfil-toolbar .filtro-ativo { font-size: 0.9rem; color: #555; }
    .perfil-toolbar .filtro-ativo strong { color: var(--f1-red); }
    .perfil-toolbar button { background: var(--f1-darker); color: #fff; border: 0; border-radius: 20px; padding: 6px 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .perfil-toolbar button:hover { background: var(--f1-red); }
    .secao-contador { background: rgba(255,255,255,0.15); color: #fff; border-radius: 20px; padding: 2px 10px; font-size: 0.8rem; margin-left: 10px; vertical-align: middle; }
    .alerta-sequencia { background: var(--f1-dark); color: #fff; border-radius: 8px; padding: 12px 15px; margin-bottom: 15px; text-align: center; }
    .alerta-sequencia strong { color: #f5c518; }
    .tag-titulo { display: inline-block; background: #f5c518; color: #15151e; font-size: 10px; font-weight: 800; border-radius: 3px; padding: 2px 6px; margin-left: 6px; text-transform: uppercase; }
    .linha-filtrada { background: #fff7e0 !important; }
    /* ordenação ao clicar no cabeçalho */
    .f1-table th.ordenado .sort-icon { opacity: 1; color: var(--f1-red); }
    .f1-table th:not(:has(.sort-icon)) { cursor: default; }
    .grafico-pontos { position: relative; height: 320px; margin-bottom: 20px; background: #fff; border-radius: 8px; padding: 10px; }
</style>
