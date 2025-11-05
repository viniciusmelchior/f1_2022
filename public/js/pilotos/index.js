let caixaBusca = document.getElementById('caixaBusca');
    let tabelaPilotos = document.getElementById('tabelaPilotos');

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

    $(document).ready(function () {
        $('.deletePiloto').click(function (e) { 
            e.preventDefault();
            var piloto_id = $(this).val();
            $('#piloto_id').val(piloto_id);
            $('#exampleModal').modal('show');
        });
    });

    //esconde as colunas de estatisticas
    document.getElementById('toggleColunas').addEventListener('click', function () {
    const table = document.getElementById('tabelaPilotos');

    // lista dos índices das colunas que vão ser alteradas
    const colunasParaAlternar = [4, 6, 8, 10];

    // Pega o estado da primeira coluna como referência
    const isHidden = table.querySelector(`thead th:nth-child(${colunasParaAlternar[0] + 1})`)
                          .classList.contains('hidden-col');

    colunasParaAlternar.forEach(colIndex => {
        // alterna no cabeçalho
        table.querySelectorAll(`thead th:nth-child(${colIndex + 1})`).forEach(th => {
            th.classList.toggle('hidden-col', !isHidden);
        });

        // alterna nas células do corpo
        table.querySelectorAll(`tbody td:nth-child(${colIndex + 1})`).forEach(td => {
            td.classList.toggle('hidden-col', !isHidden);
        });
    });

    // atualiza texto do botão
    this.textContent = isHidden ? 'Esconder percentuais' : 'Mostrar percentuais';
});