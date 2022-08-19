<table class="mt-5 mb-5">
    <tr>
        <th colspan="2">{{$modelPiloto->nome}} {{$modelPiloto->sobrenome}}</th>
    </tr>
    <tr>
        <td>Total De Corridas</td>
        <td>{{$totCorridas}}</td>
    </tr>
    <tr>
        <td>Vitórias</td>
        <td>{{$totVitorias}}</td>
    </tr>
    <tr>
        <td>Pole Positions</td>
        <td>{{$totPoles}}</td>
    </tr>
    <tr>
        <td>Pódios</td>
        <td>{{$totPodios}}</td>
    </tr>
    <tr>
        <td>Total de Pontos</td>
        <td>{{$totPontos}}</td>
    </tr>
    <tr>
        <td>Chegadas no Top 10</td>
        <td>{{$totTopTen}}</td>
    </tr>
    <tr>
        <td>Melhor Largada</td>
        <td>{{$melhorPosicaoLargada}}</td>
    </tr>
    <tr>
        <td>Pior Largada</td>
        <td>{{$piorPosicaoLargada}}</td>
    </tr>
    <tr>
        <td>Melhor Chegada</td>
        <td>{{$melhorPosicaoChegada}}</td>
    </tr>
    <tr>
        <td>Pior Chegada</td>
        <td>{{$piorPosicaoChegada}}</td>
    </tr>
    <tr>
        <td>Voltas Mais Rápidas</td>
        <td>0</td>
    </tr>
    {{-- <tr>
        <td>Abandonos</td>
        <td>0</td>
    </tr> --}}
    <tr>
        <td>Status</td>
        <td>
            @if($modelPiloto->flg_ativo == 'S')
                Em Atividade
            @else 
                Aposentado
            @endif
        </td>
    </tr>
</table>