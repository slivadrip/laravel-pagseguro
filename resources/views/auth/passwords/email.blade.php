@extends('store.layouts.main')

@section('content')
<div class="container text-center">
    <h1 class="title">Recuperar Senha?</h1>


    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif

    <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
        {{ csrf_field() }}

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <label for="email">E-Mail</label>

            <input id="email" type="email" class="form-control" placeholder="E-mail" name="email" value="{{ old('email') }}" required>

            @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                Enviar Link de Recuperação de Senha
            </button>
        </div>
    </form>
</div>
@endsection
