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

    
});
