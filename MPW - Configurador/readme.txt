* 1.10.18: el botón Agregar al carrito mantiene su apariencia y texto; después de agregar aparece únicamente el enlace "Ir al carrito para pagar →".
=== Configurator Machine ===
Contributors: fabianibanez
Author: Fabian
Tags: woocommerce, elementor, configurador, imprenta, productos personalizados
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
WC requires at least: 7.0
Stable tag: 1.10.19
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Configurador visual de productos para imprentas. El widget muestra solo la columna de opciones; cada producto se configura en su ficha de WooCommerce y el look & feel se ajusta en Elementor.

== Description ==

**MPW · Configurador** permite que tus clientes armen su producto paso a paso (formato, sustrato, terminación, impresión, cantidad y subida de su propio diseño) con precio en CLP calculado en vivo.

El widget renderiza **solo la columna de opciones**: la portada, el título y la descripción los maquetas libremente en Elementor alrededor del configurador.

Separación clara de responsabilidades, al estilo de las herramientas MPW:

* **Lógica por producto:** desde la pestaña "Configurador" de cada producto WooCommerce defines los pasos, las opciones, los rótulos (todos editables) y los precios (precio base, recargos por opción y multiplicadores por cantidad), según las características de ese producto. ¿Productos parecidos? Duplica el producto en WooCommerce y su configuración viaja con él.
* **Look & feel en Elementor:** un widget propio expone colores, tipografías, radio y relleno que se inyectan como variables CSS, sin tocar código.

El precio siempre se recalcula en el servidor: el cliente nunca define el monto. El producto se agrega al carrito con el precio configurado y las selecciones quedan registradas como metadatos del pedido, incluida la URL del archivo subido.

= Características =

* Pasos configurables de tres tipos: opciones (swatch), cantidad y subida de diseño.
* Resumen de precio con desglose: configuración base, recargos, descuento por volumen, total e importe por unidad.
* Sellos de descuento editables por tramo de cantidad (ej. -15%, -30%, -45%).
* Subida de arte por zona de arrastre (drag & drop) con enlace opcional a plantilla.
* Sellos de confianza y botón de contacto opcional, todo editable.
* Precio en vivo en CLP con formato chileno ($ 14.990) y redondeo a la decena.
* Compatibilidad con HPOS (almacenamiento de pedidos en tablas propias).
* Shortcode `[mpwcfg_configurador]` además del widget de Elementor.
* Interfaz en español, lista para traducir (text domain: mpw-configurador-productos).

== Installation ==

1. Sube la carpeta `mpw-configurador-productos` a `/wp-content/plugins/` o instala el ZIP desde Plugins → Añadir nuevo → Subir plugin.
2. Activa el plugin desde el menú "Plugins".
3. Asegúrate de tener WooCommerce activo (y Elementor si quieres usar el widget).
4. Edita un producto, abre la pestaña **Configurador**, actívalo y define sus pasos según sus características.
5. En Elementor, arrastra el widget **MPW · Configurador** junto a tu portada/título y ajusta colores y tipografías.

== Frequently Asked Questions ==

= ¿Dónde defino las opciones? =
En la pestaña **Configurador** de cada producto. Toda la lógica vive en la ficha del producto, así que se adapta a las características de cada uno.

= ¿El widget incluye la imagen y el título? =
No. El widget muestra únicamente la columna de opciones y el resumen de precio. La portada, el título y la descripción los maquetas en Elementor.

= ¿Cómo reutilizo una configuración entre productos parecidos? =
Usa "Duplicar" en WooCommerce: la configuración del producto se copia con él y solo ajustas las diferencias.

= ¿El cliente puede manipular el precio desde el navegador? =
No. El cálculo del precio es la fuente de verdad en el servidor; el navegador solo muestra una estimación que se confirma al agregar al carrito.

= ¿Necesito Elementor? =
No es obligatorio: existe el shortcode `[mpwcfg_configurador]`. El widget de Elementor solo añade el control visual del look & feel.

== Changelog ==

= 1.9.0 =
* Nuevo modo exclusivo de configurador por combinaciones.
* Selector por producto: tradicional o combinaciones.
* Niveles, opciones y matriz de precios por combinación.
* Frontend en cascada y precio recalculado en servidor.
* El configurador tradicional se conserva intacto.


= 1.8.1 =
* Renombrado de "Cactus · Configurador de Productos" a **Configurator Machine** (prefijos, clases, text domain, clases CSS y variables `--mpwcfg-*` internos; el meta key del producto ahora es `_mpwcfg_config`, manteniendo la misma meta key para conservar la configuración existente).
* Corrige la subida de diseño: el dropzone (click, arrastrar y soltar) ahora llama al endpoint de subida, valida el archivo en el cliente y muestra estados de carga/éxito/error; antes no tenía ningún listener y era decorativo.
* Corrige "Agregar al carrito": ahora se procesa la respuesta del servidor — botón deshabilitado y con texto "Agregando…" durante la petición, mensaje de éxito con enlace "Ir al carrito", y mensaje de error visible si WooCommerce rechaza el producto. Antes la petición se enviaba sin leer la respuesta.
* El archivo de diseño subido ahora viaja correctamente al agregar al carrito (antes el JS no enviaba `file_url`/`file_name`, así que nunca llegaba al pedido aunque la subida hubiera funcionado).
* Corrige el detalle de configuración en el carrito/checkout, que no escapaba el valor de cada línea antes de mostrarlo.
* Unifica el número de versión (antes convivían 1.1.1 en este changelog, 1.4.0 en el encabezado y 1.4.2 en la constante interna).

= 1.1.1 =
* La configuración es 100% por producto, según sus características: se retira el panel de opciones por categoría para evitar ambigüedad en catálogos heterogéneos.

= 1.1.0 =
* El widget pasa a mostrar **solo la columna de opciones** (sin lienzo, breadcrumb, título ni descripción): esos elementos se maquetan en Elementor.
* Resumen de precio con desglose (base, recargos, descuento por volumen, total e importe por unidad) y nota de IVA.
* Sellos de descuento editables por tramo de cantidad y sellos de confianza editables.
* Subida de diseño por zona de arrastre con enlace opcional a plantilla y botón de contacto opcional.

= 1.0.0 =
* Versión inicial: pestaña de configuración en el producto, cálculo de precios en servidor, carrito y metadatos de pedido, widget de Elementor con variables CSS, subida de diseño y vista previa en vivo.
