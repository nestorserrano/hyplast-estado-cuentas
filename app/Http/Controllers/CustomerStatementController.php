<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DocumentoCC;
use App\Models\AsientoDiario;
use App\Models\Diario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\SchemaHelper;

class CustomerStatementController extends Controller
{
    /**
     * Mostrar formulario de búsqueda de cliente
     */
    public function index()
    {
        $user = auth()->user();

        // Si es cliente, redirigir directamente a su estado de cuenta
        if ($user->hasRole('customer')) {
            if (!$user->cliente_codigo) {
                return back()->with('error', 'Su usuario no tiene un código de cliente asignado. Contacte al administrador.');
            }
            return redirect()->route('customer-statement.show', $user->cliente_codigo);
        }

        // Para otros roles, mostrar formulario de búsqueda
        return view('customer-statement.home');
    }

    /**
     * Buscar clientes por código o nombre
     */
    public function searchCustomers(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $schema = SchemaHelper::getSchema();

            $customers = DB::connection('softland')
                ->table("{$schema}.CLIENTE")
                ->where('ACTIVO', 'S')
                ->where(function($query) use ($search) {
                    $query->where('CLIENTE', 'like', "%{$search}%")
                          ->orWhere('NOMBRE', 'like', "%{$search}%");
                })
                ->select('CLIENTE', 'NOMBRE', 'TELEFONO1', 'E_MAIL', 'SALDO')
                ->limit(20)
                ->get();

            return response()->json($customers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar clientes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener datos del cliente y documentos
     */
    public function show($customerCode, Request $request)
    {
        try {
            $user = auth()->user();

            // Si es cliente, verificar que solo acceda a su propio código
            if ($user->hasRole('customer')) {
                if (!$user->cliente_codigo) {
                    return redirect()->route('home')->with('error', 'Su usuario no tiene un código de cliente asignado.');
                }

                if ($user->cliente_codigo !== $customerCode) {
                    abort(403, 'No está autorizado para ver este estado de cuenta.');
                }
            }

            $schema = SchemaHelper::getSchema();

            $customer = DB::connection('softland')
                ->table("{$schema}.CLIENTE")
                ->where('CLIENTE', $customerCode)
                ->select('CLIENTE', 'NOMBRE', 'CONTACTO', 'TELEFONO1', 'E_MAIL', 'SALDO', 'SALDO_LOCAL', 'SALDO_DOLAR')
                ->first();

            if (!$customer) {
                return back()->with('error', 'Cliente no encontrado');
            }

            // Obtener fechas del request
            $fechaDesde = $request->get('fecha_desde');
            $fechaHasta = $request->get('fecha_hasta');

            // Calcular saldo anterior (documentos antes de fecha_desde con saldo pendiente)
            $saldoAnterior = 0;
            if ($fechaDesde) {
                $saldoAnterior = DB::connection('softland')
                    ->table("{$schema}.DOCUMENTOS_CC")
                    ->where('CLIENTE', $customerCode)
                    ->where('FECHA_DOCUMENTO', '<', $fechaDesde)
                    ->where('SALDO', '>', 0)
                    ->where(function($query) {
                        $query->whereNull('ANULADO')
                              ->orWhere('ANULADO', '!=', 'S');
                    })
                    ->sum('SALDO');
            }

            // Query base de documentos
            $query = DB::connection('softland')
                ->table("{$schema}.DOCUMENTOS_CC as doc")
                ->leftJoin("{$schema}.NCF_DOCUMENTO as ncf", 'doc.RowPointer', '=', 'ncf.DOC_RowPointer')
                ->where('doc.CLIENTE', $customerCode)
                ->where(function($q) {
                    $q->whereNull('doc.ANULADO')
                      ->orWhere('doc.ANULADO', '!=', 'S');
                });

            // Aplicar filtros de fecha si existen
            if ($fechaDesde) {
                $query->where('doc.FECHA_DOCUMENTO', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->where('doc.FECHA_DOCUMENTO', '<=', $fechaHasta);
            }

            // Obtener documentos
            $documents = $query->select(
                    'doc.DOCUMENTO',
                    'doc.TIPO',
                    'doc.FECHA_DOCUMENTO',
                    'doc.FECHA_VENCE',
                    'doc.MONTO',
                    'doc.SALDO',
                    'doc.MONTO_DOLAR',
                    'doc.SALDO_DOLAR',
                    'doc.MONTO_LOCAL',
                    'doc.SALDO_LOCAL',
                    'doc.TIPO_CAMBIO_DOLAR',
                    'doc.MONEDA',
                    'doc.APLICACION',
                    'doc.ASIENTO',
                    'ncf.NCF',
                    DB::raw("CASE
                        WHEN doc.TIPO = 'FAC' THEN 'Factura'
                        WHEN doc.TIPO = 'DEV' THEN 'Devolución'
                        WHEN doc.TIPO = 'REC' THEN 'Recibo'
                        WHEN doc.TIPO = 'DEP' THEN 'Depósito'
                        WHEN doc.TIPO = 'N/C' THEN 'Nota de Crédito'
                        WHEN doc.TIPO = 'N/D' THEN 'Nota de Débito'
                        WHEN doc.TIPO = 'O/D' THEN 'Otro Débito'
                        WHEN doc.TIPO = 'O/C' THEN 'Otro Crédito'
                        ELSE doc.TIPO
                    END as TIPO_NOMBRE"),
                    DB::raw("DATEDIFF(day, doc.FECHA_VENCE, GETDATE()) as DIAS_VENCIDO")
                )
                ->orderBy('doc.FECHA_DOCUMENTO', 'desc')
                ->get();

            // Calcular total de documentos pendientes (con saldo > 0)
            $totalPendiente = $documents->where('SALDO', '>', 0)->sum('SALDO');

            return view('customer-statement.home', compact('customer', 'documents', 'saldoAnterior', 'totalPendiente', 'fechaDesde', 'fechaHasta'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener los datos: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle del asiento contable
     */
    public function showAsiento($asiento)
    {
        $schema = SchemaHelper::getSchema();

        // Intentar buscar en ASIENTO_DE_DIARIO (asientos no mayorizados)
        $asientoData = DB::connection('softland')
            ->table("{$schema}.ASIENTO_DE_DIARIO")
            ->where('ASIENTO', $asiento)
            ->first();

        $tipoAsiento = null;
        $detalles = collect();

        if ($asientoData) {
            // Asiento encontrado en DIARIO
            $tipoAsiento = 'DIARIO';

            // Obtener detalles del asiento con campos de moneda
            $detalles = DB::connection('softland')
                ->table("{$schema}.DIARIO")
                ->where('ASIENTO', $asiento)
                ->select(
                    'CUENTA_CONTABLE',
                    'CENTRO_COSTO',
                    'REFERENCIA as CONCEPTO',
                    'DEBITO_LOCAL',
                    'CREDITO_LOCAL',
                    'DEBITO_DOLAR',
                    'CREDITO_DOLAR',
                    'TIPO_CAMBIO',
                    'FUENTE as DOCUMENTO',
                    'NIT as ORIGEN'
                )
                ->get();
        } else {
            // Si no está en DIARIO, buscar en ASIENTO_MAYORIZADO
            $asientoData = DB::connection('softland')
                ->table("{$schema}.ASIENTO_MAYORIZADO")
                ->where('ASIENTO', $asiento)
                ->first();

            if ($asientoData) {
                // Asiento encontrado en MAYOR
                $tipoAsiento = 'MAYORIZADO';

                // Obtener detalles del MAYOR
                $detalles = DB::connection('softland')
                    ->table("{$schema}.MAYOR")
                    ->where('ASIENTO', $asiento)
                    ->select(
                        'CUENTA_CONTABLE',
                        'CENTRO_COSTO',
                        'REFERENCIA as CONCEPTO',
                        'DEBITO_LOCAL',
                        'CREDITO_LOCAL',
                        'DEBITO_DOLAR',
                        'CREDITO_DOLAR',
                        'TIPO_CAMBIO',
                        'FUENTE as DOCUMENTO',
                        'NIT as ORIGEN'
                    )
                    ->get();
            }
        }

        if (!$asientoData) {
            return back()->with('error', 'Asiento no encontrado en DIARIO ni en MAYOR');
        }

        return view('customer-statement.asiento', compact('asientoData', 'detalles', 'tipoAsiento'));
    }

    /**
     * Imprimir estado de cuenta
     */
    public function print(Request $request)
    {
        try {
            $user = auth()->user();
            $customerCode = $request->get('customer');

            // Si es cliente, validar que solo imprima su propio código
            if ($user->hasRole('customer')) {
                if (!$user->cliente_codigo) {
                    return back()->with('error', 'Su usuario no tiene un código de cliente asignado.');
                }

                if ($user->cliente_codigo !== $customerCode) {
                    abort(403, 'No está autorizado para imprimir este estado de cuenta.');
                }
            }

            $schema = SchemaHelper::getSchema();
            $customerCode = $request->get('customer');
            $fechaDesde = $request->get('fecha_desde');
            $fechaHasta = $request->get('fecha_hasta');

            $customer = DB::connection('softland')
                ->table("{$schema}.CLIENTE")
                ->where('CLIENTE', $customerCode)
                ->first();

            if (!$customer) {
                return back()->with('error', 'Cliente no encontrado');
            }

            // Calcular saldo anterior
            $saldoAnterior = 0;
            if ($fechaDesde) {
                $saldoAnterior = DB::connection('softland')
                    ->table("{$schema}.DOCUMENTOS_CC")
                    ->where('CLIENTE', $customerCode)
                    ->where('FECHA_DOCUMENTO', '<', $fechaDesde)
                    ->where('SALDO', '>', 0)
                    ->where(function($query) {
                        $query->whereNull('ANULADO')
                              ->orWhere('ANULADO', '!=', 'S');
                    })
                    ->sum('SALDO');
            }

            // Query de documentos
            $query = DB::connection('softland')
                ->table("{$schema}.DOCUMENTOS_CC as doc")
                ->leftJoin("{$schema}.NCF_DOCUMENTO as ncf", 'doc.RowPointer', '=', 'ncf.DOC_RowPointer')
                ->where('doc.CLIENTE', $customerCode)
                ->where(function($q) {
                    $q->whereNull('doc.ANULADO')
                      ->orWhere('doc.ANULADO', '!=', 'S');
                });

            if ($fechaDesde) {
                $query->where('doc.FECHA_DOCUMENTO', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->where('doc.FECHA_DOCUMENTO', '<=', $fechaHasta);
            }

            $documents = $query->select(
                    'doc.DOCUMENTO',
                    'doc.TIPO',
                    'doc.FECHA_DOCUMENTO',
                    'doc.FECHA_VENCE',
                    'doc.MONTO',
                    'doc.SALDO',
                    'doc.MONTO_DOLAR',
                    'doc.SALDO_DOLAR',
                    'doc.MONTO_LOCAL',
                    'doc.SALDO_LOCAL',
                    'doc.TIPO_CAMBIO_DOLAR',
                    'doc.MONEDA',
                    'doc.APLICACION',
                    'doc.ASIENTO',
                    'ncf.NCF',
                    DB::raw("CASE
                        WHEN doc.TIPO = 'FAC' THEN 'Factura'
                        WHEN doc.TIPO = 'DEV' THEN 'Devolución'
                        WHEN doc.TIPO = 'REC' THEN 'Recibo'
                        WHEN doc.TIPO = 'DEP' THEN 'Depósito'
                        WHEN doc.TIPO = 'N/C' THEN 'Nota de Crédito'
                        WHEN doc.TIPO = 'N/D' THEN 'Nota de Débito'
                        WHEN doc.TIPO = 'O/D' THEN 'Otro Débito'
                        WHEN doc.TIPO = 'O/C' THEN 'Otro Crédito'
                        ELSE doc.TIPO
                    END as TIPO_NOMBRE"),
                    DB::raw("DATEDIFF(day, doc.FECHA_VENCE, GETDATE()) as DIAS_VENCIDO")
                )
                ->orderBy('doc.FECHA_DOCUMENTO', 'desc')
                ->get();

            $totalPendiente = $documents->where('SALDO', '>', 0)->sum('SALDO');

            return view('customer-statement.print', compact('customer', 'documents', 'saldoAnterior', 'totalPendiente', 'fechaDesde', 'fechaHasta'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar impresión: ' . $e->getMessage());
        }
    }

    /**
     * Exportar estado de cuenta a Excel
     */
    public function export(Request $request)
    {
        try {
            $user = auth()->user();
            $customerCode = $request->get('customer');

            // Si es cliente, validar que solo exporte su propio código
            if ($user->hasRole('customer')) {
                if (!$user->cliente_codigo) {
                    return back()->with('error', 'Su usuario no tiene un código de cliente asignado.');
                }

                if ($user->cliente_codigo !== $customerCode) {
                    abort(403, 'No está autorizado para exportar este estado de cuenta.');
                }
            }

            $schema = SchemaHelper::getSchema();
            $customerCode = $request->get('customer');
            $fechaDesde = $request->get('fecha_desde');
            $fechaHasta = $request->get('fecha_hasta');

            $customer = DB::connection('softland')
                ->table("{$schema}.CLIENTE")
                ->where('CLIENTE', $customerCode)
                ->first();

            if (!$customer) {
                return back()->with('error', 'Cliente no encontrado');
            }

            // Query de documentos
            $query = DB::connection('softland')
                ->table("{$schema}.DOCUMENTOS_CC as doc")
                ->leftJoin("{$schema}.NCF_DOCUMENTO as ncf", 'doc.RowPointer', '=', 'ncf.DOC_RowPointer')
                ->where('doc.CLIENTE', $customerCode)
                ->where(function($q) {
                    $q->whereNull('doc.ANULADO')
                      ->orWhere('doc.ANULADO', '!=', 'S');
                });

            if ($fechaDesde) {
                $query->where('doc.FECHA_DOCUMENTO', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->where('doc.FECHA_DOCUMENTO', '<=', $fechaHasta);
            }

            $documents = $query->select(
                    'ncf.NCF',
                    'doc.DOCUMENTO',
                    'doc.TIPO',
                    'doc.FECHA_DOCUMENTO',
                    'doc.FECHA_VENCE',
                    'doc.APLICACION as CONCEPTO',
                    'doc.MONTO',
                    'doc.SALDO',
                    'doc.MONTO_DOLAR',
                    'doc.SALDO_DOLAR',
                    'doc.MONTO_LOCAL',
                    'doc.SALDO_LOCAL',
                    'doc.TIPO_CAMBIO_DOLAR',
                    'doc.MONEDA'
                )
                ->orderBy('doc.FECHA_DOCUMENTO', 'desc')
                ->get();

            // Crear CSV
            $filename = 'estado_cuenta_' . $customerCode . '_' . date('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($documents, $customer) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Encabezado del reporte
                fputcsv($file, ['ESTADO DE CUENTA DE CLIENTE'], ';');
                fputcsv($file, ['Cliente:', $customer->CLIENTE . ' - ' . $customer->NOMBRE], ';');
                fputcsv($file, ['Fecha:', date('d/m/Y H:i:s')], ';');
                fputcsv($file, [], ';');

                // Encabezados de columnas
                fputcsv($file, ['NCF', 'Documento', 'Tipo', 'Fecha Doc.', 'Fecha Vence', 'Concepto', 'Moneda', 'Tipo Cambio', 'Monto Local', 'Saldo Local', 'Monto Dólar', 'Saldo Dólar'], ';');

                // Datos
                $totalMontoLocal = 0;
                $totalSaldoLocal = 0;
                $totalMontoDolar = 0;
                $totalSaldoDolar = 0;

                foreach ($documents as $doc) {
                    $totalMontoLocal += floatval($doc->MONTO_LOCAL ?? $doc->MONTO);
                    $totalSaldoLocal += floatval($doc->SALDO_LOCAL ?? $doc->SALDO);
                    $totalMontoDolar += floatval($doc->MONTO_DOLAR ?? $doc->MONTO);
                    $totalSaldoDolar += floatval($doc->SALDO_DOLAR ?? $doc->SALDO);

                    fputcsv($file, [
                        $doc->NCF ?? '',
                        $doc->DOCUMENTO,
                        $doc->TIPO,
                        date('d/m/Y', strtotime($doc->FECHA_DOCUMENTO)),
                        $doc->FECHA_VENCE ? date('d/m/Y', strtotime($doc->FECHA_VENCE)) : '',
                        $doc->CONCEPTO ?? '',
                        $doc->MONEDA ?? '',
                        $doc->TIPO_CAMBIO_DOLAR ? number_format($doc->TIPO_CAMBIO_DOLAR, 2) : '',
                        number_format($doc->MONTO_LOCAL ?? $doc->MONTO, 2),
                        number_format($doc->SALDO_LOCAL ?? $doc->SALDO, 2),
                        number_format($doc->MONTO_DOLAR ?? $doc->MONTO, 2),
                        number_format($doc->SALDO_DOLAR ?? $doc->SALDO, 2)
                    ], ';');
                }

                // Totales
                fputcsv($file, [], ';');
                fputcsv($file, ['', '', '', '', '', '', '', 'TOTALES:', number_format($totalMontoLocal, 2), number_format($totalSaldoLocal, 2), number_format($totalMontoDolar, 2), number_format($totalSaldoDolar, 2)], ';');

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Obtener pagos relacionados a un documento desde AUXILIAR_CC
     */
    public function getPayments(Request $request)
    {
        try {
            // Log para debugging
            \Log::info('getPayments llamado', [
                'documento' => $request->get('documento'),
                'tipo' => $request->get('tipo'),
                'cliente' => $request->get('cliente'),
                'moneda' => $request->get('moneda')
            ]);

            $user = auth()->user();
            $documento = $request->get('documento');
            $tipo = $request->get('tipo');
            $cliente = $request->get('cliente');
            $moneda = $request->get('moneda', 'local'); // Por defecto local

            // Validar que cliente solo acceda a su código
            if ($user->hasRole('customer')) {
                if (!$user->cliente_codigo) {
                    return response()->json(['success' => false, 'message' => 'Usuario sin código de cliente'], 403);
                }
                if ($user->cliente_codigo !== $cliente) {
                    return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
                }
            }

            $schema = SchemaHelper::getSchema();

            // Determinar qué campo de monto usar según la moneda
            $campoMonto = $moneda === 'dolar' ? 'aux.MONTO_DOLAR' : 'aux.MONTO_LOCAL';

            // Buscar en AUXILIAR_CC los pagos relacionados
            // Basado en la estructura: DEBITO/TIPO_DEBITO y CREDITO/TIPO_CREDITO
            $pagos = collect();

            // Para facturas y notas de débito, buscar los pagos (créditos) aplicados
            if (in_array($tipo, ['FAC', 'N/D'])) {
                $pagos = DB::connection('softland')
                    ->table("{$schema}.AUXILIAR_CC as aux")
                    ->leftJoin("{$schema}.DOCUMENTOS_CC as doc", function($join) {
                        $join->on('aux.CREDITO', '=', 'doc.DOCUMENTO')
                             ->on('aux.TIPO_CREDITO', '=', 'doc.TIPO');
                    })
                    ->leftJoin("{$schema}.NCF_DOCUMENTO as ncf", 'doc.RowPointer', '=', 'ncf.DOC_RowPointer')
                    ->where('aux.DEBITO', $documento)
                    ->where('aux.TIPO_DEBITO', $tipo)
                    ->select(
                        'aux.CREDITO as DOCUMENTO_PAGO',
                        'aux.TIPO_CREDITO as TIPO',
                        'aux.FECHA',
                        DB::raw("{$campoMonto} as DEBITO"),
                        DB::raw('0 as CREDITO'),
                        'aux.MONTO_DOLAR',
                        'aux.MONTO_LOCAL',
                        'aux.ASIENTO',
                        'ncf.NCF',
                        DB::raw("CASE
                            WHEN aux.TIPO_CREDITO = 'REC' THEN 'Recibo'
                            WHEN aux.TIPO_CREDITO = 'DEP' THEN 'Depósito'
                            WHEN aux.TIPO_CREDITO = 'N/C' THEN 'Nota de Crédito'
                            WHEN aux.TIPO_CREDITO = 'O/C' THEN 'Otro Crédito'
                            WHEN aux.TIPO_CREDITO = 'TEF' THEN 'Transferencia'
                            ELSE aux.TIPO_CREDITO
                        END as TIPO_NOMBRE"),
                        'aux.DEBITO as APLICADO_A'
                    )
                    ->orderBy('aux.FECHA', 'desc')
                    ->get();
            }
            // Para pagos/créditos (REC, DEP, N/C, O/C, TEF), buscar las facturas a las que se aplicaron
            else {
                $pagos = DB::connection('softland')
                    ->table("{$schema}.AUXILIAR_CC as aux")
                    ->leftJoin("{$schema}.DOCUMENTOS_CC as doc", function($join) {
                        $join->on('aux.DEBITO', '=', 'doc.DOCUMENTO')
                             ->on('aux.TIPO_DEBITO', '=', 'doc.TIPO');
                    })
                    ->leftJoin("{$schema}.NCF_DOCUMENTO as ncf", 'doc.RowPointer', '=', 'ncf.DOC_RowPointer')
                    ->where('aux.CREDITO', $documento)
                    ->where('aux.TIPO_CREDITO', $tipo)
                    ->select(
                        'aux.CREDITO as DOCUMENTO_PAGO',
                        'aux.TIPO_CREDITO as TIPO',
                        'aux.FECHA',
                        DB::raw('0 as DEBITO'),
                        DB::raw("{$campoMonto} as CREDITO"),
                        'aux.MONTO_DOLAR',
                        'aux.MONTO_LOCAL',
                        'aux.ASIENTO',
                        'ncf.NCF',
                        DB::raw("CASE
                            WHEN aux.TIPO_CREDITO = 'REC' THEN 'Recibo'
                            WHEN aux.TIPO_CREDITO = 'DEP' THEN 'Depósito'
                            WHEN aux.TIPO_CREDITO = 'N/C' THEN 'Nota de Crédito'
                            WHEN aux.TIPO_CREDITO = 'O/C' THEN 'Otro Crédito'
                            WHEN aux.TIPO_CREDITO = 'TEF' THEN 'Transferencia'
                            ELSE aux.TIPO_CREDITO
                        END as TIPO_NOMBRE"),
                        'aux.DEBITO as APLICADO_A'
                    )
                    ->orderBy('aux.FECHA', 'desc')
                    ->get();
            }

            \Log::info('Pagos encontrados', [
                'count' => $pagos->count(),
                'pagos' => $pagos->toArray()
            ]);

            return response()->json([
                'success' => true,
                'pagos' => $pagos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos: ' . $e->getMessage()
            ], 500);
        }
    }
}

