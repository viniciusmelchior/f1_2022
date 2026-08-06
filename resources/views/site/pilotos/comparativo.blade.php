@extends('layouts.main')

@section('section')
<!-- Importando a fonte estilo F1 (Titillium Web) -->
<link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

<style>
    /* =========================================
       TEMA F1 - ESTILOS GERAIS
       ========================================= */
    :root {
        --f1-red: #e10600;
        --f1-dark: #15151e;
        --f1-darker: #000000;
        --f1-grey: #38383f;
        --f1-light-grey: #f3f3f3;
        --f1-text: #ffffff;
        --f1-border: #2a2a35;
    }

    body {
        font-family: 'Titillium Web', sans-serif;
        background-color: var(--f1-light-grey);
    }

    /* Responsividade: Flex direction muda em telas menores */
    .f1-container {
        display: flex;
        flex-direction: column; 
        gap: 2rem;
        margin-top: 2rem;
        margin-bottom: 3rem;
        width: 100%;
    }

    @media (min-width: 992px) {
        .f1-container {
            flex-direction: row;
            align-items: flex-start;
        }
        #form-container { width: 35%; position: sticky; top: 20px; }
        #tabela-container { width: 65%; }
    }

    /* =========================================
       FORMULÁRIO E INPUTS (LADO ESQUERDO)
       ========================================= */
    .f1-panel {
        background-color: var(--f1-text);
        border-radius: 12px;
        border-top: 5px solid var(--f1-red);
        padding: 25px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        width: 100%;
    }

    .f1-form-control {
        font-family: 'Titillium Web', sans-serif;
        font-size: 1.1rem;
        font-weight: 600;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 12px 15px;
        background-color: #fafafa;
        color: var(--f1-dark);
        transition: all 0.3s ease;
        width: 100%;
        margin-bottom: 20px;
        cursor: pointer;
    }

    .f1-form-control:focus {
        border-color: var(--f1-red);
        box-shadow: 0 0 0 3px rgba(225, 6, 0, 0.15);
        outline: none;
        background-color: #fff;
    }

    /* =========================================
       SCROLL DA TABELA DE PILOTOS
       ========================================= */
    .tabela-pilotos-wrapper {
        max-height: 350px; /* Limita a altura e cria o scroll */
        overflow-y: auto;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    /* Customizando a barra de rolagem */
    .tabela-pilotos-wrapper::-webkit-scrollbar { width: 8px; }
    .tabela-pilotos-wrapper::-webkit-scrollbar-track { background: #f9f9f9; border-radius: 8px; }
    .tabela-pilotos-wrapper::-webkit-scrollbar-thumb { background: #ccc; border-radius: 8px; }
    .tabela-pilotos-wrapper::-webkit-scrollbar-thumb:hover { background: var(--f1-red); }

    .f1-table-selection {
        width: 100%;
        border-collapse: collapse;
    }

    .f1-table-selection thead th {
        position: sticky;
        top: 0;
        background-color: var(--f1-dark);
        color: var(--f1-text);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
        padding: 12px;
        text-align: left;
        z-index: 10;
    }

    .f1-table-selection td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        font-weight: 600;
        color: var(--f1-dark);
        vertical-align: middle;
    }

    .f1-table-selection tr:last-child td { border-bottom: none; }
    .f1-table-selection tr:hover td { background-color: rgba(225, 6, 0, 0.05); }

    .f1-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: var(--f1-red);
    }

    /* =========================================
       BOTÕES
       ========================================= */
    .f1-btn-group {
        display: flex;
        gap: 15px;
    }

    .f1-btn {
        font-family: 'Titillium Web', sans-serif;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        flex: 1;
        text-align: center;
    }

    .f1-btn-primary { background-color: var(--f1-red); color: var(--f1-text); }
    .f1-btn-primary:hover { background-color: #b30500; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(225,6,0,0.3); }
    .f1-btn-secondary { background-color: var(--f1-grey); color: var(--f1-text); text-decoration: none; }
    .f1-btn-secondary:hover { background-color: var(--f1-dark); color: var(--f1-text); }

    /* =========================================
       TABELA DE COMPARAÇÃO (LADO DIREITO - MODERNA)
       ========================================= */
    .div_tabela {
        width: 100%;
        overflow-x: auto; /* Permite scroll horizontal no celular se necessário */
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    }

    .tabela_comparativos {
        width: 100%;
        min-width: 600px; /* Garante que o layout não esmague no mobile */
        text-align: center;
        background-color: var(--f1-dark);
        color: var(--f1-text);
        border-collapse: collapse;
        display: none; 
    }

    .tabela_comparativos thead th {
        padding: 30px 15px;
        background-color: var(--f1-darker);
        vertical-align: bottom;
        width: 35%;
        border-bottom: 3px solid var(--f1-red);
    }

    .tabela_comparativos thead th.col-centro {
        width: 30%;
        vertical-align: middle;
        border-bottom: 3px solid var(--f1-grey);
    }

    /* Linhas zebradas super sutis */
    .tabela_comparativos tbody tr {
        border-bottom: 1px solid var(--f1-border);
        transition: background-color 0.2s;
    }
    .tabela_comparativos tbody tr:nth-child(even) { background-color: #1a1a24; }
    .tabela_comparativos tbody tr:hover { background-color: #242430; }

    .tabela_comparativos tbody tr td {
        padding: 18px 10px;
        font-size: 1.5rem;
        font-weight: 700;
        width: 35%;
    }

    /* Estilo do título da estatística no centro */
    .desc_comparativo {
        text-transform: uppercase;
        font-size: 0.85rem !important;
        color: #999;
        letter-spacing: 2px;
        font-weight: 600 !important;
        border-left: 1px solid var(--f1-border);
        border-right: 1px solid var(--f1-border);
        background-color: rgba(255, 255, 255, 0.02);
        width: 30% !important;
    }

    /* Enquadramento das fotos e Nomes */
    .img-piloto-wrapper {
        background: linear-gradient(145deg, #2a2a35 0%, #15151e 100%);
        border-radius: 12px;
        padding-top: 15px;
        margin: 0 auto 15px auto;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        width: 140px;
        height: 140px;
        border: 1px solid var(--f1-border);
        overflow: hidden;
    }

    #piloto1-imagem, #piloto2-imagem {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: bottom;
        transform: scale(1.15);
    }

    .desc_piloto {
        text-transform: uppercase;
        font-size: 1.4rem;
        font-weight: 700;
        display: block;
        letter-spacing: 1px;
        color: var(--f1-text);
    }

    .vs-badge {
        display: inline-block;
        background-color: var(--f1-red);
        color: white;
        font-size: 1.2rem;
        padding: 5px 20px;
        border-radius: 30px;
        font-weight: 700;
        font-style: italic;
        letter-spacing: 2px;
        box-shadow: 0 4px 15px rgba(225,6,0,0.4);
    }

</style>

<div class="f1-container container">
    {{-- ESCOLHA DA TEMPORADA E PILOTOS --}}
    <div id="form-container">
        <div class="f1-panel">
            <form method="POST" action="" id="form-comparativos">
                <h4 style="text-transform: uppercase; font-weight: 700; margin-bottom: 20px; color: var(--f1-dark); letter-spacing: -0.5px;">
                    Configurar Comparativo
                </h4>
                
                <div class="mb-3">
                    <select name="temporada_id" id="temporada_id" class="f1-form-control">
                        <option value="">SELECIONAR TEMPORADA</option>
                        @foreach($temporadas as $temporada)
                            <option value="{{$temporada->id}}">{{$temporada->des_temporada}} ({{$temporada->referencia}})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Wrapper para scroll na tabela --}}
                <div class="tabela-pilotos-wrapper">
                    <table class="f1-table-selection" id="tabela-pilotos">
                        <thead>
                            <tr>
                                <th style="width: 15%;">#</th>
                                <th>Piloto</th>
                                <th style="text-align: center; width: 20%;">Selecione</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-pilotos-tbody">
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 25px; color: #888;">
                                    Selecione uma temporada acima
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="f1-btn-group">
                    {{-- <button type="submit" class="f1-btn f1-btn-primary">Comparar</button> --}}
                    <a href="" class="f1-btn f1-btn-secondary">Voltar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- PAINEL DE RESULTADOS (LAYOUT CENTRALIZADO) --}}
    <div id="tabela-container">
        <div class="div_tabela">
            <table class="tabela_comparativos">
                <thead>
                    <tr>
                        <th>
                            <div class="img-piloto-wrapper">
                                <img src="" alt="" id="piloto1-imagem">
                            </div>
                            <span class="desc_piloto" id="piloto1-desc"></span>
                        </th>
                        <th class="col-centro">
                            <div class="vs-badge">VS</div>
                        </th>
                        <th>
                            <div class="img-piloto-wrapper">
                                <img src="" alt="" id="piloto2-imagem">
                            </div>
                            <span class="desc_piloto" id="piloto2-desc"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Os nomes das estatísticas agora ficam no meio para um layout mais moderno -->
                    <tr>
                        <td id="piloto1TotPontos"></td>
                        <td class="desc_comparativo">Pontos</td>
                        <td id="piloto2TotPontos"></td>
                    </tr>
                    <tr>
                        <td id="piloto1TotVitorias"></td>
                        <td class="desc_comparativo">Vitórias</td>
                        <td id="piloto2TotVitorias"></td>
                    </tr>
                    <tr>
                        <td id="piloto1TotPolePositions"></td>
                        <td class="desc_comparativo">Pole Positions</td>
                        <td id="piloto2TotPolePositions"></td>
                    </tr>
                    <tr>
                        <td id="piloto1Chegada"></td>
                        <td class="desc_comparativo">Corridas</td>
                        <td id="piloto2Chegada"></td>
                    </tr>
                    <tr>
                        <td id="piloto1Largada"></td>
                        <td class="desc_comparativo">Classificação</td>
                        <td id="piloto2Largada"></td>
                    </tr>
                    <tr>
                        <td id="piloto1TotPodios"></td>
                        <td class="desc_comparativo">Pódios</td>
                        <td id="piloto2TotPodios"></td>
                    </tr>
                    <tr>
                        <td id="piloto1TotAbandonos"></td>
                        <td class="desc_comparativo">Abandonos (DNF)</td>
                        <td id="piloto2TotAbandonos"></td>
                    </tr>
                    <tr>
                        <td id="piloto1TotVoltasRapidas"></td>
                        <td class="desc_comparativo">Voltas Rápidas</td>
                        <td id="piloto2TotVoltasRapidas"></td>
                    </tr>
                    <tr>
                        <td id="piloto1MelhorChegada"></td>
                        <td class="desc_comparativo">Melhor Chegada</td>
                        <td id="piloto2MelhorChegada"></td>
                    </tr>
                    <tr>
                        <td id="piloto1PiorChegada"></td>
                        <td class="desc_comparativo">Pior Chegada</td>
                        <td id="piloto2PiorChegada"></td>
                    </tr>
                    <tr>
                        <td id="piloto1MelhorLargada"></td>
                        <td class="desc_comparativo">Melhor Grid</td>
                        <td id="piloto2MelhorLargada"></td>
                    </tr>
                    <tr>
                        <td id="piloto1PiorLargada"></td>
                        <td class="desc_comparativo">Pior Grid</td>
                        <td id="piloto2PiorLargada"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    urlcomparativos = "<?=route('ajax.comparativos')?>"
    urlPilotosPorTemporada = "<?=route('ajax.getPilotosPorTemporada')?>"

    $('#form-comparativos').submit(function (e) { 
        e.preventDefault();
        
        // Impede a submissão se não tiver exatamente 2 pilotos selecionados
        if($('input.piloto_id:checkbox:checked').length !== 2){
            alert("Selecione exatamente 2 pilotos para comparar.");
            return;
        }

        pilotosId = [];
        temporada_id = $('#temporada_id').val();

        tabela_comparativos = $('.tabela_comparativos');
        piloto1_desc = $('#piloto1-desc');
        piloto2_desc = $('#piloto2-desc');
        piloto1_imagem = $('#piloto1-imagem');
        piloto2_imagem = $('#piloto2-imagem');

        piloto1TotPontos = $('#piloto1TotPontos');
        piloto2TotPontos = $('#piloto2TotPontos');
        piloto1TotVitorias = $('#piloto1TotVitorias');
        piloto2TotVitorias = $('#piloto2TotVitorias');
        piloto1TotPolePositions = $('#piloto1TotPolePositions');
        piloto2TotPolePositions = $('#piloto2TotPolePositions');
        piloto1Chegada = $('#piloto1Chegada');
        piloto2Chegada = $('#piloto2Chegada');
        piloto1Largada = $('#piloto1Largada');
        piloto2Largada = $('#piloto2Largada');
        piloto1TotPodios = $('#piloto1TotPodios');
        piloto2TotPodios = $('#piloto2TotPodios');
        piloto1TotAbandonos = $('#piloto1TotAbandonos');
        piloto2TotAbandonos = $('#piloto2TotAbandonos');
        piloto1TotVoltasRapidas = $('#piloto1TotVoltasRapidas');
        piloto2TotVoltasRapidas = $('#piloto2TotVoltasRapidas');
        piloto1MelhorChegada = $('#piloto1MelhorChegada');
        piloto2MelhorChegada = $('#piloto2MelhorChegada');
        piloto1PiorChegada = $('#piloto1PiorChegada');
        piloto2PiorChegada = $('#piloto2PiorChegada');
        piloto1MelhorLargada = $('#piloto1MelhorLargada');
        piloto2MelhorLargada = $('#piloto2MelhorLargada');
        piloto1PiorLargada = $('#piloto1PiorLargada');
        piloto2PiorLargada = $('#piloto2PiorLargada');
        
        $('input.piloto_id:checkbox:checked').each(function () {
            pilotosId.push($(this).val());
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: urlcomparativos,
            data: {
                pilotosId: pilotosId,
                temporada_id: temporada_id
            },
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                tabela_comparativos.css('display','table'); 

                piloto1_desc.text(`${response.dadosPiloto1[0]['nome']} ${response.dadosPiloto1[0]['sobrenome']}`)
                piloto2_desc.text(`${response.dadosPiloto2[0]['nome']} ${response.dadosPiloto2[0]['sobrenome']}`)
                
                piloto1_imagem.attr("src", `https://f1.vitorvasconcellos.com.br/images/${response.dadosPiloto1[0]['imagem']}`)
                piloto2_imagem.attr("src", `https://f1.vitorvasconcellos.com.br/images/${response.dadosPiloto2[0]['imagem']}`)

                piloto1TotPontos.text(response.piloto1TotPontos)
                piloto2TotPontos.text(response.piloto2TotPontos)
                piloto1TotVitorias.text(response.piloto1TotVitorias);
                piloto2TotVitorias.text(response.piloto2TotVitorias);
                piloto1TotPolePositions.text(response.piloto1TotPolePositions)
                piloto2TotPolePositions.text(response.piloto2TotPolePositions)
                piloto1Chegada.text(response.piloto1Chegada)
                piloto2Chegada.text(response.piloto2Chegada)
                piloto1Largada.text(response.piloto1Largada)
                piloto2Largada.text(response.piloto2Largada)
                piloto1TotPodios.text(response.piloto1TotPodios)
                piloto2TotPodios.text(response.piloto2TotPodios)
                piloto1TotAbandonos.text(response.piloto1TotAbandonos)
                piloto2TotAbandonos.text(response.piloto2TotAbandonos)
                piloto1TotVoltasRapidas.text(response.piloto1TotVoltasRapidas)
                piloto2TotVoltasRapidas.text(response.piloto2TotVoltasRapidas)
                piloto1MelhorChegada.text(response.piloto1MelhorChegada);
                piloto2MelhorChegada.text(response.piloto2MelhorChegada);
                piloto1PiorChegada.text(response.piloto1PiorChegada);
                piloto2PiorChegada.text(response.piloto2PiorChegada);
                piloto1MelhorLargada.text(response.piloto1MelhorLargada);
                piloto2MelhorLargada.text(response.piloto2MelhorLargada);
                piloto1PiorLargada.text(response.piloto1PiorLargada);
                piloto2PiorLargada.text(response.piloto2PiorLargada);
            }
        });
    });

    /*Listando pilotos por temporada*/
    $('#temporada_id').change(function (e) { 
        e.preventDefault();
        
        tabela_pilotos = $('#tabela-pilotos');
        temporada_id = $('#temporada_id').val();
        tbody = $('#tabela-pilotos-tbody');

        if(temporada_id == ""){
            tbody.html('<tr><td colspan="3" style="text-align: center; padding: 25px; color: #888;">Selecione uma Temporada acima</td></tr>');
            $('.tabela_comparativos').hide(); // Esconde a tabela se mudar pra temporada vazia
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: urlPilotosPorTemporada,
            data: {
                temporada_id:temporada_id
            },
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                if(response.pilotos.length > 0){
                    contPilotos = 1;
                    tbody.html('');
                    response.pilotos.map(function(response){ 
                        tbody.append("<tr><td>"+contPilotos+"</td><td>"+response.nome+" "+response.sobrenome+"</td><td style='text-align: center;'><input type='checkbox' class='piloto_id single-checkbox f1-checkbox' name='piloto_id[]' value='"+response.id+"'></td></tr>");
                        contPilotos++;
                    });
                } else {
                    tbody.html("<tr><td colspan='3' style='text-align:center;'>Sem Dados Cadastrados nesta temporada</td></tr>");
                }  
            
                /* Lógica Auto-Comparar e Limite de Checkboxes */
                var limit = 2;
                $('input.piloto_id').on('change', function (e) {
                    var checkedCount = $('input.piloto_id:checked').length;
                    
                    if (checkedCount > limit) {
                        $(this).prop('checked', false);
                        alert("Escolha apenas 2 pilotos para a comparação.");
                    } else if (checkedCount === limit) {
                        // Quando seleciona exatamente 2, dispara a consulta automaticamente
                        $('#form-comparativos').submit();
                    }
                }); 
            }
        });
    });
</script>
@endsection