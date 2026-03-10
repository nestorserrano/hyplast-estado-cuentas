@extends('adminlte::page')

@section('title', 'Asiento Contable - ' . $asientoData->ASIENTO)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-book"></i> Detalle del Asiento Contable
            @if($tipoAsiento == 'DIARIO')
                <span class="badge badge-primary ml-2">
                    <i class="fas fa-file-alt"></i> ASIENTO EN DIARIO
                </span>
            @elseif($tipoAsiento == 'MAYORIZADO')
                <span class="badge badge-success ml-2">
                    <i class="fas fa-check-circle"></i> ASIENTO MAYORIZADO
                </span>
            @endif
        </h1>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Información del Asiento -->
        <div class="row">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Información del Asiento</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="window.close()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="30%">Asiento:</th>
                                        <td><strong>{{ $asientoData->ASIENTO }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha:</th>
                                        <td>{{ \Carbon\Carbon::parse($asientoData->FECHA)->format('d/m/Y') ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Origen:</th>
                                        <td>{{ $asientoData->ORIGEN ?? 'N/A' }}</td>
                                    </tr>
                                    @if($tipoAsiento == 'DIARIO')
                                    <tr>
                                        <th>Paquete:</th>
                                        <td>{{ $asientoData->PAQUETE ?? 'N/A' }}</td>
                                    </tr>
                                    @endif
                                    @if($tipoAsiento == 'MAYORIZADO')
                                    <tr>
                                        <th>Mayor Auditoría:</th>
                                        <td>{{ $asientoData->MAYOR_AUDITORIA ?? 'N/A' }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="30%">Tipo:</th>
                                        <td>{{ $asientoData->TIPO_ASIENTO ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Clase:</th>
                                        <td>{{ $asientoData->CLASE_ASIENTO ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Usuario:</th>
                                        <td>{{ $asientoData->ULTIMO_USUARIO ?? 'N/A' }}</td>
                                    </tr>
                                    @if($tipoAsiento == 'DIARIO')
                                    <tr>
                                        <th>Marcado:</th>
                                        <td>
                                            @if(isset($asientoData->MARCADO) && $asientoData->MARCADO == 'S')
                                                <span class="badge badge-warning">Sí</span>
                                            @else
                                                <span class="badge badge-success">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @if($tipoAsiento == 'MAYORIZADO')
                                    <tr>
                                        <th>Exportado:</th>
                                        <td>
                                            @if(isset($asientoData->EXPORTADO) && $asientoData->EXPORTADO == 'S')
                                                <span class="badge badge-info">Sí</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        @if($tipoAsiento == 'DIARIO' && isset($asientoData->TOTAL_DEBITO_LOC))
                        <!-- Totales para asientos en DIARIO -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Totales del Asiento (DIARIO):</strong><br>
                                    Débito Local: RD${{ number_format($asientoData->TOTAL_DEBITO_LOC ?? 0, 2) }} |
                                    Crédito Local: RD${{ number_format($asientoData->TOTAL_CREDITO_LOC ?? 0, 2) }}<br>
                                    Débito Dólar: ${{ number_format($asientoData->TOTAL_DEBITO_DOL ?? 0, 2) }} |
                                    Crédito Dólar: ${{ number_format($asientoData->TOTAL_CREDITO_DOL ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($tipoAsiento == 'MAYORIZADO' && isset($asientoData->MONTO_TOTAL_LOCAL))
                        <!-- Totales para asientos MAYORIZADOS -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <strong>Totales del Asiento (MAYORIZADO):</strong><br>
                                    Monto Total Local: RD${{ number_format($asientoData->MONTO_TOTAL_LOCAL ?? 0, 2) }}<br>
                                    Monto Total Dólar: ${{ number_format($asientoData->MONTO_TOTAL_DOLAR ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($asientoData->NOTAS)
                        <div class="row">
                            <div class="col-12">
                                <strong>Notas:</strong>
                                <p style="white-space: pre-wrap;">{{ $asientoData->NOTAS }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle del Asiento -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient-info">
                        <h3 class="card-title"><i class="fas fa-list"></i> Movimientos del Asiento</h3>
                        <div class="card-tools">
                            <label class="mr-2">Tipo de Moneda:</label>
                            <select class="form-control form-control-sm d-inline-block" id="tipo_moneda" style="width: 180px;">
                                <option value="local">Moneda Local (RD$)</option>
                                <option value="dolar">Dólares (USD)</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="12%">Cuenta</th>
                                    <th width="8%">Centro Costo</th>
                                    <th width="25%">Concepto</th>
                                    <th width="10%" class="text-right">Tipo Cambio</th>
                                    <th width="10%" class="text-right">Débito</th>
                                    <th width="10%" class="text-right">Crédito</th>
                                    <th width="10%">Documento</th>
                                    <th width="10%">Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalDebito = 0;
                                    $totalCredito = 0;
                                    $totalDebitoLocal = 0;
                                    $totalCreditoLocal = 0;
                                    $totalDebitoDolar = 0;
                                    $totalCreditoDolar = 0;
                                @endphp
                                @foreach($detalles as $detalle)
                                    @php
                                        $totalDebitoLocal += $detalle->DEBITO_LOCAL ?? 0;
                                        $totalCreditoLocal += $detalle->CREDITO_LOCAL ?? 0;
                                        $totalDebitoDolar += $detalle->DEBITO_DOLAR ?? 0;
                                        $totalCreditoDolar += $detalle->CREDITO_DOLAR ?? 0;
                                    @endphp
                                    <tr data-debito-local="{{ $detalle->DEBITO_LOCAL ?? 0 }}"
                                        data-credito-local="{{ $detalle->CREDITO_LOCAL ?? 0 }}"
                                        data-debito-dolar="{{ $detalle->DEBITO_DOLAR ?? 0 }}"
                                        data-credito-dolar="{{ $detalle->CREDITO_DOLAR ?? 0 }}">
                                        <td>{{ $detalle->CUENTA_CONTABLE }}</td>
                                        <td>{{ $detalle->CENTRO_COSTO ?? '-' }}</td>
                                        <td>{{ $detalle->CONCEPTO ?? '-' }}</td>
                                        <td class="text-right">
                                            {{ $detalle->TIPO_CAMBIO ? number_format($detalle->TIPO_CAMBIO, 2) : '-' }}
                                        </td>
                                        <td class="text-right debito-cell">
                                            <span class="debito-local">
                                                @if(($detalle->DEBITO_LOCAL ?? 0) > 0)
                                                    <strong>RD${{ number_format($detalle->DEBITO_LOCAL ?? 0, 2) }}</strong>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                            <span class="debito-dolar" style="display:none;">
                                                @if(($detalle->DEBITO_DOLAR ?? 0) > 0)
                                                    <strong>${{ number_format($detalle->DEBITO_DOLAR ?? 0, 2) }}</strong>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-right credito-cell">
                                            <span class="credito-local">
                                                @if(($detalle->CREDITO_LOCAL ?? 0) > 0)
                                                    <strong>RD${{ number_format($detalle->CREDITO_LOCAL ?? 0, 2) }}</strong>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                            <span class="credito-dolar" style="display:none;">
                                                @if(($detalle->CREDITO_DOLAR ?? 0) > 0)
                                                    <strong>${{ number_format($detalle->CREDITO_DOLAR ?? 0, 2) }}</strong>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $detalle->DOCUMENTO ?? '-' }}</td>
                                        <td><span class="badge badge-secondary">{{ $detalle->ORIGEN ?? '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="4" class="text-right">TOTALES:</td>
                                    <td class="text-right" id="total-debito">
                                        <strong class="text-success">RD${{ number_format($totalDebitoLocal, 2) }}</strong>
                                    </td>
                                    <td class="text-right" id="total-credito">
                                        <strong class="text-danger">RD${{ number_format($totalCreditoLocal, 2) }}</strong>
                                    </td>
                                    <td colspan="2" class="text-center" id="estado-cuadre">
                                        @if($totalDebitoLocal == $totalCreditoLocal)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Cuadrado
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Descuadrado:
                                                RD${{ number_format(abs($totalDebitoLocal - $totalCreditoLocal), 2) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    @media print {
        .main-header, .main-sidebar, .card-tools {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
    }
</style>
@stop

@section('js')
<script>
    // Cambiar tipo de moneda en asientos contables
    $(document).ready(function() {
        $('#tipo_moneda').on('change', function() {
            const tipoMoneda = $(this).val();

            if (tipoMoneda === 'dolar') {
                // Mostrar columnas en dólares
                $('.debito-local').hide();
                $('.credito-local').hide();
                $('.debito-dolar').show();
                $('.credito-dolar').show();
            } else {
                // Mostrar columnas en moneda local
                $('.debito-dolar').hide();
                $('.credito-dolar').hide();
                $('.debito-local').show();
                $('.credito-local').show();
            }

            // Recalcular totales
            calcularTotalesAsiento(tipoMoneda);
        });

        // Función para calcular totales según moneda
        function calcularTotalesAsiento(tipoMoneda) {
            let totalDebito = 0;
            let totalCredito = 0;
            const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

            $('table tbody tr').each(function() {
                if (tipoMoneda === 'dolar') {
                    totalDebito += parseFloat($(this).data('debito-dolar')) || 0;
                    totalCredito += parseFloat($(this).data('credito-dolar')) || 0;
                } else {
                    totalDebito += parseFloat($(this).data('debito-local')) || 0;
                    totalCredito += parseFloat($(this).data('credito-local')) || 0;
                }
            });

            $('#total-debito').html('<strong class="text-success">' + prefix + totalDebito.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</strong>');
            $('#total-credito').html('<strong class="text-danger">' + prefix + totalCredito.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</strong>');

            // Actualizar estado de cuadre
            if (Math.abs(totalDebito - totalCredito) < 0.01) {
                $('#estado-cuadre').html('<span class="badge badge-success"><i class="fas fa-check"></i> Cuadrado</span>');
            } else {
                const diferencia = Math.abs(totalDebito - totalCredito);
                $('#estado-cuadre').html('<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Descuadrado: ' + prefix + diferencia.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '</span>');
            }
        }
    });
</script>
@stop

@section('js')
<script>
    // Cerrar ventana con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.close();
        }
    });
</script>
@stop
