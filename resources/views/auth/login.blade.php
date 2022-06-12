@extends('layouts.auth')

@section('content')

<div class="kt-grid__item kt-grid__item--fluid  kt-grid__item--order-tablet-and-mobile-1  kt-login__wrapper">

    <!--begin::Head-->
    <div class="kt-login__head">
        <span class="kt-login__signup-label">Don't have an account yet?</span>&nbsp;&nbsp;
        <a href="#" class="kt-link kt-login__signup-link">Sign Up!</a>
    </div>

    <!--end::Head-->

    <!--begin::Body-->
    <div class="kt-login__body">

        <!--begin::Signin-->
        <div class="kt-login__form">
            <div class="kt-login__title">
                <h3>Sign In</h3>
            </div>

            <!--begin::Form-->
            {{-- <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group has-feedback ">
                    <input type="username" name="email" class="form-control" value="" placeholder="Nombre de usuario">
                    <span class="fas fa-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback ">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña">
                    <span class="fas fa-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <a href="{{ route('password.request') }}" class="text-center">Olvidé mi contraseña</a>
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit" style="border-radius: 7px;" class="btn btn-primary btn-block btn-flat">Entrar</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form> --}}
            {{-- <form class="kt-form" action="{{ route('login') }}" novalidate="novalidate" id="kt_login_form">
                @csrf --}}
                @if(count($errors) > 0)
                <div class="alert alert-danger" role "alert">
                    <h4>Por favor corrige los siguientes errores:</h4>
                        <ul>
                            @foreach($errors-> all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach	
                        </ul>	
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                <div class="form-group">
                    <input class="form-control" type="text" placeholder="email" name="email" autocomplete="off">
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" placeholder="Password" name="password" autocomplete="off">
                </div>

                <!--begin::Action-->
                <div class="kt-login__actions">
                    <a href="#" class="kt-link kt-login__link-forgot">
                        Forgot Password ?
                    </a>
                    <button {{-- id="kt_login_signin_submit"  --}}class="btn btn-primary btn-elevate kt-login__btn-primary">Sign In</button>
                </div>

                <!--end::Action-->
            </form>

            <!--end::Form-->

            <!--begin::Divider-->
            <div class="kt-login__divider">
                <div class="kt-divider">
                    <span></span>
                    <span>OR</span>
                    <span></span>
                </div>
            </div>

            <!--end::Divider-->

            <!--begin::Options-->
            <div class="kt-login__options">
                <a href="#" class="btn btn-primary kt-btn">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </a>
                <a href="#" class="btn btn-info kt-btn">
                    <i class="fab fa-twitter"></i>
                    Twitter
                </a>
                <a href="#" class="btn btn-danger kt-btn">
                    <i class="fab fa-google"></i>
                    Google
                </a>
            </div>

            <!--end::Options-->
        </div>

        <!--end::Signin-->
    </div>

    <!--end::Body-->
</div>
@endsection
