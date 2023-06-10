@extends('layouts.main')

@php 
    $route = route('pistas.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('pistas.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="@if(isset($model)) {{$model->nome}}  @endif" required>
        </div>
        <div class="mb-3">
            <label for="pais_id" class="form-label">País</label>
            <select name="pais_id" id="pais_id" class="form-control" required>
                <option value="">Selecione</option>
                @foreach($paises as $pais)
                    <option value="{{$pais->id}}" @if(isset($model) && $model->pais->id == $pais->id) selected @endif>{{$pais->des_nome}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="qtd_carros" class="form-label">Quantidade de Carros</label>
            <input type="text" class="form-control" id="qtd_carros" name="qtd_carros" value="@if(isset($model)) {{$model->qtd_carros}}  @endif">
        </div>

        @if(Route::currentRouteName() != 'pistas.create')
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
        <a href="{{route('pistas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection