# MPW - Configurador de productos --- Historia técnica, arquitectura, flujo y evolución

**Proyecto:** MPW - Configurador de productos\
**Autor:** Fabian Ibañez de MPW\
**Plataforma:** WordPress + Elementor + WooCommerce\
**Tipo:** Plugin de configuración avanzada de productos\
**Versión de referencia al cierre de esta etapa:** 1.10.34
**Licencia:** GPL-2.0-or-later\
**Documento:** memoria técnica y pauta funcional

------------------------------------------------------------------------

## 1. Resumen ejecutivo

MPW - Configurador de productos nació como una capa de configuración avanzada sobre
el flujo normal de productos de WooCommerce.

La premisa fundamental del proyecto fue mantener el producto
administrable de manera convencional en WooCommerce y agregar encima una
capa de configuración específica por producto. El administrador crea y
mantiene el producto desde WooCommerce y, cuando corresponde, activa
MPW - Configurador de productos para definir la experiencia de compra.

Durante la evolución del plugin se incorporó una segunda modalidad de
configuración: **Combinaciones**. Esta modalidad permite construir
estructuras de opciones dependientes y asociar un precio a una
combinación exacta de selecciones.

El resultado final es un sistema con dos modalidades excluyentes por
producto:

1.  **Configurador tradicional**
2.  **Combinaciones**

Un producto puede funcionar sin configurador, con configurador
tradicional o con combinaciones, pero las dos modalidades no se ejecutan
simultáneamente sobre el mismo producto.

La evolución técnica se concentró en cinco objetivos:

-   mantener la compatibilidad con WooCommerce;
-   permitir configuraciones específicas por producto;
-   resolver combinaciones dependientes con precios exactos;
-   llevar correctamente el precio configurado al carrito y al pedido;
-   mantener una experiencia visual coherente entre ambas modalidades.

------------------------------------------------------------------------

# 1.1. Identificación y licencia

**Nombre:** MPW - Configurador de productos  
**Autor:** Fabian Ibañez de MPW  
**Plataforma:** WordPress + Elementor + WooCommerce  
**Licencia:** GNU GPL v2 o posterior (`GPL-2.0-or-later`)

La licencia se declara en el encabezado principal del plugin. La autoría y los avisos de copyright se mantienen aunque el código sea modificado o redistribuido conforme a los términos de la GPL.

---

# 2. Historia del plugin

## 2.1. Primera etapa: configurador tradicional

La primera versión estable del sistema se orientó a resolver productos
configurables sin reemplazar WooCommerce.

La arquitectura se apoyó en una configuración almacenada por producto
mediante:

`_mpwcfg_config`

Esto permitió que cada producto tuviera sus propias reglas y opciones.

El configurador tradicional incorporó progresivamente:

-   pasos de configuración;
-   opciones por paso;
-   cantidades;
-   reglas de precio;
-   carga de archivos;
-   persistencia de configuración;
-   integración con carrito;
-   persistencia de datos en pedido;
-   integración con WooCommerce;
-   compatibilidad con HPOS;
-   integración con Elementor;
-   configuración de apariencia;
-   acciones posteriores al agregado al carrito;
-   botón de WhatsApp;
-   contenido de confianza.

La versión **1.8.1** fue utilizada como una primera base estable. En
ella se validó el flujo completo de compra.

------------------------------------------------------------------------

## 2.2. El problema que originó Combinaciones

El configurador tradicional funciona bien cuando las opciones pueden
evaluarse como pasos independientes o mediante reglas generales.

Sin embargo, apareció una necesidad diferente:

> El precio debía depender de una combinación exacta de opciones.

Por ejemplo, en un producto de tipo talonario:

-   Formato
-   Cantidad de hojas
-   Unidades

No todas las cantidades disponibles tenían que existir para todos los
formatos.

Por ejemplo:

  Formato        Hojas      Unidades disponibles
  -------------- ---------- ----------------------
  7,5 × 7,5 cm   50 / 100   8 / 16 / 32 / 64
  10 × 15 cm     50 / 100   9 / 18 / 36 / 54
  15 × 21 cm     50 / 100   2 / 10 / 30 / 50

