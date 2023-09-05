<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Ano;
use App\Models\Site\Corrida;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemporadaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $temporadas = Temporada::where('user_id', Auth::user()->id)->get();

        return view('site.temporadas.index', compact('temporadas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        return view('site.temporadas.form', compact('anos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $temporada = new Temporada();
        $temporada->des_temporada = $request->des_temporada;
        $temporada->user_id = Auth::user()->id;
        $temporada->ano_id = $request->ano_id;
        $temporada->observacoes = $request->observacoes;
        $temporada->flg_finalizada = 'N';

        $temporada->save();

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //aqui exibe as corridas
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        $usuario = Auth::user()->id;
        $model = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        //$retorno = $this->montaClassificacao($usuario, $model);

        return view('site.temporadas.form', compact('model','anos'));
    }

    public function classificacao($id){
        $usuario = Auth::user()->id; 
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first();

        $corridaAtual = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->where('resultados.user_id', Auth::user()->id)
                                    ->where('corridas.temporada_id', $id)
                                    ->where('resultados.chegada', '<>', null)
                                    ->orderBy('corrida_id', 'desc')
                                    ->first();

        $totalCorridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->where('flg_sprint', 'N')->count();

        $corridaAtual = Corrida::find($corridaAtual->corrida_id);

        $retorno = $this->montaClassificacao($usuario, $temporada);
        $resultadosPilotos = $retorno['resultadoPilotos'];
        $resultadosEquipes = $retorno['resultadoEquipes'];

        $retornoClassico = $this->montaClassificacaoClassica($usuario, $temporada);
        $resultadosPilotosClassico = $retornoClassico['resultadoPilotosClassico'];
        $resultadosEquipesClassico = $retornoClassico['resultadoEquipesClassico'];

        $retornoInvertida = $this->montaClassificacaoInvertida($usuario, $temporada);
        $resultadosPilotosInvertida = $retornoInvertida['resultadoPilotosInvertida'];
        $resultadosEquipesInvertida = $retornoInvertida['resultadoEquipesInvertida'];

        $retornoAlternativa = $this->montaClassificacaoAlternativa($usuario, $temporada);
        $resultadosPilotosAlternativa = $retornoAlternativa['resultadoPilotosAlternativa'];
        $resultadosEquipesAlternativa = $retornoAlternativa['resultadoEquipesAlternativa'];

        return view('site.temporadas.classificacao', compact('corridaAtual','totalCorridas','temporada', 'resultadosPilotos','resultadosEquipes','resultadosPilotosClassico','resultadosEquipesClassico','resultadosPilotosInvertida','resultadosEquipesInvertida','resultadosPilotosAlternativa','resultadosEquipesAlternativa'));
    } 

    public function montaClassificacao($usuario, $temporada){
                $resultadosPilotos = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id, pilotos.nome, pilotos.sobrenome ,equipes.nome as equipe, equipes.imagem, sum(pontuacao) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join pilotos on pilotos.id = piloto_equipes.piloto_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.piloto_id
                order by total desc');

                $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.equipe_id
                order by total desc');

                return [
                    'resultadoPilotos' => $resultadosPilotos,
                    'resultadoEquipes' => $resultadosEquipes,
                    'piloto_campeao' => $resultadosPilotos,
                    'equipe_campea' => $resultadosEquipes
                ];
    }
    public function montaClassificacaoClassica($usuario, $temporada){
                $resultadosPilotosClassico = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id,pilotos.nome,pilotos.sobrenome,equipes.imagem, equipes.nome as equipe, sum(pontuacao_classica) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join pilotos on pilotos.id = piloto_equipes.piloto_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.piloto_id
                order by total desc');

                $resultadosEquipesClassico = DB::select('select equipe_id, equipes.imagem, equipes.nome as nome, sum(pontuacao_classica) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.equipe_id
                order by total desc');

                return [
                    'resultadoPilotosClassico' => $resultadosPilotosClassico,
                    'resultadoEquipesClassico' => $resultadosEquipesClassico,
                ];
    }

    public function montaClassificacaoInvertida($usuario, $temporada){
                $resultadosPilotosInvertida = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id, pilotos.nome, pilotos.sobrenome,equipes.imagem, equipes.nome as equipe, sum(pontuacao_invertida) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join pilotos on pilotos.id = piloto_equipes.piloto_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.piloto_id
                order by total desc');

                $resultadosEquipesInvertida = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao_invertida) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.equipe_id
                order by total desc');

                return [
                    'resultadoPilotosInvertida' => $resultadosPilotosInvertida,
                    'resultadoEquipesInvertida' => $resultadosEquipesInvertida,
                ];
    }

    public function montaClassificacaoAlternativa($usuario, $temporada){
                $resultadosPilotosAlternativa = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id, pilotos.nome, pilotos.sobrenome, equipes.imagem, equipes.nome as equipe, sum(pontuacao_personalizada) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join pilotos on pilotos.id = piloto_equipes.piloto_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.piloto_id
                order by total desc');

                $resultadosEquipesAlternativa = DB::select('select equipe_id,equipes.imagem, equipes.nome as nome, sum(pontuacao_personalizada) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = '.$temporada->id.'
                and resultados.user_id = '.$usuario.'
                group by piloto_equipes.equipe_id
                order by total desc');

                return [
                    'resultadoPilotosAlternativa' => $resultadosPilotosAlternativa,
                    'resultadoEquipesAlternativa' => $resultadosEquipesAlternativa,
                ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $temporada = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $temporada->des_temporada = $request->des_temporada;
        $temporada->user_id = Auth::user()->id;
        $temporada->ano_id = $request->ano_id;
        $usuario = Auth::user()->id; 
        $temporada->observacoes = $request->observacoes;
        //pesquisa pela classificação atualizada
        $retorno = $this->montaClassificacao($usuario, $temporada);

        if ($request->has('flg_finalizada')) {
            $temporada->flg_finalizada = $request->flg_finalizada;
            //criar o título
            if(count($retorno['piloto_campeao']) > 0){
                $titulo = new Titulo();
                $titulo->temporada_id = $temporada->id;
                $titulo->pilotoEquipe_id = $retorno['piloto_campeao'][0]->pilotoEquipe_id;
                $titulo->equipe_id = $retorno['equipe_campea'][0]->equipe_id;
                $titulo->user_id = Auth::user()->id;
                $titulo->save();
            }
        } else {
            $temporada->flg_finalizada = 'N';

            //se tiver título registrado, apaga. Se não, sem ação;
            $titulo = Titulo::where('temporada_id', $temporada->id)->where('user_id', Auth::user()->id)->first();
            if($titulo != null){
                $titulo->delete();
            }
        }

        $temporada->update();

        return redirect()->route('temporadas.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $temporada = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $temporada->delete();
        return redirect()->back();
    }

    /**
     * rotas para montar classificação de acordo com a corrida
     */

     public function getClassificacaoAposCorrida(Request $request){

        $usuario = Auth::user()->id; 
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $request->temporada_id)->first();
        $ordem = $request->corrida_id;

        $retorno = $this->montaClassificacaoAposCorrida($usuario, $ordem, $temporada);
        $resultadosPilotos = $retorno['resultadoPilotos'];

        $corrida = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
                                    ->where('resultados.user_id', Auth::user()->id)
                                    ->where('corridas.temporada_id', $temporada->id)
                                    ->where('resultados.chegada', '<>', null)
                                    ->where('corridas.ordem', $ordem)
                                    ->orderBy('corrida_id', 'desc')
                                    ->first();

        $corrida = Corrida::find($corrida->corrida_id);
        $ordemCorrida = $corrida->ordem;
        $corridaAtual = $corrida->pista->nome;

        // dd($resultadosPilotos);

        return response()->json([
            'message' => 'OK',
            'ordemCorrida' => $ordemCorrida,
            'corridaAtual' => $corridaAtual,
            'resultadosPilotos' => $resultadosPilotos
        ]);  

     }

    public function montaClassificacaoAposCorrida($usuario, $ordem, $temporada){
                                        $resultadosPilotos = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id, pilotos.nome, pilotos.sobrenome ,equipes.nome as equipe, equipes.imagem, sum(pontuacao) as total from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join pilotos on pilotos.id = piloto_equipes.piloto_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = '.$temporada->id.'
                                        and corridas.ordem <= '.$ordem.'
                                        and resultados.user_id = '.$usuario.'
                                        group by piloto_equipes.piloto_id
                                        order by total desc');

                                        $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = '.$temporada->id.'
                                        and resultados.user_id = '.$usuario.'
                                        group by piloto_equipes.equipe_id
                                        order by total desc');

        return [
            'resultadoPilotos' => $resultadosPilotos,
            'resultadoEquipes' => $resultadosEquipes,
            'piloto_campeao' => $resultadosPilotos,
            'equipe_campea' => $resultadosEquipes
        ];
    }

    public function resultados($id){

        $usuario = Auth::user()->id; 
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first();

        $retorno = $this->montaClassificacao($usuario, $temporada);
        $resultadosPilotos = $retorno['resultadoPilotos'];

        $corridas = Corrida::where('temporada_id', $id)
                            ->where('user_id', $usuario)
                            // ->where('flg_sprint', 'N')
                            ->orderBy('ordem', 'ASC')
                            ->get();

        return view('site.temporadas.resultados', compact('temporada', 'resultadosPilotos','corridas'));
    }

}
