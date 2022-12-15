@extends('layouts.main')

@section('section')
    <div class="sidebar">
        <h1>Painel</h1>
        <a href="{{route('home')}}">Home</a>
        <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Países
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('paises.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('paises.create')}}">Cadastrar</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Pilotos
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('pilotos.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('pilotos.create')}}">Cadastrar</a>
              <a class="dropdown-item" href="{{route('pilotos.comparativo')}}">Comparativos</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Equipes
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('equipes.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('equipes.create')}}">Cadastrar</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Pilotos & Equipes
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('pilotoEquipe.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('pilotoEquipe.create')}}">Cadastrar</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Pistas
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('pistas.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('pistas.create')}}">Cadastrar</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Condições Climáticas
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('condicaoClimatica.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('condicaoClimatica.create')}}">Cadastrar</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             Anos
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('anos.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('anos.create')}}">Cadastrar</a>
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Temporadas
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{route('temporadas.index')}}">Visualizar</a>
              <a class="dropdown-item" href="{{route('temporadas.create')}}">Cadastrar</a>
              {{-- <a class="dropdown-item" href="{{route('temporadas.create')}}">Cadastrar Corridas</a> --}}
            </div>
          </div>

          <div class="dropdown">
            <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Cursos
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="http://127.0.0.1/moodle/server/moodle/admin/search.php#linkreports">Acessar</a>
            </div>
          </div>

          <a href="{{route('home')}}">Voltar</a>
    </div>
@endsection