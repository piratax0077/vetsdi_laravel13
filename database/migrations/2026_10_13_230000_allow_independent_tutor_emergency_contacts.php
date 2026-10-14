<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('contactos_emergencia'))return;
        $indexes=Schema::getIndexes('contactos_emergencia');
        foreach($indexes as $index){
            if($index['unique'] && !$index['primary'] && $index['columns']===['email']){
                Schema::table('contactos_emergencia',fn(Blueprint $t)=>$t->dropUnique($index['name']));
            }
        }
        if(!collect(Schema::getIndexes('contactos_emergencia'))->contains(fn($i)=>$i['columns']===['email'])){
            Schema::table('contactos_emergencia',fn(Blueprint $t)=>$t->index('email','contactos_emergencia_email_index'));
        }
    }

    public function down(): void
    {
        if(!Schema::hasTable('contactos_emergencia'))return;
        if(DB::table('contactos_emergencia')->whereNotNull('email')->groupBy('email')->havingRaw('COUNT(*) > 1')->exists()){
            throw new RuntimeException('Hay contactos independientes con el mismo correo. No se puede restaurar la restricción sin revisar esos contactos.');
        }
        foreach(Schema::getIndexes('contactos_emergencia') as $index){
            if(!$index['unique'] && $index['columns']===['email'])Schema::table('contactos_emergencia',fn(Blueprint $t)=>$t->dropIndex($index['name']));
        }
        Schema::table('contactos_emergencia',fn(Blueprint $t)=>$t->unique('email','contactos_emergencia_email_unique'));
    }
};
