# Hyplast Estado de Cuentas - Consultas Softland

## Descripción
Sistema para consultar estados de cuenta de clientes desde Softland, con visualización de facturas, pagos y saldos pendientes.

## Características Principales
- 💰 Estado de cuenta por cliente
- 📊 Visualización de facturas
- 💳 Registro de pagos
- 📈 Análisis de antigüedad
- 💱 Soporte multi-moneda
- 📄 Generación de PDFs
- 🔍 Búsqueda avanzada

## Modelos Principales
- **Customer**: Clientes
- **Factura**: Facturas
- **FacturaLinea**: Líneas de factura
- **Moneda**: Monedas

## API Endpoints
```
GET    /api/customers/{id}/statement      # Estado de cuenta
GET    /api/customers/{id}/invoices       # Facturas del cliente
GET    /api/customers/{id}/balance        # Saldo actual
POST   /api/customers/{id}/statement/pdf  # Generar PDF
```

## Esquemas de Base de Datos
Conexión a múltiples esquemas Softland:
- C01, C02, C03, etc. (por empresa)

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x
- Conexión a Softland SQL Server

## Instalación
```bash
composer install
php artisan migrate
```

## Configuración
```env
SOFTLAND_DB_HOST=servidor_softland
SOFTLAND_DB_DATABASE=softland
SOFTLAND_DB_USERNAME=usuario
SOFTLAND_DB_PASSWORD=password
```

## Autor y Propietario
**Néstor Serrano**  
Desarrollador Full Stack  
GitHub: [@nestorserrano](https://github.com/nestorserrano)

## Licencia
Propietario - © 2026 Néstor Serrano. Todos los derechos reservados.
