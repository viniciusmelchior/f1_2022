<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
   
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
    <header class="main-header">
        <div>
            <nav class="navbar-main container">
                <div class="f1-logo-wrapper">
                   {{--  <a href="{{route('home')}}"><img src="{{asset('images/F1_logo_PNG1.png')}}" alt="logo-da-f1" class="f1-logo"></a> --}}
                   @php $teste = 'F1_logo_PNG1.png' @endphp
                    <a href="{{route('home')}}"><img src="{{asset('images/'.$teste)}}" alt="logo-da-f1" class="f1-logo"></a>
                </div>
                @if(Auth::guest())
                <ul>
                    <li><a href="#">Sobre o Projeto</a></li>
                    <li><a href="{{route('user.register')}}">Registrar-se</a></li>
                    <li><a href="{{route('login')}}">Login</a></li>
                </ul>
                @else 
                <ul>
                    <li>Usuário: {{Auth::user()->name}}</li>
                    <li><a href="{{route('dashboard')}}">Dashboard</a></li>
                    <li>
                        <form action="{{route('user.logout')}}" method="POST">
                            @csrf
                            <button type="submit" class="">Logout</button>
                        </form>
                    </li>
                </ul>
                @endif
            </nav>
        </div>
    </header>
    @yield('section')

    <footer>
        <p>Desenvolvido por &copy;Vinicius Melchior | {{date('Y')}} </p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
  </script>
</body>
</html>