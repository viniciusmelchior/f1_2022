@extends('layouts.main')

@php 
    use App\Models\Site\PilotoEquipe;
    $route = route('pilotoEquipe.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('pilotoEquipe.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="piloto_id" class="form-label">Piloto</label>
            <select name="piloto_id" id="piloto_id" class="form-control" required>
                <option value="">Selecione</option>
                @foreach($pilotos as $piloto)
                    <option value="{{$piloto->id}}" @if(isset($model) && $model->piloto_id == $piloto->id) selected @endif>{{$piloto->nome }} {{$piloto->sobrenome}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="equipe_id" class="form-label">Equipe</label>
            <select name="equipe_id" id="equipe_id" class="form-control" required>
                <option value="">Selecione</option>
                @foreach($equipes as $equipe)
                    <option value="{{$equipe->id}}" @if(isset($model) && $model->equipe_id == $equipe->id) selected @endif>{{$equipe->nome}}</option>
                @endforeach
            </select>
        </div>

        {{--modelo do carro--}}
        <div class="mb-3">
            <label for="skin_id" class="form-label">Modelo do Carro</label>
            <select name="modelo_carro" id="modelo_carro" class="form-control">
                <option value="">Selecione</option>
                @foreach(PilotoEquipe::getCarros() as $carro)
                    <option value="{{$carro}}" @if(isset($model) && $model->modelo_carro == $carro) selected @endif>{{$carro}}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="skin_id" class="form-label">Skin</label>
            <select name="skin_id" id="skin_id" class="form-control">
                <option value="">Selecione</option>
                @foreach($skins as $skin)
                    <option value="{{$skin->id}}" @if(isset($model) && $model->skin_id == $skin->id) selected @endif>{{$skin->skin}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="ano_id" class="form-label">Temporada/Ano</label>
            <select name="ano_id" id="ano_id" class="form-control" required>
                @foreach($anos as $ano)
                    <option value="{{$ano->id}}" @if(isset($model) && $model->ano_id == $ano->id) selected @endif>{{$ano->ano}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group form-check mb-3">
            <input
            type="checkbox"
            class="form-check-input"
            id="flg_super_corrida"
            name="flg_super_corrida"
            value="S"
            @if(isset($model) && $model->flg_super_corrida == 'S') checked @endif
            >
            <label class="form-check-label" for="flg_super_corrida">Super corrida?</label>
        </div>

        @if(Route::currentRouteName() != 'pilotoEquipe.create')
            <div class="form-group form-check mb-3">
                <input
                type="checkbox"
                class="form-check-input"
                id="flg_ativo"
                name="flg_ativo"
                value="S"
                @if(isset($model) && $model->flg_ativo == 'S') checked @endif
                >
                <label class="form-check-label" for="flg_ativo">Ativo?</label>
            </div>
        @endif
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('pilotoEquipe.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection

