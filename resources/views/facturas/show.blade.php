@extends('adminlte::page')


@section('template_title')
  {!! trans('hyplast.showing-employee', ['name' => $facturas->factura]) !!}
@endsection

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="card">
                    <div class="card-header text-white @if ($facturas->ESTADO_EMPLEADO == "ACT") bg-success @else bg-warning @endif">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            {!! trans('hyplast.showing-employee-title', ['name' => $employee->NOMBRE]) !!}
                            <div class="pull-right">
                                <a href="{{ route('facturas') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machines') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h1>Detalle de Factura {{ $factura->factura }}</h1>
                        <p><strong>Tipo Documento:</strong> {{ $factura->tipo_documento }}</p>
                        <p><strong>Asiento Documento:</strong> {{ $factura->asiento_documento }}</p>
                        <p><strong>Pedido:</strong> {{ $factura->pedido }}</p>
                        <p><strong>Total Volumen:</strong> {{ $factura->total_volumen }}</p>
                        <p><strong>Total Peso:</strong> {{ $factura->total_peso }}</p>
                        <p><strong>Total Impuesto 1:</strong> {{ $factura->total_impuesto1 }}</p>
                        <p><strong>Total Impuesto 2:</strong> {{ $factura->total_impuesto2 }}</p>
                        <p><strong>Porcentaje Descuento 1:</strong> {{ $factura->porc_descuento1 }}</p>
                        <p><strong>Porcentaje Descuento 2:</strong> {{ $factura->porc_descuento2 }}</p>
                        <p><strong>Monto Descuento 1:</strong> {{ $factura->monto_descuento1 }}</p>
                        <p><strong>Monto Descuento 2:</strong> {{ $factura->monto_descuento2 }}</p>
                        <p><strong>Tipo Descuento 1:</strong> {{ $factura->tipo_descuento1 }}</p>
                        <p><strong>Tipo Descuento 2:</strong> {{ $factura->tipo_descuento2 }}</p>
                        <p><strong>Fecha:</strong> {{ $factura->fecha }}</p>
                        <p><strong>Fecha Entrega:</strong> {{ $factura->fecha_entrega }}</p>
                        <p><strong>Total Factura:</strong> {{ $factura->total_factura }}</p>
                        <p><strong>Fecha Pedido:</strong> {{ $factura->fecha_pedido }}</p>
                        <p><strong>Total Mercadería:</strong> {{ $factura->total_mercaderia }}</p>
                        <p><strong>Total Unidades:</strong> {{ $factura->total_unidades }}</p>
                        <p><strong>Tipo Cambio:</strong> {{ $factura->tipo_cambio }}</p>
                        <p><strong>Anulada:</strong> {{ $factura->anulada }}</p>
                        <p><strong>Cargado SG:</strong> {{ $factura->cargado_sg }}</p>
                        <p><strong>Cargado CXC:</strong> {{ $factura->cargado_cxc }}</p>
                        <p><strong>Embarcar A:</strong> {{ $factura->embarcar_a }}</p>
                        <p><strong>Dirección Factura:</strong> {{ $factura->direccion_factura }}</p>
                        <p><strong>Observaciones:</strong> {{ $factura->observaciones }}</p>
                        <p><strong>Moneda:</strong> {{ $factura->moneda->moneda }}</p>
                        <p><strong>Nivel Precio:</strong> {{ $factura->nivel_precio }}</p>
                        <p><strong>Cobrador:</strong> {{ $factura->cobrador->nombre }}</p>
                        <p><strong>Ruta:</strong> {{ $factura->ruta->descripcion }}</p>
                        <p><strong>Usuario:</strong> {{ $factura->usuario }}</p>
                        <p><strong>Condición Pago:</strong> {{ $factura->condicionPago->descripcion }} (Días Neto: {{ $factura->condicionPago->dias_neto }})</p>
                        <p><strong>Zona:</strong> {{ $factura->zona->nombre }}</p>
                        <p><strong>Vendedor:</strong> {{ $factura->vendedor->nombre }}</p>
                        <p><strong>Rubro 1:</strong> {{ $factura->rubro1 }}</p>
                        <p><strong>Rubro 2:</strong> {{ $factura->rubro2 }}</p>
                        <p><strong>Rubro 3:</strong> {{ $factura->rubro3 }}</p>
                        <p><strong>Rubro 4:</strong> {{ $factura->rubro4 }}</p>
                        <p><strong>Rubro 5:</strong> {{ $factura->rubro5 }}</p>
                    </div>
                        <div class="row">
                            <div class="col-md-12 align-self-start">
                                <h2>Líneas de Factura</h2>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Línea</th>
                                                <th>Artículo</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Total Impuesto 1</th>
                                                <th>Total Impuesto 2</th>
                                                <th>Descuento Total Línea</th>
                                                <th>Descuento Total General</th>
                                                <th>Descuento Volumen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($factura->lineas as $linea)
                                            <tr>
                                                <td>{{ $linea->linea }}</td>
                                                <td>{{ $linea->articulo->id }}</td>
                                                <td>{{ $linea->articulo->descripcion }}</td>
                                                <td>{{ $linea->cantidad }}</td>
                                                <td>{{ $linea->precio_unitario }}</td>
                                                <td>{{ $linea->total_impuesto1 }}</td>
                                                <td>{{ $linea->total_impuesto2 }}</td>
                                                <td>{{ $linea->desc_tot_linea }}</td>
                                                <td>{{ $linea->desc_tot_general }}</td>
                                                <td>{{ $linea->descuento_volumen }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <br />
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')

  @if(config('machine.tooltipsEnabled'))
    @include('scripts.tooltips')
  @endif
 @endsection
