@extends('layouts.main')

@php 
    $route = route('paises.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('paises.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="des_nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="des_nome" name="des_nome" value="@if(isset($model)) {{$model->des_nome}}  @endif">
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('paises.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection