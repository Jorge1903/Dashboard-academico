# Validación realizada

26 de octubre de 2026. Proyecto nuevo, sin modificar webscraping2.

Interfaz final: Dashboard Académico UTM, encabezado “Resumen del rendimiento estudiantil”, sin sección de metodología, textos decorativos de evaluación/tecnologías ni botón propio de descarga. PDF incluido como archivo de entrega.

- Laravel 13.31.0 inicia correctamente con PHP 8.4. Composer valida el manifiesto y el lock; instalación en seco: ninguna dependencia pendiente.
- React 19.3.0, Vite 7.3.7: compilación de producción correcta; archivos publicados dentro de Laravel.
- Aplicación abierta y comprobada en navegador en http://127.0.0.1:8000.
- Cinco gráficas visibles sin errores dentro de Metabase y dentro del iframe de la aplicación. Se comprobó también recarga completa del dashboard. Los errores iniciales de representación desaparecieron al abrir una sesión nueva y recargar; no se atribuye una causa técnica no demostrada.
- Se corrigió la dispersión para usar horas de estudio como eje X numérico. Se declaró explícitamente la muestra de 2,000 filas por el límite predeterminado de consultas sin agregación de Metabase.
- Cinco consultas comprobadas en acceso embebido sin sesión administrativa, mediante JWT.
- Filtro firmado: todos = 12,000; sin internet = 5,951; con internet = 5,869. Los 180 datos de internet desconocidos permanecen en “Todos”. Los totales se compararon con el CSV limpio.
- Filtro inválido (2): HTTP 422. Token con firma alterada: rechazado por Metabase.
- Prueba PHP: firma HS256, vencimiento de cinco minutos, recurso fijo, preservación del filtro 0, ausencia de clave en respuesta y rechazo de configuración sin clave.
- Endpoint de resumen: HTTP 200, 12,000 estudiantes, promedio 66.9%, aprobación 98.2%, internet 49.7% de 11,820 respuestas conocidas.
- Endpoint de descarga: HTTP 200 y contenido PDF válido.
- PDF exportado directamente de Metabase después de corregir la dispersión. Su página fue renderizada y revisada visualmente.

Límites: no se probó una instalación desde cero en otro equipo ni se esperaron cinco minutos para observar la renovación automática del JWT en tiempo real. El código programa esa renovación 30 segundos antes del vencimiento. No se realizó despliegue público ni prueba de dispositivo móvil físico. El proyecto es una demostración local de lectura; requiere autenticación y HTTPS antes de exponer datos privados.
