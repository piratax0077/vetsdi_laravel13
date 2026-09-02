<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación del consentimiento veterinario</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; padding: 32px 16px; color: #263445; background: #eef5f5; font-family: Arial, sans-serif; }
        .card { max-width: 680px; margin: 0 auto; overflow: hidden; background: #fff; border-radius: 16px; box-shadow: 0 14px 40px rgba(32, 61, 74, .14); }
        .header { padding: 24px 28px; color: #fff; background: #168c83; }
        .brand { font-size: 25px; font-weight: 700; }
        .header p { margin: 5px 0 0; opacity: .9; }
        .body { padding: 28px; }
        .ok { margin-bottom: 22px; padding: 14px; color: #146b49; background: #e5f7ee; border: 1px solid #aadfc7; border-radius: 10px; font-weight: 700; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .item { padding: 13px; background: #f7fafb; border: 1px solid #d9e2e8; border-radius: 9px; }
        .label { color: #657789; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .value { margin-top: 4px; font-weight: 700; }
        .token { margin-top: 20px; color: #657789; font-size: 11px; overflow-wrap: anywhere; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 16px; color: #fff; background: #65328c; border-radius: 8px; text-decoration: none; font-weight: 700; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="card">
        <header class="header">
            <div class="brand">VET SDI</div>
            <p>Validación de consentimiento informado veterinario</p>
        </header>
        <section class="body">
            <div class="ok">Firma del tutor válida. Consentimiento firmado y autorizado.</div>
            <div class="grid">
                <div class="item">
                    <div class="label">Tutor responsable</div>
                    <div class="value">{{ $firmaTutor['nombre'] }}</div>
                    <div>RUT: {{ $firmaTutor['rut'] ?: 'No informado' }}</div>
                </div>
                <div class="item">
                    <div class="label">Fecha de firma</div>
                    <div class="value">{{ $firmaTutor['fecha'] }}</div>
                </div>
                <div class="item">
                    <div class="label">Mascota</div>
                    <div class="value">{{ optional($mascota)->nombre ?: 'Sin registro' }}</div>
                    <div>Ficha N° {{ optional($ficha)->id }}</div>
                </div>
                <div class="item">
                    <div class="label">Profesional responsable</div>
                    <div class="value">{{ trim(optional($profesional)->nombre.' '.optional($profesional)->apellido_uno.' '.optional($profesional)->apellido_dos) }}</div>
                </div>
            </div>
            <div class="token">Identificador de firma: {{ $firmaTutor['token'] }}</div>
            <a class="btn" href="{{ asset($documento->url) }}" target="_blank">Ver consentimiento firmado</a>
        </section>
    </main>
</body>
</html>