Por lo tanto, una matriz cartesiana simple no era suficiente.

La solución fue crear un sistema de **perfiles dependientes por nivel**.

------------------------------------------------------------------------

# 3. Nueva arquitectura: modalidad Combinaciones

La modalidad Combinaciones se diseñó como una estructura jerárquica:

``` text
Nivel 0
└── Opción / Formato
    └── Nivel 1
        └── Opción / Cantidad
            └── Nivel 2
                └── Opción / Unidades
                    └── Precio
```

La estructura es genérica.

No está limitada a:

-   talonarios;
-   formatos;
-   hojas;
-   unidades.

El administrador define los nombres y las opciones.

Por ejemplo:

``` text
Nivel 0: Formato
Nivel 1: Cantidad de hojas
Nivel 2: Unidades
```

pero también podría ser:

``` text
Nivel 0: Material
Nivel 1: Grosor
Nivel 2: Cantidad
```

o:

``` text
Nivel 0: Producto
Nivel 1: Acabado
Nivel 2: Tiraje
```

La estructura pertenece al producto y no está hardcodeada.

------------------------------------------------------------------------

# 4. Administración de combinaciones

La interfaz de administración permite definir:

### Niveles

-   nombre;
-   orden;
-   tipo de selección;
-   opciones disponibles.

### Dependencias

Cada opción de un nivel puede tener su propio conjunto de opciones para
el nivel siguiente.

Esto evita obligar a todos los formatos a utilizar las mismas
cantidades.

### Precios

Cada combinación final tiene un precio asociado.

El precio pertenece a la combinación y no a una opción aislada.

Conceptualmente:

``` text
Formato A
 ├── Opción nivel 1
 │    ├── Unidad 1 → $X
 │    ├── Unidad 2 → $Y
 │    └── Unidad 3 → $Z
 └── Opción nivel 1
      ├── Unidad 1 → $A
      └── Unidad 2 → $B
```

------------------------------------------------------------------------

# 5. Flujo de información

## 5.1. Backend

``` text
Administrador
     │
     ▼
Producto WooCommerce
     │
     ▼
MPW - Configurador de productos
     │
     ├── Configurador tradicional
     │
     └── Combinaciones
            │
            ├── Niveles
            ├── Opciones
            ├── Dependencias
            └── Precios
```

------------------------------------------------------------------------

## 5.2. Frontend

Para el usuario final:

``` text
Producto
  │
  ▼
Seleccionar configuración
  │
  ├── Paso 1
  ├── Paso 2
  ├── Paso 3
  ├── ...
  │
  ▼
Cálculo
  │
  ▼
Resumen
  │
  ▼
Agregar al carrito
  │
  ▼
Mensaje / enlace:
"Ir al carrito →"
```

La misma lógica de interacción debe mantenerse en ambas modalidades.

------------------------------------------------------------------------

# 6. Arquitectura de precio

Uno de los problemas técnicos más importantes apareció cuando el precio
era visible correctamente en pantalla, pero el producto no siempre podía
agregarse al carrito con ese mismo precio.

La solución evolucionó hasta separar claramente:

1.  cálculo;
2.  validación;
3.  persistencia;
4.  incorporación al carrito.

## 6.1. Cálculo en frontend

El frontend solicita el precio al servidor mediante AJAX.

Conceptualmente:

``` text
Selección
   │
   ▼
AJAX
   │
   ▼
Servidor
   │
   ▼
Motor de pricing
   │
   ▼
Precio + estado + líneas
```

El navegador no es la autoridad final del precio.

------------------------------------------------------------------------

## 6.2. Token de precio

Se implementó un token firmado que contiene información como:

-   product_id;
-   selections;
-   total;
-   unit_price;
-   qty_units;
-   líneas calculadas;
-   expiración.

El token utiliza una firma HMAC para evitar que el cliente modifique
arbitrariamente el precio.

Conceptualmente:

``` text
payload
   │
   ▼
JSON
   │
   ▼
Base64
   │
   ▼
HMAC-SHA256
   │
   ▼
price_token
```

El servidor valida:

-   producto;
-   firma;
-   expiración;
-   total;
-   selección.

------------------------------------------------------------------------

# 7. Plan B: persistencia en sesión WooCommerce

El mecanismo de token por sí solo generó un problema práctico: podía
existir una diferencia entre lo que había calculado el endpoint de
precio y lo que recibía el endpoint de agregar al carrito.

Se implementó entonces un segundo mecanismo:

## Sesión WooCommerce

Cuando el servidor calcula correctamente una combinación, guarda
temporalmente el resultado en la sesión de WooCommerce.

``` text
Usuario selecciona
       │
       ▼
Endpoint de precio
       │
       ├── calcula
       ├── genera token
       └── guarda resultado en sesión
                     │
                     ▼
               WooCommerce
                     │
                     ▼
             Agregar al carrito
```

Esto permitió resolver el problema crítico:

> El precio mostrado y el precio utilizado al agregar al carrito debían
> corresponder al mismo cálculo.

La versión **1.10.8** marcó un punto importante de estabilidad en este
flujo.

------------------------------------------------------------------------

# 8. Integración con el carrito

El flujo final se consolidó en:

``` text
Configuración
    ↓
Precio calculado
    ↓
Validación servidor
    ↓
Agregar al carrito
    ↓
WooCommerce Cart
    ↓
Checkout
    ↓
Pedido
```

Se mantuvo además la persistencia de información de configuración
asociada al producto.

Un punto especialmente importante fue evitar la duplicación del producto
en el minicart.

El producto podía verse correctamente como una sola línea en el carrito
aunque existieran problemas de comportamiento en el estado visual del
botón. Ese comportamiento fue posteriormente corregido.

------------------------------------------------------------------------

# 9. Flujo final del botón Agregar al carrito

La experiencia final se simplificó deliberadamente.

Antes hubo diferentes comportamientos experimentales después del
agregado, incluyendo cambios del propio botón.

Finalmente se definió:

``` text
[ Agregar al carrito ]
          │
          ▼
      Producto agregado
          │
          ▼
Ir al carrito →
```

El texto **"Ir al carrito →"** aparece después de realizar correctamente
el agregado.

El botón original **Agregar al carrito** mantiene su comportamiento y no
se transforma en un botón de navegación.

La versión **1.10.18** quedó como referencia estable de este
comportamiento.

------------------------------------------------------------------------

# 10. Dos modalidades, una misma experiencia

Una de las condiciones del proyecto fue que:

-   Configurador tradicional
-   Combinaciones

compartieran criterios visuales y funcionales.

Esto llevó a la unificación de componentes.

## Componentes compartidos

### Dropzone

El componente de carga de archivos debe utilizar la misma estructura y
estilo en ambas modalidades.

### Resumen

El resumen de configuración debe mantener la misma estructura.

### CTA

Los botones principales deben mantener:

-   proporciones;
-   tipografía;
-   colores;
-   comportamiento;
-   ubicación.

### WhatsApp

Cuando está habilitado, mantiene el mismo tratamiento visual.

------------------------------------------------------------------------

# 11. Carga de archivos

El configurador incluye carga de diseño.

El texto evolucionó a:

**"Sube tu diseño"**

sin la palabra "opcional".

La funcionalidad de carga se mantiene.

La integración externa con Dropbox/Google Drive fue descartada durante
el desarrollo y no forma parte de la implementación final de esta etapa.

------------------------------------------------------------------------

# 12. Cantidad dinámica

Otro punto importante fue el sistema de cantidades.

El slider de cantidad debía ser dinámico.

El problema detectado era que el usuario podía aumentar la cantidad
visualmente sin que el precio cambiara correctamente.

Se implementó el cálculo dinámico:

``` text
Cantidad seleccionada
       │
       ▼
Regla / tramo de cantidad
       │
       ▼
Precio unitario correspondiente
       │
       ▼
Precio total
       │
       ▼
Resumen
```

Esto permite que los tramos de volumen afecten el precio.

Por ejemplo:

``` text
100 unidades → precio/tramo A
250 unidades → precio/tramo B
500 unidades → precio/tramo C
1000 unidades → precio/tramo D
```

El cálculo es realizado por el servidor y reflejado en la interfaz.

