@php $impersonation = \App\Support\Impersonation::current(request()); @endphp
@if ($impersonation)
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between mb-0 rounded-0" role="alert" id="impersonation-banner">
        <span>
            Você está vendo como <strong>{{ auth()->user()->name }}</strong> ·
            <form method="POST" action="{{ route('usuarios.voltar') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-dark">Voltar para minha conta</button>
            </form>
        </span>
        <small>
            Somente leitura.
            @if (session('impersonation_blocked'))
                {{ session('impersonation_blocked') }}
            @endif
        </small>
    </div>
@endif
