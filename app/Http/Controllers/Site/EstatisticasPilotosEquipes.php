<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site\Temporada;
use App\Models\Site\Piloto;
use App\Models\Site\Equipe;
use App\Models\Site\Resultado;
use App\Models\Site\Corrida;
use Illuminate\Support\Facades\Auth;

class EstatisticasPilotosEquipes extends Controller
{
    
    public function index(){
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();
        $pilotos  = Piloto::where('user_id', Auth::user()->id)->orderBy('nome', 'ASC')->get();
        $equipes  = Equipe::where('user_id', Auth::user()->id)->orderBy('nome', 'ASC')->get();
        $meuPiloto = Piloto::where('nome', 'Vinicius')->where('sobrenome', 'Melchior')->where('user_id', Auth::user()->id)->first();
        
        return view('site.estatisticasPilotosEquipes.index', compact('temporadas', 'pilotos', 'equipes', 'meuPiloto'));
    }

    public function buscar(Request $request){
        // dd($request->all());

        //A função será reaproveitada e para isso vou decidir algumas configurações que serão usadas no select
        $limite = $request->limite; //define se é vitórias/poles, pódios, top10, top5 etc
        $tipoConsulta = $request->tipo; //define se busco por posição de chegada ou largada
        $pilotosParaIgnorar = $request->pilotos_ignorados;
        $equipesParaIgnorar = $request->equipes_ignoradas;
        
        $temporada_id = $request->temporada;
        $operadorConsultaTemporada = '=';
        $condicaoConsultaTemporada = $temporada_id;
        
        if($temporada_id == null){
            $operadorConsultaTemporada = '>';
            $condicaoConsultaTemporada = 0; 
        }

        $retornoDadosPilotos = [];
        $retornoDadosEquipes = [];

        $descricaoPagina = '';

        if($tipoConsulta == 'chegada'){
            if($limite == 1){
                $descricaoPagina = 'Vitórias';
            }else{
                $descricaoPagina = 'Top '.$limite. ' chegadas';
            }
        }else{
            if($limite == 1){
                $descricaoPagina = 'Pole Positions';
            }else{
                $descricaoPagina = 'Top '.$limite. ' largadas';
            }
        }

        //primeiro encontra as corridas
        $corridas = Corrida::where('user_id', Auth::user()->id)
                            ->where('flg_sprint', 'N')
                            ->where('exibir_resultado',1)
                            // ->whereIn('id', [278, 248, 261])//fixado temporariamente
                            ->where('temporada_id', $operadorConsultaTemporada, $condicaoConsultaTemporada)//fixado temporariamente
                            ->get();

        foreach ($corridas as $corrida) {

            $resultados = Resultado::where(function($query) use ($corrida) {
                $query->where('resultados.user_id', Auth::user()->id)
                      ->where('corrida_id', $corrida->id);
            })
            ->join('piloto_equipes', 'resultados.pilotoEquipe_id', '=', 'piloto_equipes.id')
            ->join('pilotos', 'pilotos.id', '=', 'piloto_equipes.piloto_id')
            ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
            ->whereNotIn('pilotos.id', $pilotosParaIgnorar)//fixado temporariamente. É o meu piloto
            ->whereNotIn('equipes.id', $equipesParaIgnorar)//fixado temporariamente. É a minha equipe
            ->orderBy('resultados.'.$tipoConsulta.'', 'ASC')
            ->limit($limite) //selecionar o top numero para decidir até qual lugar pesquisar
            ->get(
                [
                    'resultados.*',
                    'pilotos.nome as nome_piloto', // Alias para o nome do piloto
                    'pilotos.sobrenome as sobrenome_piloto', // Alias para o sobrenome do piloto
                    'equipes.nome as nome_equipe', // Alias para o nome da equipe
                    'equipes.id as id_equipe',
                    'pilotos.id as id_piloto',
                ]
            );

            //Agora preciso colocar no array todas os resultados. 
            foreach($resultados as $resultado){
                $retornoDadosPilotos[] = [
                    'equipe_id' => $resultado->id_equipe,     
                    'equipe_nome' => $resultado->nome_equipe, 
                    'id_piloto' => $resultado->id_piloto,
                    'nome_piloto' => $resultado->nome_piloto,
                    'sobrenome' => $resultado->sobrenome_piloto,  
                    'corrida' => $resultado->corrida->pista->nome,
                    'pais' => $resultado->corrida->pista->pais->des_nome,
                    'pais_id' => $resultado->corrida->pista->pais->id,
                    'evento' => $resultado->corrida->evento->des_nome,
                    'evento_id' => $resultado->corrida->evento->id,
                    'pista_id' =>$resultado->corrida->pista->id   
                ];

                $retornoDadosEquipes[] = [
                    'equipe_id' => $resultado->id_equipe,     
                    'equipe_nome' => $resultado->nome_equipe, 
                    'id_piloto' => $resultado->id_piloto,
                    'nome_piloto' => $resultado->nome_piloto,
                    'sobrenome' => $resultado->sobrenome_piloto,  
                    'corrida' => $resultado->corrida->pista->nome,
                    'pais' => $resultado->corrida->pista->pais->des_nome,
                    'pais_id' => $resultado->corrida->pista->pais->id,
                    'evento' => $resultado->corrida->evento->des_nome,
                    'evento_id' => $resultado->corrida->evento->id,
                    'pista_id' =>$resultado->corrida->pista->id
                ];
            }
        }

        // Função para ordenar arrays pela quantidade (decrescente)
        $ordenarPorQuantidade = function ($a, $b) {
            return $b['quantidade'] - $a['quantidade'];
        };

        // Passo 1: Agrupar e contar vitórias por equipe e piloto
        $quantidadePorEquipe = [];
        // dd($retornoDadosPilotos);
        foreach ($retornoDadosEquipes as $item) {
            $equipeId = $item['equipe_id'];
            $pilotoId = $item['id_piloto'];
            $nomePiloto = $item['nome_piloto'] . ' ' . $item['sobrenome'];

            // Agrupar por equipe
            if (!isset($quantidadePorEquipe[$equipeId])) {
                $quantidadePorEquipe[$equipeId] = [
                    'equipe_id' => $equipeId,
                    'equipe_nome' => $item['equipe_nome'],
                    'quantidade_equipe' => 0,
                    'pilotos' => [],
                ];
            }
            $quantidadePorEquipe[$equipeId]['quantidade_equipe']++;

            // Agrupar por piloto dentro da equipe
            if (!isset($quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId])) {
                $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId] = [
                    'nome_piloto' => $nomePiloto,
                    'quantidade' => 0,
                    'quantidade_por_pista' => [],
                    'quantidade_por_evento' => [],
                    'quantidade_por_pais' => [],
                ];
            }
            $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade']++;

