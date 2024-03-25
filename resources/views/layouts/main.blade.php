<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

     <!-- Boxiocns CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
   
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
     <!-- CSRF Token -->
     
     <meta name="csrf-token" content="{{ csrf_token() }}">
     
    <title>F1 Stats</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
  
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
</head>
<body>
    <div class="sidebar close">
        <div class="logo-details">
          {{-- <i class='bx bxl-c-plus-plus'></i> --}}
          <div class="px-3"><img src="{{asset('images/F1_logo_PNG1.png')}}" alt="" style="width:50px; height:35px;"></div>
          <span class="logo_name">STATS</span>
        </div>
        <ul class="nav-links">
          <li>
            <a href="{{route("landingPage")}}">
              <i class='bx bx-grid-alt'></i>
              <span class="link_name">Inicio</span>
            </a>
            <ul class="sub-menu blank">
              <li><a class="link_name" href="{{route('landingPage')}}">Inicio</a></li>
            </ul>
          </li>
          <li>
            <a href="{{route('home')}}">
              <i class='bx bx-pie-chart-alt-2'></i>
              <span class="link_name">Estatisticas</span>
            </a>
            <ul class="sub-menu blank">
              <li><a class="link_name" href="{{route('home')}}">Estatisticas</a></li>
              <li><a class="link_name" href="{{route('tempos')}}">Análises de Voltas</a></li>
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                <i class='bx bx-world' ></i>
                <span class="link_name">Países</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Países</a></li>
              <li><a href="{{route('paises.index')}}">Visualizar</a></li>
              <li><a href="{{route('paises.create')}}">Cadastrar</a></li>
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                <i class='bx bxs-user-pin' ></i>
                <span class="link_name">Pilotos</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Pilotos</a></li>
              <li><a href="{{route('pilotos.index')}}">Visualizar</a></li>
              <li><a href="{{route('pilotos.create')}}">Cadastrar</a></li>
              <li><a href="{{route('pilotos.comparativo')}}">Comparativos</a></li>
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                {{-- <i class='bx bx-book-alt'></i> --}}
                <i class='bx bxs-car' ></i>
                <span class="link_name">Equipes</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Equipes</a></li>
              <li><a href="{{route('equipes.index')}}">Visualizar</a></li>
              <li><a href="{{route('equipes.create')}}">Cadastrar</a></li>
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                <i class='bx bx-plug'></i>
                <span class="link_name">Duplas</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Duplas</a></li>
              <li><a href="{{route('pilotoEquipe.index')}}">Visualizar</a></li>
              <li><a href="{{route('pilotoEquipe.create')}}">Cadastrar</a></li>
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                <i class='bx bxs-location-plus'></i>
                <span class="link_name">Eventos</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Eventos</a></li>
              <li><a href="{{route('eventos.index')}}">Eventos</a></li>
              <li><a href="{{route('pistas.index')}}">Pistas</a></li>
              {{-- <li><a href="{{route('pistas.create')}}">Cadastrar Pistas</a></li> --}}
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                <i class='bx bxs-sun'></i>
                <span class="link_name">Clima</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Clima</a></li>
              <li><a href="{{route('condicaoClimatica.index')}}">Visualizar</a></li>
              <li><a href="{{route('condicaoClimatica.create')}}">Cadastrar</a></li>
            </ul>
          </li>
          <li>
            <div class="iocn-link">
              <a href="#">
                <i class='bx bxs-calendar-plus'></i>
                <span class="link_name">Anos</span>
              </a>
              <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="#">Anos</a></li>
              <li><a href="{{route('anos.index')}}">Visualizar</a></li>
              <li><a href="{{route('anos.create')}}">Cadastrar</a></li>
            </ul>
          </li>
          {{-- <li>
            <a href="#">
              <i class='bx bx-line-chart'></i>
              <span class="link_name">Chart</span>
            </a>
            <ul class="sub-menu blank">
              <li><a class="link_name" href="#">Chart</a></li>
            </ul>
          </li> --}}
          <li>
            <div class="iocn-link">
              <a href="{{route('temporadas.index')}}">
                <i class='bx bx-table' ></i>
                <span class="link_name">Temporadas</span>
              </a>
              {{-- <i class='bx bxs-chevron-down arrow'></i> --}}
            </div>
            <ul class="sub-menu">
              <li><a class="link_name" href="{{route('temporadas.index')}}">Temporadas</a></li>
             {{--  <li><a href="#">UI Face</a></li>
              <li><a href="#">Pigments</a></li>
              <li><a href="#">Box Icons</a></li> --}}
            </ul>
          </li>
         {{--  <li>
            <a href="#">
              <i class='bx bx-compass'></i>
              <span class="link_name">Explore</span>
            </a>
            <ul class="sub-menu blank">
              <li><a class="link_name" href="#">Explore</a></li>
            </ul>
          </li> --}}
          {{-- <li>
            <a href="{{route('temporadas.index')}}">
                <i class='bx bx-table' ></i>
                <span class="link_name">Temporadas</span>
            </a>
            <ul class="sub-menu blank">
              <li><a class="link_name" href="">apagar</a></li>
            </ul>
          </li> --}}
          {{-- <li>
            <a href="#">
              <i class='bx bx-cog'></i>
              <span class="link_name">Setting</span>
            </a>
            <ul class="sub-menu blank">
              <li><a class="link_name" href="#">Setting</a></li>
            </ul>
          </li> --}}
          <li>
            <div class="profile-details">
              <div class="profile-content">
                <img src="{{asset('images/profile.jpg')}}" alt="profileImg">
              </div>
              <div class="name-job">
                <div class="profile_name">Usuário</div>
                <div class="job">Admin</div>
              </div>
              <a href="{{ route('user.logout') }}"
              onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
              <i class='bx bx-log-out'></i>
            </a>
            <form id="logout-form" action="{{ route('user.logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            </div>
          </li>
        </ul>
    </div>
      <section class="home-section">
        <div class="home-content">
          <i class='bx bx-menu'></i>
        </div>
        @yield('section')
    </section>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    let arrow = document.querySelectorAll(".arrow");
    for (var i = 0; i < arrow.length; i++) {
    arrow[i].addEventListener("click", (e)=>{
    let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
    arrowParent.classList.toggle("showMenu");
    });
    }

    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    console.log(sidebarBtn);
    sidebarBtn.addEventListener("click", ()=>{
    sidebar.classList.toggle("close");
    });
</script>
</body>
</html>