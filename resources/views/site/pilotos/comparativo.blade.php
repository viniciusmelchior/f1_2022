@extends('layouts.main')

<style>
   .container-comparativos {
    /*   display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center; */
   }

   .div_tabela{
      /* width: 100%; */
   }

   .tabela_comparativos{
      text-align: center;
      font-size: 1.6rem;
      font-weight: 100;
      margin-bottom: 10%;
      margin-top: 10%;
      display: none;
      /* padding-left: 50px;
      padding-right: 50px; */
   }

   .tabela_comparativos tbody tr{
      border-bottom: 1px solid black;
   }

   .tabela_comparativos tbody tr td{
      padding: 0.5rem;
   }

   .desc_piloto{
      text-transform: uppercase;
      font-size: 1.2rem;
   }

   .desc_comparativo {
      text-transform: uppercase;
      text-align: left;
   }

   #piloto1-imagem{
      width: 256px;
      height: 256px;
   }

   #piloto2-imagem{
      width: 256px;
      height: 256px;
   }

   #tabela-pilotos {
     /* height: 110px;
     overflow: auto; */
   }

   #form {
      /* height: 100vh;
      overflow: auto; */
   }

</style>

@section('section')
<div class="d-flex container">
   {{--escolha da temporada--}}
   <div id="form" style="width: 40%;">
      <div>
         <form method="POST" action="" class="mt-3 mb-3" id="form-comparativos" {{-- style="background-color: blue;" --}}>
            <div class="mb-3">
               <select name="temporada_id" id="temporada_id" class="form-control">
                        <option value="">Selecionar Temporada</option>
                   @foreach($temporadas as $temporada)
                       <option value="{{$temporada->id}}">{{$temporada->des_temporada}}</option>
                   @endforeach
               </select>
           </div>

            <table class="table text-center" style="width: 80%;" id="tabela-pilotos">
            <thead>
               <tr>
                   <th>#</th>
                   <th>Piloto</th>
                   <th>Ações</th>
               </tr>
            </thead>
               <tbody id="tabela-pilotos-tbody">
                  <tr>
                     <td colspan="3">
                       Selecione uma Temporada
                     </td>
                  </tr>
               </tbody>
         </table>
         <button type="submit" class="btn btn-primary">Comparar</button>
         <a href="" class="btn btn-secondary ml-3">Voltar</a>
         </form>
      </div>
   </div>
   <div id="tabela" {{-- style="background-color: green; width:60%;" --}}>
      <div class="div_tabela">
         <Table class="tabela_comparativos">
            <thead>
               <tr>
                  <th></th>
                  <th>
                     <div>
                        <img src="{{-- {{asset('images/'.$dadosPiloto1[0]->imagem)}} --}}" alt="" id="piloto1-imagem">
                     </div>
                     <span class="desc_piloto" id="piloto1-desc">{{-- {{$dadosPiloto1[0]->nome}} {{$dadosPiloto1[0]->sobrenome}} --}}</span>
                  </th>
                  <th>
                     <div>
                        <img src="{{-- {{asset('images/'.$dadosPiloto2[0]->imagem)}} --}}" alt="" id="piloto2-imagem">
                     </div>
                     <span class="desc_piloto" id="piloto2-desc">{{-- {{$dadosPiloto2[0]->nome}} {{$dadosPiloto2[0]->sobrenome}} --}}</span>
                  </th>
               </tr>
               <tbody>
                  <tr>
                     <td class="desc_comparativo">Pontos</td>
                     <td id="piloto1TotPontos"></td>
                     <td id="piloto2TotPontos"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Vitorias</td>
                     <td id="piloto1TotVitorias"></td>
                     <td id="piloto2TotVitorias"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Poles</td>
                     <td id="piloto1TotPolePositions"></td>
                     <td id="piloto2TotPolePositions"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Corrida</td>
                     <td id="piloto1Chegada"></td>
                     <td id="piloto2Chegada"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Treino</td>
                     <td id="piloto1Largada"></td>
                     <td id="piloto2Largada"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Pódios</td>
                     <td id="piloto1TotPodios"></td>
                     <td id="piloto2TotPodios"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Abandonos</td>
                     <td id="piloto1TotAbandonos"></td>
                     <td id="piloto2TotAbandonos"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Voltas Rápidas</td>
                     <td id="piloto1TotVoltasRapidas"></td>
                     <td id="piloto2TotVoltasRapidas"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Melhor Chegada</td>
                     <td id="piloto1MelhorChegada"></td>
                     <td id="piloto2MelhorChegada"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Pior Chegada</td>
                     <td id="piloto1PiorChegada"></td>
                     <td id="piloto2PiorChegada"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Melhor Grid</td>
                     <td id="piloto1MelhorLargada"></td>
                     <td id="piloto2MelhorLargada"></td>
                  </tr>
                  <tr>
                     <td class="desc_comparativo">Pior Grid</td>
                     <td id="piloto1PiorLargada"></td>
                     <td id="piloto2PiorLargada"></td>
                  </tr>
               </tbody>
            </thead>
         </table>
      </div>
   </div>
</div>
   <script>
      urlcomparativos = "<?=route('ajax.comparativos')?>"
      urlPilotosPorTemporada = "<?=route('ajax.getPilotosPorTemporada')?>"
      
   //   $(document).ready(function () {
   //    var limit = 2;
   //    $('input[type=checkbox]').on('change', function (e) {
   //       if ($('input[type=checkbox]:checked').length > limit) {
   //          $(this).prop('checked', false);
   //          alert("Escolher apenas 2 pilotos");
   //       }
   //    });
   //   });

      $('#form-comparativos').submit(function (e) { 
         e.preventDefault();
         pilotosId = [];

         temporada_id = $('#temporada_id').val();

         //Alteração Dinamica
         tabela_comparativos = $('.tabela_comparativos');
         piloto1_desc = $('#piloto1-desc');
         piloto2_desc = $('#piloto2-desc');
         piloto1_imagem = $('#piloto1-imagem');
         piloto2_imagem = $('#piloto2-imagem');

         piloto1TotPontos = $('#piloto1TotPontos');
         piloto2TotPontos = $('#piloto2TotPontos');

         piloto1TotVitorias = $('#piloto1TotVitorias');
         piloto2TotVitorias = $('#piloto2TotVitorias');

         piloto1TotPolePositions = $('#piloto1TotPolePositions');
         piloto2TotPolePositions = $('#piloto2TotPolePositions');

         piloto1Chegada = $('#piloto1Chegada');
         piloto2Chegada = $('#piloto2Chegada');

         piloto1Largada = $('#piloto1Largada');
         piloto2Largada = $('#piloto2Largada');

         piloto1TotPodios = $('#piloto1TotPodios');
         piloto2TotPodios = $('#piloto2TotPodios');

         piloto1TotAbandonos = $('#piloto1TotAbandonos');
         piloto2TotAbandonos = $('#piloto2TotAbandonos');

         piloto1TotVoltasRapidas = $('#piloto1TotVoltasRapidas');
         piloto2TotVoltasRapidas = $('#piloto2TotVoltasRapidas');

         piloto1MelhorChegada = $('#piloto1MelhorChegada');
         piloto2MelhorChegada = $('#piloto2MelhorChegada');

         piloto1PiorChegada = $('#piloto1PiorChegada');
         piloto2PiorChegada = $('#piloto2PiorChegada');

         piloto1MelhorLargada = $('#piloto1MelhorLargada');
         piloto2MelhorLargada = $('#piloto2MelhorLargada');

         piloto1PiorLargada = $('#piloto1PiorLargada');
         piloto2PiorLargada = $('#piloto2PiorLargada');
         
         $('input.piloto_id:checkbox:checked').each(function () {
            pilotosId.push($(this).val());
         });

         $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
         });

         $.ajax({
            type: "POST",
            url: urlcomparativos,
            data: {
               pilotosId: pilotosId,
               temporada_id: temporada_id
            },
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
               console.log(response);
               tabela_comparativos.css('display','block');

               piloto1_desc.text(`${response.dadosPiloto1[0]['nome']} ${response.dadosPiloto1[0]['sobrenome']}`)
               piloto2_desc.text(`${response.dadosPiloto2[0]['nome']} ${response.dadosPiloto2[0]['sobrenome']}`)
               
               // piloto1_imagem.attr("src", `http://127.0.0.1:8000/images/${response.dadosPiloto1[0]['imagem']}`)
               // piloto2_imagem.attr("src", `http://127.0.0.1:8000/images/${response.dadosPiloto2[0]['imagem']}`)
               piloto1_imagem.attr("src", `https://f1.vitorvasconcellos.com.br/images/${response.dadosPiloto1[0]['imagem']}`)
               piloto2_imagem.attr("src", `https://f1.vitorvasconcellos.com.br/images/${response.dadosPiloto2[0]['imagem']}`)

               piloto1TotPontos.text(response.piloto1TotPontos)
               piloto2TotPontos.text(response.piloto2TotPontos)

               piloto1TotVitorias.text(response.piloto1TotVitorias);
               piloto2TotVitorias.text(response.piloto2TotVitorias);

               piloto1TotPolePositions.text(response.piloto1TotPolePositions)
               piloto2TotPolePositions.text(response.piloto2TotPolePositions)

               piloto1Chegada.text(response.piloto1Chegada)
               piloto2Chegada.text(response.piloto2Chegada)

               piloto1Largada.text(response.piloto1Largada)
               piloto2Largada.text(response.piloto2Largada)

               piloto1TotPodios.text(response.piloto1TotPodios)
               piloto2TotPodios.text(response.piloto2TotPodios)

               piloto1TotAbandonos.text(response.piloto1TotAbandonos)
               piloto2TotAbandonos.text(response.piloto2TotAbandonos)

               piloto1TotVoltasRapidas.text(response.piloto1TotVoltasRapidas)
               piloto2TotVoltasRapidas.text(response.piloto2TotVoltasRapidas)

               piloto1MelhorChegada.text(response.piloto1MelhorChegada);
               piloto2MelhorChegada.text(response.piloto2MelhorChegada);

               piloto1PiorChegada.text(response.piloto1PiorChegada);
               piloto2PiorChegada.text(response.piloto2PiorChegada);

               piloto1MelhorLargada.text(response.piloto1MelhorLargada);
               piloto2MelhorLargada.text(response.piloto2MelhorLargada);

               piloto1PiorLargada.text(response.piloto1PiorLargada);
               piloto2PiorLargada.text(response.piloto2PiorLargada);
            }
         });
      });

      /*Listando pilotos por temporada*/

      $('#temporada_id').change(function (e) { 
         e.preventDefault();
         
         //limpa a tabela
         tabela_pilotos = $('#tabela-pilotos');
         // tabela_pilotos.html('');
         temporada_id = $('#temporada_id').val();
         tbody = $('#tabela-pilotos-tbody');

         if(temporada_id == ""){
            tbody.html('<tr><td colspan="3">Selecione uma Temporada</td></tr>')
         }

         $.ajaxSetup({
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
         });

         $.ajax({
            type: "POST",
            url: urlPilotosPorTemporada,
            data: {
               temporada_id:temporada_id
            },
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            success: function (response) {
               if(response.pilotos.length > 0){
                        contPilotos = 1;
                        tbody.html('');
                        response.pilotos.map(function(response){ 
                        tbody.append("<tr><td>"+contPilotos+"</td><td>"+response.nome+" "+response.sobrenome+"</td><td><input type='checkbox' class='piloto_id single-checkbox' name='piloto_id[]' id='piloto_id' style='width:30px; height:30px;' value='"+response.id+"'></td></tr>");
                        contPilotos++
                        })
                    } else {
                       /*  tabelaClassificacaoPilotos.append('<tr><th>Posição</th><th>Piloto</th><th>Pontos</th></tr>')
                        tabelaClassificacaoPilotos.append("<tr><td colspan='3'>Sem Dados Cadastrados</td></tr>"); */
                    }  
            
            /*função que limita a escolha de checkbox*/
            var limit = 2;
            $('input[type=checkbox]').on('change', function (e) {
               if ($('input[type=checkbox]:checked').length > limit) {
                  $(this).prop('checked', false);
                  alert("Escolher apenas 2 pilotos");
               }
            }); 
            }
         });
      });
   </script>
@endsection