@extends('layouts.main')

@php 
    $route = route('anos.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('anos.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="ano" class="form-label">Ano</label>
            <input type="text" class="form-control" id="ano" name="ano" value="@if(isset($model)) {{$model->ano}}  @endif">
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
        <a href="{{route('anos.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection