<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Corrida;
use App\Models\Site\Evento;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule;

class EventoController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$eventos = Evento::where('user_id', Auth::user()->id)->orderBy('des_nome', 'ASC')->get();

		return view('site.eventos.index', compact('eventos'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return view('site.eventos.form');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		try {

			$request->validate([
				'des_nome' => ['required', 'string', ValidationRule::unique('eventos', 'des_nome')],
			], [
				'des_nome.unique' => 'Ja existe um evento cadastrado com este nome'
			]);

			$evento = new Evento();
			$evento->des_nome = $request->des_nome;
			$evento->user_id = Auth::user()->id;
			$evento->save();

			return redirect()->back()->with('status', 'Evento cadastrado com sucesso');
		} catch (\Throwable $th) {
			throw $th;
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$resultadoCorridas = Corrida::where('user_id', Auth::user()->id)
									->where('evento_id', $id)
									->orderBy('temporada_id', 'DESC')
									->orderBy('ordem', 'DESC')
									->get();

		if(count($resultadoCorridas) > 0){
			return view('site.eventos.show', compact('resultadoCorridas'));
		}else{
			return redirect()->route('eventos.index')->with('error', 'Evento não possui corridas disputadas');
		}

        return view('site.eventos.show', compact('id'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$model = Evento::where('id', $id)->where('user_id', Auth::user()->id)->first();

		return view('site.eventos.form', compact('model'));
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
		try {

			$request->validate([
				'des_nome' => ['required', 'string', ValidationRule::unique('eventos', 'des_nome')->ignore($id)],
			], [
				'des_nome.unique' => 'Ja existe um evento cadastrado com este nome'
			]);

			$evento = Evento::find($id);
			$evento->des_nome = $request->des_nome;
			$evento->update();

			return redirect()->route('eventos.index')->with('status', 'Evento atualizado com sucesso');
		} catch (\Throwable $th) {
			throw $th;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request)
	{
		try {

			$evento = Evento::find($request->evento_id);

			if ($evento) {

				$evento->delete();
				return redirect()->route('eventos.index')->with('status', 'Evento deletado com sucesso');
			}

		} catch (\Throwable $th) {
			throw $th;
		}
	}
}
