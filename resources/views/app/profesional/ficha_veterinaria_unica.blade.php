@extends('template.profesional.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="row">
            <div class="col-md-12 mb-2">
                <h5 class="f-26 d-inline">Ficha Veterinaria Única</h5>
            </div>
        </div>

        @include('general.secciones_ficha.fmu')
    </div>
</div>
@endsection
