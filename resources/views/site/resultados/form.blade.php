@extends('layouts.main')

@php 
    use App\Models\Site\Resultado;
    use Illuminate\Support\Facades\Artisan;
    $route = route('resultados.update', [$corrida->id]);
    $method = method_field('POST');
    if(isset($model)){
        $route = route('resultados.update', [$corrida->id]);
        $method = method_field('POST');
    }
    Artisan::call('view:clear');
@endphp

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
</style>

@section('section')
   <div class="container">
        <div>
            <h2>GP de {{$corrida->pista->nome}} - {{$corrida->temporada->ano->ano}}</h2>
        </div>
    <form method="POST" action="{{ $route }}" class="col-md-9 mt-3 mb-3">
        {{ $method }}
        @csrf

        <div class="card mb-3 mt-3 p-3 bg-dark text-white">
            <div class="mb-3">
                <label for="condicao_id" class="form-label">Condição Climática</label>
                <select name="condicao_id" id="condicao_id" class="form-control">
                    @foreach($condicoesClimaticas as $condicaoClimatica)
                        <option value="{{$condicaoClimatica->id}}" @if(isset($corrida) && $corrida->condicao_id == $condicaoClimatica->id) selected @endif>{{$condicaoClimatica->descricao}}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <select name="categoria" id="categoria" class="form-control">
                    <option value="F1">Formula 1</option>
                    <option value="F1Sprint">Formula 1 (Sprint)</option>
                    <option value="SC">Super Corrida</option>
                    <option value="Indy">Indy</option>
                    <option value="FormulaE">Formula E</option>
                    <option value="Stock1">Stock Car (Corrida 1)</option>
                    <option value="Stock2">Stock Car (Corrida 2)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="qtd_safety_car" class="form-label">Quantidade Safety Car</label>
                <input
                    type="number"
                    name="qtd_safety_car" 
                    id="qtd_safety_car" 
                    style="width:30px; height:30px;" 
                    @if(isset($corrida->qtd_safety_car))
                        value="{{$corrida->qtd_safety_car}}"
                    @else 
                        value="0">
                    @endif 
            </div>
            <div class="mb-3 mt-3">
                <label for="dificuldade_ia" class="form-label">Dificuldade IA</label>
                <input
                    type="number"
                    name="dificuldade_ia" 
                    id="dificuldade_ia" 
                    style="width:30px; height:30px;" 
                    @if(isset($corrida->dificuldade_ia))
                        value="{{$corrida->dificuldade_ia}}"
                    @else 
                        value="">
                    @endif 
            </div>

            <div class="form-group form-check mt-3 mb-3">
                <input
                type="checkbox"
                class="form-check-input"
                id="flg_super_corrida"
                name="flg_super_corrida"
                value="S"
                >
                <label class="form-check-label" for="flg_super_corrida">Super Corrida F1</label>
            </div>

            <hr>

            <div class="">
                <div style="margin-bottom: 25px;">
                    <label for="inputFileLargada">Upload JSON Largada</label>
                    <br>
                    <input type="file" id="inputFileLargada">
                </div>
                  <div style="argin-bottom: 25px; ">
                      <label for="inputFileChegada">Upload JSON Chegada</label>
                      <br>
                      <input type="file" id="inputFileChegada">
                  </div>
            </div>

        <hr>

        <table class="table text-white">
            <thead>
                <tr>
                    <th colspan="2">Piloto</th>
                    <th>Largada</th>
                    <th>Chegada</th>
                    <th>Abandono</th>
                    <th>Pontuação</th>
                    <th>Clássica</th>
                    <th>Personalizada</th>
                    <th>Invertida</th>
                </tr>
            </thead>
            <tbody id="tabela">
                @foreach($model as $pilotoEquipe)
                @php 
                    $infoPiloto =  Resultado::where('user_id', Auth::user()->id)->where('pilotoEquipe_id', $pilotoEquipe->id)->where('corrida_id', $corrida->id)->first();
                @endphp
                    {{-- <tr class="linha_resultados"> --}}
                    {{-- <tr style="color:{{$pilotoEquipe->equipe->des_cor}};" class="linha_resultados"> --}}
                    <tr class="linha_resultados {{$pilotoEquipe->flg_super_corrida == 'S' ? 'd-none': ''}}">
                        <td> <img src="{{asset('images/'.$pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;"></td>
                        <td style="vertical-align: middle;">
                            {{-- <img src="{{asset('images/'.$pilotoEquipe->equipe->imagem)}}" alt="" style="width: 25px; height:25px;"> --}}
                            {{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}} 
                            {{-- <span> <input type="hidden" name="pilotoEquipe_id[]" value="{{$pilotoEquipe->id}}"></span> --}}
                        </td>
                        <td>
                            <input
                            type="number"
                            class="largada" 
                            name="largada[]" 
                            id="largada" 
                            style="width:30px; height:30px;" 
                            @if(isset($infoPiloto->largada))
                                value="{{$infoPiloto->largada}}"
                            @else 
                                value="">
                            @endif 
                        </td>
                        <td>
                            <input 
                            type="number" 
                            class="chegada" 
                            name="chegada[]" id="chegada" 
                            style="width:30px; height:30px;" 
                            @if(isset($infoPiloto->chegada))
                                value="{{$infoPiloto->chegada}}"
                            @else 
                                value="">
                            @endif 
                        </td>
                        <td>
                           {{--  <input type="hidden" value="N" name="flg_abandono[]" class="flg_abandono"> --}}
                            <input 
                            type="checkbox" 
                            class="flg_abandono" 
                            name="flg_abandono[]" id="flg_abandono" 
                            style="width:30px; height:30px;" 
                            value="<?= $pilotoEquipe->id ?>"
                            @if(isset($infoPiloto->flg_abandono) && $infoPiloto->flg_abandono == 'S')
                               checked
                            @else 
                                
                            @endif 
                            >
                        </td>
                        <td>
                            <input 
                            type="number" 
                            class="chegada" disabled 
                            name="" 
                            id="chegada" 
                            style="width:30px; height:30px;" 
                            @if(isset($infoPiloto->pontuacao))
                                value="{{$infoPiloto->pontuacao}}"
                            @else 
                                value="">
                            @endif 
                        </td>
                        <td>
                            <input 
                            type="number" 
                            class="chegada" disabled 
                            name="" 
                            id="chegada" 
                            style="width:30px; height:30px;" 
                            @if(isset($infoPiloto->pontuacao_classica))
                                value="{{$infoPiloto->pontuacao_classica}}"
                            @else 
                                value="">
                            @endif 
                        </td>
                        <td>
                            <input 
                            type="number" 
                            class="chegada" disabled 
                            name="" 
                            id="chegada" 
                            style="width:30px; height:30px;" 
                            @if(isset($infoPiloto->pontuacao_personalizada))
                                value="{{$infoPiloto->pontuacao_personalizada}}"
                            @else 
                                value="">
                            @endif 
                        </td>
                        <td>
                            <input 
                            type="number" 
                            class="chegada" disabled 
                            name="" 
                            id="chegada" 
                            style="width:30px; height:30px;" 
                            @if(isset($infoPiloto->pontuacao_invertida))
                                value="{{$infoPiloto->pontuacao_invertida}}"
                            @else 
                                value="">
                            @endif 
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
       {{--  {{dd($model)}} --}}
        <div class="mb-3">
            <label for="volta_rapida" class="form-label">Volta mais rápida</label>
            <select name="volta_rapida" id="volta_rapida" class="form-control">
                <option value="">selecione</option>
                @foreach($model as $pilotoEquipe)
                   <option value="{{$pilotoEquipe->id}}" @if(isset($model) && $pilotoEquipe->id == $corrida->volta_rapida) selected @endif>{{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}</option>

                  {{--  <option value="{{$pais->id}}" @if(isset($model) && $model->pais->id == $pais->id) selected @endif>{{$pais->des_nome}}</option> --}}
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="3">
                @if($corrida->observacoes)
                {{$corrida->observacoes}}
                @endif
            </textarea>
        </div>

    </div>

        @if(isset($corrida->updated_at))
            <div class="mb-3 fst-italic">
                <span>Atualizado em {{date('d/m/Y', strtotime($corrida->updated_at))}} às {{date('H:i:s', strtotime($corrida->updated_at))}}</span>
            </div>
        @endif

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('temporadas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
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

    document.getElementById('inputFileChegada').addEventListener('change', function(event) {
        // console.log('funcionou chegada')
        const arquivo = event.target.files[0];
        const leitor = new FileReader();

        leitor.onload = function(e) {
            const conteudo = e.target.result;
            let objetoJSON = JSON.parse(conteudo);
            gerarResultadoChegada(objetoJSON)
        };

        leitor.readAsText(arquivo);
    });

    function gerarResultadoChegada(resultados) {
       
        var resultados = resultados
        // console.log(resultados)
        var pilotos = resultados['players'];
        var resultadoFinal = resultados['sessions'][0]['raceResult'];

        let ordemChegada = [];

        for (let index = 0; index < resultadoFinal.length; index++) {
            ordemChegada.push(pilotos[resultadoFinal[index]]['name']);
        }

        const tabela = document.getElementById('tabela');

        // Obtendo todas as linhas da tabela
        const linhas = tabela.rows;
        
        //deixar o código dinâmico
        const arrayDeLinhas = Array.from(linhas);

        //transformar o array em UPPERCASE
        ordemChegada = ordemChegada.map(item => item.toUpperCase())

        arrayDeLinhas.forEach((linha, indice) => {
            let nomePiloto = linha.cells[1].innerHTML.toUpperCase().trim() //limpa os espaços do inicio e fim da string
            nomePiloto = nomePiloto.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); //retira os ascentos
            const valorChegada = parseInt(ordemChegada.indexOf(nomePiloto))+1
            if(valorChegada > 0){
                linha.cells[3].querySelector('input[type="number"]').value = valorChegada
            }
        });
    }

    function gerarResultadoLargada(resultados){
        var resultados = resultados;
        var pilotos = resultados['players'];
        var resultadoFinal = resultados['sessions'][1]['bestLaps'].sort((a, b) => a.time - b.time);

        let ordemLargada = [];

        for (let index = 0; index < resultadoFinal.length; index++) {
            ordemLargada.push(pilotos[resultadoFinal[index]['car']]['name']);
        }

        const tabela = document.getElementById('tabela');

        // Obtendo todas as linhas da tabela
        const linhas = tabela.rows;
        
        //deixar o código dinâmico
        const arrayDeLinhas = Array.from(linhas);

        //transformar o array em UPPERCASE
        ordemLargada = ordemLargada.map(item => item.toUpperCase())

        arrayDeLinhas.forEach((linha, indice) => {
            let nomePiloto = linha.cells[1].innerHTML.toUpperCase().trim() //limpa os espaços do inicio e fim da string
            nomePiloto = nomePiloto.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); ///retira os ascentos
            const valorLargada = parseInt(ordemLargada.indexOf(nomePiloto))+1

            if(valorLargada > 0){
                linha.cells[2].querySelector('input[type="number"]').value = valorLargada
            }
        });
    }

    var checkbox = $("#flg_super_corrida");

    // Adiciona um ouvinte de eventos para o evento 'change' (mudança) do checkbox
    checkbox.change(function() {
    // Verifica se o checkbox está marcado
        if (checkbox.is(":checked")) {
            // Esconde todas as linhas quando o checkbox está marcado
            $(".linha_resultados").removeClass("d-none");
        } else {
            // Exibe todas as linhas quando o checkbox está desmarcado
            // $(".linha_resultados").addClass("d-none");
            location.reload()
        }
    });
</script>