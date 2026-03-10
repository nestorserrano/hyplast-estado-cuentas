<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta - {{ $customer->NOMBRE }}</title>
    <style>
        @media print {
            .no-print { display: none; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }

        .customer-info {
            margin: 20px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }

        .customer-info table {
            width: 100%;
        }

        .customer-info td {
            padding: 5px;
        }

        .customer-info .label {
            font-weight: bold;
            width: 150px;
        }

        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .document-table th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            border: 1px solid #333;
        }

        .document-table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .document-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            margin-top: 20px;
            text-align: right;
        }

        .totals table {
            float: right;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px 15px;
            font-weight: bold;
        }

        .totals .label {
            text-align: right;
            background-color: #f5f5f5;
        }

        .totals .amount {
            text-align: right;
            background-color: #e8f4f8;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
            <strong>🖨️ Imprimir</strong>
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
        <span style="margin-left: 30px; font-weight: bold;">Tipo de Moneda:</span>
        <select id="tipo_moneda" style="padding: 8px; font-size: 14px; cursor: pointer;">
            <option value="local">Moneda Local (RD$)</option>
            <option value="dolar">Dólares (USD)</option>
        </select>
    </div>

    <div class="header">
        <div class="company-name">{{ session('conjunto_actual') ?? 'C01' }} - {{ session('conjunto_nombre') ?? 'HYPLAST S.R.L.' }}</div>
        <div class="report-title">ESTADO DE CUENTA DE CLIENTE</div>
        <div style="font-size: 10px; margin-top: 5px;">
            Fecha de Impresión: {{ now()->format('d/m/Y H:i:s') }}
            @if($fechaDesde || $fechaHasta)
                <br>
                Período:
                @if($fechaDesde && $fechaHasta)
                    {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                @elseif($fechaDesde)
                    Desde {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
                @else
                    Hasta {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                @endif
            @endif
        </div>
    </div>

    <div class="customer-info">
        <table>
            <tr>
                <td class="label">Código de Cliente:</td>
                <td><strong>{{ $customer->CLIENTE }}</strong></td>
                <td class="label">Teléfono:</td>
                <td>{{ $customer->TELEFONO1 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Nombre:</td>
                <td><strong>{{ $customer->NOMBRE }}</strong></td>
                <td class="label">Email:</td>
                <td>{{ $customer->E_MAIL ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Contacto:</td>
                <td>{{ $customer->CONTACTO ?? 'N/A' }}</td>
                <td class="label">Saldo Total:</td>
                <td><strong style="color: {{ floatval($customer->SALDO ?? 0) > 0 ? 'red' : 'green' }};">
                    ${{ number_format(floatval($customer->SALDO ?? 0), 2) }}
                </strong></td>
            </tr>
        </table>
    </div>

    @if(isset($saldoAnterior) && $saldoAnterior > 0)
    <div class="alert-info">
        <strong>⚠️ Saldo Anterior:</strong> ${{ number_format($saldoAnterior, 2) }}
        <small>(Documentos pendientes antes del {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }})</small>
    </div>
    @endif

    <table class="document-table">
        <thead>
            <tr>
                <th style="width: 100px;">NCF</th>
                <th style="width: 90px;">Documento</th>
                <th style="width: 70px;">Tipo</th>
                <th style="width: 75px;">Fecha Doc.</th>
                <th style="width: 75px;">Fecha Vence</th>
                <th>Concepto</th>
                <th style="width: 60px;">Moneda</th>
                <th style="width: 70px;" class="text-right">T. Cambio</th>
                <th style="width: 90px;" class="text-right">Monto</th>
                <th style="width: 90px;" class="text-right">Saldo</th>
                <th style="width: 80px;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMonto = 0;
                $totalSaldo = 0;
                $totalMontoLocal = 0;
                $totalSaldoLocal = 0;
                $totalMontoDolar = 0;
                $totalSaldoDolar = 0;
            @endphp
            @forelse($documents as $doc)
                @php
                    $totalMonto += floatval($doc->MONTO);
                    $totalSaldo += floatval($doc->SALDO);
                    $totalMontoLocal += floatval($doc->MONTO_LOCAL ?? $doc->MONTO);
                    $totalSaldoLocal += floatval($doc->SALDO_LOCAL ?? $doc->SALDO);
                    $totalMontoDolar += floatval($doc->MONTO_DOLAR ?? $doc->MONTO);
                    $totalSaldoDolar += floatval($doc->SALDO_DOLAR ?? $doc->SALDO);
                    $diasVencido = intval($doc->DIAS_VENCIDO ?? 0);
                @endphp
                <tr data-monto-local="{{ $doc->MONTO_LOCAL ?? $doc->MONTO }}"
                    data-saldo-local="{{ $doc->SALDO_LOCAL ?? $doc->SALDO }}"
                    data-monto-dolar="{{ $doc->MONTO_DOLAR ?? $doc->MONTO }}"
                    data-saldo-dolar="{{ $doc->SALDO_DOLAR ?? $doc->SALDO }}">
                    <td>{{ $doc->NCF ?? '-' }}</td>
                    <td><strong>{{ $doc->DOCUMENTO }}</strong></td>
                    <td>{{ $doc->TIPO_NOMBRE }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($doc->FECHA_DOCUMENTO)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @if($doc->FECHA_VENCE)
                            {{ \Carbon\Carbon::parse($doc->FECHA_VENCE)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $doc->APLICACION ?? '-' }}</td>
                    <td class="text-center">
                        <strong>{{ $doc->MONEDA ?? 'N/A' }}</strong>
                    </td>
                    <td class="text-right">
                        {{ $doc->TIPO_CAMBIO_DOLAR ? number_format($doc->TIPO_CAMBIO_DOLAR, 2) : '-' }}
                    </td>
                    <td class="text-right monto-cell">
                        <span class="monto-local">RD${{ number_format(floatval($doc->MONTO_LOCAL ?? $doc->MONTO), 2) }}</span>
                        <span class="monto-dolar" style="display:none;">${{ number_format(floatval($doc->MONTO_DOLAR ?? $doc->MONTO), 2) }}</span>
                    </td>
                    <td class="text-right saldo-cell" style="color: {{ floatval($doc->SALDO) > 0 ? 'red' : 'green' }}; font-weight: bold;">
                        <span class="saldo-local">RD${{ number_format(floatval($doc->SALDO_LOCAL ?? $doc->SALDO), 2) }}</span>
                        <span class="saldo-dolar" style="display:none;">${{ number_format(floatval($doc->SALDO_DOLAR ?? $doc->SALDO), 2) }}</span>
                    </td>
                    <td class="text-center">
                        @if(floatval($doc->SALDO) <= 0)
                            <span class="badge-success">Pagado</span>
                        @elseif($diasVencido > 0)
                            <span class="badge-danger">Vencido</span>
                        @else
                            <span class="badge-warning">Pendiente</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #666;">
                        No se encontraron documentos para este cliente en el período seleccionado
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Total Documentos:</td>
                <td class="amount" id="total-monto">RD${{ number_format($totalMontoLocal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Saldo Pendiente:</td>
                <td class="amount" id="total-saldo" style="color: red;">RD${{ number_format($totalSaldoLocal, 2) }}</td>
            </tr>
            @if(isset($totalPendiente))
            <tr>
                <td class="label">Total a Pagar:</td>
                <td class="amount" style="background-color: #fff3cd; font-size: 12px;">
                    <strong id="total-pagar">RD${{ number_format($totalPendiente, 2) }}</strong>
                </td>
            </tr>
            @endif
            @if(isset($saldoAnterior) && $saldoAnterior > 0)
            <tr>
                <td class="label">Saldo Anterior:</td>
                <td class="amount" id="saldo-anterior">RD${{ number_format($saldoAnterior, 2) }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td class="label" style="font-size: 12px;">SALDO TOTAL:</td>
                <td class="amount" style="background-color: #d4edda; font-size: 13px;">
                    <strong>${{ number_format($saldoAnterior + $totalPendiente, 2) }}</strong>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div style="clear: both;"></div>

    @if($documents->count() > 0)
    <!-- Análisis de Vencimiento -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <h3 style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
            ANÁLISIS DE VENCIMIENTO
        </h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                    <!-- Documentos Vencidos -->
                    <div style="border: 2px solid #dc3545; padding: 15px; background-color: #f8d7da; margin-bottom: 10px;">
                        <h4 style="margin: 0 0 15px 0; color: #721c24; text-align: center;">
                            <strong>📊 DOCUMENTOS VENCIDOS</strong>
                        </h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            @php
                                $sinVencer = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) < 0 && floatval($d->SALDO) > 0)->sum('SALDO');
                                $vencido1a30 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) >= 1 && intval($d->DIAS_VENCIDO ?? 0) <= 30 && floatval($d->SALDO) > 0)->sum('SALDO');
                                $vencido31a60 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) >= 31 && intval($d->DIAS_VENCIDO ?? 0) <= 60 && floatval($d->SALDO) > 0)->sum('SALDO');
                                $vencido61a90 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) >= 61 && intval($d->DIAS_VENCIDO ?? 0) <= 90 && floatval($d->SALDO) > 0)->sum('SALDO');
                                $vencidoMas90 = $documents->filter(fn($d) => intval($d->DIAS_VENCIDO ?? 0) > 90 && floatval($d->SALDO) > 0)->sum('SALDO');
                                $totalVencido = $vencido1a30 + $vencido31a60 + $vencido61a90 + $vencidoMas90;
                            @endphp
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Sin Vencer:</td>
                                <td style="padding: 8px; text-align: right; color: green;">${{ number_format($sinVencer, 2) }}</td>
                            </tr>
                            <tr style="background-color: #fff; border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Vencido 1 a 30 días:</td>
                                <td style="padding: 8px; text-align: right; color: #856404;">${{ number_format($vencido1a30, 2) }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Vencido 31 a 60 días:</td>
                                <td style="padding: 8px; text-align: right; color: #d39e00;">${{ number_format($vencido31a60, 2) }}</td>
                            </tr>
                            <tr style="background-color: #fff; border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Vencido 61 a 90 días:</td>
                                <td style="padding: 8px; text-align: right; color: #c82333;">${{ number_format($vencido61a90, 2) }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Vencido sobre 90 días:</td>
                                <td style="padding: 8px; text-align: right; color: #721c24; font-weight: bold;">${{ number_format($vencidoMas90, 2) }}</td>
                            </tr>
                            <tr style="background-color: #dc3545; color: white;">
                                <td style="padding: 10px; font-weight: bold; font-size: 12px;">TOTAL VENCIDO:</td>
                                <td style="padding: 10px; text-align: right; font-weight: bold; font-size: 12px;">${{ number_format($totalVencido, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                    <!-- Documentos Por Vencer -->
                    <div style="border: 2px solid #ffc107; padding: 15px; background-color: #fff3cd; margin-bottom: 10px;">
                        <h4 style="margin: 0 0 15px 0; color: #856404; text-align: center;">
                            <strong>⏰ DOCUMENTOS POR VENCER</strong>
                        </h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            @php
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

                                $totalPorVencer = $porVencer1a30 + $porVencer31a60 + $porVencer61a90 + $porVencerMas90;
                            @endphp
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Por vencer de 1 a 30 días:</td>
                                <td style="padding: 8px; text-align: right; color: #0c5460;">${{ number_format($porVencer1a30, 2) }}</td>
                            </tr>
                            <tr style="background-color: #fff; border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Por vencer de 31 a 60 días:</td>
                                <td style="padding: 8px; text-align: right; color: #0c5460;">${{ number_format($porVencer31a60, 2) }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Por vencer de 61 a 90 días:</td>
                                <td style="padding: 8px; text-align: right; color: #004085;">${{ number_format($porVencer61a90, 2) }}</td>
                            </tr>
                            <tr style="background-color: #fff; border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; font-weight: bold;">Por vencer sobre 90 días:</td>
                                <td style="padding: 8px; text-align: right; color: green;">${{ number_format($porVencerMas90, 2) }}</td>
                            </tr>
                            <tr style="background-color: #ffc107;">
                                <td style="padding: 10px; font-weight: bold; font-size: 12px;">TOTAL POR VENCER:</td>
                                <td style="padding: 10px; text-align: right; font-weight: bold; font-size: 12px;">${{ number_format($totalPorVencer, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Resumen Final -->
        <div style="margin-top: 20px; padding: 15px; background-color: #e9ecef; border: 2px solid #333; text-align: center;">
            <table style="width: 100%; margin: auto; max-width: 500px;">
                <tr>
                    <td style="padding: 8px; text-align: right; font-size: 14px;"><strong>Total Vencido:</strong></td>
                    <td style="padding: 8px; text-align: right; font-size: 14px; color: red; font-weight: bold;">${{ number_format($totalVencido, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; text-align: right; font-size: 14px;"><strong>Total Por Vencer:</strong></td>
                    <td style="padding: 8px; text-align: right; font-size: 14px; color: orange; font-weight: bold;">${{ number_format($totalPorVencer, 2) }}</td>
                </tr>
                <tr style="border-top: 2px solid #333;">
                    <td style="padding: 10px; text-align: right; font-size: 15px;"><strong>SALDO TOTAL:</strong></td>
                    <td style="padding: 10px; text-align: right; font-size: 16px; color: #000; font-weight: bold;">${{ number_format($totalVencido + $totalPorVencer, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Este documento es una representación impresa del estado de cuenta del cliente.</p>
        <p>Sistema de Gestión Hyplast - {{ now()->format('Y') }}</p>
    </div>

    <script>
        // Cambiar tipo de moneda en impresión
        document.getElementById('tipo_moneda').addEventListener('change', function() {
            const tipoMoneda = this.value;
            const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

            // Obtener datos almacenados para totales
            let totalMonto = 0;
            let totalSaldo = 0;

            // Cambiar visualización de montos en la tabla
            document.querySelectorAll('.document-table tbody tr').forEach(function(row) {
                const montoLocal = parseFloat(row.dataset.montoLocal) || 0;
                const saldoLocal = parseFloat(row.dataset.saldoLocal) || 0;
                const montoDolar = parseFloat(row.dataset.montoDolar) || 0;
                const saldoDolar = parseFloat(row.dataset.saldoDolar) || 0;

                if (tipoMoneda === 'dolar') {
                    // Mostrar en dólares
                    row.querySelectorAll('.monto-local').forEach(el => el.style.display = 'none');
                    row.querySelectorAll('.saldo-local').forEach(el => el.style.display = 'none');
                    row.querySelectorAll('.monto-dolar').forEach(el => el.style.display = 'inline');
                    row.querySelectorAll('.saldo-dolar').forEach(el => el.style.display = 'inline');

                    totalMonto += montoDolar;
                    totalSaldo += saldoDolar;
                } else {
                    // Mostrar en moneda local
                    row.querySelectorAll('.monto-dolar').forEach(el => el.style.display = 'none');
                    row.querySelectorAll('.saldo-dolar').forEach(el => el.style.display = 'none');
                    row.querySelectorAll('.monto-local').forEach(el => el.style.display = 'inline');
                    row.querySelectorAll('.saldo-local').forEach(el => el.style.display = 'inline');

                    totalMonto += montoLocal;
                    totalSaldo += saldoLocal;
                }
            });

            // Actualizar totales
            const formatNumber = (num) => {
                return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            };

            document.getElementById('total-monto').textContent = prefix + formatNumber(totalMonto);
            document.getElementById('total-saldo').textContent = prefix + formatNumber(totalSaldo);

            // Actualizar total a pagar si existe
            const totalPagar = document.getElementById('total-pagar');
            if (totalPagar) {
                totalPagar.textContent = prefix + formatNumber(totalSaldo);
            }
        });

        // Auto imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
