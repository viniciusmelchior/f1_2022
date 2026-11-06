<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Piloto;
use App\Services\CuriosidadesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * 20261105 - Curiosidades e recordes (somente Grandes Prêmios, sem sprints).
 *
 * Os números (CuriosidadesService) e o HTML já renderizado ficam em cache com a mesma assinatura dos
 * dados: enquanto nenhum resultado/corrida/piloto mudar, a página sai direto do cache.
 * ?recalcular=1 força o recálculo.
 */
class CuriosidadesController extends Controller
{
    public function index(Request $request, CuriosidadesService $service)
    {
        $userId = Auth::user()->id;
        $forcar = $request->boolean('recalcular');
        // o HTML guarda URLs absolutas (asset/route), então o endereço de acesso também entra na chave
        $assinatura = $service->assinatura($userId).'_'.substr(md5(url('/')), 0, 8);
        $chave = 'curiosidades_html_v2_'.$userId.'_'.$assinatura;

        // dados mudaram: descarta o cache da versão anterior para não acumular arquivos
        $anterior = Cache::get('curiosidades_assinatura_'.$userId);
        if ($anterior && $anterior !== $assinatura) {
            Cache::forget('curiosidades_html_v2_'.$userId.'_'.$anterior);
            $service->esquecer($userId, $anterior);
        }
        Cache::forever('curiosidades_assinatura_'.$userId, $assinatura);

        if ($forcar) {
            Cache::forget($chave);
        }

        $conteudo = Cache::remember($chave, now()->addDays(15), function () use ($service, $userId, $forcar, $assinatura) {
            // mesmo critério da tela de estatísticas de pilotos/equipes para identificar o piloto do usuário
            $meuPiloto = Piloto::where('nome', 'Vinicius')->where('sobrenome', 'Melchior')->where('user_id', 3)->first(['id', 'nome', 'sobrenome']);
            $dados = $service->gerar($userId, $forcar, $assinatura);

            return view('site.curiosidades._conteudo', compact('dados', 'meuPiloto'))->render();
        });

        if ($forcar) {
            return redirect()->route('curiosidades.index');
        }

        return view('site.curiosidades.index', compact('conteudo'));
    }
}
