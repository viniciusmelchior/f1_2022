<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TESTE PAGINATE</title>
    <style>
        td, th {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div>
        <input type="text" name="" id="busca" placeholder="Busca" onkeyup="buscar()">
    </div>
    <div>
        <label for="">Quantidade de Resultados</label>
        <select name="" id="qtdResultados" onchange="buscar()">
            <option value="3">3</option>
            <option value="6">6</option>
            <option value="9">9</option>
            <option value="9" selected>10</option>
        </select>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Sobrenome</th>
                <th>País</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <div id="paginacao">

    </div>

    <input type="hidden" name="" id="url_busca" value="{{route('getAllDrivers')}}">

</body>
</html>

<script>

    //ao abrir a pagina ja busca resultados
    buscar();

    async function buscar(url = null){
        let busca = document.getElementById('busca').value
        let qtdResultados = document.getElementById('qtdResultados').value
        url = url ? url : document.getElementById('url_busca').value;
        const token = document.querySelector('meta[name="csrf-token"]').content

        const req = await fetch(url, {
            method: 'POST',
            headers: {
                'content-type' : 'application/json',
                'x-csrf-token' : token
            },
            body: JSON.stringify({
                busca: busca,
                qtdResultados:qtdResultados
            })
        })

        const res = await req.json();

        //preencher TBODY da tabela
        preencherTbody(res.pilotos.data);
        preencherPaginacao(res.pilotos.links);

    }

    function preencherTbody(data){
      
        let tbody = document.querySelector('tbody')

        //reseta o tbody toda vez que faz uma busca
        tbody.innerHTML = '';
        
        data.forEach(element => {
            
            tbody.innerHTML += `<tr>
                                    <td>${element.id}</td>
                                    <td>${element.nome}</td>
                                    <td>${element.sobrenome}</td>
                                    <td>${element.pais.des_nome}</td>
                                <tr/>`
        });
    }

    function preencherPaginacao(links){
        let paginacao = document.getElementById('paginacao');

        paginacao.innerHTML = '';

        links.forEach(link => {
            paginacao.innerHTML += `<button onclick="buscar('${link.url}')">${link.label}</button>`
        })

    }

   

</script>