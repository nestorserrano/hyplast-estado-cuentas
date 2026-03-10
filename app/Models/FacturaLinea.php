<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaLinea extends SoftlandModel
{
    use HasFactory;

    protected $table = 'AMS.factura_linea'; // Nombre de tu tabla

    protected $fillable = [
        'factura', 'tipo_documento', 'linea', 'bodega', 'pedido', 'articulo',
        'anulada', 'fecha_factura', 'cantidad', 'precio_unitario', 'total_impuesto1',
        'total_impuesto2', 'desc_tot_linea', 'desc_tot_general', 'descuento_volumen',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura', 'factura');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo');
    }
}
