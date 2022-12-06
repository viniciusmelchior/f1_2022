@extends('layouts.main')

@php 
    $route = route('corridas.alterar', [$temporada->id]);
    $method = method_field('POST');
    if(isset($model)){
        $route = route('corridas.alterar', [$temporada->id]);
        $method = method_field('POST');
    }
@endphp

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
</style>

@section('section')
   <div class="container">
        <div>
            <h2>Temporada {{$temporada->ano->ano}}</h2>
        </div>
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
      {{--   <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="@if(isset($model)) {{$model->nome}}  @endif">
        </div> --}}
      {{--   <div class="mb-3">
            <label for="sobrenome" class="form-label">Sobrenome</label>
            <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="@if(isset($model)) {{$model->sobrenome}}  @endif">
        </div> --}}
      {{--   <div class="mb-3">
            <label for="pais_id" class="form-label">País</label>
            <select name="pais_id" id="pais_id" class="form-control">
                @foreach($paises as $pais)
                    <option value="{{$pais->id}}" @if(isset($model) && $model->pais->id == $pais->id) selected @endif>{{$pais->des_nome}}</option>
                @endforeach
            </select>
        </div> --}}
        <div class="mb-3">
            <label for="pista_id">Pista</label>
            <select name="pista_id" id="pista_id" class="form-control">
                @foreach($model as $pista)
                    <option value="{{$pista->id}}">{{$pista->nome}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="ordem">Ordem</label>
        <input type="number" class="pista_ordem" name="ordem" id="ordem" style="width:30px; height:30px;">
        </div>

        



        <input type="hidden" name="temporada_id" value="{{$temporada->id}}">
      {{--   <table class="table table-sm">
            <thead>
                <tr>
                    <th>Pista</th>
                    <th>Ordem</th>
                </tr>
            </thead>
            <tbody>
                @foreach($model as $pista)
                    <tr>
                        <td>
                            <div class="form-group form-check mb-3 col-md-6">
                                <input
                                type="checkbox"
                                class="form-check-input check-pista"
                                id="pista_id"
                                name="pista_id[]"
                                value="{{$pista->id}}"
                                >
                                <label class="form-check-label" for="flg_ativo">{{$pista->nome}}</label>
                            </div>
                        </td>
                        <td>
                            <input type="number" class="pista_ordem" name="ordem[]" id="ordem" style="width:30px; height:30px;">
                        </td>
                    </tr>
                @endforeach
            </tbody>
           
        </table> --}}

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('temporadas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
           
    </script>
@endsection