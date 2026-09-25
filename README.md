# MPW - Configurador de productos

<p align="center">
  <img src="assets/screen.png" alt="MPW - Configurador de productos" width="100%">
</p>

Plugin para WordPress + Elementor + WooCommerce que permite crear experiencias de configuración de productos directamente sobre WooCommerce.

## Descripción

MPW - Configurador de productos agrega una capa de configuración avanzada sobre WooCommerce sin reemplazar su estructura de productos, carrito, checkout ni pedidos.

Dispone de dos modalidades:

- Configurador tradicional
- Combinaciones

Cada producto puede utilizar una modalidad, o funcionar sin configurador.

## Características

### Configurador tradicional

- Subida de diseños.
- Pasos y opciones configurables.
- Formatos, impresión, sustratos y terminaciones.
- Cantidad.
- Resumen de configuración.
- Cálculo de precios.
- Cantidades dinámicas.
- Reglas de precio.
- Integración con carrito y pedidos.
- WhatsApp.
- Sellos de confianza.
- Integración con Elementor.

### Combinaciones

Permite construir estructuras de opciones dependientes:

```text
Formato
  └── Cantidad de hojas
       └── Unidades
            └── Precio
```

Las opciones de cada nivel pueden depender de la selección anterior. El administrador define las combinaciones y el precio correspondiente.

## Precios dinámicos

El precio se calcula según la configuración seleccionada y las reglas de cantidad.

```text
Selección
   ↓
AJAX
   ↓
Motor de pricing
   ↓
Precio calculado
   ↓
Validación del servidor
   ↓
Resumen
   ↓
Agregar al carrito
```

Para mantener la consistencia entre el precio mostrado y el precio enviado a WooCommerce se utilizan cálculo AJAX, token de precio firmado, validación del token y persistencia temporal mediante sesión de WooCommerce.

## Integración con WooCommerce

El plugin funciona como una capa sobre WooCommerce y mantiene:

- Productos.
- Productos variables.
- Carrito.
- Checkout.
- Pedidos.
- Inventario.
- Impuestos.
- Envío.

También contempla compatibilidad con HPOS.

No existe lógica dependiente de un ID de producto específico.

## Experiencia de usuario

Las modalidades tradicional y Combinaciones comparten criterios visuales y componentes, incluyendo Dropzone, resumen y acciones principales.

Después de agregar correctamente un producto aparece:

**Ir al carrito →**

Este enlace lleva al carrito de WooCommerce.

## Arquitectura

```text
WordPress
   │
   └── WooCommerce
          │
          └── MPW - Configurador de productos
                  │
                  ├── Configurador tradicional
                  ├── Combinaciones
                  ├── Motor de pricing
                  ├── Validación servidor
                  ├── Sesión WooCommerce
                  └── Integración carrito/pedido
```

## Flujo de compra

### Configurador tradicional

```text
Subir diseño
     ↓
Formato
     ↓
Impresión
     ↓
Sustrato
     ↓
Terminación
     ↓
Cantidad
     ↓
Resumen
     ↓
Agregar al carrito
     ↓
Ir al carrito →
```

### Combinaciones

```text
Nivel 0
   ↓
Nivel 1 dependiente
   ↓
Nivel 2 dependiente
   ↓
Precio
   ↓
Resumen
   ↓
Agregar al carrito
   ↓
Ir al carrito →
```

## Backend

El administrador puede configurar:

- Selección del modo.
- Pasos.
- Niveles.
- Opciones.
- Dependencias.
- Precios.
- Cantidades.
- Apariencia.
- Sellos de confianza.
- Integración con Elementor.

Los estilos administrativos propios del plugin se mantienen aislados de los estilos globales de WooCommerce.

## Instalación

1. Descargar o clonar el repositorio.
2. Copiar el plugin en `wp-content/plugins/`.
3. Activar **MPW - Configurador de productos** desde WordPress.
4. Crear o editar un producto WooCommerce.
5. Abrir Configurator Machine.
6. Seleccionar el modo.
7. Configurar opciones y precios.
8. Guardar.
9. Revisar el producto en el frontend.

## Requisitos

- WordPress.
- WooCommerce.
- Elementor cuando se utilice su integración.
- PHP compatible con la versión de WordPress/WooCommerce utilizada.

## Licencia

**GNU GPL v2 o posterior (`GPL-2.0-or-later`)**

Copyright © 2026 Fabian Ibañez de MPW.

## Autor

**Fabian Ibañez de MPW**

## Estado

**Versión de referencia: 1.10.34**

El proyecto mantiene separadas las responsabilidades de administración, configuración, pricing, validación, presentación, carrito y pedidos.
