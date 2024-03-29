<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\CondicaoClimatica;
use App\Models\Site\Corrida;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class ResultadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $corrida = Corrida::where('id', $id)->first();
        $voltaRapida = PilotoEquipe::find($corrida->volta_rapida);
        $descEvento = 'Grand Prix';
        if($corrida->flg_sprint == 'S'){
            $descEvento = 'Sprint';
        }

        if($corrida->flg_super_corrida == 'S'){
            $descEvento = 'Super-Race';
        }

        $condicoesClimaticas = CondicaoClimatica::where('user_id', Auth::user()->id)->get();
        $model = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $corrida->id)->orderBy('chegada')->paginate(11);
        $vencedor = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $corrida->id)->where('chegada', 1)->orderBy('chegada')->first();
        if(count($model) == 0){
            return redirect()->back()->with('error', 'Não existem resultados cadastrados para o evento selecionado');
        }

        return view('site.resultados.show', compact('corrida', 'model', 'condicoesClimaticas','vencedor','descEvento','voltaRapida'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        $corrida = Corrida::where('id', $id)->first();
        $condicoesClimaticas = CondicaoClimatica::where('user_id', Auth::user()->id)->get();
        $model = PilotoEquipe::where('user_id', Auth::user()->id)->where('ano_id', $corrida->temporada->ano_id)->where('flg_ativo', 'S')->orderBy('equipe_id')->get();

        return view('site.resultados.form', compact('corrida', 'model', 'condicoesClimaticas'));
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
        $corrida = Corrida::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $corrida->volta_rapida = $request->volta_rapida;
        $corrida->condicao_id = $request->condicao_id;
        $corrida->qtd_safety_car = $request->qtd_safety_car;
        $corrida->dificuldade_ia = $request->dificuldade_ia;
        $corrida->observacoes = $request->observacoes;
        $corrida->updated_at = date('Y-m-d H:i:s');

        if ($request->categoria == 'F1Sprint') {
            $corrida->flg_sprint = 'S';
        } else {
            $corrida->flg_sprint = 'N';
        }

        if ($request->categoria == 'SC') {
            $corrida->flg_super_corrida = 'S';
            $corrida->flg_sprint = 'S';
        }
        
        $corrida->update();
        
        foreach($request->pilotoEquipe_id as $key=> $pilotoEquipe){
            $model = Resultado::where('user_id', Auth::user()->id)->where('pilotoEquipe_id', $pilotoEquipe)->where('corrida_id', $corrida->id)->first();
            $largada = $request->input('largada')[$key];
            $chegada = $request->input('chegada')[$key];
                if($model){
                    $model->largada = $largada;
                    $model->chegada = $chegada;
                    $model->flg_abandono = 'N';
                    if($request->input('flg_abandono')){
                        foreach($request->input('flg_abandono') as $isAbandono){
                            if($isAbandono == $pilotoEquipe){
                                $model->flg_abandono = 'S';
                            }
                        }
                    }

                    if($request->categoria == 'FormulaE'){
                         //Regra de 3 pontos para o pole position
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada)+3;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }
                    }

                    if($request->categoria == 'F1'){
                        $model->pontuacao = $this->calcularPontuacao($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }

                    if($request->categoria == 'F1Sprint'){
                        $model->pontuacao = $this->calcularPontuacaoSprint($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);  
                    }

                    if($request->categoria == 'SC'){
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada)+2;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);  
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);  
                        }
                    }

                    if($request->categoria == 'Indy'){
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada)+1;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }
                    }

                    if($request->categoria == 'Stock1'){
                        //Regra de 2 pontos para o pole position da Corrida 1
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada)+2;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }
                    }
                    
                    if($request->categoria == 'Stock2'){
                        $model->pontuacao = $this->calcularPontuacaoStockCar2($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }
                    
                    $model->update();

                }else{
                    $model = new Resultado();
                    $model->corrida_id = $corrida->id;
                    $model->pilotoEquipe_id = $pilotoEquipe;
                    $model->user_id = Auth::user()->id;
                    $model->largada = $largada;
                    $model->chegada = $chegada;
                    if($request->input('flg_abandono')){
                        foreach($request->input('flg_abandono') as $isAbandono){
                            if($isAbandono == $pilotoEquipe){
                                $model->flg_abandono = 'S';
                            }
                        }
                    }

                    if($request->categoria == 'FormulaE'){
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada)+3;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao = $this->calcularPontuacaoInvertida($model, $chegada);
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoFormulaE($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }
                    }

                    if($request->categoria == 'F1'){
                        $model->pontuacao = $this->calcularPontuacao($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }

                    if($request->categoria == 'F1Sprint'){
                        $model->pontuacao = $this->calcularPontuacaoSprint($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);  
                    }

                    if($request->categoria == 'SC'){
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada)+2;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada); 
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoSuperCorrida($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);  
                        }
                    }

                    if($request->categoria == 'Indy'){
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada)+1;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoIndy($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }
                    }

                    if($request->categoria == 'Stock1'){
                        //Regra de 3 pontos para o pole position da Corrida 1
                        if($model->largada == 1){
                            $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada)+2;
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }else{
                            $model->pontuacao = $this->calcularPontuacaoStockCar1($model, $chegada);
                            $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                            $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                            $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                        }
                    }
                    
                    if($request->categoria == 'Stock2'){
                        $model->pontuacao = $this->calcularPontuacaoStockCar2($model, $chegada);
                        $model->pontuacao_personalizada = $this->calcularPontuacaoPersonalizada($model, $chegada);
                        $model->pontuacao_classica = $this->calcularPontuacaoClassica($model, $chegada);
                        $model->pontuacao_invertida = $this->calcularPontuacaoInvertida($model, $chegada);
                    }

                    //salva as alterações 
                    if(($model->largada != null) || $model->chegada != null){
                        $model->save();
                    }
    
                }

        }

        /**Cáculo da volta mais rapida Formula 1 */
        if($corrida->volta_rapida != null && $corrida->flg_super_corrida != 'S'){
            $resultadoVoltaRapida = Resultado::where('pilotoEquipe_id', $corrida->volta_rapida)
                                                ->where('user_id', Auth::user()->id)
                                                ->where('corrida_id', $corrida->id)
                                                ->first();

            if($resultadoVoltaRapida->chegada <= 10){
                $resultadoVoltaRapida->pontuacao = $resultadoVoltaRapida->pontuacao+1;
                $resultadoVoltaRapida->update();
            }

        }

        /**calcula das super corridas F1 */
        if($corrida->volta_rapida != null && $corrida->flg_super_corrida == 'S'){
            $resultadoVoltaRapida = Resultado::where('pilotoEquipe_id', $corrida->volta_rapida)
                                                ->where('user_id', Auth::user()->id)
                                                ->where('corrida_id', $corrida->id)
                                                ->first();

            if($resultadoVoltaRapida->chegada <= 10){
                $resultadoVoltaRapida->pontuacao = $resultadoVoltaRapida->pontuacao+1;
                $resultadoVoltaRapida->update();
            }

        }
 
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function calcularPontuacao($model, $chegada){
        $primeiro = 25;
        $segundo = 18;
        $terceiro = 15;
        $quarto = 12;
        $quinto = 10;
        $sexto = 8;
        $setimo = 6;
        $oitavo = 4;
        $nono = 2;
        $decimo = 1; 

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            default:
            $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoFormulaE($model, $chegada){
        $primeiro = 25;
        $segundo = 18;
        $terceiro = 15;
        $quarto = 12;
        $quinto = 10;
        $sexto = 8;
        $setimo = 6;
        $oitavo = 4;
        $nono = 2;
        $decimo = 1; 

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            default:
            $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }

    public function calcularPontuacaoClassica($model, $chegada){
        $primeiro = 10;
        $segundo = 6;
        $terceiro = 4;
        $quarto = 3;
        $quinto = 2;
        $sexto = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao_classica = $primeiro;
                break;
            case 2:
                $model->pontuacao_classica = $segundo;
                break;
            case 3:
                $model->pontuacao_classica = $terceiro;
                break;
            case 4:
                $model->pontuacao_classica = $quarto;
                break;
            case 5:
                $model->pontuacao_classica = $quinto;
                break;
            case 6:
                $model->pontuacao_classica = $sexto;
                break; 
            default:
            $model->pontuacao_classica = 0;
        }

        return $model->pontuacao_classica;
    }

    // public function calcularPontuacaoSuperCorrida($model, $chegada){
    //     $primeiro = 50;
    //     $segundo = 40;
    //     $terceiro = 35;
    //     $quarto = 32;
    //     $quinto = 30;
    //     $sexto = 28;
    //     $setimo = 26;
    //     $oitavo = 24;
    //     $nono = 22;
    //     $decimo = 20; 
    //     $decimoPrimeiro = 19; 
    //     $decimoSegundo = 18; 
    //     $decimoTerceiro = 17; 
    //     $decimoQuarto = 16; 
    //     $decimoQuinto = 15; 
    //     $decimoSexto = 14; 
    //     $decimoSetimo = 13; 
    //     $decimoOitavo = 12; 
    //     $decimoNono = 11; 
    //     $vigesimo = 10;
    //     $vigesimoPrimeiro = 9;
    //     $vigesimoSegundo = 8;
    //     $vigesimoTerceiro = 7;
    //     $vigesimoQuarto = 6;

    //     switch ($chegada) {
    //         case 1:
    //             $model->pontuacao = $primeiro;
    //             break;
    //         case 2:
    //             $model->pontuacao = $segundo;
    //             break;
    //         case 3:
    //             $model->pontuacao = $terceiro;
    //             break;
    //         case 4:
    //             $model->pontuacao = $quarto;
    //             break;
    //         case 5:
    //             $model->pontuacao = $quinto;
    //             break;
    //         case 6:
    //             $model->pontuacao = $sexto;
    //             break;
    //         case 7:
    //             $model->pontuacao = $setimo;
    //             break;
    //         case 8:
    //             $model->pontuacao = $oitavo;
    //             break;
    //         case 9:
    //             $model->pontuacao = $nono;
    //             break;
    //         case 10:
    //             $model->pontuacao = $decimo;
    //             break;
    //         case 11:
    //             $model->pontuacao = $decimoPrimeiro;
    //             break;
    //         case 12:
    //             $model->pontuacao = $decimoSegundo;
    //             break;
    //         case 13:
    //             $model->pontuacao = $decimoTerceiro;
    //             break;
    //         case 14:
    //             $model->pontuacao = $decimoQuarto;
    //             break;
    //         case 15:
    //             $model->pontuacao = $decimoQuinto;
    //             break;
    //         case 16:
    //             $model->pontuacao = $decimoSexto;
    //             break;
    //         case 17:
    //             $model->pontuacao = $decimoSetimo;
    //             break;
    //         case 18:
    //             $model->pontuacao = $decimoOitavo;
    //             break;
    //         case 19:
    //             $model->pontuacao = $decimoNono;
    //             break;
    //         case 20:
    //             $model->pontuacao = $vigesimo;
    //             break;
    //         case 21:
    //             $model->pontuacao = $vigesimoPrimeiro;
    //             break;
    //         case 22:
    //             $model->pontuacao = $vigesimoSegundo;
    //             break;
    //         case 23:
    //             $model->pontuacao = $vigesimoTerceiro;
    //             break;
    //         case 24:
    //             $model->pontuacao = $vigesimoQuarto;
    //             break;
    //         default:
    //         $model->pontuacao = 5;
    //     }

    //     return $model->pontuacao;
    // }
    public function calcularPontuacaoSuperCorrida($model, $chegada){
        $primeiro = 10;
        $segundo = 8;
        $terceiro = 6;
        $quarto = 5;
        $quinto = 4;
        $sexto = 3;
        $setimo = 2;
        $oitavo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            default:
            $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoIndy($model, $chegada){
        $primeiro = 50;
        $segundo = 40;
        $terceiro = 35;
        $quarto = 32;
        $quinto = 30;
        $sexto = 28;
        $setimo = 26;
        $oitavo = 24;
        $nono = 22;
        $decimo = 20; 
        $decimoPrimeiro = 19; 
        $decimoSegundo = 18; 
        $decimoTerceiro = 17; 
        $decimoQuarto = 16; 
        $decimoQuinto = 15; 
        $decimoSexto = 14; 
        $decimoSetimo = 13; 
        $decimoOitavo = 12; 
        $decimoNono = 11; 
        $vigesimo = 10;
        $vigesimoPrimeiro = 9;
        $vigesimoSegundo = 8;
        $vigesimoTerceiro = 7;
        $vigesimoQuarto = 6;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            case 11:
                $model->pontuacao = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao = $decimoSexto;
                break;
            case 17:
                $model->pontuacao = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao = $decimoNono;
                break;
            case 20:
                $model->pontuacao = $vigesimo;
                break;
            case 21:
                $model->pontuacao = $vigesimoPrimeiro;
                break;
            case 22:
                $model->pontuacao = $vigesimoSegundo;
                break;
            case 23:
                $model->pontuacao = $vigesimoTerceiro;
                break;
            case 24:
                $model->pontuacao = $vigesimoQuarto;
                break;
            default:
            $model->pontuacao = 5;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoStockCar1($model, $chegada){
        $primeiro = 30;
        $segundo = 26;
        $terceiro = 22;
        $quarto = 19;
        $quinto = 17;
        $sexto = 15;
        $setimo = 14;
        $oitavo = 13;
        $nono = 12;
        $decimo = 11; 
        $decimoPrimeiro = 10; 
        $decimoSegundo = 9; 
        $decimoTerceiro = 8; 
        $decimoQuarto = 7; 
        $decimoQuinto = 6; 
        $decimoSexto = 5; 
        $decimoSetimo = 4; 
        $decimoOitavo = 3; 
        $decimoNono = 2; 
        $vigesimo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            case 11:
                $model->pontuacao = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao = $decimoSexto;
                break;
            case 17:
                $model->pontuacao = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao = $decimoNono;
                break;
            case 20:
                $model->pontuacao = $vigesimo;
                break;
            default:
            $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
    public function calcularPontuacaoStockCar2($model, $chegada){
        $primeiro = 24;
        $segundo = 20;
        $terceiro = 18;
        $quarto = 17;
        $quinto = 16;
        $sexto = 15;
        $setimo = 14;
        $oitavo = 13;
        $nono = 12;
        $decimo = 11; 
        $decimoPrimeiro = 10; 
        $decimoSegundo = 9; 
        $decimoTerceiro = 8; 
        $decimoQuarto = 7; 
        $decimoQuinto = 6; 
        $decimoSexto = 5; 
        $decimoSetimo = 4; 
        $decimoOitavo = 3; 
        $decimoNono = 2; 
        $vigesimo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            case 9:
                $model->pontuacao = $nono;
                break;
            case 10:
                $model->pontuacao = $decimo;
                break;
            case 11:
                $model->pontuacao = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao = $decimoSexto;
                break;
            case 17:
                $model->pontuacao = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao = $decimoNono;
                break;
            case 20:
                $model->pontuacao = $vigesimo;
                break;
            default:
            $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }

    public function calcularPontuacaoPersonalizada($model, $chegada){
        $primeiro = 50;
        $segundo = 40;
        $terceiro = 35;
        $quarto = 28;
        $quinto = 26;
        $sexto = 24;
        $setimo = 22;
        $oitavo = 20;
        $nono = 18;
        $decimo = 16; 
        $decimoPrimeiro = 12; 
        $decimoSegundo = 11; 
        $decimoTerceiro = 10; 
        $decimoQuarto = 9; 
        $decimoQuinto = 8; 
        $decimoSexto = 7; 
        $decimoSetimo = 6; 
        $decimoOitavo = 5; 
        $decimoNono = 4; 
        $vigesimo = 3;
        $vigesimoPrimeiro = 2;
        $vigesimoSegundo = 1;


        switch ($chegada) {
            case 1:
                $model->pontuacao_personalizada = $primeiro;
                break;
            case 2:
                $model->pontuacao_personalizada = $segundo;
                break;
            case 3:
                $model->pontuacao_personalizada = $terceiro;
                break;
            case 4:
                $model->pontuacao_personalizada = $quarto;
                break;
            case 5:
                $model->pontuacao_personalizada = $quinto;
                break;
            case 6:
                $model->pontuacao_personalizada = $sexto;
                break;
            case 7:
                $model->pontuacao_personalizada = $setimo;
                break;
            case 8:
                $model->pontuacao_personalizada = $oitavo;
                break;
            case 9:
                $model->pontuacao_personalizada = $nono;
                break;
            case 10:
                $model->pontuacao_personalizada = $decimo;
                break;
            case 11:
                $model->pontuacao_personalizada = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao_personalizada = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao_personalizada = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao_personalizada = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao_personalizada = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao_personalizada = $decimoSexto;
                break;
            case 17:
                $model->pontuacao_personalizada = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao_personalizada = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao_personalizada = $decimoNono;
                break;
            case 20:
                $model->pontuacao_personalizada = $vigesimo;
                break;
            case 21:
                $model->pontuacao_personalizada = $vigesimoPrimeiro;
                break;
            case 22:
                $model->pontuacao_personalizada = $vigesimoSegundo;
                break;
            default:
            $model->pontuacao_personalizada = 1;
        }

        return $model->pontuacao_personalizada;
    }

    public function calcularPontuacaoInvertida($model, $chegada){
        $primeiro = 1;
        $segundo = 2;
        $terceiro = 3;
        $quarto = 4;
        $quinto = 5;
        $sexto = 6;
        $setimo = 7;
        $oitavo = 8;
        $nono = 9;
        $decimo = 10; 
        $decimoPrimeiro = 11; 
        $decimoSegundo = 12; 
        $decimoTerceiro = 16; 
        $decimoQuarto = 18; 
        $decimoQuinto = 20; 
        $decimoSexto = 22; 
        $decimoSetimo = 24; 
        $decimoOitavo = 26; 
        $decimoNono = 28; 
        $vigesimo = 35;
        $vigesimoPrimeiro = 40;
        $vigesimoSegundo = 50;


        switch ($chegada) {
            case 1:
                $model->pontuacao_invertida = $primeiro;
                break;
            case 2:
                $model->pontuacao_invertida = $segundo;
                break;
            case 3:
                $model->pontuacao_invertida = $terceiro;
                break;
            case 4:
                $model->pontuacao_invertida = $quarto;
                break;
            case 5:
                $model->pontuacao_invertida = $quinto;
                break;
            case 6:
                $model->pontuacao_invertida = $sexto;
                break;
            case 7:
                $model->pontuacao_invertida = $setimo;
                break;
            case 8:
                $model->pontuacao_invertida = $oitavo;
                break;
            case 9:
                $model->pontuacao_invertida = $nono;
                break;
            case 10:
                $model->pontuacao_invertida = $decimo;
                break;
            case 11:
                $model->pontuacao_invertida = $decimoPrimeiro;
                break;
            case 12:
                $model->pontuacao_invertida = $decimoSegundo;
                break;
            case 13:
                $model->pontuacao_invertida = $decimoTerceiro;
                break;
            case 14:
                $model->pontuacao_invertida = $decimoQuarto;
                break;
            case 15:
                $model->pontuacao_invertida = $decimoQuinto;
                break;
            case 16:
                $model->pontuacao_invertida = $decimoSexto;
                break;
            case 17:
                $model->pontuacao_invertida = $decimoSetimo;
                break;
            case 18:
                $model->pontuacao_invertida = $decimoOitavo;
                break;
            case 19:
                $model->pontuacao_invertida = $decimoNono;
                break;
            case 20:
                $model->pontuacao_invertida = $vigesimo;
                break;
            case 21:
                $model->pontuacao_invertida = $vigesimoPrimeiro;
                break;
            case 22:
                $model->pontuacao_invertida = $vigesimoSegundo;
                break;
            default:
            $model->pontuacao_invertida = 70;
        }

        return $model->pontuacao_invertida;
    }

    public function calcularPontuacaoSprint($model, $chegada){
        $primeiro = 8;
        $segundo = 7;
        $terceiro = 6;
        $quarto = 5;
        $quinto = 4;
        $sexto = 3;
        $setimo = 2;
        $oitavo = 1;

        switch ($chegada) {
            case 1:
                $model->pontuacao = $primeiro;
                break;
            case 2:
                $model->pontuacao = $segundo;
                break;
            case 3:
                $model->pontuacao = $terceiro;
                break;
            case 4:
                $model->pontuacao = $quarto;
                break;
            case 5:
                $model->pontuacao = $quinto;
                break;
            case 6:
                $model->pontuacao = $sexto;
                break;
            case 7:
                $model->pontuacao = $setimo;
                break;
            case 8:
                $model->pontuacao = $oitavo;
                break;
            default:
            $model->pontuacao = 0;
        }

        return $model->pontuacao;
    }
}
