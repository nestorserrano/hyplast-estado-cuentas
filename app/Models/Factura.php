<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends SoftlandModel
{
    use HasFactory;

    protected $primaryKey = 'FACTURA';

    public $timestamps = true;

    protected $guarded = [
        'FACTURA',
    ];

    protected $table = 'AMS.factura'; // Nombre de tu tabla

    protected $fillable = [
        'tipo_documento', 'asiento_documento', 'pedido', 'cliente', 'pais','total_volumen', 'total_peso',
        'total_impuesto1', 'total_impuesto2', 'porc_descuento1', 'porc_descuento2',
        'monto_descuento1', 'monto_descuento2', 'tipo_descuento1', 'tipo_descuento2',
        'fecha', 'fecha_entrega', 'total_factura', 'fecha_pedido', 'total_mercaderia',
        'total_unidades', 'tipo_cambio', 'anulada', 'cargado_sg', 'cargado_cxc',
        'embarcar_a', 'direccion_factura', 'observaciones', 'moneda', 'nivel_precio',
        'cobrador', 'ruta', 'usuario', 'condicion_pago', 'zona', 'vendedor',
        'rubro1', 'rubro2', 'rubro3', 'rubro4', 'rubro5',
    ];

    public function getKeyName(){
        return "FACTURA";
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda');
    }

    public function cobrador()
    {
        return $this->belongsTo(Cobrador::class, 'cobrador');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta');
    }

    public function condicionPago()
    {
        return $this->belongsTo(CondicionPago::class, 'condicion_pago');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente');
    }

    public function lineas()
    {
        return $this->hasMany(FacturaLinea::class, 'factura', 'factura');
    }
}
