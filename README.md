# Dashboard Académico UTM

Proyecto nuevo e independiente: frontend React, backend Laravel 13 y dashboard real de Metabase firmado mediante JWT HS256. No depende del código de webscraping2.

## Ver la instalación local preparada

Abre http://127.0.0.1:8000. Metabase debe seguir activo en http://localhost:3000 y MySQL en el contenedor mysql-84 (puerto 3307). El dashboard está en http://localhost:3000/dashboard/39.

El ZIP de entrega contiene frontend, backend, dataset, SQL, definiciones de Metabase y PDF. Excluye .env, claves API, node_modules, vendor, logs y cachés. La carpeta local sí conserva sus dependencias y configuración para ejecutarse en este equipo.

## Ejecutar una copia en otro equipo

Requisitos: PHP >=8.4 con extensiones estándar de Laravel, Composer 2, Node >=20.19 (o >=22.12), npm, Python >=3.10, MySQL 8 y Metabase compatible con incrustación firmada. Esta instalación fue validada con Metabase 0.63.16.8.

1. En `backend`, ejecuta `composer install`, copia `.env.example` a `.env` y ejecuta `php artisan key:generate`. El backend no requiere migraciones ni una base propia: consulta el resumen del CSV limpio y firma el acceso al dashboard.
2. Crea una base MySQL exclusiva, por ejemplo `CREATE DATABASE evaluacion_academica CHARACTER SET utf8mb4;`. Importa `data/estudiantes.sql` en ella. Puedes usar Workbench: abre el SQL, selecciona la base nueva y ejecútalo. El SQL no elimina tablas ni contiene contraseñas; usa `INSERT IGNORE` y clave primaria por estudiante para evitar duplicados al reimportar.
3. Añade esa base a Metabase con un usuario que tenga únicamente SELECT. Anota el ID numérico de la conexión en Metabase.
4. Crea una clave API temporal de administrador en Metabase y ejecuta desde la raíz: `python metabase/recrear.py --database-id ID --url http://localhost:3000`. La clave se solicita sin mostrarla y no se guarda. El script crea colección, modelo, cinco preguntas y dashboard, activa la incrustación firmada y configura el `.env` del backend. Conserva `metabase/estado-local.json` para reintentos; no reutilices ese estado en otra instancia. Puedes revocar la clave API una vez completada la configuración: la aplicación no la necesita.
5. En `frontend`: `npm ci`, `npm run build`, `npm run publish:local`.
6. En `backend`: `php artisan serve --host=127.0.0.1 --port=8000` y abre http://127.0.0.1:8000.

Para desarrollar el frontend por separado, inicia Laravel como arriba y ejecuta `npm run dev` en frontend. Vite envía `/api` al backend; no se necesita abrir CORS a todos los sitios.

## Datos y cinco gráficas

Fuente: CSV proporcionado, [Grade Prediction Based on Socioeconomic Factors](https://www.kaggle.com/datasets/chaitanyajamble/grade-prediction-based-on-socioeconomic-factors).

Se conservan las 12,025 filas originales en el archivo recibido; el CSV limpio elimina 25 duplicados exactos y contiene 12,000 estudiantes únicos. No se imputan valores faltantes. Los análisis excluyen únicamente filas sin las variables necesarias.

1. Barras: conteo por grade_class, población completa.
2. Barras agrupadas: calificación final promedio por educación del padre y género; educación ausente aparece como “Sin dato”.
3. Dispersión: horas de estudio semanales en X y calificación final en Y. Muestra determinista de 2,000 estudiantes con horas conocidas, ordenados por student_id. Se indica en el título porque Metabase limita por defecto las consultas no agregadas a 2,000 filas. Los indicadores y las otras cuatro gráficas no usan esta muestra.
4. Dona: aprobación/reprobación con filtro de internet (todos/con/sin). Aprobación >=40%, coherente con las clases originales: F<40 y D desde40. No se presenta como norma escolar mexicana. Internet desconocido se incluye solo en “Todos”.
5. Línea: promedio de calificación por intervalos de asistencia de diez puntos, límite superior incluido: (0,10], (10,20], etc. No se inventan intervalos sin observaciones.

Resultados completos: 12,000 estudiantes; promedio 66.9%; aprobación 98.2%; internet 49.7% sobre 11,820 respuestas conocidas. Los indicadores superiores muestran siempre el total; el selector afecta únicamente la dona. Correlación no implica causalidad.

## Integración y seguridad

`GET /api/dashboard/embed?internet=0|1` valida el filtro y devuelve una URL firmada de cinco minutos. Solo Laravel conoce `METABASE_EMBEDDING_SECRET_KEY`; el cliente no elige el dashboard, algoritmo o caducidad. El filtro se firma dentro de `params` y está bloqueado en Metabase. El frontend renueva el token antes de caducar. La respuesta usa `Cache-Control: no-store` y el iframe `referrerPolicy=no-referrer`.

La API tiene un límite de 60 solicitudes por minuto para la firma. Esta entrega es una demostración local con acceso de lectura al dataset académico. JWT protege la integridad del recurso, pero no identifica a un usuario de la aplicación: antes de exponer datos privados en internet, añade autenticación/autorización y HTTPS. No compartas `.env` ni la clave API administrativa.

## Verificación y PDF

Ejecuta `php tests/verify-jwt.php` desde backend para comprobar firma, expiración, recurso fijo, filtro 0 y rechazo sin clave. `npm run build` comprueba el frontend.

`dashboard-academico.pdf` se exportó desde el dashboard real de Metabase. El PDF se entrega como archivo separado; la interfaz no incluye un botón de descarga propio. Después de cambiar datos/gráficas, vuelve a exportar desde Metabase y sustituye ese archivo. `GET /api/summary` lee el CSV local: si actualizas los datos de MySQL, actualiza también el CSV limpio para mantener ambos sincronizados.

Si Metabase muestra errores de representación tras crear preguntas, recarga completamente la página; valida que cada pregunta y el dashboard carguen antes de exportar. El estado de ejecución y las pruebas realizadas se detallan en `VALIDACION.md`.
