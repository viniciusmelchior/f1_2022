
//Bloco responsável por mostrar e/ou recolher as tabelas de vitórias, poles etc 
$('#toggle_chegadas').click(function (e) { 
    e.preventDefault();
    $('#div_chegadas').toggleClass('d-none');
});

$('#toggle_largadas').click(function (e) { 
    e.preventDefault();
    $('#div_largadas').toggleClass('d-none');
});

$('#toggle_resultados').click(function (e) { 
    e.preventDefault();
    $('#div_resultados').toggleClass('d-none');
});

/**
 * 
 * Chegadas ############################################################################################################################################################################################
 * 
 */

//mudar posição inicial da tabela de chegadas dos pilotos 
$('#inicioPosicaoChegadasPilotos').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoChegadasPilotos = $('#inicioPosicaoChegadasPilotos').val();
    fimPosicaoChegadasPilotos = $('#fimPosicaoChegadasPilotos').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoChegadasPilotos > fimPosicaoChegadasPilotos){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getChegadasPilotosPorPosicao(inicioPosicaoChegadasPilotos, fimPosicaoChegadasPilotos, pais_id);

});

//mudar posição final da tabela de chegadas dos pilotos 
$('#fimPosicaoChegadasPilotos').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoChegadasPilotos = $('#inicioPosicaoChegadasPilotos').val();
    fimPosicaoChegadasPilotos = $('#fimPosicaoChegadasPilotos').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoChegadasPilotos > fimPosicaoChegadasPilotos){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getChegadasPilotosPorPosicao(inicioPosicaoChegadasPilotos, fimPosicaoChegadasPilotos, pais_id);

});

/**
 * Função responsável por buscar e montar a tabela de chegadas dos pilotos, de acordo com o que foi colocado nos inputs de inicio e fim
 */
function getChegadasPilotosPorPosicao(inicio, fim, pais_id){

    tabelaChegadasPilotos = $('#tabelaChegadasPilotos');
    tabelaChegadasPilotos.html('');
    tabelaChegadasPilotos.append('<tr><th>#</th><th>Piloto</th><th>Chegadas</th></tr>')

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        url: ajaxGetChegadasPilotos,
        data: {inicio:inicio, fim:fim, pais_id:pais_id},
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (response) {
            response.totalVitoriasPorPiloto.forEach(function(piloto, index) {
                tabelaChegadasPilotos.append("<tr><td>#</td><td>"+piloto.piloto_nome_completo+"</td><td>"+piloto.chegadas);
            }); 
        },
        error:function(){
            alert(error)
        }
    });
}

//mudar posição inicial da tabela de chegadas dos Equipes 
$('#inicioPosicaoChegadasEquipes').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoChegadasEquipes = $('#inicioPosicaoChegadasEquipes').val();
    fimPosicaoChegadasEquipes = $('#fimPosicaoChegadasEquipes').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoChegadasEquipes > fimPosicaoChegadasEquipes){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getChegadasEquipesPorPosicao(inicioPosicaoChegadasEquipes, fimPosicaoChegadasEquipes, pais_id);

});

//mudar posição final da tabela de chegadas dos pilotos 
$('#fimPosicaoChegadasEquipes').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoChegadasEquipes = $('#inicioPosicaoChegadasEquipes').val();
    fimPosicaoChegadasEquipes = $('#fimPosicaoChegadasEquipes').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoChegadasEquipes > fimPosicaoChegadasEquipes){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getChegadasEquipesPorPosicao(inicioPosicaoChegadasEquipes, fimPosicaoChegadasEquipes, pais_id);

});

/**
 * Função responsável por buscar e montar a tabela de chegadas das equipes, de acordo com o que foi colocado nos inputs de inicio e fim
 */
function getChegadasEquipesPorPosicao(inicio, fim, pais_id){

    tabelaChegadasEquipes = $('#tabelaChegadasEquipes');
    tabelaChegadasEquipes.html('');
    tabelaChegadasEquipes.append('<tr><th>#</th><th>Piloto</th><th>Chegadas</th></tr>')

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        url: ajaxGetChegadasEquipes,
        data: {inicio:inicio, fim:fim, pais_id:pais_id},
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (response) {
            response.totalVitoriasPorEquipe.forEach(function(equipe, index) {
                tabelaChegadasEquipes.append("<tr><td>#</td><td>"+equipe.equipe_nome+"</td><td>"+equipe.chegadas);
            }); 
        },
        error:function(){
            alert(error)
        }
    });
}

