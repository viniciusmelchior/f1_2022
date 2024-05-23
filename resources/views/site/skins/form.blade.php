@extends('layouts.main')

@php 
    $route = route('skins.store');
    $method = method_field('POST');

    if(isset($model)){
        $route = route('skins.update', [$model->id]);
        $method = method_field('PUT');
    }
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
            <label for="des_nome" class="form-label">Nome da Skin</label>
            <input type="text" class="form-control" id="skin" name="skin" value="@if(isset($model)) {{$model->skin}}  @endif">
        </div>
        <div class="mb-3">
            <label for="pais_id" class="form-label">Equipe</label>
            <select name="equipe_id" id="equipe_id" class="form-control">
                @foreach($equipes as $equipe)
                    <option value="{{$equipe->id}}" @if(isset($model) && $model->equipe_id == $equipe->id) selected @endif>{{$equipe->nome}}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('skins.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection