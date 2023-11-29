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
    
});