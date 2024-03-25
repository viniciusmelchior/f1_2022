@extends('layouts.main')

@section('section')
   <div class="container">
    <div class="">
        <div style="margin-bottom: 25px;">
            <label for="inputFileLargada">Upload JSON Largada</label>
            <br>
            <input type="file" id="inputFileLargada">
        </div> 
    </div>

    <div class="card mt-3 mb-3 p-3">
        <div class="card-body">
        <label for="">Pesquisar</label>
        <input type="text" id="caixaBusca">
        </div>
    </div>

    <table class="table text-center" id="table">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Piloto</th>
                <th onclick="sortTable(1)">Carro</th>
                <th onclick="sortTable(2)">Volta</th>
                <th onclick="sortTable(3)">Setor 1</th>
                <th onclick="sortTable(4)">Setor 2</th>
                <th onclick="sortTable(5)">Setor 3</th>
                <th onclick="sortTable(6)">Total</th>
                <th onclick="sortTable(7)">Melhor volta</th>
                <th onclick="sortTable(8)">Pneu</th>
            </tr>
        </thead>
        <tbody>
           
        </tbody>
   </div>
@endsection

<script type="module">
    document.getElementById('inputFileLargada').addEventListener('change', function(event) {
        // console.log('funcionou largada')
        const arquivo = event.target.files[0];
        const leitor = new FileReader();

        leitor.onload = function(e) {
            const conteudo = e.target.result;
            let objetoJSON = JSON.parse(conteudo);
            gerarResultadoLargada(objetoJSON)
        };

        leitor.readAsText(arquivo);
    });

    function gerarResultadoLargada(resultados){
        var resultados = resultados;
        let sessoesDisponiveis = resultados['sessions'];
        var pilotos = resultados['players'];
        let melhorVolta = '';

        sessoesDisponiveis.forEach((sessao,key) => {
            if(sessao['name'] == 'Qualifying'){
                let voltas = resultados['sessions'][key]['laps']
                // console.log(voltas[1])
                var melhoresVoltas = sessao['bestLaps']
               
            
                let tabela = document.getElementById('table');
                voltas.forEach((volta, indice) =>{
                    
                    //exibe só as voltas válidas
                    if(volta['time'] != '-1'){
                       
                        let novaLinha = document.createElement('tr');

                        let piloto = document.createElement('td');
                        piloto.textContent = pilotos[volta['car']]['name'];
                        novaLinha.appendChild(piloto);

                        let carro = document.createElement('td');
                        carro.textContent = pilotos[volta['car']]['car'];
                        novaLinha.appendChild(carro);

                        let numeroDaVolta = document.createElement('td');
                        numeroDaVolta.textContent = volta['lap']+1;
                        novaLinha.appendChild(numeroDaVolta);
                        
                        voltas[indice]['sectors'].forEach((setor, indice) => {
                            let tempo_setor = document.createElement('td');
                            tempo_setor.textContent = converterTempo(setor);
                            novaLinha.appendChild(tempo_setor);
                        })

                        let tempo_total = document.createElement('td');
                        
                        tempo_total.textContent = volta['time'] == '-1' ? '-' : converterTempo(volta['time']);
                        novaLinha.appendChild(tempo_total);
                           
                        let melhorVolta = document.createElement('td');
                        melhorVolta.textContent = verificaVoltaRapida(volta, melhoresVoltas) == true ? 'SIM' : '-';
                        novaLinha.appendChild(melhorVolta);

                        let pneu = document.createElement('td');
                        pneu.textContent = volta['tyre'];
                        novaLinha.appendChild(pneu);
                        
                        if(verificaVoltaRapida(volta, melhoresVoltas) == true){
                            novaLinha.classList.add('text-danger');
                        }

                        tabela.appendChild(novaLinha);

                    }
                });

            }
        });

        function verificaVoltaRapida(volta, melhoresVoltas){
            let piloto = volta['car']
            let numeroDaVolta = volta['lap']
            let melhorVoltaDoPiloto = false

            melhoresVoltas.forEach(melhorVolta => {
                if(melhorVolta['car'] == piloto && melhorVolta['lap'] == numeroDaVolta){
                    melhorVoltaDoPiloto = true
                }
            })

            return melhorVoltaDoPiloto
        }

        function converterTempo(tempoDeVolta){

             // Calcula os minutos
            let minutos = Math.floor(tempoDeVolta / 60000);

            // Calcula os segundos restantes após a conversão dos minutos
            let segundosRestantes = tempoDeVolta % 60000;

            // Calcula os segundos
            let segundos = Math.floor(segundosRestantes / 1000);

            // Calcula os milissegundos restantes após a conversão dos segundos
            let milissegundosRestantes = segundosRestantes % 1000;

            // Retorna um objeto contendo os minutos, segundos e milissegundos
            return `${minutos}:${segundos}:${milissegundosRestantes}`
        }
    }

   
        window.sortTable = function(columnIndex) {
        let table, rows, switching, i, x, y, shouldSwitch;
        table = document.getElementById("table");
        switching = true;
        
        while (switching) {
            switching = false;
            rows = table.rows;
            
            for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("td")[columnIndex];
            y = rows[i + 1].getElementsByTagName("td")[columnIndex];
            
            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                shouldSwitch = true;
                break;
            }
            }
            
            if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            }
        }
        }
</script>