/**
 * 
 * LARGADAS ############################################################################################################################################################################################
 * 
 */

//mudar posição inicial da tabela de chegadas dos pilotos 
$('#inicioPosicaoLargadasPilotos').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoLargadasPilotos = $('#inicioPosicaoLargadasPilotos').val();
    fimPosicaoLargadasPilotos = $('#fimPosicaoLargadasPilotos').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoLargadasPilotos > fimPosicaoLargadasPilotos){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getLargadasPilotosPorPosicao(inicioPosicaoLargadasPilotos, fimPosicaoLargadasPilotos, pais_id);

});

//mudar posição final da tabela de chegadas dos pilotos 
$('#fimPosicaoLargadasPilotos').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoLargadasPilotos = $('#inicioPosicaoLargadasPilotos').val();
    fimPosicaoLargadasPilotos = $('#fimPosicaoLargadasPilotos').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoLargadasPilotos > fimPosicaoLargadasPilotos){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getLargadasPilotosPorPosicao(inicioPosicaoLargadasPilotos, fimPosicaoLargadasPilotos, pais_id);

});

/**
 * Função responsável por buscar e montar a tabela de Largadas dos pilotos, de acordo com o que foi colocado nos inputs de inicio e fim
 */
function getLargadasPilotosPorPosicao(inicio, fim, pais_id){

    console.log(inicio, fim, pais_id)

    tabelaLargadasPilotos = $('#tabelaLargadasPilotos');
    tabelaLargadasPilotos.html('');
    tabelaLargadasPilotos.append('<tr><th>#</th><th>Piloto</th><th>Chegadas</th></tr>')

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        url: ajaxGetLargadasPilotos,
        data: {inicio:inicio, fim:fim, pais_id:pais_id},
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (response) {
            response.totalLargadasPorPiloto.forEach(function(piloto, index) {
                tabelaLargadasPilotos.append("<tr><td>#</td><td>"+piloto.piloto_nome_completo+"</td><td>"+piloto.largadas);
            }); 
        },
        error:function(){
            alert(error)
        }
    });
}

//mudar posição inicial da tabela de chegadas dos pilotos 
$('#inicioPosicaoLargadasEquipes').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoLargadasEquipes = $('#inicioPosicaoLargadasEquipes').val();
    fimPosicaoLargadasEquipes = $('#fimPosicaoLargadasEquipes').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoLargadasEquipes > fimPosicaoLargadasEquipes){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    getLargadasEquipesPorPosicao(inicioPosicaoLargadasPilotos, fimPosicaoLargadasPilotos, pais_id);

});

//mudar posição final da tabela de chegadas dos pilotos 
$('#fimPosicaoLargadasEquipes').change(function (e) { 
    e.preventDefault();
    
    inicioPosicaoLargadasEquipes = $('#inicioPosicaoLargadasEquipes').val();
    fimPosicaoLargadasEquipes = $('#fimPosicaoLargadasEquipes').val();
    pais_id = $('#pais_id').val();

    if(inicioPosicaoLargadasEquipes > fimPosicaoLargadasEquipes){
        alert('Posição de inicio não pode ser maior que a do fim!!!');
        return
    }

    // console.log(inicioPosicaoLargadasEquipes, fimPosicaoLargadasEquipes, pais_id)

    getLargadasEquipesPorPosicao(inicioPosicaoLargadasEquipes, fimPosicaoLargadasEquipes, pais_id);

});

/**
 * Função responsável por buscar e montar a tabela de Largadas dos pilotos, de acordo com o que foi colocado nos inputs de inicio e fim
 */
function getLargadasEquipesPorPosicao(inicio, fim, pais_id){

    tabelaLargadasEquipes = $('#tabelaLargadasEquipes');
    tabelaLargadasEquipes.html('');
    tabelaLargadasEquipes.append('<tr><th>#</th><th>Piloto</th><th>Chegadas</th></tr>')

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
        url: ajaxGetLargadasEquipes,
        data: {inicio:inicio, fim:fim, pais_id:pais_id},
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (response) {
            response.totalLargadasPorEquipe.forEach(function(equipe, index) {
                tabelaLargadasEquipes.append("<tr><td>#</td><td>"+equipe.nome+"</td><td>"+equipe.largadas);
            }); 
        },
        error:function(){
            alert(error)
        }
    });
}

