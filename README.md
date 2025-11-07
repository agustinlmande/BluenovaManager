BLUENOVA MANAGER — DOCUMENTACIÓN TÉCNICA
Versión: 1.0
Autor: Agustín Leonardo Mande
Fecha: Noviembre 2025
Tecnología base: Laravel 12.36.1 / PHP 8.2.12 / MySQL
1. Resumen del sistema
Bluenova Manager es un sistema de gestión para emprendimientos pequeños/medianos. Soporta: productos, compras (registro de compras con envío por producto), ventas (con vendedor/comisión), historial de precios, caja (entradas/salidas), reportes y gestiones básicas (categorías, proveedores en compras, vendedores).

Principales módulos:
- Productos
- Compras (con detalle y envío por artículo)
- Ventas (con comisiones por vendedor; envío no entra en comisión)
- Caja (registro de ingresos/egresos)
- Historial de precios / cotizaciones
- Reportes
2. Requisitos
• PHP 8.1+ (probado en 8.2.12)
• Composer
• Node / npm (para assets si corresponde)
• MySQL / MariaDB
• Laravel 12.x (versión usada: 12.36.1)
• Git
3. Instalación (local)
1. Clonar repositorio:
   git clone https://github.com/agustinlmande/BluenovaManager.git
   cd BluenovaManager

2. Instalar dependencias PHP:
   composer install

3. Instalar dependencias JS:
   npm install && npm run dev

4. Copiar y configurar .env:
   cp .env.example .env
   php artisan key:generate

5. Importar base de datos:
   mysql -u root -p bluenova_db < /ruta/al/bluenova_db.sql

6. Levantar servidor:
   php artisan serve
   Acceder en http://127.0.0.1:8000
4. Estructura del proyecto
Controladores principales:
- ProductoController
- CompraController
- VentaController

Vistas:
- resources/views/productos/
- resources/views/compras/
- resources/views/ventas/
- layouts/app.blade.php

Rutas principales en routes/web.php:
- /productos, /compras, /ventas
5. Base de datos — resumen esquemático
Tablas principales:
- productos (precio_compra_usd, precio_venta_ars, envio_ars, etc.)
- compras y detalle_compras (incluye envio_ars por producto)
- ventas y detalle_ventas
- caja (ingresos/egresos)
- historial_precios y cotizacion_dolars
6. Manual de usuario (principales acciones)
• Registrar una compra: completar proveedor, fecha, cotización, productos, envío.
• Editar compra: modificar detalle, precios, envío.
• Registrar una venta: elegir productos, vendedor, tipo de entrega, costo de envío.
• Editar producto: actualizar precios y cotización.
• Consultar reportes: ventas, compras, caja, productos más rentables.
7. Reportes útiles (SQL)
Ejemplos:
- Ventas por mes
- Compras por proveedor
- Productos con mayor ganancia
- Caja (saldo actual)

Cada consulta puede ejecutarse directamente desde phpMyAdmin o exportarse a Excel.
8. Buenas prácticas y recomendaciones
• Guardar envío como costo por unidad.
• No aplicar comisión sobre envío.
• Mantener cotizaciones actualizadas.
• Registrar movimientos de caja correctamente.
• Documentar cambios de versión.
9. Historial de versiones
v1.0 — Noviembre 2025
- Versión inicial completa del sistema.
- Módulos: Productos, Compras, Ventas, Caja, Reportes.
- Correcciones en cálculos de comisión y envío.
