@extends('layouts.main')

@php 
    $route = route('temporadas.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('temporadas.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="des_temporada" class="form-label">Descrição</label>
            <input type="text" class="form-control" id="des_temporada" name="des_temporada" value="@if(isset($model)) {{$model->des_temporada}}  @endif">
        </div>
        <div class="mb-3">
            <label for="ano_id" class="form-label">Ano</label>
            <select name="ano_id" id="ano_id" class="form-control">
                @foreach($anos as $ano)
                    <option value="{{$ano->id}}" @if(isset($model) && $model->ano->id == $ano->id) selected @endif>{{$ano->ano}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="10">
                @if($model->observacoes)
                    {{$model->observacoes}}
                @endif
            </textarea>
        </div>

        {{--Form aparece apenas rota de edit--}}
        @if(Route::currentRouteName() == 'temporadas.edit')
            <div class="form-group form-check mb-3">
                <input
                type="checkbox"
                class="form-check-input"
                id="flg_finalizada"
                name="flg_finalizada"
                value="S"
                @if(isset($model) && $model->flg_finalizada == 'S') checked @endif
                >
                <label class="form-check-label" for="flg_ativo">Finalizar Temporada</label>
            </div>
        @endif
    
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('temporadas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection