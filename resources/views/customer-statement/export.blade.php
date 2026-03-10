<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta - {{ $customer->NOMBRE }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            color: #333;
        }
        .customer-info {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .customer-info table {
            width: 100%;
        }
        .customer-info th {
            text-align: left;
            width: 150px;
            font-weight: bold;
        }
        .documents-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .documents-table th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        .documents-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .documents-table tr:hover {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            background-color: #f0f0f0;
            padding: 15px;
            font-weight: bold;
            border-radius: 5px;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ESTADO DE CUENTA</h1>
        <p>{{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="customer-info">
        <h3 style="margin-top: 0;">Información del Cliente</h3>
        <table>
            <tr>
                <th>Código:</th>
                <td>{{ $customer->CLIENTE }}</td>
                <th>Teléfono:</th>
                <td>{{ $customer->TELEFONO1 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Nombre:</th>
                <td>{{ $customer->NOMBRE }}</td>
                <th>Email:</th>
                <td>{{ $customer->E_MAIL ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Contacto:</th>
                <td>{{ $customer->CONTACTO ?? 'N/A' }}</td>
                <th>Balance:</th>
                <td>
                    <strong style="color: {{ $customer->BALANCE > 0 ? '#dc3545' : '#28a745' }}">
                        ${{ number_format($customer->BALANCE, 2) }}
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <h3>Detalle de Documentos</h3>
    <table class="documents-table">
        <thead>
            <tr>
                <th width="12%">NCF</th>
                <th width="10%">Documento</th>
                <th width="10%">Tipo</th>
                <th width="10%">Fecha Doc.</th>
                <th width="10%">Fecha Vence</th>
                <th width="25%">Concepto</th>
                <th width="10%" class="text-right">Monto</th>
                <th width="10%" class="text-right">Saldo</th>
                <th width="13%" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMonto = 0;
                $totalSaldo = 0;
            @endphp
            @foreach($documents as $doc)
                @php
                    $totalMonto += $doc->MONTO;
                    $totalSaldo += $doc->SALDO;
                    $diasVencido = \Carbon\Carbon::parse($doc->FECHA_VENCE)->diffInDays(now(), false);
                @endphp
                <tr>
                    <td>{{ $doc->NCF ?? '-' }}</td>
                    <td>{{ $doc->DOC_NUMERO }}</td>
                    <td>{{ $doc->TIPO_DOC }}</td>
                    <td>{{ date('d/m/Y', strtotime($doc->FECHA_DOCUMENTO)) }}</td>
                    <td>{{ date('d/m/Y', strtotime($doc->FECHA_VENCE)) }}</td>
                    <td>{{ $doc->CONCEPTO ?? '-' }}</td>
                    <td class="text-right">${{ number_format($doc->MONTO, 2) }}</td>
                    <td class="text-right" style="color: {{ $doc->SALDO > 0 ? '#dc3545' : '#28a745' }}">
                        <strong>${{ number_format($doc->SALDO, 2) }}</strong>
                    </td>
                    <td class="text-center">
                        @if($doc->SALDO <= 0)
                            <span class="badge badge-success">Pagado</span>
                        @elseif($diasVencido > 0)
                            <span class="badge badge-danger">Vencido ({{ $diasVencido }}d)</span>
                        @else
                            <span class="badge badge-warning">Pendiente</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table width="100%">
            <tr>
                <td width="70%" style="text-align: right;">TOTAL MONTO:</td>
                <td width="15%" style="text-align: right;">${{ number_format($totalMonto, 2) }}</td>
                <td width="15%"></td>
            </tr>
            <tr>
                <td style="text-align: right;">TOTAL SALDO:</td>
                <td style="text-align: right; color: {{ $totalSaldo > 0 ? '#dc3545' : '#28a745' }}">
                    <strong style="font-size: 16px;">${{ number_format($totalSaldo, 2) }}</strong>
                </td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema HYPLAST</p>
        <p>Fecha de generación: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto-imprimir al cargar
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
