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
});
