<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidacionRecibo extends Model
{
    use HasFactory;
    protected $table = 'liquidacion_recibo';

    // Descifra un dato de la cuenta; si no se puede (por ejemplo, fue guardado
    // con otra APP_KEY) devuelve vacío en vez de botar la pantalla.
    public static function descifrar_dato($valor)
    {
        if($valor === null || $valor === '')
            return '';

        try
        {
            return decrypt($valor);
        }
        catch (DecryptException $e)
        {
            return '';
        }
    }

    // Deja la cuenta lista para mostrar en el perfil: datos descifrados y su banco.
    public function preparar_para_perfil()
    {
        $id_banco = static::descifrar_dato($this->casa);
        $banco = $id_banco != '' ? Bancos::select('id', 'nombre')->where('id', $id_banco)->first() : null;

        $this->banco = $banco ?: ['id' => '', 'nombre' => 'Cuenta bancaria (datos no disponibles)'];
        $this->serie = static::descifrar_dato($this->serie);
        $this->autor = static::descifrar_dato($this->autor);
        $this->casa = $id_banco;
        $this->numero_control = static::descifrar_dato($this->numero_control);
        $this->email = static::descifrar_dato($this->email);
        $this->otro = static::descifrar_dato($this->otro);

        return $this;
    }
}
