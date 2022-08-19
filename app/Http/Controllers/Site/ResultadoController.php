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
        $condicoesClimaticas = CondicaoClimatica::where('user_id', Auth::user()->id)->get();
        $model = Resultado::where('user_id', Auth::user()->id)->where('corrida_id', $corrida->id)->orderBy('chegada')->get();
        //dd($model);

        return view('site.resultados.show', compact('corrida', 'model', 'condicoesClimaticas'));
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
        $model = PilotoEquipe::where('user_id', Auth::user()->id)->where('ano_id', $corrida->temporada->ano_id)->get();

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
        if ($request->has('flg_sprint')) {
            $corrida->flg_sprint = $request->flg_sprint;
        } else {
            $corrida->flg_sprint = 'N';
        }
        
        $corrida->update();
        
        foreach($request->pilotoEquipe_id as $key=> $pilotoEquipe){
            $model = Resultado::where('user_id', Auth::user()->id)->where('pilotoEquipe_id', $pilotoEquipe)->where('corrida_id', $corrida->id)->first();
            $largada = $request->input('largada')[$key];
            $chegada = $request->input('chegada')[$key];
                if($model){
                    $model->largada = $largada;
                    $model->chegada = $chegada;
                    if($corrida->flg_sprint == 'N'){
                        $model->pontuacao = $this->calcularPontuacao($model, $chegada);
                    }else{
                        $model->pontuacao = $this->calcularPontuacaoSprint($model, $chegada);
                    }
                   
                    $model->update();
    
                }else{
                    $model = new Resultado();
                    $model->corrida_id = $corrida->id;
                    $model->pilotoEquipe_id = $pilotoEquipe;
                    $model->user_id = Auth::user()->id;
                    $model->largada = $largada;
                    $model->chegada = $chegada;
                    if($corrida->flg_sprint == 'N'){
                        $model->pontuacao = $this->calcularPontuacao($model, $chegada);
                    }else{
                        $model->pontuacao = $this->calcularPontuacaoSprint($model, $chegada);
                    }
    
                    $model->save();
                }

        }
         /**Cáculo da volta mais rapida */
        if($corrida->volta_rapida != null){
            $resultadoVoltaRapida = Resultado::where('pilotoEquipe_id', $corrida->volta_rapida)
                                                ->where('user_id', Auth::user()->id)
                                                ->where('corrida_id', $corrida->id)
                                                ->first();

            if($resultadoVoltaRapida->chegada <= 10){
                $resultadoVoltaRapida->pontuacao = $resultadoVoltaRapida->pontuacao+1;
                $resultadoVoltaRapida->update();
            }
        }

        //Enviar Email Avisando que o resultado foi inserido
        $data["email"] = "vmelchior.93@gmail.com";
        $data["title"] = "Teste Email";
        $data["body"] = "Corpo do Email Teste";
        
        $pdf = PDF::loadView('emails.teste', $data);
  
        Mail::send('emails.teste', $data, function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
                    ->subject($data["title"])
                    ->attachData($pdf->output(), "text.pdf");
        });              

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
