<?php

namespace App\Services;

class CatalogoRadiologicoVeterinario
{
    public const EXAMENES = [
        'Canina' => ['Tórax', 'Abdomen', 'Pelvis y caderas', 'Columna cervical', 'Columna torácica', 'Columna lumbar y lumbosacra', 'Miembro anterior', 'Miembro posterior', 'Rodilla', 'Codo', 'Cráneo', 'Estudio de displasia de cadera'],
        'Felina' => ['Tórax', 'Abdomen', 'Pelvis y caderas', 'Columna vertebral', 'Miembro anterior', 'Miembro posterior', 'Cráneo'],
        'Equina' => ['Casco', 'Cuartilla', 'Menudillo', 'Carpo', 'Tarso', 'Rodilla', 'Columna cervical', 'Cráneo y senos paranasales'],
        'Aviar' => ['Cuerpo completo', 'Tórax y abdomen', 'Alas', 'Miembros pélvicos'],
        'Conejo' => ['Cráneo y dentición', 'Tórax', 'Abdomen', 'Columna vertebral', 'Extremidades'],
        'Roedores' => ['Cuerpo completo', 'Cráneo y dentición', 'Extremidades'],
        'Reptiles' => ['Cuerpo completo', 'Cráneo', 'Columna vertebral', 'Extremidades'],
    ];
}
