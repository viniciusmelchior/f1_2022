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
    @if (session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
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
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="@if(isset($model)) {{$model->nome}} @endif {{old('nome')}}" required>
        </div>
        <div class="mb-3">
            <label for="pais_id" class="form-label">País</label>
            <select name="pais_id" id="pais_id" class="form-control" required>
                <option value="">Selecione</option>
                @foreach($paises as $pais)
                    {{-- <option value="{{$pais->id}}" @if(isset($model) && $model->pais->id == $pais->id) selected @endif>{{$pais->des_nome}}</option> --}}
                    <option value="{{$pais->id}}" @if(isset($model) && $model->pais->id == $pais->id) selected  @else {{old('pais_id') == $pais->id ? 'selected': ''}} @endif>{{$pais->des_nome}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="qtd_carros" class="form-label">Quantidade de Carros</label>
            <input type="text" class="form-control" id="qtd_carros" name="qtd_carros" value="@if(isset($model)) {{$model->qtd_carros}} @else {{old('qtd_carros')}}  @endif">
        </div>
        <div class="mb-3">
            <label for="tamanho_km" class="form-label">Tamanho da pista (KM)</label>
            <input type="text" class="form-control" id="tamanho_km" name="tamanho_km" value="@if(isset($model)) {{$model->tamanho_km}} @else {{old('tamanho_km')}}  @endif">
        </div>

        <div class="mb-3">
            <label for="autor_id" class="form-label">Autor</label>
            <select name="autor_id" id="autor_id" class="form-control">
                <option value="">Selecione</option>
                @foreach($autores as $autor)
                    <option value="{{$autor->id}}" @if(isset($model) && $model->autor_id == $autor->id) selected  @else {{old('autor_id') == $autor->id ? 'selected': ''}} @endif>{{$autor->nome}}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="drs" class="form-label">DRS</label>
            <select name="drs" id="drs" class="form-control" required>
                @foreach($modelDrs as $drs)
                    <option value="{{$drs}}" @if(isset($model) && $model->drs == $drs) selected  @else {{old('drs') == $drs ? 'selected': ''}} @endif>{{$drs}}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" id="tipo" class="form-control" required>
                @foreach($tipos as $tipo)
                    <option value="{{$tipo}}" @if(isset($model) && $model->tipo == $tipo) selected  @else {{old('tipo') == $tipo ? 'selected': ''}} @endif>{{$tipo}}</option>
                @endforeach
            </select>
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