@extends('sitio.layout')

@section('title', 'Pago de promoción · VeterChile')
@section('content')
<section class="sw-checkout">
    <div class="sw-wrap sw-checkout-grid">
        <div class="sw-checkout-main">
            <nav class="sw-breadcrumb"><a href="{{ route('sitio.promocion.index') }}">Planes</a><span>›</span><span>Pago</span></nav>
            <p class="sw-kicker">Orden #{{ $campana->id }}</p>
            <h1>Finalizar contratación</h1>
            <div class="sw-test-payment">
                <span>MODO PRUEBA</span>
                <h2>Simular pago aprobado</h2>
                <p>Esta opción prueba el flujo completo sin conectarse a un banco, sin ingresar tarjeta y sin realizar un cobro real.</p>
                <form method="POST" action="{{ route('sitio.promocion.pago-simulado', $campana) }}">
                    @csrf
                    <label><input type="checkbox" name="acepta_simulacion" value="1" required> Comprendo que es una simulación sin valor comercial.</label>
                    <button class="sw-btn sw-btn-solid sw-btn-block" type="submit">Simular pago de ${{ number_format($campana->monto, 0, ',', '.') }}</button>
                </form>
            </div>
            <div class="sw-transfer-payment">
                <h2>Informar transferencia</h2>
                <p>Para pruebas administrativas puedes adjuntar un comprobante. Los datos bancarios reales deben configurarse antes de habilitar esta opción en producción.</p>
                <form method="POST" enctype="multipart/form-data" action="{{ route('sitio.promocion.informar-pago', $campana) }}">
                    @csrf
                    <label>Referencia u operación<input name="referencia_pago" required maxlength="100"></label>
                    <label>Comprobante<input type="file" name="comprobante" accept=".pdf,.jpg,.jpeg,.png" required></label>
                    <button class="sw-btn sw-btn-ghost sw-btn-block" type="submit">Enviar a revisión</button>
                </form>
            </div>
        </div>
        <aside class="sw-order-summary">
            <span>Resumen</span><h2>{{ $campana->nombre_plan }}</h2>
            <dl><div><dt>Duración</dt><dd>{{ $campana->duracion_dias }} días</dd></div><div><dt>Redes</dt><dd>{{ collect($campana->redes)->map(fn($r) => ucfirst($r))->join(', ') }}</dd></div><div><dt>Objetivo</dt><dd>{{ ucfirst($campana->objetivo) }}</dd></div></dl>
            <p><span>Total</span><strong>${{ number_format($campana->monto, 0, ',', '.') }} CLP</strong></p>
            <small>No se solicitarán ni almacenarán datos de tarjeta en el modo simulado.</small>
        </aside>
    </div>
</section>
@endsection
