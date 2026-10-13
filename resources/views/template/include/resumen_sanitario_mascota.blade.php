{{-- Control sanitario de la mascota en el resumen del menú lateral:
     última desparasitación y si el esquema de vacunas está al día --}}
@php
    $aFechaSanitaria = function ($valor) {
        try {
            return empty($valor) ? null : \Carbon\Carbon::parse($valor)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    };
    $aRegistrosSanitarios = function ($valor) {
        if (is_string($valor)) {
            $valor = json_decode($valor, true);
        }

        return collect(is_array($valor) ? $valor : [])->filter(function ($registro) {
            return is_array($registro);
        })->values();
    };
    $hoySanitario = \Carbon\Carbon::today();

    // Desparasitación: vale la fecha más nueva entre la ficha de la mascota y su carné
    $desparasitacionesMenu = $aRegistrosSanitarios(data_get($mascota, 'desparasitaciones_registro'))
        ->map(function ($registro) use ($aFechaSanitaria) {
            return [
                'fecha' => $aFechaSanitaria($registro['fecha_dosis'] ?? null),
                'producto' => trim((string) ($registro['antiparasitario'] ?? '')),
            ];
        })
        ->push([
            'fecha' => $aFechaSanitaria(data_get($mascota, 'ultima_desparasitacion')),
            'producto' => trim((string) data_get($mascota, 'producto_desparasitacion', '')),
        ])
        ->filter(function ($item) {
            return $item['fecha'] !== null;
        })
        ->sortByDesc(function ($item) {
            return $item['fecha']->timestamp;
        })
        ->values();

    $ultimaDesparasitacionMenu = data_get($desparasitacionesMenu->first(), 'fecha');
    $productoDesparasitacionMenu = '';
    if ($ultimaDesparasitacionMenu) {
        $productoDesparasitacionMenu = (string) $desparasitacionesMenu
            ->filter(function ($item) use ($ultimaDesparasitacionMenu) {
                return $item['fecha']->equalTo($ultimaDesparasitacionMenu) && $item['producto'] !== '';
            })
            ->pluck('producto')
            ->first();
    }

    // Vacunas: cada dosis del carné indica cuándo toca la siguiente
    $dosisVacunasMenu = $aRegistrosSanitarios(data_get($mascota, 'vacunas_registro'))
        ->map(function ($registro) use ($aFechaSanitaria) {
            return [
                'fecha' => $aFechaSanitaria($registro['fecha_dosis'] ?? null),
                'proxima' => $aFechaSanitaria($registro['proxima_dosis'] ?? null),
            ];
        })
        ->filter(function ($item) {
            return $item['fecha'] !== null;
        })
        ->values();

    // El detalle va corto porque el menú es angosto; la frase completa queda como ayuda al pasar el mouse
    $estadoVacunasMenu = 'sin-registro';
    $textoVacunasMenu = 'Sin registro';
    $detalleVacunasMenu = '';
    $ayudaVacunasMenu = 'Sin vacunas anotadas en el carné';

    if ($dosisVacunasMenu->isNotEmpty()) {
        // Una próxima dosis vencida deja de contar si después se puso otra dosis
        // (se acepta que se haya adelantado hasta 30 días)
        $vencidasMenu = $dosisVacunasMenu
            ->filter(function ($item) use ($dosisVacunasMenu, $hoySanitario) {
                if ($item['proxima'] === null || $item['proxima']->gte($hoySanitario)) {
                    return false;
                }

                $desde = $item['proxima']->copy()->subDays(30);

                return ! $dosisVacunasMenu->contains(function ($otra) use ($item, $desde) {
                    return $otra['fecha']->gt($item['fecha']) && $otra['fecha']->gte($desde);
                });
            })
            ->sortBy(function ($item) {
                return $item['proxima']->timestamp;
            })
            ->values();

        if ($vencidasMenu->isNotEmpty()) {
            $estadoVacunasMenu = 'atrasada';
            $textoVacunasMenu = 'Atrasadas';
            $detalleVacunasMenu = 'Refuerzo: '.$vencidasMenu->first()['proxima']->format('d-m-Y');
            $ayudaVacunasMenu = 'Tiene un refuerzo vencido desde el '.$vencidasMenu->first()['proxima']->format('d-m-Y');
        } else {
            $estadoVacunasMenu = 'al-dia';
            $textoVacunasMenu = 'Al día';
            $proximaMenu = $dosisVacunasMenu
                ->filter(function ($item) use ($hoySanitario) {
                    return $item['proxima'] !== null && $item['proxima']->gte($hoySanitario);
                })
                ->sortBy(function ($item) {
                    return $item['proxima']->timestamp;
                })
                ->first();
            $detalleVacunasMenu = $proximaMenu ? 'Refuerzo: '.$proximaMenu['proxima']->format('d-m-Y') : '';
            $ayudaVacunasMenu = $proximaMenu
                ? 'Esquema al día. Próxima dosis el '.$proximaMenu['proxima']->format('d-m-Y')
                : 'Esquema al día. Sin próxima dosis anotada';
        }
    }
@endphp
<div class="ficha-resumen-dato">
    <dt>Última desparasitación</dt>
    <dd id="resumen_mascota_desparasitacion">
        <span class="ficha-resumen-valor">{{ $ultimaDesparasitacionMenu ? $ultimaDesparasitacionMenu->format('d-m-Y') : 'Sin registro' }}</span>
        <small class="ficha-resumen-detalle" title="{{ $productoDesparasitacionMenu }}">{{ $productoDesparasitacionMenu }}</small>
    </dd>
</div>
<div class="ficha-resumen-dato">
    <dt>Vacunas</dt>
    <dd id="resumen_mascota_vacunas" title="{{ $ayudaVacunasMenu }}">
        <span class="ficha-resumen-estado ficha-resumen-estado-{{ $estadoVacunasMenu }}">{{ $textoVacunasMenu }}</span>
        <small class="ficha-resumen-detalle">{{ $detalleVacunasMenu }}</small>
    </dd>
</div>

@once
<script>
    // Refresca el control sanitario del menú cuando se anotan vacunas o desparasitaciones
    // durante la atención. Usa la misma regla que al cargar la ficha.
    function actualizarResumenSanitarioMascota(vacunas, desparasitaciones) {
        function aIso(valor) {
            var texto = valor ? String(valor).slice(0, 10) : '';
            return /^\d{4}-\d{2}-\d{2}$/.test(texto) ? texto : '';
        }

        function aTexto(iso) {
            var partes = iso.split('-');
            return partes[2] + '-' + partes[1] + '-' + partes[0];
        }

        function restarDias(iso, dias) {
            var partes = iso.split('-');
            var fecha = new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2]) - dias);
            var mes = String(fecha.getMonth() + 1);
            var dia = String(fecha.getDate());
            return fecha.getFullYear() + '-' + (mes.length < 2 ? '0' + mes : mes) + '-' + (dia.length < 2 ? '0' + dia : dia);
        }

        function pintar(caja, selector, texto) {
            var elemento = caja ? caja.querySelector(selector) : null;
            if (elemento) {
                elemento.textContent = texto;
            }
            return elemento;
        }

        var ahora = new Date();
        var hoy = restarDias(ahora.getFullYear() + '-' + (ahora.getMonth() + 1) + '-' + ahora.getDate(), 0);

        if (Array.isArray(desparasitaciones)) {
            var cajaDesparasitacion = document.getElementById('resumen_mascota_desparasitacion');
            var actual = cajaDesparasitacion ? cajaDesparasitacion.querySelector('.ficha-resumen-valor') : null;
            var partesActual = actual ? actual.textContent.trim().split('-') : [];
            var ultima = partesActual.length === 3 ? aIso(partesActual[2] + '-' + partesActual[1] + '-' + partesActual[0]) : '';
            var producto = null;

            desparasitaciones.forEach(function (registro) {
                var fecha = aIso(registro && registro.fecha_dosis);
                if (fecha && fecha >= ultima) {
                    ultima = fecha;
                    producto = (registro.antiparasitario || '').trim();
                }
            });

            if (ultima) {
                pintar(cajaDesparasitacion, '.ficha-resumen-valor', aTexto(ultima));
                if (producto !== null) {
                    var detalleProducto = pintar(cajaDesparasitacion, '.ficha-resumen-detalle', producto);
                    if (detalleProducto) {
                        detalleProducto.title = producto;
                    }
                }
            }
        }

        if (Array.isArray(vacunas)) {
            var dosis = [];
            vacunas.forEach(function (registro) {
                var fecha = aIso(registro && registro.fecha_dosis);
                if (fecha) {
                    dosis.push({ fecha: fecha, proxima: aIso(registro.proxima_dosis) });
                }
            });

            var estado = 'sin-registro';
            var texto = 'Sin registro';
            var detalle = '';
            var ayuda = 'Sin vacunas anotadas en el carné';

            if (dosis.length) {
                var vencidas = dosis.filter(function (item) {
                    if (!item.proxima || item.proxima >= hoy) {
                        return false;
                    }
                    var desde = restarDias(item.proxima, 30);
                    return !dosis.some(function (otra) {
                        return otra.fecha > item.fecha && otra.fecha >= desde;
                    });
                }).map(function (item) {
                    return item.proxima;
                }).sort();

                if (vencidas.length) {
                    estado = 'atrasada';
                    texto = 'Atrasadas';
                    detalle = 'Refuerzo: ' + aTexto(vencidas[0]);
                    ayuda = 'Tiene un refuerzo vencido desde el ' + aTexto(vencidas[0]);
                } else {
                    var proximas = dosis.filter(function (item) {
                        return item.proxima && item.proxima >= hoy;
                    }).map(function (item) {
                        return item.proxima;
                    }).sort();

                    estado = 'al-dia';
                    texto = 'Al día';
                    detalle = proximas.length ? 'Refuerzo: ' + aTexto(proximas[0]) : '';
                    ayuda = proximas.length
                        ? 'Esquema al día. Próxima dosis el ' + aTexto(proximas[0])
                        : 'Esquema al día. Sin próxima dosis anotada';
                }
            }

            var cajaVacunas = document.getElementById('resumen_mascota_vacunas');
            var etiqueta = pintar(cajaVacunas, '.ficha-resumen-estado', texto);
            if (etiqueta) {
                etiqueta.className = 'ficha-resumen-estado ficha-resumen-estado-' + estado;
            }
            pintar(cajaVacunas, '.ficha-resumen-detalle', detalle);
            if (cajaVacunas) {
                cajaVacunas.title = ayuda;
            }
        }
    }
</script>
@endonce
