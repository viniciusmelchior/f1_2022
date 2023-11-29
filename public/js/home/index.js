$(document).ready(function () {

    //Bloco responsável por mostrar e/ou recolher as tabelas de vitórias, poles etc 
    $('#toggle_vitorias').click(function (e) { 
        e.preventDefault();
        $('#div_vitorias').toggleClass('d-none');
    });

    $('#toggle_poles').click(function (e) { 
        e.preventDefault();
        $('#div_poles').toggleClass('d-none');
    });

    $('#toggle_podios').click(function (e) { 
        e.preventDefault();
        $('#div_podios').toggleClass('d-none');
    });

    $('#toggle_abandonos').click(function (e) { 
        e.preventDefault();
        $('#div_abandonos').toggleClass('d-none');
    });

    $('#toggle_chegadastop10').click(function (e) { 
        e.preventDefault();
        $('#div_chegadastop10').toggleClass('d-none');
    });

    $('#toggle_titulos').click(function (e) { 
        e.preventDefault();
        $('#div_titulos').toggleClass('d-none');
    });

    $('#toggle_classificacao_historica').click(function (e) { 
        e.preventDefault();
        $('#div_classificacao_historica').toggleClass('d-none');
    });

    // Paginação da tabela dos resultados das corridas
    var table = document.getElementById("tabelaResultadoCorridas");
    var rows = table.tBodies[0].rows;
    var rowsPerPage = 10;
    var currentPage = 0;
    var pages = Math.ceil(rows.length / rowsPerPage);
    var pagination = document.getElementById("pagination");
    
    
    for (var i = 0; i < pages; i++) {
        var page = Array.prototype.slice.call(rows, i * rowsPerPage, (i + 1) * rowsPerPage);
        page.forEach(function(row) {
            row.style.display = "none";
        });
    }

    showPage();
    
    function showPage() {
        for (var i = 0; i < pages; i++) {
        var page = Array.prototype.slice.call(rows, i * rowsPerPage, (i + 1) * rowsPerPage);
            if (i === currentPage) {
                page.forEach(function(row) {
                    row.style.display = "table-row";
                });
            } else {
                page.forEach(function(row) {
                    row.style.display = "none";
                });
            }
        }
    }

    showPage();

    for (var i = 0; i < pages; i++) {
        var pageNumber = document.createElement("button");
        pageNumber.innerHTML = i + 1;
        pageNumber.classList.add("page-number");
        pageNumber.addEventListener("click", function() {
            currentPage = parseInt(this.innerHTML) - 1;
            showPage();
        });
        pagination.appendChild(pageNumber);
    }

    //mudar classificação dos pilotos e equipes por temporada 
    $("#mudarTemporada").change(function (e) { 
        e.preventDefault();

        temporada_id = $("#mudarTemporada").val();
        tabelaClassificacaoPilotos = $('#tabelaClassificacaoPilotos');
        tabelaClassificacaoEquipes = $('#tabelaClassificacaoEquipes');

        if(temporada_id == ""){
            tituloClassificacao = $('#tituloClassificacao').text('Classificação Geral');
            tabelaClassificacaoPilotos.html('')
            tabelaClassificacaoEquipes.html('')
            tabelaClassificacaoPilotos.append('<tr><th>Posição</th><th>Piloto</th>><th>Equipe</th><th>Pontos</th></tr>')
            tabelaClassificacaoPilotos.append("<tr><td colspan='4'>Selecione uma Temporada</td></tr>");
            tabelaClassificacaoEquipes.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
            tabelaClassificacaoEquipes.append("<tr><td colspan='3'>Selecione uma Temporada</td></tr>");
        }
            
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: urlclassificacaoGeralPorTemporada,
            data: {temporada_id: temporada_id},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                tituloClassificacao = $('#tituloClassificacao').text('Classificação Geral '+response.temporada.des_temporada);
                tabelaClassificacaoPilotos.html('')
                tabelaClassificacaoEquipes.html('')
                if(response.resultadosPilotos.length > 0){
                    contPilotos = 1;
                    tabelaClassificacaoPilotos.append('<tr><th style="width:5%;">Posição</th><th style="width:5%;">Equipe</th>><th <th style="width:25%;">Piloto</th><th <th style="width:15%;">Pontos</th></tr>')
                    response.resultadosPilotos.map(function(response){ 
                        var assetPath = "{{ asset('images/') }}" + "/"+response.imagem;
                        tabelaClassificacaoPilotos.append("<tr class='remover'><td>" + contPilotos + "</td><td><img src='" + assetPath + "' style='width:25px; height:25px;'></td><td>" + response.nome + "</td><td>" + response.total + "</td></tr>");

                    contPilotos++
                    })
                    
                } else {
                    tabelaClassificacaoPilotos.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                    tabelaClassificacaoPilotos.append("<tr><td colspan='3'>Sem Dados Cadastrados</td></tr>");
                }

                if(response.resultadosEquipes.length > 0){
                    contEquipes = 1;
                    tabelaClassificacaoEquipes.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                    response.resultadosEquipes.map(function(response){ 
                    tabelaClassificacaoEquipes.append("<tr><td>"+contEquipes+"</td><td>"+response.nome+"</td><td>"+response.total+"</td></tr>");
                    contEquipes++
                    })
                }else{
                    tabelaClassificacaoEquipes.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                    tabelaClassificacaoEquipes.append("<tr><td colspan='3'>Sem Dados Cadastrados</td></tr>");
                }
            
            },
            error:function(){
                alert(error)
            }
        });
    });

    /* montagem da tabela de vitórias dos pilotos por temporada */
    $('#vitoriasPilotosPorTemporada').change(function (e) { 
        e.preventDefault();

        vitoriasPilotosTemporadaId = $('#vitoriasPilotosPorTemporada').val();
        tabelaVitoriasPilotos = $('#tabelaVitoriasPilotos');
        tabelaVitoriasPilotos.html('');
        tabelaVitoriasPilotos.append('<tr><th>#</th><th>Piloto</th><th>Vitórias</th></tr>')

        selectTemporadaVitoriasPiloto = $('#selectTemporadaVitoriasPiloto').text('Selecione uma Temporada');

        if(vitoriasPilotosTemporadaId != ''){
            selectTemporadaVitoriasPiloto = $('#selectTemporadaVitoriasPiloto').text('Geral');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: ajaxGetVitoriasPilotoPorTemporada,
            data: {vitoriasPilotosTemporadaId: vitoriasPilotosTemporadaId},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                response.totPorPiloto.forEach(function(piloto, index) {
                    // tabelaVitoriasPilotos.append("<tr><td><a href='visualizarVitoriasPiloto'><i class='bi bi-eye'></i></a></td><td>"+piloto.nome+"</td><td>"+piloto.vitorias+" id: "+piloto.id+"</td></tr>");
                    tabelaVitoriasPilotos.append("<tr><td>#</td><td>"+piloto.nome+"</td><td>"+piloto.vitorias);
                }); 
            },
            error:function(){
                alert(error)
            }
        });
    });

    /* montagem da tabela de vitórias das equipes por temporada */
    $('#vitoriasEquipesPorTemporada').change(function (e) { 
        e.preventDefault();

        vitoriasEquipesTemporadaId = $('#vitoriasEquipesPorTemporada').val();
        tabelaVitoriasEquipes = $('#tabelaVitoriasEquipes');
        tabelaVitoriasEquipes.html('');
        tabelaVitoriasEquipes.append('<tr><th>#</th><th>Equipe</th><th>Vitórias</th></tr>')

        selectTemporadaVitoriasEquipes = $('#selectTemporadaVitoriasEquipes').text('Selecione uma Temporada');

        if(vitoriasEquipesTemporadaId != ''){
            selectTemporadaVitoriasEquipes = $('#selectTemporadaVitoriasEquipes').text('Geral');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: ajaxGetVitoriasEquipesPorTemporada,
            data: {vitoriasEquipesTemporadaId: vitoriasEquipesTemporadaId},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                for (const key in response.totVitoriasPorEquipe) {
                    // console.log(`${key}: ${response.totPorPiloto[key]}`);
                    tabelaVitoriasEquipes.append("<tr><td>#</td><td>"+key+"</td><td>"+response.totVitoriasPorEquipe[key]+"</td></tr>");
                }
            },
            error:function(){
                alert(error)
            }
        });
    });

    //montagem da tabela de pole position dos pilotos por temporada
    $('#PolesPilotosPorTemporada').change(function (e) { 
        e.preventDefault();

        polesPilotosTemporadaId = $('#PolesPilotosPorTemporada').val();
        console.log(polesPilotosTemporadaId)
        tabelaPolesPilotos = $('#tabelaPolesPilotos');
        tabelaPolesPilotos.html('');
        tabelaPolesPilotos.append('<tr><th>#</th><th>Piloto</th><th>Poles</th></tr>')

        selectTemporadaPolesPiloto = $('#selectTemporadaPolesPilotos').text('Selecione uma Temporada');

        if(polesPilotosTemporadaId != ''){
            selectTemporadaPolesPiloto = $('#selectTemporadaPolesPilotos').text('Geral');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: ajaxGetPolesPilotosPorTemporada,
            data: {polesPilotosTemporadaId: polesPilotosTemporadaId},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                for (const key in response.totPolesPorPiloto) {
                    // console.log(`${key}: ${response.totPorPiloto[key]}`);
                    tabelaPolesPilotos.append("<tr><td>#</td><td>"+key+"</td><td>"+response.totPolesPorPiloto[key]+"</td></tr>");
                }
            },
            error:function(){
                alert(error)
            }
        });
    });

    //montagem da tabela de pole position das equipes por temporada
    $('#PolesEquipesPorTemporada').change(function (e) { 
        e.preventDefault();

        polesEquipesTemporadaId = $('#PolesEquipesPorTemporada').val();
        console.log(polesEquipesTemporadaId)
        tabelaPolesEquipes = $('#tabelaPolesEquipes');
        tabelaPolesEquipes.html('');
        tabelaPolesEquipes.append('<tr><th>#</th><th>Piloto</th><th>Poles</th></tr>')

        selectTemporadaPolesEquipes = $('#selectTemporadaPolesEquipes').text('Selecione uma Temporada');

        if(polesEquipesTemporadaId != ''){
            selectTemporadaPolesEquipe = $('#selectTemporadaPolesEquipes').text('Geral');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: ajaxGetPolesEquipesPorTemporada,
            data: {polesEquipesTemporadaId: polesEquipesTemporadaId},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
                for (const key in response.totPolesPorEquipe) {
                    // console.log(`${key}: ${response.totPorPiloto[key]}`);
                    tabelaPolesEquipes.append("<tr><td>#</td><td>"+key+"</td><td>"+response.totPolesPorEquipe[key]+"</td></tr>");
                }
            },
            error:function(){
                alert(error)
            }
        });
    });

    /* montagem da tabela de podios por piloto por temporada*/
    $('#podiosPilotosPorTemporada').change(function (e) { 
        e.preventDefault();

        podiosPilotosTemporadaId = $('#podiosPilotosPorTemporada').val();
        tabelaPodiosPilotos = $('#tabelaPodiosPilotos');
        tabelaPodiosPilotos.html('');
        tabelaPodiosPilotos.append('<tr><th>#</th><th>Piloto</th><th>Podios</th></tr>')

        selectTemporadaPodiosPiloto = $('#selectTemporadaPodiosPilotos').text('Selecione uma Temporada');

        if(podiosPilotosTemporadaId != ''){
            selectTemporadaPodiosPiloto = $('#selectTemporadaPodiosPilotos').text('Geral');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: ajaxGetPodiosPilotoPorTemporada,
            data: {podiosPilotosTemporadaId: podiosPilotosTemporadaId},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
            response.totPorPiloto.forEach(function(piloto, index) {
                    tabelaPodiosPilotos.append("<tr><td>#</td><td>"+piloto.nome+"</td><td>"+piloto.podios);
                }); 
            },
            error:function(){
                alert(error)
            }
        });
    });

     /* montagem da tabela de podios por equipes por temporada*/
     $('#podiosEquipesPorTemporada').change(function (e) { 
        e.preventDefault();

        podiosEquipesTemporadaId = $('#podiosEquipesPorTemporada').val();
        tabelaPodiosEquipes = $('#tabelaPodiosEquipes');
        tabelaPodiosEquipes.html('');
        tabelaPodiosEquipes.append('<tr><th>#</th><th>Piloto</th><th>Podios</th></tr>')

        selectTemporadaPodiosEquipe = $('#selectTemporadaPodiosEquipes').text('Selecione uma Temporada');

        if(podiosEquipesTemporadaId != ''){
            selectTemporadaPodiosEquipe = $('#selectTemporadaPodiosEquipes').text('Geral');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: ajaxGetPodiosEquipesPorTemporada,
            data: {podiosEquipesTemporadaId: podiosEquipesTemporadaId},
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
            response.totPorEquipe.forEach(function(equipe, index) {
                    tabelaPodiosEquipes.append("<tr><td>#</td><td>"+equipe.nome+"</td><td>"+equipe.podios);
                }); 
            },
            error:function(){
                alert(error)
            }
        });
    });
    
});