La versión **1.10.29** incorporó la corrección dinámica de este flujo.

------------------------------------------------------------------------

# 13. Backoffice: evolución visual

Una segunda fase del proyecto estuvo dedicada exclusivamente a mejorar
el backoffice sin alterar la lógica funcional.

Los objetivos fueron:

-   reducir ruido visual;
-   mejorar alineación;
-   ordenar campos;
-   aprovechar mejor el ancho disponible;
-   evitar herencias visuales problemáticas de WooCommerce.

La regla principal fue:

> modificar únicamente la UI propia de MPW - Configurador de productos.

No se debía alterar globalmente la interfaz de WooCommerce.

------------------------------------------------------------------------

# 14. Selector de modalidad

Se mejoró la selección entre:

-   Configurador tradicional
-   Combinaciones

Se eliminó la etiqueta visual innecesaria de "Nuevo".

El selector quedó como una elección clara de modo.

![Selector de modos](capturas/01-backoffice-modos.png)

------------------------------------------------------------------------

# 15. Eliminación de configuración obsoleta

Se descartaron elementos que ya no formaban parte del flujo definido.

Entre ellos:

-   "Texto del enlace de plantilla"
-   "Enlace de plantilla"
-   "Permitir cantidad personalizada (pedidos grandes)"

La eliminación de estos campos reduce configuración innecesaria y evita
confusión en el backoffice.

------------------------------------------------------------------------

# 16. Sellos de confianza

Se modificó el sistema de sellos de confianza para permitir:

-   hasta 4 sellos;
-   sellos opcionales;
-   no obligar al administrador a completar todos los espacios.

La intención es que el contenido de confianza sea configurable sin
convertirse en un requisito estructural del producto.

![Configuración de sellos](capturas/03-backoffice-sellos.png)

------------------------------------------------------------------------

# 17. Opciones simétricas

Se detectó que las opciones dentro de los pasos podían quedar
desalineadas.

Se estableció una regla de distribución automática:

### 2 opciones

``` text
|       50%       |       50%       |
```

### 4 opciones

``` text
| 25% | 25% | 25% | 25% |
```

La distribución debe ocupar el contenedor disponible.

Esto evita espacios visuales innecesarios y mantiene las opciones
simétricas.

![Distribución de opciones](capturas/06-backoffice-cantidad.png)

------------------------------------------------------------------------

# 18. Espaciado interno

Se realizaron ajustes de espaciado en los componentes propios del
plugin.

Entre las reglas solicitadas se encuentran:

``` css
.mpwcfg-panel .mpwcfg-mode-switch {
    padding: 10px;
}

.mpwcfg-panel .mpwcfg-field {
    padding: 5px;
}

.mpwcfg-panel .mpwcfg-subgroup > legend {
    padding: 10px 0;
}

.mpwcfg-panel .mpwcfg-globals > .mpwcfg-subgroup {
    padding: 10px;
}

.mpwcfg-panel .mpwcfg-step__body {
    padding: 10px;
}

.mpwcfg-panel .mpwcfg-grid2 {
    gap: 0;
}
```

También se trabajó el ajuste de altura de:

``` css
.mpwcfg-panel .mpwcfg-subgroup
```

para que el contenedor se adapte a su contenido.

------------------------------------------------------------------------

# 19. Herencia de estilos de WooCommerce

Durante la optimización del backoffice se detectó una regla global de
WooCommerce:

``` css
.woocommerce_options_panel label,
.woocommerce_options_panel legend {
    width: 150px;
}
```

Esta regla interfería con determinados componentes del plugin.

La solución correcta no fue eliminarla globalmente, porque eso podía
romper otros paneles de WooCommerce.

La corrección se limitó a los componentes propios de Configurator
Machine.

Este punto es importante desde el punto de vista arquitectónico:

> Los estilos de MPW - Configurador de productos deben tener alcance local y no
> alterar estilos globales de WooCommerce.

------------------------------------------------------------------------

# 20. Ajustes de formularios

También se trabajó la distribución interna de los inputs y etiquetas.

Objetivos:

