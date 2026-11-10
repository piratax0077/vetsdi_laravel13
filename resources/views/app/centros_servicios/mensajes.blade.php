@if(session('mensaje'))<div class="alert alert-success" role="status">{{ session('mensaje') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