            // Agrupar vitórias por pista
            $pista = $item['corrida'];
            if (!isset($quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_pista'][$pista])) {
                $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_pista'][$pista] = [
                    'pista' => $pista,
                    'pista_id' => $item['pista_id'],
                    'quantidade' => 0,
                ];
            }
            $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_pista'][$pista]['quantidade']++;

            // Agrupar vitórias por evento
            $eventoId = $item['evento_id'];
            if (!isset($quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_evento'][$eventoId])) {
                $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_evento'][$eventoId] = [
                    'evento' => $item['evento'],
                    'evento_id' => $item['evento_id'],
                    'quantidade' => 0,
                ];
            }
            $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_evento'][$eventoId]['quantidade']++;

            // Agrupar vitórias por país
            $paisId = $item['pais_id'];
            if (!isset($quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_pais'][$paisId])) {
                $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_pais'][$paisId] = [
                    'pais' => $item['pais'],
                    'pais_id' => $item['pais_id'],
                    'quantidade' => 0,
                ];
            }
            $quantidadePorEquipe[$equipeId]['pilotos'][$pilotoId]['quantidade_por_pais'][$paisId]['quantidade']++;
        }

        // Passo 2: Ordenar as vitórias por quantidade em cada categoria
        foreach ($quantidadePorEquipe as &$equipe) {
            foreach ($equipe['pilotos'] as &$piloto) {
                // Ordenar vitórias por pista
                uasort($piloto['quantidade_por_pista'], $ordenarPorQuantidade);
                // Ordenar vitórias por evento
                uasort($piloto['quantidade_por_evento'], $ordenarPorQuantidade);
                // Ordenar vitórias por país
                uasort($piloto['quantidade_por_pais'], $ordenarPorQuantidade);
            }
        }

        // Passo 3: Remover chaves numéricas dos pilotos
        foreach ($quantidadePorEquipe as &$equipe) {
            $equipe['pilotos'] = array_values($equipe['pilotos']);
        }

        // Ordena pelo campo "quantidade_equipe" de forma decrescente
        usort($quantidadePorEquipe, function($a, $b) {
            return $b['quantidade_equipe'] <=> $a['quantidade_equipe'];
        });

        $ordenarPorQuantidade = function ($a, $b) {
            return $b['quantidade'] - $a['quantidade'];
        };
        
        // Passo 1: Agrupar e contar vitórias por piloto
        $vitoriasPorPiloto = [];
        foreach ($retornoDadosEquipes as $item) {
            $pilotoId = $item['id_piloto'];
            $nomePiloto = $item['nome_piloto'] . ' ' . $item['sobrenome'];
        
            if (!isset($vitoriasPorPiloto[$pilotoId])) {
                $vitoriasPorPiloto[$pilotoId] = [
                    'nome_piloto' => $nomePiloto,
                    'quantidade' => 0,
                    'equipes' => [],
                    'paises' => [],
                    'eventos' => [],
                    'pistas' => [],
                ];
            }
            $vitoriasPorPiloto[$pilotoId]['quantidade']++;
        
            // Agrupar vitórias por equipe
            $equipeId = $item['equipe_id'];
            if (!isset($vitoriasPorPiloto[$pilotoId]['equipes'][$equipeId])) {
                $vitoriasPorPiloto[$pilotoId]['equipes'][$equipeId] = [
                    'equipe_nome' => $item['equipe_nome'],
                    'equipe_id' => $item['equipe_id'],
                    'quantidade' => 0,
                ];
            }
            $vitoriasPorPiloto[$pilotoId]['equipes'][$equipeId]['quantidade']++;
        
            // Agrupar vitórias por país
            $paisId = $item['pais_id'];
            if (!isset($vitoriasPorPiloto[$pilotoId]['paises'][$paisId])) {
                $vitoriasPorPiloto[$pilotoId]['paises'][$paisId] = [
                    'pais' => $item['pais'],
                    'pais_id' => $item['pais_id'],
                    'quantidade' => 0,
                ];
            }
            $vitoriasPorPiloto[$pilotoId]['paises'][$paisId]['quantidade']++;
        
            // Agrupar vitórias por evento
            $eventoId = $item['evento_id'];
            if (!isset($vitoriasPorPiloto[$pilotoId]['eventos'][$eventoId])) {
                $vitoriasPorPiloto[$pilotoId]['eventos'][$eventoId] = [
                    'evento' => $item['evento'],
                    'evento_id' => $item['evento_id'],
                    'quantidade' => 0,
                ];
            }
            $vitoriasPorPiloto[$pilotoId]['eventos'][$eventoId]['quantidade']++;
        
            // Agrupar vitórias por pista
            $pista = $item['corrida'];
            if (!isset($vitoriasPorPiloto[$pilotoId]['pistas'][$pista])) {
                $vitoriasPorPiloto[$pilotoId]['pistas'][$pista] = [
                    'pista' => $pista,
                    'pista_id' => $item['pista_id'],
                    'quantidade' => 0,
                ];
            }
            $vitoriasPorPiloto[$pilotoId]['pistas'][$pista]['quantidade']++;
        }
        
        // Passo 2: Ordenar cada agrupamento pela quantidade de vitórias
        foreach ($vitoriasPorPiloto as &$piloto) {
            uasort($piloto['equipes'], $ordenarPorQuantidade);
            uasort($piloto['paises'], $ordenarPorQuantidade);
            uasort($piloto['eventos'], $ordenarPorQuantidade);
            uasort($piloto['pistas'], $ordenarPorQuantidade);
        }
        
        // Passo 3: Remover chaves numéricas dos agrupamentos
        foreach ($vitoriasPorPiloto as &$piloto) {
            $piloto['equipes'] = array_values($piloto['equipes']);
            $piloto['paises'] = array_values($piloto['paises']);
            $piloto['eventos'] = array_values($piloto['eventos']);
            $piloto['pistas'] = array_values($piloto['pistas']);
        }

        usort($vitoriasPorPiloto, function($a, $b) {
            return $b['quantidade'] <=> $a['quantidade'];
        });
        
        //retornar json
        return response()->json([
            'descricaoPagina' => $descricaoPagina,
            'dadosPiloto' => $vitoriasPorPiloto,
            'dadosEquipe' => $quantidadePorEquipe
        ]);

        // dd($vitoriasPorPiloto,$quantidadePorEquipe);
    }
}
