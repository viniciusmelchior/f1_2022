@extends('layouts.main')

@section('section')
    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-3 mb-3">

        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Listagem de Usuários</li>
            </ol>
        </nav>

        <div class="card mt-3 mb-3 p-3">
            <div class="card-body">
            <label for="caixaBusca">Pesquisar</label>
            <input type="text" id="caixaBusca">
            </div>
        </div>

        @if(count($usuarios) > 0)
            <table class="custom-responsive-table" id="tabelaUsuarios">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'tabelaUsuarios')" style="cursor: pointer; text-align: center;">#</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th style="text-align: center;">Cadastro</th>
                        <th onclick="sortTable(4, 'tabelaUsuarios')" style="cursor: pointer; text-align: center;">Corridas</th>
                        <th onclick="sortTable(5, 'tabelaUsuarios')" style="cursor: pointer; text-align: center;">Resultados</th>
                        <th style="text-align: center;">Perfil</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $key => $usuario)
                        <tr>
                            <td data-label="#">{{$key+1}}</td>
                            <td data-label="Nome" style="vertical-align: middle; text-align:left;">{{$usuario->name}}</td>
                            <td data-label="E-mail" style="text-align:left;">{{$usuario->email}}</td>
                            <td data-label="Cadastro">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                            <td data-label="Corridas">{{$usuario->corridas_count}}</td>
                            <td data-label="Resultados">{{$usuario->resultados_count}}</td>
                            <td data-label="Perfil">{{ $usuario->is_admin ? 'Admin' : 'Usuário' }}</td>
                            <td data-label="Ações">
                                @if (! $usuario->is_admin)
                                    <form method="POST" action="{{ route('usuarios.entrarComo', [$usuario->id]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-in-right"></i> Entrar como</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum usuário cadastrado</p>
        @endif
    </div>

<script src="{{asset('js/app.js')}}"></script>
<script>
    document.getElementById('caixaBusca').addEventListener('keyup', function () {
        const keyword = this.value.toUpperCase();

        document.querySelectorAll('#tabelaUsuarios tbody tr').forEach(function (linha) {
            linha.style.display = linha.textContent.toUpperCase().indexOf(keyword) > -1 ? '' : 'none';
        });
    });
</script>

@endsection
