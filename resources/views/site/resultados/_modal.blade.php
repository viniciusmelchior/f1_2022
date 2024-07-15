  @php 
     use App\Models\Site\ImagensCorrida;

     $arquivos_corrida = ImagensCorrida::getImagensCorrida($corrida->id);
  @endphp

<style>
    .mySwiper{
        width: 100%;
        height: 100%;
    }
    swiper-container {
      width: 100%;
      height: 100%;
    }

    swiper-slide {
      text-align: center;
      font-size: 18px;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    swiper-slide img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .modal-dialog{
        max-width: 80%; /* Ajuste a largura desejada */
        margin: auto;
    }
</style>
  
  <!-- Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="width:100%;">
                {{-- <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Galeria de Fotos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div> --}}
                <div class="modal-body">
                    <swiper-container class="mySwiper">
                        @foreach($arquivos_corrida as $arquivo_corrida)
                            <swiper-slide><img src="{{ asset('images/'.$arquivo_corrida->imagem) }}" alt=""></swiper-slide>
                        @endforeach
                      </swiper-container>
                      <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>
                </div>
            </div>
        </div>
    </div>
