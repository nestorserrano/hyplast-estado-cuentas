<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends SoftlandModel
{
    protected $table = 'CLIENTE';
    protected $primaryKey = 'CLIENTE';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'CLIENTE',
        'NOMBRE',
        'CONTACTO',
        'TELEFONO1',
        'CATEGORIA_CLIENTE',
        'E_MAIL',
        'BALANCE',
        'ACTIVO',
    ];

    /**
     * Obtener documentos del cliente
     */
    public function documents()
    {
        return $this->hasMany(DocumentoCC::class, 'CLIENTE', 'CLIENTE');
    }
}
