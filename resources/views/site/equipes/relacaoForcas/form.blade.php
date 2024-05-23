@extends('layouts.main')

@php 
    $route = route('equipes.relacaoForcas.update');
    $method = method_field('POST');
@endphp

@section('section')

    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="des_nome" class="form-label">{{ $equipe->nome}}</label>
            <input type="text" class="form-control" id="forca" name="forca" value="@if(isset($model)) {{$model->forca}}  @endif">
            <input type="hidden" name="ano_id" value="{{ $ano_id }}">
            <input type="hidden" name="equipe_id" value="{{ $equipe->id }}">
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('pilotos.relacaoForcas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection