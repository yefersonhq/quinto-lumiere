# Plan de trabajo — Metodología Scrum

**Proyecto:** Tienda online Quinto Lumière (<https://quintolumiere.gt.tc>)
**Empresa:** Quinto Lumière — RUC 10802022471 — Km. 36 Antigua Panamericana Sur, Mercado San Pedro de Lurín, Stand 36, Lima
**Practicante:** Yeferson Huamán Quinto — Desarrollador de Software Full Stack
**Periodo:** del lunes 2 de noviembre de 2026 al domingo 24 de enero de 2027 (12 semanas)
**Dedicación:** 30 horas semanales (lunes a viernes, 6 horas diarias) — **360 horas en total**
**Repositorio y tablero:** <https://github.com/yefersonhq/quinto-lumiere> (cada sprint es un *milestone* y cada historia de usuario un *issue*)

---

## 1. Objetivo del proyecto

Desarrollar, desplegar y mantener una tienda en línea para Quinto Lumière que permita vender cosméticos y productos capilares por internet, con catálogo público, inventario administrable, pagos con Mercado Pago, cuentas de administrador y cliente, asistente virtual de búsqueda, carta de colores para tintes y medición con Google Analytics, cumpliendo la normativa peruana de protección al consumidor.

## 2. Por qué Scrum

El negocio necesita ver avances funcionando cada pocas semanas y cambiar prioridades según las ventas (campañas, temporada navideña, productos nuevos). Scrum permite entregar incrementos usables cada **2 semanas**, revisarlos con la dueña del negocio y ajustar el siguiente sprint.

## 3. Roles Scrum

| Rol | Persona | Responsabilidades |
|---|---|---|
| **Product Owner** | Titular de Quinto Lumière | Define y prioriza el Product Backlog, aprueba los entregables en cada Sprint Review |
| **Scrum Master** | Yeferson Huamán Quinto | Organiza los eventos Scrum, elimina impedimentos (hosting, credenciales, accesos) |
| **Equipo de desarrollo** | Yeferson Huamán Quinto | Análisis, diseño, desarrollo, pruebas, despliegue y documentación |

> Equipo de una persona: los roles de Scrum Master y Desarrollador los cumple el practicante, adaptación habitual en proyectos pequeños.

## 4. Eventos Scrum

| Evento | Frecuencia | Duración | Evidencia |
|---|---|---|---|
| Sprint Planning | Inicio de cada sprint | 2 h | Issues asignados al milestone del sprint |
| Daily Scrum | Diario | 15 min | Registro diario de horas (sección 9) |
| Sprint Review | Fin de cada sprint | 1 h | Demo a la Product Owner y aceptación |
| Sprint Retrospective | Fin de cada sprint | 1 h | Notas de mejora en el milestone |

## 5. Artefactos

- **Product Backlog:** issues abiertos del repositorio con la etiqueta `historia`.
- **Sprint Backlog:** issues del milestone del sprint en curso.
- **Incremento:** versión publicada en <https://quintolumiere.gt.tc> y commits del sprint en GitHub.
- **Definición de Terminado (DoD):** la funcionalidad está publicada, funciona en celular y computadora, no muestra errores en el panel de Diagnóstico, el código está en GitHub y la Product Owner la aprobó.

## 6. Cronograma por sprints

| Sprint | Fechas | Horas | Objetivo del sprint |
|---|---|---|---|
| **Sprint 1** | 2 – 15 nov 2026 | 60 | Requisitos, diseño y primera versión publicada (MVP) |
| **Sprint 2** | 16 – 29 nov 2026 | 60 | Pagos con Mercado Pago, Google Analytics y pruebas de compra |
| **Sprint 3** | 30 nov – 13 dic 2026 | 60 | Catálogo real: productos, fotos, precios, stock y marcas |
| **Sprint 4** | 14 – 27 dic 2026 | 60 | Calidad, seguridad, rendimiento y campaña navideña |
| **Sprint 5** | 28 dic 2026 – 10 ene 2027 | 60 | Posicionamiento (SEO), redes sociales y reportes de analítica |
| **Sprint 6** | 11 – 24 ene 2027 | 60 | Mejora del asistente, capacitación, documentación y cierre |
| | **Total** | **360** | |

## 7. Plan semanal

| Semana | Fechas | Sprint | Actividades principales | Horas | Acumulado |
|---|---|---|---|---|---|
| 1 | 2 – 8 nov | 1 | Reunión de requisitos, análisis, diseño de base de datos e interfaz; desarrollo de catálogo, carrito, cuentas y panel; despliegue en hosting con HTTPS; repositorio Git | 30 | 30 |
| 2 | 9 – 15 nov | 1 | Ajustes del MVP según la Product Owner, pruebas en celular, documentación inicial, Sprint Review y Retrospectiva | 30 | 60 |
| 3 | 16 – 22 nov | 2 | Credenciales de prueba de Mercado Pago, flujo de pago, verificación por API, control de stock | 30 | 90 |
| 4 | 23 – 29 nov | 2 | Google Analytics 4 y eventos de e-commerce, compras de prueba de punta a punta, paso a producción | 30 | 120 |
| 5 | 30 nov – 6 dic | 3 | Toma y edición de fotos, carga de productos reales con variantes y palabras clave | 30 | 150 |
| 6 | 7 – 13 dic | 3 | Precios y stock reales, logos de marcas faltantes, tonos de tintes, revisión con la Product Owner | 30 | 180 |
| 7 | 14 – 20 dic | 4 | Pruebas funcionales y de usabilidad, accesibilidad, corrección de errores | 30 | 210 |
| 8 | 21 – 27 dic | 4 | Revisión de seguridad, copias de seguridad, rendimiento, banners de campaña navideña | 30 | 240 |
| 9 | 28 dic – 3 ene | 5 | SEO: títulos, descripciones, sitemap, Google Search Console, vista previa al compartir en redes | 30 | 270 |
| 10 | 4 – 10 ene | 5 | Enlaces desde Instagram/TikTok/Beacons, Perfil de Empresa en Google, primer reporte de analítica | 30 | 300 |
| 11 | 11 – 17 ene | 6 | Mejoras del asistente Lumi según búsquedas sin resultado, ajustes finales | 30 | 330 |
| 12 | 18 – 24 ene | 6 | Capacitación a la administradora, manual de uso, informe final, Sprint Review final | 30 | **360** |

> Feriados (8 y 25 de diciembre, 1 de enero): las horas se recuperan dentro de la misma semana.

## 8. Product Backlog por sprint

Cada historia es un issue en GitHub con su estimación en horas.

### Sprint 1 — Requisitos y MVP (60 h)
| ID | Historia de usuario | Horas |
|---|---|---|
| HU-01 | Como dueña, quiero definir los requisitos y el alcance de la tienda para priorizar el desarrollo | 6 |
| HU-02 | Como equipo, quiero diseñar la base de datos y la interfaz con la identidad de la marca | 8 |
| HU-03 | Como visitante, quiero ver el catálogo y buscar productos sin iniciar sesión | 10 |
| HU-04 | Como clienta, quiero crear mi cuenta, armar mi carrito y hacer mi pedido | 10 |
| HU-05 | Como administradora, quiero gestionar productos, pedidos, usuarios y reclamos desde un panel | 12 |
| HU-06 | Como visitante, quiero un asistente y una carta de colores que me recomienden productos | 6 |
| HU-07 | Como dueña, quiero la tienda publicada con HTTPS y el código versionado en GitHub | 8 |

### Sprint 2 — Pagos y analítica (60 h)
| ID | Historia de usuario | Horas |
|---|---|---|
| HU-08 | Como clienta, quiero pagar con tarjeta, Yape o PagoEfectivo mediante Mercado Pago | 16 |
| HU-09 | Como administradora, quiero que el stock se descuente solo cuando el pago está aprobado | 8 |
| HU-10 | Como dueña, quiero ver en Google Analytics visitas, búsquedas y ventas | 12 |
| HU-11 | Como equipo, quiero probar compras completas en modo prueba antes de pasar a producción | 16 |
| HU-12 | Como dueña, quiero mi correo de contacto en la tienda y en el Libro de Reclamaciones | 4 |
| — | Eventos Scrum del sprint | 4 |

### Sprint 3 — Catálogo real (60 h)
| ID | Historia de usuario | Horas |
|---|---|---|
| HU-13 | Como clienta, quiero ver fotos reales de cada producto | 16 |
| HU-14 | Como administradora, quiero cargar todos los productos con precios, stock y variantes reales | 20 |
| HU-15 | Como clienta, quiero ver los logos de todas las marcas | 6 |
| HU-16 | Como clienta, quiero que el asistente encuentre productos aunque escriba con errores | 14 |
| — | Eventos Scrum del sprint | 4 |

### Sprint 4 — Calidad y campaña navideña (60 h)
| ID | Historia de usuario | Horas |
|---|---|---|
| HU-17 | Como clienta, quiero que la tienda funcione bien y rápido en mi celular | 16 |
| HU-18 | Como dueña, quiero que la tienda sea segura y tenga copias de seguridad | 16 |
| HU-19 | Como equipo, quiero corregir los errores encontrados en las pruebas | 14 |
| HU-20 | Como dueña, quiero destacar productos y promociones de Navidad | 10 |
| — | Eventos Scrum del sprint | 4 |

### Sprint 5 — SEO y redes (60 h)
| ID | Historia de usuario | Horas |
|---|---|---|
| HU-21 | Como dueña, quiero que mi tienda aparezca en Google | 20 |
| HU-22 | Como dueña, quiero que al compartir un producto en redes se vea su foto y precio | 10 |
| HU-23 | Como dueña, quiero enlazar la tienda desde Instagram, TikTok, Beacons y Google | 10 |
| HU-24 | Como dueña, quiero un reporte mensual de visitas y ventas | 16 |
| — | Eventos Scrum del sprint | 4 |

### Sprint 6 — Mejora y cierre (60 h)
| ID | Historia de usuario | Horas |
|---|---|---|
| HU-25 | Como clienta, quiero que el asistente responda mejor según lo que buscan otras clientas | 16 |
| HU-26 | Como administradora, quiero una capacitación y un manual para manejar la tienda sola | 16 |
| HU-27 | Como practicante, quiero entregar la documentación técnica y el informe final | 16 |
| HU-28 | Como equipo, quiero hacer la revisión final y la entrega formal a la Product Owner | 8 |
| — | Eventos Scrum del sprint | 4 |

## 9. Registro de horas (evidencia)

Completar cada día con las horas **realmente trabajadas**. Al cierre de cada sprint, la Product Owner firma la conformidad.

| Fecha | Sprint | Historia (issue) | Actividad realizada | Horas | Evidencia (commit / enlace) |
|---|---|---|---|---|---|
| | | | | | |

**Conformidad del sprint**

| Sprint | Horas registradas | Fecha de review | Firma Product Owner |
|---|---|---|---|
| 1 | | | |
| 2 | | | |
| 3 | | | |
| 4 | | | |
| 5 | | | |
| 6 | | | |

## 10. Riesgos

| Riesgo | Mitigación |
|---|---|
| El hosting gratuito bloquea avisos de Mercado Pago | Verificación de pagos al volver a la tienda y botón "Verificar pagos pendientes" (ya implementado) |
| Retraso en fotos o datos de productos | Priorizar los productos más vendidos y usar imágenes temporales |
| Cambios de precios por campaña de catálogo | Edición rápida de stock y precios desde el panel |
| Pérdida de datos | Copias de seguridad semanales de la base de datos y fotos |
