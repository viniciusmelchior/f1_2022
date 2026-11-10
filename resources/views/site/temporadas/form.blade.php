@extends('layouts.main')

@php 
    $route = route('temporadas.store');
    $method = method_field('POST');
    if(isset($model)){
        $route = route('temporadas.update', [$model->id]);
        $method = method_field('PUT');
    }
@endphp

@section('section')
   <div class="container">
    <form method="POST" action="{{ $route }}" class="col-md-6 mt-3 mb-3">
        {{ $method }}
        @csrf
        <div class="mb-3">
            <label for="des_temporada" class="form-label">Descrição</label>
            <input type="text" class="form-control" id="des_temporada" name="des_temporada" value="@if(isset($model)) {{$model->des_temporada}}  @endif">
        </div>
        <div class="mb-3">
            <label for="ano_id" class="form-label">Ano</label>
            <select name="ano_id" id="ano_id" class="form-control">
                @foreach($anos as $ano)
                    <option value="{{$ano->id}}" @if(isset($model) && $model->ano->id == $ano->id) selected @endif>{{$ano->ano}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Temporada(s) de Referência</label>
            <input type="text" class="form-control" name="referencia" id="referencia" value="@if(isset($model)) {{$model->referencia}}  @endif">
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" name="observacoes" id="observacoes" rows="10">
                @if(isset($model->observacoes))
                    {{$model->observacoes}}
                @endif
            </textarea>
        </div>

        {{-- MELHORIA 20261110 (playoff): mata-mata opcional dentro do campeonato --}}
        @php
            $playoffAtivo = old('flg_playoff', isset($model) ? $model->flg_playoff : 'N') === 'S';
            $qtdCorridas = old('qtd_corridas', isset($model) ? $model->qtd_corridas : null);
            $classificadosPo = (int) old('playoff_classificados', isset($model) ? $model->playoff_classificados : 8) === 16 ? 16 : 8;
        @endphp
        <div class="mb-3 p-3" style="border: 1px solid #dee2e6; border-radius: 6px;">
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" id="flg_playoff" name="flg_playoff" value="S" @if($playoffAtivo) checked @endif>
                <label class="form-check-label fw-bold" for="flg_playoff">Habilitar playoffs</label>
            </div>
            <div id="bloco-playoff" style="{{ $playoffAtivo ? '' : 'display:none;' }}">
                <div class="d-flex flex-wrap gap-3">
                    <div>
                        <label for="qtd_corridas" class="form-label">Quantidade de corridas da temporada (sem sprints)</label>
                        <input type="number" class="form-control" id="qtd_corridas" name="qtd_corridas" min="10" step="1" value="{{ $qtdCorridas }}" style="max-width: 160px;">
                    </div>
                    <div>
                        <label for="playoff_classificados" class="form-label">Classificados</label>
                        {{-- MELHORIA 20261110 (playoff - oitavas) --}}
                        <select class="form-control" id="playoff_classificados" name="playoff_classificados" style="max-width: 260px;">
                            <option value="8" @if($classificadosPo === 8) selected @endif>8 pilotos · quartas, semi e final</option>
                            <option value="16" @if($classificadosPo === 16) selected @endif>16 pilotos · oitavas, quartas, semi e final</option>
                        </select>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" id="resumo-playoff"></small>
                <small class="text-muted d-block">
                    Os primeiros da fase classificatória avançam (só pontos das corridas principais).
                    8 classificados: quartas 1x8, 4x5, 2x7, 3x6 · 16 classificados: oitavas 1x16, 8x9, 4x13, 5x12, 2x15, 7x10, 3x14, 6x11.
                    Em cada fase avança quem somar mais pontos nas 3 corridas (empate: melhor classificado).
                    A classificação geral e a das sprints não mudam.
                </small>
            </div>
        </div>
        <script>
            (function () {
                const chk = document.getElementById('flg_playoff');
                const bloco = document.getElementById('bloco-playoff');
                const qtd = document.getElementById('qtd_corridas');
                const resumo = document.getElementById('resumo-playoff');
                const classificados = document.getElementById('playoff_classificados');
                const $j = window.jQuery; // select2 global (ver layout)
                function atualiza() {
                    bloco.style.display = chk.checked ? '' : 'none';
                    qtd.required = chk.checked;
                    const n = parseInt(qtd.value, 10);
                    const oitavas = classificados.value === '16';
                    const mataMata = oitavas ? 12 : 9;
                    qtd.min = mataMata + 1;
                    resumo.textContent = n > mataMata
                        ? (n - mataMata) + ' classificatória(s) + ' + (oitavas ? '3 oitavas de final + ' : '') + '3 quartas de final + 3 semifinais + 3 finais'
                        : 'Informe ao menos ' + (mataMata + 1) + ' corridas (mínimo de 1 classificatória).';
                }
                chk.addEventListener('change', atualiza);
                qtd.addEventListener('input', atualiza);
                classificados.addEventListener('change', atualiza);
                if ($j) $j(classificados).on('change', atualiza);
                atualiza();
            })();
        </script>

        {{--Form aparece apenas rota de edit--}}
        @if(Route::currentRouteName() == 'temporadas.edit')
            <div class="form-group form-check mb-3">
                <input
                type="checkbox"
                class="form-check-input"
                id="flg_finalizada"
                name="flg_finalizada"
                value="S"
                @if(isset($model) && $model->flg_finalizada == 'S') checked @endif
                >
                <label class="form-check-label" for="flg_ativo">Finalizar Temporada</label>
            </div>
        @endif
    
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{route('temporadas.index')}}" class="btn btn-secondary ml-3">Voltar</a>
      </form>
   </div>
@endsection