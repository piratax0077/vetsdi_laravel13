<script>
window.otrosTrabajadoresConfig={
    institucion:@json($institucion->id),csrf:@json(csrf_token()),
    categorias:@json($otrosTrabajadores['categorias']),
    lugares:@json($otrosTrabajadores['lugares']),
    tiposContrato:@json(\App\Services\TrabajadoresCentroVeterinario::TIPOS_CONTRATO),
    guardar:@json(route('vet.trabajadores.guardar')),categoria:@json(route('vet.trabajadores.categoria')),contrato:@json(route('vet.trabajadores.contrato')),
    detalle:@json(route('vet.trabajadores.detalle',['id'=>'__ID__'])),estado:@json(route('vet.trabajadores.estado',['id'=>'__ID__'])),mensaje:@json(route('vet.trabajadores.mensaje',['id'=>'__ID__'])),terminar:@json(route('vet.trabajadores.terminar',['id'=>'__ID__'])),
    tab:@json(($modoOtrosContratos ?? false)?'contratos_otros':'otros_trabajadores'),hoy:@json(now(config('operacion_veterinaria.timezone','America/Santiago'))->toDateString())
};
</script>
<script src="{{ asset('js/otros-trabajadores.js') }}?v={{ filemtime(public_path('js/otros-trabajadores.js')) }}"></script>
