@extends('adminlte::page')

@section('title', 'Estado de Cuenta - Clientes')

@php
    use Carbon\Carbon;
@endphp

@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar"></i> Estado de Cuenta de Clientes</h1>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Formulario de Búsqueda -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Buscar Cliente</h3>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                            </div>
                        @endif

                        @if(Auth::user()->hasRole('customer'))
                            {{-- Vista para clientes: campo deshabilitado --}}
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Información:</strong> A continuación puede consultar su estado de cuenta.
                            </div>

                            <form method="GET" id="search-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_code">Cliente Asignado</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="customer_code"
                                                   name="customer_code"
                                                   value="{{ Auth::user()->cliente_codigo }} - {{ Auth::user()->cliente_nombre }}"
                                                   readonly
                                                   disabled
                                                   style="background-color: #e9ecef; cursor: not-allowed;">
                                            <input type="hidden" name="customer_code" value="{{ Auth::user()->cliente_codigo }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_desde">Desde (opcional)</label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="fecha_desde"
                                                   name="fecha_desde"
                                                   @if(isset($fechaDesde)) value="{{ $fechaDesde }}" @endif>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_hasta">Hasta (opcional)</label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="fecha_hasta"
                                                   name="fecha_hasta"
                                                   @if(isset($fechaHasta)) value="{{ $fechaHasta }}" @endif>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-search"></i> Ver Mi Estado de Cuenta
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @else
                            {{-- Vista para otros roles: campo habilitado con búsqueda --}}
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Información:</strong> Seleccione un cliente y haga clic en "Ver Estado de Cuenta".
                            </div>

                            <form method="GET" id="search-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_code">Código de Cliente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text"
                                                       class="form-control"
                                                       id="customer_code"
                                                       name="customer_code"
                                                       placeholder="Ingrese el código del cliente"
                                                       required
                                                       @if(isset($customer)) value="{{ $customer->CLIENTE }}" @endif>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#customersModal">
                                                        <i class="fas fa-search"></i> Buscar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_desde">Desde (opcional)</label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="fecha_desde"
                                                   name="fecha_desde"
                                                   @if(isset($fechaDesde)) value="{{ $fechaDesde }}" @endif>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_hasta">Hasta (opcional)</label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="fecha_hasta"
                                                   name="fecha_hasta"
                                                   @if(isset($fechaHasta)) value="{{ $fechaHasta }}" @endif>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-search"></i> Ver Estado de Cuenta
                                        </button>
                                        @if(isset($customer))
                                            <a href="{{ route('customer-statement.index') }}" class="btn btn-secondary btn-lg">
                                                <i class="fas fa-redo"></i> Nueva Búsqueda
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(isset($customer) && isset($documents))
        <!-- Resultados -->
        <div class="row">
            <div class="col-12">
                <!-- Información del Cliente -->
                <div class="card card-info">
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
                                        <th>Saldo:</th>
                                        <td class="saldo-cliente">
                                            <h4 class="mb-0">
                                                <span class="badge badge-{{ floatval($customer->SALDO_LOCAL ?? $customer->SALDO ?? 0) > 0 ? 'danger' : 'success' }} badge-lg saldo-local-cliente">
                                                    RD${{ number_format(floatval($customer->SALDO_LOCAL ?? $customer->SALDO ?? 0), 2) }}
                                                </span>
                                                <span class="badge badge-{{ floatval($customer->SALDO_DOLAR ?? $customer->SALDO ?? 0) > 0 ? 'danger' : 'success' }} badge-lg saldo-dolar-cliente" style="display:none;">
                                                    ${{ number_format(floatval($customer->SALDO_DOLAR ?? $customer->SALDO ?? 0), 2) }}
                                                </span>
                                            </h4>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentos -->
                <div class="card">
                    <div class="card-header bg-primary">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-file-alt"></i> Documentos
                                @if(isset($fechaDesde) && isset($fechaHasta))
                                    ({{ Carbon::parse($fechaDesde)->format('d/m/Y') }} - {{ Carbon::parse($fechaHasta)->format('d/m/Y') }})
                                @elseif(isset($fechaDesde))
                                    (Desde {{ Carbon::parse($fechaDesde)->format('d/m/Y') }})
                                @elseif(isset($fechaHasta))
                                    (Hasta {{ Carbon::parse($fechaHasta)->format('d/m/Y') }})
                                @else
                                    (Todos)
                                @endif
                            </h3>
                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0 mr-2 font-weight-bold text-white" for="tipo_moneda" style="white-space:nowrap;">Moneda:</label>
                                <select class="form-control form-control-sm d-inline-block mr-2" id="tipo_moneda" style="width: 170px;">
                                    <option value="local">Moneda Local (RD$)</option>
                                    <option value="dolar">Dólares (USD)</option>
                                </select>
                                <a href="{{ route('customer-statement.print', ['customer' => $customer->CLIENTE, 'fecha_desde' => $fechaDesde ?? null, 'fecha_hasta' => $fechaHasta ?? null]) }}"
                                   class="btn btn-sm btn-success mr-2" target="_blank">
                                    <i class="fas fa-print"></i> Imprimir Estado de Cuenta
                                </a>
                                <button type="button" class="btn btn-sm btn-info" onclick="exportarExcel()">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($saldoAnterior) && $saldoAnterior > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Saldo Anterior:</strong> ${{ number_format($saldoAnterior, 2) }}
                            <small>(Documentos pendientes antes del {{ Carbon::parse($fechaDesde)->format('d/m/Y') }})</small>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table id="documents-table" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Documento</th>
                                        <th>NCF</th>
                                        <th>Fecha Doc.</th>
                                        <th>Fecha Vence</th>
                                        <th>Concepto</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-right">Saldo</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Asiento</th>
                                        <th class="text-center">Aplicaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalMonto = 0;
                                        $totalSaldo = 0;
                                    @endphp
                                    @forelse($documents as $doc)
                                        @php
                                            $totalMonto += floatval($doc->MONTO);
                                            $totalSaldo += floatval($doc->SALDO);
                                            $diasVencido = $doc->DIAS_VENCIDO ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $doc->TIPO_NOMBRE }}</td>
                                            <td class="text-center">{{ $doc->DOCUMENTO }}</td>
                                            <td>
                                                @if($doc->NCF)
                                                    {{ $doc->NCF }}
                                                @else
                                                    <span class="text-muted">Sin NCF</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ Carbon::parse($doc->FECHA_DOCUMENTO)->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ Carbon::parse($doc->FECHA_VENCE)->format('d/m/Y') }}</td>
                                            <td>{{ $doc->APLICACION ?? '-' }}</td>
                                            <td class="text-right monto-cell"
                                                data-monto-local="{{ $doc->MONTO_LOCAL ?? $doc->MONTO }}"
                                                data-monto-dolar="{{ $doc->MONTO_DOLAR ?? $doc->MONTO }}">
                                                <span class="monto-local">RD${{ number_format(floatval($doc->MONTO_LOCAL ?? $doc->MONTO), 2) }}</span>
                                                <span class="monto-dolar" style="display:none;">${{ number_format(floatval($doc->MONTO_DOLAR ?? $doc->MONTO), 2) }}</span>
                                            </td>
                                            <td class="text-right saldo-cell"
                                                data-saldo-local="{{ $doc->SALDO_LOCAL ?? $doc->SALDO }}"
                                                data-saldo-dolar="{{ $doc->SALDO_DOLAR ?? $doc->SALDO }}">
                                                <span class="saldo-local {{ floatval($doc->SALDO_LOCAL ?? $doc->SALDO) > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">RD${{ number_format(floatval($doc->SALDO_LOCAL ?? $doc->SALDO), 2) }}</span>
                                                <span class="saldo-dolar {{ floatval($doc->SALDO_DOLAR ?? $doc->SALDO) > 0 ? 'text-danger font-weight-bold' : 'text-success' }}" style="display:none;">${{ number_format(floatval($doc->SALDO_DOLAR ?? $doc->SALDO), 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if(floatval($doc->SALDO) <= 0)
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
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-primary btn-sm btn-ver-pagos"
                                                        data-documento="{{ $doc->DOCUMENTO }}"
                                                        data-tipo="{{ $doc->TIPO }}"
                                                        title="Ver Pagos">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">
                                                No se encontraron documentos para este cliente
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="6" class="text-right">TOTALES:</td>
                                        <td class="text-right" id="total-monto">${{ number_format($totalMonto, 2) }}</td>
                                        <td class="text-right" id="total-saldo">${{ number_format($totalSaldo, 2) }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                    @if(isset($totalPendiente) && $totalPendiente > 0)
                                    <tr class="bg-warning font-weight-bold">
                                        <td colspan="6" class="text-right">TOTAL PENDIENTE:</td>
                                        <td colspan="2" class="text-right">
                                            <h5 class="mb-0 text-danger">${{ number_format($totalPendiente, 2) }}</h5>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    @endif
                                    @if(isset($saldoAnterior) && $saldoAnterior > 0)
                                    <tr class="bg-info font-weight-bold">
                                        <td colspan="6" class="text-right">SALDO TOTAL (Incluye Saldo Anterior):</td>
                                        <td colspan="2" class="text-right">
                                            <h5 class="mb-0 text-dark">${{ number_format($saldoAnterior + $totalPendiente, 2) }}</h5>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>

                        @if($documents->count() > 0)
                        <!-- Análisis de Vencimiento -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-danger">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Documentos Vencidos</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            @php
                                                $sinVencer = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) < 0 && floatval($d->SALDO) > 0)->sum('SALDO');
                                                $vencido1a30 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) >= 1 && intval($d->DIAS_VENCIDO ?? 0) <= 30 && floatval($d->SALDO) > 0)->sum('SALDO');
                                                $vencido31a60 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) >= 31 && intval($d->DIAS_VENCIDO ?? 0) <= 60 && floatval($d->SALDO) > 0)->sum('SALDO');
                                                $vencido61a90 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) >= 61 && intval($d->DIAS_VENCIDO ?? 0) <= 90 && floatval($d->SALDO) > 0)->sum('SALDO');
                                                $vencidoMas90 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) > 90 && floatval($d->SALDO) > 0)->sum('SALDO');
                                            @endphp
                                            <tr>
                                                <td class="font-weight-bold" style="width: 70%;">Sin Vencer:</td>
                                                <td class="text-right text-success">${{ number_format($sinVencer, 2) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <td class="font-weight-bold">Vencido 1 a 30 días:</td>
                                                <td class="text-right text-warning">${{ number_format($vencido1a30, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Vencido 31 a 60 días:</td>
                                                <td class="text-right text-orange">${{ number_format($vencido31a60, 2) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <td class="font-weight-bold">Vencido 61 a 90 días:</td>
                                                <td class="text-right text-danger">${{ number_format($vencido61a90, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Vencido sobre 90 días:</td>
                                                <td class="text-right font-weight-bold text-danger">${{ number_format($vencidoMas90, 2) }}</td>
                                            </tr>
                                            <tr class="bg-danger text-white">
                                                <td class="font-weight-bold">TOTAL VENCIDO:</td>
                                                <td class="text-right font-weight-bold">${{ number_format($vencido1a30 + $vencido31a60 + $vencido61a90 + $vencidoMas90, 2) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-clock"></i> Documentos Por Vencer</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            @php
                                                // Calcular documentos por vencer (DIAS_VENCIDO negativo significa no ha vencido aún)
                                                $porVencer1a30 = $documents->filter(function($d) {
                                                    $dias = intval($d->DIAS_VENCIDO ?? 0);
                                                    return $dias >= -30 && $dias < 0 && floatval($d->SALDO) > 0;
                                                })->sum('SALDO');

                                                $porVencer31a60 = $documents->filter(function($d) {
                                                    $dias = intval($d->DIAS_VENCIDO ?? 0);
                                                    return $dias >= -60 && $dias < -30 && floatval($d->SALDO) > 0;
                                                })->sum('SALDO');

                                                $porVencer61a90 = $documents->filter(function($d) {
                                                    $dias = intval($d->DIAS_VENCIDO ?? 0);
                                                    return $dias >= -90 && $dias < -60 && floatval($d->SALDO) > 0;
                                                })->sum('SALDO');

                                                $porVencerMas90 = $documents->filter(function($d) {
                                                    $dias = intval($d->DIAS_VENCIDO ?? 0);
                                                    return $dias < -90 && floatval($d->SALDO) > 0;
                                                })->sum('SALDO');
                                            @endphp
                                            <tr>
                                                <td class="font-weight-bold" style="width: 70%;">Por vencer de 1 a 30 días:</td>
                                                <td class="text-right text-info">${{ number_format($porVencer1a30, 2) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <td class="font-weight-bold">Por vencer de 31 a 60 días:</td>
                                                <td class="text-right text-info">${{ number_format($porVencer31a60, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Por vencer de 61 a 90 días:</td>
                                                <td class="text-right text-primary">${{ number_format($porVencer61a90, 2) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <td class="font-weight-bold">Por vencer sobre 90 días:</td>
                                                <td class="text-right text-success">${{ number_format($porVencerMas90, 2) }}</td>
                                            </tr>
                                            <tr class="bg-warning">
                                                <td class="font-weight-bold">TOTAL POR VENCER:</td>
                                                <td class="text-right font-weight-bold">${{ number_format($porVencer1a30 + $porVencer31a60 + $porVencer61a90 + $porVencerMas90, 2) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(!Auth::user()->hasRole('customer'))
    <!-- Modal de Búsqueda de Clientes -->
    <div class="modal fade" id="customersModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title">
                        <i class="fas fa-search"></i> Buscar Cliente
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_search">Buscar por código o nombre:</label>
                        <input type="text"
                               class="form-control"
                               id="modal_search"
                               placeholder="Escriba al menos 2 caracteres...">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th class="text-right">Saldo</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="customers_tbody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Escriba en el campo de búsqueda para ver clientes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Aplicaciones de Documentos -->
    <div class="modal fade" id="pagosModal" tabindex="-1" role="dialog" aria-labelledby="pagosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="pagosModalLabel">
                        <i class="fas fa-file-invoice"></i> Aplicaciones de Documentos
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalPagos()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Resumen del Documento -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-2">
                                    <strong>Documento:</strong> <span id="resumen-documento"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Tipo:</strong> <span id="resumen-tipo"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>NCF:</strong> <span id="resumen-ncf"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Monto:</strong> <span id="resumen-monto"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Saldo:</strong> <span id="resumen-saldo"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="pagos-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Cargando aplicaciones...</p>
                    </div>
                    <div id="pagos-content" style="display:none;">
                        <div class="table-responsive" id="tabla-pagos-imprimir">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Documento Pago</th>
                                        <th>Aplicado A</th>
                                        <th>NCF</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Débito</th>
                                        <th class="text-right">Crédito</th>
                                        <th>Asiento</th>
                                    </tr>
                                </thead>
                                <tbody id="pagos-tbody">
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right">TOTALES:</td>
                                        <td class="text-right" id="total-debito">$0.00</td>
                                        <td class="text-right" id="total-credito">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div id="pagos-error" class="alert alert-danger" style="display:none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalPagos()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="imprimirPagos()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
// Verificar que jQuery y Swal estén disponibles
console.log('jQuery version:', $.fn.jquery);
console.log('SweetAlert disponible:', typeof Swal !== 'undefined');

$(document).ready(function() {
    console.log('Estado de Cuenta - Script cargado');
    console.log('Modal search input exists:', $('#modal_search').length > 0);
    console.log('Modal pagosModal exists:', $('#pagosModal').length > 0);
    console.log('Botones Ver Aplicaciones encontrados:', $('.btn-ver-pagos').length);

    // Manejar cambio de tipo de moneda
    $('#tipo_moneda').on('change', function() {
        const tipoMoneda = $(this).val();
        if (tipoMoneda === 'dolar') {
            $('.monto-local').hide();
            $('.saldo-local').hide();
            $('.monto-dolar').show();
            $('.saldo-dolar').show();
            $('.saldo-local-cliente').hide();
            $('.saldo-dolar-cliente').show();
        } else {
            $('.monto-dolar').hide();
            $('.saldo-dolar').hide();
            $('.monto-local').show();
            $('.saldo-local').show();
            $('.saldo-dolar-cliente').hide();
            $('.saldo-local-cliente').show();
        }
        calcularTotalesGeneral(tipoMoneda);
    });

    function calcularTotalesGeneral(tipoMoneda) {
        let totalMonto = 0;
        let totalSaldo = 0;
        const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';
        $('#documents-table tbody tr').each(function() {
            if (tipoMoneda === 'dolar') {
                totalMonto += parseFloat($(this).find('.monto-cell').data('monto-dolar')) || 0;
                totalSaldo += parseFloat($(this).find('.saldo-cell').data('saldo-dolar')) || 0;
            } else {
                totalMonto += parseFloat($(this).find('.monto-cell').data('monto-local')) || 0;
                totalSaldo += parseFloat($(this).find('.saldo-cell').data('saldo-local')) || 0;
            }
        });
        $('#total-monto').text(prefix + totalMonto.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#total-saldo').text(prefix + totalSaldo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
    }
    // Inicializar totales en moneda local
    calcularTotalesGeneral('local');

    // Manejar envío del formulario
    $('#search-form').on('submit', function(e) {
        e.preventDefault();

        const customerCode = $('#customer_code').val().trim();

        if (!customerCode) {
            Swal.fire('Error', 'Por favor ingrese un código de cliente', 'error');
            return;
        }

        // Construir URL con parámetros de fecha si existen
        let url = '{{ url("customer-statement") }}/' + customerCode;
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();

        if (desde || hasta) {
            url += '?';
            if (desde) url += 'fecha_desde=' + desde;
            if (desde && hasta) url += '&';
            if (hasta) url += 'fecha_hasta=' + hasta;
        }

        window.location.href = url;
    });

    // Buscar clientes en modal
    let searchTimeout;
    $('#modal_search').on('keyup', function() {
        const search = $(this).val().trim();
        console.log('Keyup detectado, búsqueda:', search, 'Longitud:', search.length);

        clearTimeout(searchTimeout);

        if (search.length < 2) {
            $('#customers_tbody').html('<tr><td colspan="6" class="text-center text-muted">Escriba al menos 2 caracteres...</td></tr>');
            return;
        }

        console.log('Iniciando búsqueda en 500ms...');
        searchTimeout = setTimeout(function() {
            console.log('Ejecutando searchCustomers con:', search);
            searchCustomers(search);
        }, 500);
    });

    function searchCustomers(search) {
        console.log('Buscando clientes con:', search);
        console.log('URL:', '{{ route("search-customers") }}');

        $.ajax({
            url: '{{ route("search-customers") }}',
            method: 'GET',
            data: { q: search },
            beforeSend: function() {
                $('#customers_tbody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</td></tr>');
            },
            success: function(data) {
                console.log('Clientes encontrados:', data);
                let html = '';

                if (data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted">No se encontraron clientes</td></tr>';
                } else {
                    data.forEach(function(customer) {
                        const balance = parseFloat(customer.SALDO || 0);
                        const balanceClass = balance > 0 ? 'text-danger' : 'text-success';

                        html += '<tr>';
                        html += '<td><strong>' + customer.CLIENTE + '</strong></td>';
                        html += '<td>' + customer.NOMBRE + '</td>';
                        html += '<td>' + (customer.TELEFONO1 || '-') + '</td>';
                        html += '<td>' + (customer.E_MAIL || '-') + '</td>';
                        html += '<td class="text-right ' + balanceClass + '"><strong>$' + balance.toFixed(2) + '</strong></td>';
                        html += '<td class="text-center">';
                        html += '<button type="button" class="btn btn-sm btn-primary select-customer" data-code="' + customer.CLIENTE + '">';
                        html += '<i class="fas fa-check"></i> Seleccionar</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                }

                $('#customers_tbody').html(html);
            },
            error: function(xhr, status, error) {
                console.error('Error en búsqueda:', xhr, status, error);
                let errorMsg = 'Error al buscar clientes';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMsg += ': ' + xhr.responseText.substring(0, 200);
                }
                Swal.fire('Error', errorMsg, 'error');
                $('#customers_tbody').html('<tr><td colspan="6" class="text-center text-danger">Error: ' + errorMsg + '</td></tr>');
            }
        });
    }

    // Seleccionar cliente del modal y cerrar modal de forma robusta
    $(document).on('click', '.select-customer', function() {
        const code = $(this).data('code');
        $('#customer_code').val(code);
        // Limpiar input de búsqueda para próxima vez
        $('#modal_search').val('');
        $('#customers_tbody').html('<tr><td colspan="6" class="text-center text-muted">Escriba en el campo de búsqueda para ver clientes...</td></tr>');

        // Forzar cierre del modal (compatibilidad Bootstrap 4/5)
        try {
            if (typeof $.fn.modal !== 'undefined') {
                $('#customersModal').modal('hide');
            } else {
                // Método alternativo si modal no está disponible
                $('#customersModal').removeClass('show');
                $('#customersModal').css('display', 'none');
            }
        } catch (e) {
            console.error('Error cerrando modal:', e);
            $('#customersModal').removeClass('show');
            $('#customersModal').css('display', 'none');
        }

        // Limpieza adicional
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

    // Función para exportar a Excel
    window.exportarExcel = function() {
        const customerCode = $('#customer_code').val();
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();

        if (!customerCode) {
            Swal.fire('Error', 'Debe seleccionar un cliente primero', 'error');
            return;
        }

        let url = '{{ route("customer-statement.export") }}?customer=' + customerCode;
        if (desde) url += '&fecha_desde=' + desde;
        if (hasta) url += '&fecha_hasta=' + hasta;

        window.location.href = url;
    };

    // Inicializar DataTable si hay documentos
    @if(isset($documents) && $documents->count() > 0)
        try {
            if ($.fn.DataTable) {
                $('#documents-table').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                    },
                    order: [[3, 'desc']],
                    pageLength: 25
                });
                console.log('DataTable inicializado correctamente');
            } else {
                console.warn('DataTable no está disponible');
            }
        } catch(e) {
            console.error('Error inicializando DataTable:', e);
        }
    @endif

    // Manejar clic en botón Ver Aplicaciones (SIEMPRE debe estar disponible)
    $(document).on('click', '.btn-ver-pagos', function(e) {
        e.preventDefault();
        console.log('Clic en botón Ver Pagos detectado');

        const $row = $(this).closest('tr');
        const documento = $(this).data('documento');
        const tipo = $(this).data('tipo');
        const customerCode = $('input[name="customer_code"]').last().val() || '{{ Auth::user()->cliente_codigo ?? "" }}';
        const tipoMoneda = $('#tipo_moneda').val() || 'local';

        // Obtener datos de la fila para el resumen (según moneda seleccionada)
        const ncf = $row.find('td:eq(0)').text().trim(); // Columna NCF
        const tipoNombre = $row.find('td:eq(2)').text().trim(); // Columna Tipo
        let monto, saldo;

        if (tipoMoneda === 'dolar') {
            monto = $row.find('.monto-dolar').text().trim();
            saldo = $row.find('.saldo-dolar').text().trim();
        } else {
            monto = $row.find('.monto-local').text().trim();
            saldo = $row.find('.saldo-local').text().trim();
        }

        console.log('Ver Pagos - Documento:', documento, 'Tipo:', tipo, 'Cliente:', customerCode, 'Moneda:', tipoMoneda);

        // Llenar resumen del documento
        $('#resumen-documento').text(documento);
        $('#resumen-tipo').text(tipoNombre);
        $('#resumen-ncf').text(ncf);
        $('#resumen-monto').text(monto);
        $('#resumen-saldo').text(saldo);

        // Preparar el modal
        $('#pagos-loading').show();
        $('#pagos-content').hide();
        $('#pagos-error').hide();

        // Mostrar el modal manualmente (sin depender de Bootstrap JS)
        const modal = document.getElementById('pagosModal');
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');

        // Agregar backdrop
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }

        // Llamada AJAX para obtener los pagos
        $.ajax({
            url: '{{ route("customer-statement.payments") }}',
            method: 'GET',
            data: {
                documento: documento,
                tipo: tipo,
                cliente: customerCode,
                moneda: tipoMoneda
            },
            success: function(response) {
                console.log('Respuesta recibida:', response);
                $('#pagos-loading').hide();

                if (response.success && response.pagos.length > 0) {
                    let html = '';
                    let totalDebito = 0;
                    let totalCredito = 0;
                    const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

                    response.pagos.forEach(function(pago) {
                        const debito = parseFloat(pago.DEBITO || 0);
                        const credito = parseFloat(pago.CREDITO || 0);
                        totalDebito += debito;
                        totalCredito += credito;

                        html += '<tr>';
                        html += '<td>' + (pago.DOCUMENTO_PAGO || '-') + '</td>';
                        html += '<td>' + (pago.APLICADO_A || '-') + '</td>';
                        html += '<td>' + (pago.NCF || 'Sin NCF') + '</td>';
                        html += '<td>' + (pago.TIPO_NOMBRE || pago.TIPO || '-') + '</td>';
                        html += '<td>' + (pago.FECHA ? new Date(pago.FECHA).toLocaleDateString('es-DO') : '-') + '</td>';
                        html += '<td class="text-right">' + (debito > 0 ? prefix + debito.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-') + '</td>';
                        html += '<td class="text-right">' + (credito > 0 ? prefix + credito.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-') + '</td>';
                        html += '<td class="text-center">';
                        if (pago.ASIENTO) {
                            html += '<span class="asiento-numero">' + pago.ASIENTO + '</span> ';
                            html += '<a href="{{ route("customer-statement.asiento", "") }}/' + pago.ASIENTO + '" class="btn btn-info btn-xs no-print" target="_blank"><i class="fas fa-book"></i></a>';
                        } else {
                            html += '-';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });

                    $('#pagos-tbody').html(html);
                    $('#total-debito').text(prefix + totalDebito.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $('#total-credito').text(prefix + totalCredito.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $('#pagos-content').show();
                } else {
                    console.log('No se encontraron pagos');
                    $('#pagos-error').html('<i class="fas fa-info-circle"></i> No se encontraron pagos relacionados con este documento.').show();
                }
            },
            error: function(xhr) {
                console.error('Error AJAX:', xhr);
                $('#pagos-loading').hide();
                let errorMsg = 'Error al cargar los pagos';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errorMsg = xhr.statusText;
                }
                $('#pagos-error').html('<i class="fas fa-exclamation-triangle"></i> ' + errorMsg).show();
            }
        });
    });
});

// Función global para cerrar el modal de pagos
function cerrarModalPagos() {
    const modal = document.getElementById('pagosModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');

    // Remover backdrop
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }

    // Limpiar contenido
    setTimeout(function() {
        $('#pagos-loading').show();
        $('#pagos-content').hide();
        $('#pagos-error').hide();
        $('#pagos-tbody').html('');
    }, 300);
}

// Cerrar modal al hacer clic en el backdrop
$(document).on('click', '.modal-backdrop', function() {
    cerrarModalPagos();
});

// Cerrar modal con tecla ESC
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#pagosModal').hasClass('show')) {
        cerrarModalPagos();
    }
});

// Función global para imprimir pagos
function imprimirPagos() {
    const documento = $('#resumen-documento').text();
    const tipo = $('#resumen-tipo').text();
    const ncf = $('#resumen-ncf').text();
    const monto = $('#resumen-monto').text();
    const saldo = $('#resumen-saldo').text();

    // Crear ventana de impresión
    const ventanaImpresion = window.open('', '_blank', 'width=800,height=600');

    // Generar HTML para impresión
    const contenidoHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Pagos Relacionados - ${documento}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
                .resumen { background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
                .resumen div { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
                th { background-color: #007bff; color: white; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                tfoot { background-color: #f8f9fa; font-weight: bold; }
                .no-print { display: none !important; }
                .asiento-numero { font-weight: bold; }
            </style>
        </head>
        <body>
            <h2>Pagos Relacionados al Documento</h2>
            <div class="resumen">
                <div><strong>Documento:</strong> ${documento}</div>
                <div><strong>Tipo:</strong> ${tipo}</div>
                <div><strong>NCF:</strong> ${ncf}</div>
                <div><strong>Monto:</strong> ${monto}</div>
                <div><strong>Saldo:</strong> ${saldo}</div>
            </div>
            ${$('#tabla-pagos-imprimir').html()}
            <div style="margin-top: 30px; text-align: center; color: #6c757d;">
                <small>Fecha de impresión: ${new Date().toLocaleDateString('es-DO')} ${new Date().toLocaleTimeString('es-DO')}</small>
            </div>
        </body>
        </html>
    `;

    ventanaImpresion.document.write(contenidoHTML);
    ventanaImpresion.document.close();

    // Esperar a que cargue y luego imprimir
    ventanaImpresion.onload = function() {
        ventanaImpresion.print();
    };
}

</script>
@stop
