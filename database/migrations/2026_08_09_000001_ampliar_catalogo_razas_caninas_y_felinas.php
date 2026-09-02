<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $correcciones = [
            'Canina' => [
                'Pastor Aleman' => 'Pastor Alemán',
                'Bulldog Frances' => 'Bulldog Francés',
            ],
            'Felina' => [
                'Siames' => 'Siamés',
                'Bengal' => 'Bengalí',
            ],
        ];

        $catalogos = [
            'Canina' => [
                'Affenpinscher', 'Akita Americano', 'Akita Inu', 'Alaskan Malamute', 'American Bully',
                'American Staffordshire Terrier', 'Australian Cattle Dog', 'Australian Kelpie',
                'Basenji', 'Basset Hound', 'Beagle', 'Bearded Collie', 'Bedlington Terrier',
                'Bernés de la Montaña', 'Bichón Frise', 'Bichón Habanero', 'Bloodhound',
                'Bobtail', 'Border Collie', 'Border Terrier', 'Borzoi', 'Boston Terrier',
                'Bóxer', 'Boyero de Flandes', 'Braco Alemán', 'Braco de Weimar', 'Bull Terrier',
                'Bull Terrier Miniatura', 'Bulldog Americano', 'Bulldog Francés', 'Bulldog Inglés',
                'Bullmastiff', 'Cairn Terrier', 'Cane Corso', 'Caniche', 'Carlino (Pug)',
                'Cavalier King Charles Spaniel', 'Chihuahua', 'Chow Chow', 'Cocker Spaniel Americano',
                'Cocker Spaniel Inglés', 'Collie de Pelo Largo', 'Collie de Pelo Corto',
                'Corgi Galés de Cardigan', 'Corgi Galés de Pembroke', 'Crestado Chino',
                'Dálmata', 'Dóberman', 'Dogo Argentino', 'Dogo de Burdeos', 'Fox Terrier',
                'Galgo Afgano', 'Galgo Español', 'Galgo Italiano', 'Golden Retriever',
                'Gran Danés', 'Grifón de Bruselas', 'Husky Siberiano', 'Jack Russell Terrier',
                'Keeshond', 'Komondor', 'Labrador Retriever', 'Lakeland Terrier', 'Lhasa Apso',
                'Maltés', 'Mastín Español', 'Mastín Inglés', 'Mastín Napolitano',
                'Mestizo', 'Norfolk Terrier', 'Norwich Terrier', 'Papillón', 'Pastor Alemán',
                'Pastor Australiano', 'Pastor Belga Groenendael', 'Pastor Belga Malinois',
                'Pastor Belga Tervueren', 'Pastor Blanco Suizo', 'Pastor de Shetland',
                'Pastor Inglés', 'Pastor Ovejero Australiano', 'Pequinés', 'Perro de Agua Español',
                'Perro de Montaña de los Pirineos', 'Pinscher Alemán', 'Pinscher Miniatura',
                'Pit Bull Terrier Americano', 'Podenco', 'Pomerania', 'Poodle', 'Poodle Toy',
                'Presa Canario', 'Rhodesian Ridgeback', 'Rottweiler', 'Samoyedo', 'San Bernardo',
                'Schnauzer Gigante', 'Schnauzer Mediano', 'Schnauzer Miniatura', 'Scottish Terrier',
                'Setter Inglés', 'Setter Irlandés', 'Shar Pei', 'Shiba Inu', 'Shih Tzu',
                'Staffordshire Bull Terrier', 'Teckel (Dachshund)', 'Terranova', 'Terrier Chileno',
                'Terrier Tibetano', 'Vizsla', 'West Highland White Terrier', 'Whippet',
                'Xoloitzcuintle', 'Yorkshire Terrier',
            ],
            'Felina' => [
                'Abisinio', 'American Curl', 'American Shorthair', 'Angora Turco', 'Azul Ruso',
                'Balinés', 'Bengalí', 'Birmano', 'Bobtail Americano', 'Bobtail Japonés',
                'Bombay', 'Bosque de Noruega', 'British Longhair', 'British Shorthair', 'Burmés',
                'Burmilla', 'Chartreux', 'Cornish Rex', 'Devon Rex', 'Don Sphynx', 'Europeo Común',
                'Exótico de Pelo Corto', 'Himalayo', 'Korat', 'LaPerm', 'Maine Coon', 'Manx',
                'Mau Egipcio', 'Mestizo', 'Munchkin', 'Nebelung', 'Ocicat', 'Oriental de Pelo Corto',
                'Persa', 'Peterbald', 'Ragdoll', 'Savannah', 'Scottish Fold', 'Selkirk Rex',
                'Siamés', 'Siberiano', 'Singapura', 'Snowshoe', 'Somalí', 'Sphynx',
                'Tonkinés', 'Toyger', 'Van Turco',
            ],
        ];

        $ahora = now();
        foreach ($correcciones as $especie => $nombres) {
            $especieId = DB::table('especies_mascotas')->where('nombre', $especie)->value('id');
            foreach ($nombres as $anterior => $correcto) {
                DB::table('razas_mascotas')
                    ->where('especie_id', $especieId)
                    ->where('nombre', $anterior)
                    ->update(['nombre' => $correcto, 'updated_at' => $ahora]);
            }
        }

        foreach ($catalogos as $especie => $razas) {
            $especieId = DB::table('especies_mascotas')->where('nombre', $especie)->value('id');
            if (!$especieId) {
                continue;
            }

            foreach (array_unique($razas) as $raza) {
                DB::table('razas_mascotas')->updateOrInsert(
                    ['especie_id' => $especieId, 'nombre' => $raza],
                    ['updated_at' => $ahora, 'created_at' => $ahora]
                );
            }
        }
    }

    public function down(): void
    {
        // El catálogo se conserva para no invalidar mascotas que ya referencien
        // alguna de estas razas al revertir otros cambios de la aplicación.
    }
};
