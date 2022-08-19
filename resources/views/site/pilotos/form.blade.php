@extends('layouts.main')

@php 
    $route = route('pilotos.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('pilotos.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
@if (session('status'))
    <div class="alert alert-success text-center">
        {{ session('status') }}
    </div>
@endif

   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="@if(isset($model)) {{$model->nome}}  @endif">
        </div>
        <div class="mb-3">
            <label for="sobrenome" class="form-label">Sobrenome</label>
            <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="@if(isset($model)) {{$model->sobrenome}}  @endif">
        </div>
        <div class="mb-3">
            <label for="pais_id" class="form-label">País</label>
            <select name="pais_id" id="pais_id" class="form-control">
                @foreach($paises as $pais)
                    <option value="{{$pais->id}}" @if(isset($model) && $model->pais->id == $pais->id) selected @endif>{{$pais->des_nome}}</option>
                @endforeach
            </select>
        </div>
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
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('pilotos.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection