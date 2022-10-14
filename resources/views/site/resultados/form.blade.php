@extends('layouts.main')

@php 
    use App\Models\Site\Resultado;
    $route = route('resultados.update', [$corrida->id]);
    $method = method_field('POST');
    if(isset($model)){
        $route = route('resultados.update', [$corrida->id]);
        $method = method_field('POST');
    }
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
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="condicao_id" class="form-label">Condição Climática</label>
            <select name="condicao_id" id="condicao_id" class="form-control">
                @foreach($condicoesClimaticas as $condicaoClimatica)
                    <option value="{{$condicaoClimatica->id}}" {{-- @if(isset($model) && $model->condicaoClimatica->id == $condicaoClimatica->id) selected @endif --}}>{{$condicaoClimatica->descricao}}</option>
                @endforeach
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
                    value="">
                @endif 
        </div>
        <div class="mb-3">
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
        <table class="table">
            <thead>
                <tr>
                    <th>Piloto</th>
                    <th>Largada</th>
                    <th>Chegada</th>
                    <th>Pontuação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($model as $pilotoEquipe)
                @php 
                //dd($pilotoEquipe);
                    $infoPiloto =  Resultado::where('user_id', Auth::user()->id)->where('pilotoEquipe_id', $pilotoEquipe->id)->where('corrida_id', $corrida->id)->first();
                    //dd($infoPiloto);
                @endphp
                    <tr style="color:{{$pilotoEquipe->equipe->des_cor}};" class="linha_resultados">
                        <td>
                            {{$pilotoEquipe->piloto->nome}} {{$pilotoEquipe->piloto->sobrenome}}
                            <input type="hidden" name="pilotoEquipe_id[]" value="{{$pilotoEquipe->id}}">
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

        <div class="form-group form-check mb-3">
            <input
            type="checkbox"
            class="form-check-input"
            id="flg_sprint"
            name="flg_sprint"
            value="S"
            @if(isset($corrida) && $corrida->flg_sprint == 'S') checked @endif
            >
            <label class="form-check-label" for="flg_ativo">Corrida Sprint</label>
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="3">
                @if($corrida->observacoes)
                {{$corrida->observacoes}}
                @endif
            </textarea>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('temporadas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
     
    </script>
@endsection