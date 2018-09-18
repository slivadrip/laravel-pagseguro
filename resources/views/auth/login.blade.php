@extends('store.layouts.main')

@section('content')
<div class="container text-center">
<div class="col-md-offset-3 col-md-6">


    <h1 class="title">Login</h1>

    <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
        {{ csrf_field() }}

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <label for="email">E-Mail</label>

            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="E-mail">

            @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
            @endif
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <label for="password">Senha</label>

            <input id="password" type="password" class="form-control" name="password" required placeholder="Senha:">

            @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary botao-login">
                Login
            </button>

       
        </div>
        <div class="form-group">
        <a class="btn btn-link" href="{{ route('password.request') }}">
                Recuperar a Senha?
            </a>
            | 
            <a class="btn btn-link" href="{{ route('register') }}">
                Cadastre-se!
            </a>
        </div>
    </form>
</div>

</div>
@endsection
