@extends('layouts.main')

@section('section')
    <div class="container">
        <form method="POST" action="{{route('user.store')}}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name">
              </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Senha</label>
              <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3">
                <label for="password-confirmation" class="form-label">Confirmar Senha</label>
                <input type="password" class="form-control" id="password-confirmation" name="password-confirmation">
              </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
          </form>
    </div>
@endsection