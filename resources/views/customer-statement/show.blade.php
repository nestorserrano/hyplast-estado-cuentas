@extends('adminlte::page')

@section('title', 'Estado de Cuenta - ' . $customer->NOMBRE)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice-dollar"></i> Estado de Cuenta</h1>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('customer-statement.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('customer-statement.export', $customer->CLIENTE) }}"
               class="btn btn-success mr-2" target="_blank">
                <i class="fas fa-file-excel"></i> Exportar
            </a>
            <button onclick="window.print()" class="btn btn-info mr-3">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <label class="mb-0 mr-2 font-weight-bold" for="tipo_moneda" style="white-space:nowrap;">Moneda:</label>
            <select class="form-control form-control-sm d-inline-block" id="tipo_moneda" style="width: 170px;">
                <option value="local">Moneda Local (RD$)</option>
                <option value="dolar">Dólares (USD)</option>
            </select>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Información del Cliente -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="30%">Código:</th>
                                        <td><strong>{{ $customer->CLIENTE }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Nombre:</th>
                                        <td><strong>{{ $customer->NOMBRE }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Contacto:</th>
                                        <td>{{ $customer->CONTACTO ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="30%">Teléfono:</th>
                                        <td>{{ $customer->TELEFONO1 ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $customer->E_MAIL ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Balance:</th>
                                        <td>
                                            <h4 class="mb-0">
                                                <span class="badge badge-{{ $customer->BALANCE > 0 ? 'danger' : 'success' }} badge-lg">
                                                    ${{ number_format($customer->BALANCE, 2) }}
                                                </span>
                                            </h4>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documentos del Cliente -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient-navy">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-file-alt"></i> Documentos del Cliente
                            </h3>
                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0 mr-2 font-weight-bold" for="tipo_moneda" style="white-space:nowrap;">Moneda:</label>
                                <select class="form-control form-control-sm d-inline-block mr-2" id="tipo_moneda" style="width: 170px;">
                                    <option value="local">Moneda Local (RD$)</option>
                                    <option value="dolar">Dólares (USD)</option>
                                </select>
                                <button type="button" class="btn btn-success btn-sm mr-2" onclick="window.open('{{ route('customer-statement.export', $customer->CLIENTE) }}', '_blank')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button type="button" class="btn btn-info btn-sm mr-2" onclick="window.print()">
                                    <i class="fas fa-print"></i> Imprimir Estado de Cuenta
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros de Fecha -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Fecha Desde:</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_desde">
                            </div>
                            <div class="col-md-4">
                                <label>Fecha Hasta:</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_hasta">
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label><br>
                                <button type="button" class="btn btn-primary btn-sm" id="btn-filtrar">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-limpiar">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="documents-table" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th width="8%">NCF</th>
                                        <th width="7%">Documento</th>
                                        <th width="8%">Tipo</th>
                                        <th width="7%">Fecha Doc.</th>
                                        <th width="7%">Fecha Vence</th>
                                        <th width="15%">Concepto</th>
                                        <th width="6%">Moneda</th>
                                        <th width="7%">Tipo Cambio</th>
                                        <th width="8%">Monto</th>
                                        <th width="8%">Saldo</th>
                                        <th width="9%">Estado</th>
                                        <th width="5%">Asiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        use Carbon\Carbon;
                                    @endphp
                                    @foreach($documents as $doc)
                                        @php
                                            $ncfData = \App\Models\NcfDocumento::where('DOC_RowPointer', $doc->RowPointer)->first();
                                            $ncf = $ncfData ? $ncfData->NCF : null;

                                            $tipoNombre = match($doc->TIPO) {
                                                'FAC' => 'Factura',
                                                'DEV' => 'Devolución',
                                                'REC' => 'Recibo',
                                                'DEP' => 'Depósito',
                                                'N/C' => 'Nota de Crédito',
                                                'N/D' => 'Nota de Débito',
                                                default => $doc->TIPO
                                            };

                                            $diasVencido = Carbon::parse($doc->FECHA_VENCE)->diffInDays(now(), false);
                                        @endphp
                                        <tr data-moneda="{{ $doc->MONEDA }}"
                                            data-monto-local="{{ $doc->MONTO_LOCAL ?? $doc->MONTO }}"
                                            data-saldo-local="{{ $doc->SALDO_LOCAL ?? $doc->SALDO }}"
                                            data-monto-dolar="{{ $doc->MONTO_DOLAR ?? $doc->MONTO }}"
                                            data-saldo-dolar="{{ $doc->SALDO_DOLAR ?? $doc->SALDO }}">
                                            <td>{!! $ncf ?? '<span class="text-muted">Sin NCF</span>' !!}</td>
                                            <td class="text-center">{{ $doc->DOCUMENTO }}</td>
                                            <td class="text-center">{{ $tipoNombre }}</td>
                                            <td class="text-center">{{ Carbon::parse($doc->FECHA_DOCUMENTO)->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ Carbon::parse($doc->FECHA_VENCE)->format('d/m/Y') }}</td>
                                            <td>{{ $doc->APLICACION ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $doc->MONEDA == 'USD' ? 'success' : 'info' }}">
                                                    {{ $doc->MONEDA ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="text-right tipo-cambio">
                                                {{ $doc->TIPO_CAMBIO_DOLAR ? number_format($doc->TIPO_CAMBIO_DOLAR, 2) : '-' }}
                                            </td>
                                            <td class="text-right monto-cell">
                                                <span class="monto-local">RD${{ number_format($doc->MONTO_LOCAL ?? $doc->MONTO, 2) }}</span>
                                                <span class="monto-dolar" style="display:none;">${{ number_format($doc->MONTO_DOLAR ?? $doc->MONTO, 2) }}</span>
                                            </td>
                                            <td class="text-right saldo-cell">
                                                <span class="saldo-local {{ ($doc->SALDO_LOCAL ?? $doc->SALDO) > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                                    RD${{ number_format($doc->SALDO_LOCAL ?? $doc->SALDO, 2) }}
                                                </span>
                                                <span class="saldo-dolar {{ ($doc->SALDO_DOLAR ?? $doc->SALDO) > 0 ? 'text-danger font-weight-bold' : 'text-success' }}" style="display:none;">
                                                    ${{ number_format($doc->SALDO_DOLAR ?? $doc->SALDO, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($doc->SALDO <= 0)
                                                    <span class="badge badge-success">Pagado</span>
                                                @elseif($diasVencido > 0)
                                                    <span class="badge badge-danger">Vencido ({{ $diasVencido }} días)</span>
                                                @else
                                                    <span class="badge badge-warning">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($doc->ASIENTO)
                                                    <a href="{{ route('customer-statement.asiento', $doc->ASIENTO) }}"
                                                       class="btn btn-info btn-sm" title="Ver Asiento" target="_blank">
                                                       <i class="fas fa-book"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="8" class="text-right">TOTALES:</td>
                                        <td id="total-monto" class="text-right">RD$0.00</td>
                                        <td id="total-saldo" class="text-right">RD$0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    @media print {
        .main-header, .main-sidebar, .content-header .btn, .card-tools, .no-print, #btn-filtrar, #btn-limpiar, .form-control {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
    }
</style>
@stop

@section('js')
@include('scripts.datatables.datatables-customer-statement')
<script type="text/javascript">
    // Cambiar tipo de moneda
    $('#tipo_moneda').on('change', function() {
        const tipoMoneda = $(this).val();

        if (tipoMoneda === 'dolar') {
            // Mostrar columnas en dólares
            $('.monto-local').hide();
            $('.saldo-local').hide();
            $('.monto-dolar').show();
            $('.saldo-dolar').show();
        } else {
            // Mostrar columnas en moneda local
            $('.monto-dolar').hide();
            $('.saldo-dolar').hide();
            $('.monto-local').show();
            $('.saldo-local').show();
        }

        // Recalcular totales
        calcularTotales(tipoMoneda);
    });

    // Función para calcular totales según moneda
    function calcularTotales(tipoMoneda) {
        let totalMonto = 0;
        let totalSaldo = 0;
        const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

        $('#documents-table tbody tr').each(function() {
            if (tipoMoneda === 'dolar') {
                totalMonto += parseFloat($(this).data('monto-dolar')) || 0;
                totalSaldo += parseFloat($(this).data('saldo-dolar')) || 0;
            } else {
                totalMonto += parseFloat($(this).data('monto-local')) || 0;
                totalSaldo += parseFloat($(this).data('saldo-local')) || 0;
            }
        });

        $('#total-monto').text(prefix + totalMonto.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#total-saldo').text(prefix + totalSaldo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
    }

    // Calcular totales inicial en moneda local
    $(document).ready(function() {
        calcularTotales('local');
    });

    // Filtro de fechas
    $('#btn-filtrar').on('click', function() {
        const fechaDesde = $('#fecha_desde').val();
        const fechaHasta = $('#fecha_hasta').val();

        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'documents-table') {
                    return true;
                }

                const fechaDoc = data[3]; // Columna de fecha documento
                if (!fechaDesde && !fechaHasta) {
                    return true;
                }

                // Convertir fecha de formato dd/mm/yyyy a Date
                const partes = fechaDoc.split('/');
                const fecha = new Date(partes[2], partes[1] - 1, partes[0]);

                const desde = fechaDesde ? new Date(fechaDesde) : null;
                const hasta = fechaHasta ? new Date(fechaHasta) : null;

                if (desde && hasta) {
                    return fecha >= desde && fecha <= hasta;
                } else if (desde) {
                    return fecha >= desde;
                } else if (hasta) {
                    return fecha <= hasta;
                }

                return true;
            }
        );

        customerStatementTable.draw();

        // Recalcular totales después de filtrar
        setTimeout(function() {
            const tipoMoneda = $('#tipo_moneda').val();
            calcularTotales(tipoMoneda);
        }, 100);
    });

    $('#btn-limpiar').on('click', function() {
        $('#fecha_desde').val('');
        $('#fecha_hasta').val('');
        $.fn.dataTable.ext.search.pop();
        customerStatementTable.draw();

        // Recalcular totales después de limpiar
        setTimeout(function() {
            const tipoMoneda = $('#tipo_moneda').val();
            calcularTotales(tipoMoneda);
        }, 100);
    });
</script>
@stop