-   evitar labels fuera de foco;
-   impedir desbordamientos;
-   mantener campos alineados;
-   mejorar lectura;
-   conservar el diseño existente.

Un caso específico fue:

**"Permitir cantidad personalizada (pedidos grandes)"**

que inicialmente se salía de foco.

Posteriormente este elemento fue eliminado del backoffice.

![Sección de cantidad](capturas/05-backoffice-inputs.png)

------------------------------------------------------------------------

# 21. Arquitectura de contenidos

La información del plugin puede organizarse en cuatro capas.

## Capa 1 --- Producto

WooCommerce administra:

-   nombre;
-   descripción;
-   imágenes;
-   SKU;
-   inventario;
-   impuestos;
-   envío;
-   producto simple/variable.

## Capa 2 --- MPW - Configurador de productos

Administra:

-   modo;
-   pasos;
-   opciones;
-   reglas;
-   cantidades;
-   apariencia;
-   sellos;
-   acciones.

## Capa 3 --- Motor de pricing

Resuelve:

-   selección;
-   combinación;
-   cantidad;
-   descuentos;
-   precio unitario;
-   precio total.

## Capa 4 --- WooCommerce

Recibe:

-   producto;
-   cantidad;
-   precio validado;
-   configuración;
-   archivo;
-   datos adicionales.

------------------------------------------------------------------------

# 22. Pauta interactiva del plugin

## A. Configurador tradicional

``` text
1. Subir tu diseño
       ↓
2. Formato
       ↓
3. Impresión
       ↓
4. Sustrato
       ↓
5. Terminación
       ↓
6. Cantidad
       ↓
7. Resumen
       ↓
8. Agregar al carrito
       ↓
9. Ir al carrito →
```

El usuario puede avanzar configurando cada paso.

El precio debe actualizarse según las reglas configuradas.

------------------------------------------------------------------------

## B. Combinaciones

``` text
1. Nivel 0
       ↓
2. Nivel 1 dependiente
       ↓
3. Nivel 2 dependiente
       ↓
4. Precio exacto
       ↓
5. Resumen
       ↓
6. Agregar al carrito
       ↓
7. Ir al carrito →
```

Las opciones del nivel siguiente dependen de la selección anterior.

El usuario no recibe combinaciones que el administrador no haya
configurado.

------------------------------------------------------------------------

# 23. Corrección de selecciones

Un principio importante de UX fue permitir que el usuario pueda corregir
una selección anterior.

Por eso las selecciones:

-   permanecen visibles;
-   no desaparecen al seleccionar otro nivel;
-   pueden revisarse;
-   pueden modificarse antes del agregado al carrito.

El resumen funciona como una representación del estado actual de
configuración.

------------------------------------------------------------------------

# 24. Resumen de configuración

El resumen funciona como punto de control antes del carrito.

Debe mostrar:

-   opciones seleccionadas;
-   configuración;
-   diseño cuando corresponda;
-   precio;
-   información relevante.

El usuario debe poder revisar la configuración antes de enviarla al
carrito.

------------------------------------------------------------------------

# 25. Arquitectura técnica simplificada

``` text
                    WORDPRESS
                        │
                        ▼
                 WOOCOMMERCE
                        │
              ┌─────────┴─────────┐
              │                   │
              ▼                   ▼
       Producto normal       Variaciones
              │
              ▼
      CONFIGURATOR MACHINE
              │
       ┌──────┴──────┐
       │             │
       ▼             ▼
Tradicional     Combinaciones
       │             │
       │       perfiles dependientes
       │             │
       └──────┬──────┘
              ▼
        MOTOR DE PRECIO
              │
       ┌──────┴──────┐
       │             │
       ▼             ▼
     AJAX       Sesión WC / Token
       │             │
       └──────┬──────┘
              ▼
       VALIDACIÓN FINAL
              │
              ▼
        CARRITO WC
              │
              ▼
           CHECKOUT
              │
              ▼
            PEDIDO
```

------------------------------------------------------------------------

# 26. Seguridad y consistencia del precio

El precio no debe considerarse confiable solamente porque el navegador
lo muestre.

El flujo implementado utiliza validación en servidor.

Principios:

