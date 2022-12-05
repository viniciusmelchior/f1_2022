@extends('layouts.main')

@php 
    $route = route('condicaoClimatica.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('condicaoClimatica.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <input type="text" class="form-control" id="descricao" name="descricao" value="@if(isset($model)) {{$model->descricao}}  @endif">
        </div>
        <div class="mb-3">
            <label for="des_icone" class="form-label">Ícone</label>
            <input type="text" class="form-control" id="des_icone" name="des_icone" value="@if(isset($model)) {{$model->des_icone}}  @endif">
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('condicaoClimatica.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection