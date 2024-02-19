@extends('layouts.main')

@php 
    $route = route('corridas.alterar', [$temporada->id]);
    $method = method_field('POST');
    if(isset($modelCorrida)){
        $route = route('corridas.update', [$temporada->id]);
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
    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif
   <div class="container">
        <div>
            <h2>Temporada {{$temporada->ano->ano}}</h2>
        </div>
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="pista_id">Pista</label>
            <select <?= isset($modelCorrida) ? '' : '' ?> name="pista_id" id="pista_id" class="form-control">
                @foreach($model as $pista)
                    <option value="{{ $pista->id }}" @isset($modelCorrida) @if($pista->id == $modelCorrida->pista_id) selected @endisset @endif>{{$pista->nome}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="ordem">Ordem</label>
        <input type="number" class="pista_ordem" name="ordem" id="ordem" style="width:30px; height:30px;" value="<?= isset($modelCorrida) ? $modelCorrida->ordem : '' ?>">
        </div>

        <div class="form-group form-check mb-3">
            <input
            type="checkbox"
            class="form-check-input"
            id="flg_sprint"
            name="flg_sprint"
            value="S"
            @if(isset($modelCorrida) && $modelCorrida->flg_sprint == 'S') checked @endif
            >
            <label class="form-check-label" for="flg_sprint">Corrida Sprint</label>
        </div>

        <input type="hidden" name="temporada_id" value="{{$temporada->id}}">
        @if(isset($modelCorrida))
            <input type="hidden" name="corrida_id" value="{{$modelCorrida->id}}">
        @endif

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('corridas.index',[$temporada->id])}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
           
    </script>
@endsection