-   el frontend solicita el cálculo;
-   el backend calcula;
-   el backend valida;
-   el token se firma;
-   la sesión WooCommerce puede conservar el cálculo;
-   el carrito recibe el valor validado.

Esto reduce el riesgo de inconsistencias entre interfaz y carrito.

------------------------------------------------------------------------

# 27. Compatibilidad con WooCommerce

El plugin fue construido como una capa sobre WooCommerce, no como un
reemplazo.

Se mantuvo:

-   producto normal de WooCommerce;
-   carrito;
-   checkout;
-   pedidos;
-   datos de pedido;
-   compatibilidad con HPOS;
-   productos variables;
-   Elementor.

La lógica especial no debe depender de un ID de producto fijo.

Se eliminó la lógica específica asociada al producto `6401`.

------------------------------------------------------------------------

# 28. Integración con Elementor

MPW - Configurador de productos dispone de integración mediante widget de
Elementor.

Esto permite insertar el configurador en la presentación del producto
sin reemplazar la infraestructura general de WooCommerce.

La configuración sigue perteneciendo al producto.

------------------------------------------------------------------------

# 29. Línea de versiones

  -----------------------------------------------------------------------
  Versión                             Hito
  ----------------------------------- -----------------------------------
  1.8.1                               Base estable del configurador
                                      tradicional

  1.9.0                               Primera implementación de
                                      Combinaciones

  1.9.1                               Carga, corrección y resumen

  1.9.2                               Corrección de representación
                                      literal

  1.9.3                               Ajustes de resumen y Dropzone

  1.9.4                               Resumen persistente y selecciones
                                      persistentes

  1.9.5                               Perfiles específicos por formato

  1.9.6                               Estructura genérica de niveles

  1.9.7                               Limpieza del backoffice

  1.9.8                               Unificación de resumen y carga

  1.9.9                               Estado posterior al agregado

  1.10.0                              Primer ajuste de precio al carrito

  1.10.1--1.10.7                      Iteraciones de consistencia de
                                      precio y UI

  1.10.8                              Solución robusta mediante sesión
                                      WooCommerce

  1.10.9--1.10.18                     Consolidación visual y flujo
                                      posterior al carrito

  1.10.18                             Estado estable del flujo "Ir al
                                      carrito →"

  1.10.19--1.10.28                    Mejoras de backoffice y aislamiento
                                      CSS

  1.10.29                             Cantidad y precios dinámicos

  1.10.30--1.10.34                    Refinamiento de espaciados y
                                      estructura del backoffice
  -----------------------------------------------------------------------

------------------------------------------------------------------------

# 30. Estado funcional al cierre

El sistema cuenta con:

-   configurador tradicional;
-   modalidad Combinaciones;
-   niveles dependientes;
-   precios por combinación;
-   cantidades dinámicas;
-   descuentos por cantidad;
-   cálculo AJAX;
-   validación de servidor;
-   token firmado;
-   persistencia temporal mediante sesión WooCommerce;
-   integración con carrito;
-   integración con pedidos;
-   carga de diseño;
-   resumen;
-   Dropzone compartido;
-   UI compartida;
-   WhatsApp;
-   sellos de confianza opcionales;
-   soporte de productos variables;
-   Elementor;
-   HPOS;
-   backoffice refinado;
-   distribución automática de opciones;
-   CSS encapsulado para evitar afectar WooCommerce.

------------------------------------------------------------------------

# 31. Principios de mantenimiento

A partir de esta etapa, cualquier modificación futura debería seguir
estas reglas:

1.  **No modificar funcionalidad estable para resolver un problema
    visual.**
2.  **No modificar colores sin solicitud explícita.**
3.  **No alterar el look & feel aprobado.**
4.  **No introducir nuevas funciones sin requerimiento.**
5.  **No utilizar lógica específica para un producto determinado.**
6.  **No aplicar CSS global sobre WooCommerce.**
7.  **Mantener los componentes equivalentes entre Configurador y
    Combinaciones.**
8.  **Validar precio siempre en servidor.**
9.  **Probar agregar al carrito después de cualquier cambio relacionado
    con pricing.**
