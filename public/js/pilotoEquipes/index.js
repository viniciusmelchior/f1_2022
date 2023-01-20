var table = document.getElementById("pilotoEquipesTable");
    var rows = table.tBodies[0].rows;
    var rowsPerPage = 22;
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

    /**Caixa de Busca */

    let caixaBusca = document.getElementById('caixaBusca');
    let tabelaPilotos = document.getElementById('pilotoEquipesTable');

    caixaBusca.addEventListener("keyup",function(){
    var keyword = this.value;
    keyword = keyword.toUpperCase();
    
    var all_tr = tabelaPilotos.getElementsByTagName("tr");

    for(var i=0; i<all_tr.length; i++){
        var all_columns = all_tr[i].getElementsByTagName("td");
        for(j=0;j<all_columns.length; j++){
            if(all_columns[j]){
                var column_value = all_columns[j].textContent || all_columns[j].innerText;
                column_value = column_value.toUpperCase();
                if(column_value.indexOf(keyword) > -1){
                    all_tr[i].style.display = ""; // show
                    break;
                }else{
                    all_tr[i].style.display = "none"; // hide
                }
            }
        }
    }
    })