10. **Probar ambos modos después de cualquier cambio de UI compartida.**
11. **Conservar la versión anterior estable antes de realizar cambios
    estructurales.**

------------------------------------------------------------------------

# 32. Checklist de regresión

Antes de publicar una nueva versión:

### Configurador tradicional

-   [ ] Cargar diseño
-   [ ] Seleccionar formato
-   [ ] Seleccionar impresión
-   [ ] Seleccionar sustrato
-   [ ] Seleccionar terminación
-   [ ] Modificar cantidad
-   [ ] Verificar precio
-   [ ] Agregar al carrito
-   [ ] Verificar una sola línea en carrito
-   [ ] Verificar resumen
-   [ ] Verificar "Ir al carrito →"

### Combinaciones

-   [ ] Seleccionar nivel 0
-   [ ] Confirmar opciones dependientes
-   [ ] Seleccionar nivel 1
-   [ ] Confirmar opciones dependientes
-   [ ] Seleccionar nivel 2
-   [ ] Confirmar precio
-   [ ] Cambiar selección anterior
-   [ ] Verificar que el resumen se actualiza
-   [ ] Agregar al carrito
-   [ ] Verificar precio en carrito
-   [ ] Verificar una sola línea en carrito
-   [ ] Verificar "Ir al carrito →"

### Cantidad

-   [ ] Mover slider
-   [ ] Verificar actualización del precio
-   [ ] Probar diferentes tramos
-   [ ] Confirmar precio en carrito

### Backoffice

-   [ ] Configurador tradicional
-   [ ] Combinaciones
-   [ ] Campos alineados
-   [ ] Labels contenidos
-   [ ] Opciones simétricas
-   [ ] Sellos opcionales
-   [ ] Sin elementos eliminados reapareciendo
-   [ ] Sin afectar paneles nativos de WooCommerce

------------------------------------------------------------------------

# 33. Capturas de referencia

Las siguientes capturas corresponden al proceso de diseño y validación
del backoffice/frontend durante esta etapa.

## Selector de modalidad

![Selector de modalidad](capturas/01-backoffice-modos.png)

## Configuración del backoffice

![Configuración](capturas/02-backoffice-configuracion.png)

## Sellos de confianza

![Sellos de confianza](capturas/03-backoffice-sellos.png)

## Opciones de configuración

![Opciones](capturas/04-backoffice-opciones.png)

## Cantidad

![Cantidad](capturas/05-backoffice-inputs.png)

## Distribución de opciones

![Distribución](capturas/06-backoffice-cantidad.png)

## Frontend --- cantidad dinámica

![Cantidad dinámica](capturas/07-frontend-cantidad.png)

## Flujo posterior al carrito

![Flujo posterior al carrito](capturas/08-frontend-carrito.png)

------------------------------------------------------------------------

# 34. Conclusión técnica

MPW - Configurador de productos evolucionó desde un configurador de pasos para
WooCommerce hacia una arquitectura de configuración de productos con dos
modalidades:

``` text
CONFIGURADOR TRADICIONAL
        +
COMBINACIONES DEPENDIENTES
        ↓
MOTOR DE PRICING
        ↓
VALIDACIÓN SERVIDOR
        ↓
WOOCOMMERCE
```

La incorporación de Combinaciones resolvió la necesidad de manejar
precios dependientes de estructuras jerárquicas y no solamente de
opciones independientes.

El principal desafío técnico fue mantener la coherencia entre:

-   selección del usuario;
-   cálculo AJAX;
-   precio mostrado;
-   precio validado;
-   carrito;
-   pedido.

La incorporación de token firmado y posteriormente de persistencia
temporal en la sesión de WooCommerce permitió consolidar ese flujo.

El segundo gran eje de evolución fue la consistencia visual entre ambas
modalidades y la mejora progresiva del backoffice, procurando que los
ajustes CSS quedaran encapsulados y no interfirieran con la interfaz
nativa de WooCommerce.

La arquitectura actual permite continuar evolucionando el plugin sin
depender de productos específicos y manteniendo separadas las
responsabilidades de:

-   administración;
-   configuración;
-   pricing;
-   validación;
-   carrito;
-   presentación.
