# Tornus Procurement Platform — Propuesta Técnica, Arquitectura y Metodología

Documento maestro de especificación funcional, arquitectura de software y metodología operativa para la plataforma de inteligencia en compras públicas y radar de licitaciones de Tornus.

---

## 1. Resumen Ejecutivo y Alcance

La plataforma Tornus Procurement Platform es un sistema empresarial de inteligencia de mercado diseñado para monitorear, calificar y gestionar oportunidades de contratación estatal en múltiples países.

### Objetivos Principales:
* **Aislamiento Estricto de Mercados:** Operación diferenciada para Colombia (SECOP II) y Perú (SEACE/OECE), garantizando que las bases de datos, monedas, cronogramas y normativas nunca se mezclen.
* **Radar Inteligente de Compras Públicas:** Ingesta continua de procesos de contratación con filtrado por palabras clave y exclusión automática de falsos positivos.
* **Control de Tiempos y Urgencia:** Algoritmo en tiempo real que calcula horas y días restantes de presentación, categorizando por nivel de criticidad.
* **Evaluación de Afinidad con Inteligencia Artificial:** Integración con la API de Google Gemini (Gemini 3.8 Flash) para analizar la viabilidad técnica y estratégica de cada pliego frente al perfil de la agencia.
* **Gestión Comercial Kanban:** Tablero interactivo de seguimiento de ofertas con notas privadas, checklist de requisitos y exportación ejecutiva a Microsoft Excel.
* **Sincronización Dinámica de Calendarios:** Agendamiento automático de fechas de cierre en Google Calendar y Outlook con alarmas previas y actualización en tiempo real ante prórrogas oficiales del Estado.
* **Identidad Visual y Regla de Estilo:** Diseño editorial sobrio, tipografía cuidada, iconografía vectorial SVG (Lucide Icons) y prohibición total de emoticones.

---

## 2. Principio de Aislamiento de Mercados (Multi-Tenant)

Para evitar contaminación cruzada entre países con legislaciones y monedas distintas, la plataforma opera bajo un modelo de aislamiento por contexto:

```text
                                  [ PANTALLA DE ACCESO ]
                                  Usuario y Contraseña
                                           |
                                           v
                              [ SELECTOR DE MERCADO ]
                        +------------------+------------------+
                        |                                     |
                        v                                     v
            [ ENTORNO COLOMBIA (CO) ]             [ ENTORNO PERÚ (PE) ]
            - Fuente: SECOP II (Socrata)          - Fuente: SEACE / OECE (OCDS)
            - Moneda: Pesos Colombianos (COP)     - Moneda: Soles (PEN)
            - Normativa: Ley 80 / D. 1082         - Normativa: Ley 30225
            - Territorio: 32 Departamentos        - Territorio: 25 Regiones
            - Base de Datos: licitaciones_co.db   - Base de Datos: licitaciones_pe.db
            - Cartera: Oportunidades CO           - Cartera: Oportunidades PE
                        |                                     |
                        +------------------+------------------+
                                           |
                                           v
                             [ MOTOR COMPARTIDO DE IA ]
                            Google Gemini 3.8 Flash API
                         Evaluación contra tornus_profile.json
```

### Reglas de Aislamiento:
1. **Selección en Login:** El usuario define al ingresar en qué país operará (`$_SESSION['pais'] = 'CO' | 'PE'`).
2. **Selector Rápido:** La barra de navegación permite alternar entre Colombia y Perú en un clic sin cerrar la sesión.
3. **Persistencia Independiente:** Las consultas del Radar, las propuestas guardadas en Cartera y las notas del equipo se almacenan en bases de datos separadas.

---

## 3. Stack Tecnológico Seleccionado

| Componente | Tecnología | Justificación |
| :--- | :--- | :--- |
| **Backend / API** | **Laravel 11 (PHP 8.2+)** | Continuidad con la base técnica existente, soporte multi-base de datos nativo, colas asíncronas para IA y programador de tareas (*Scheduler*). |
| **Frontend / UI** | **Tailwind CSS + Blade / Componentes** | Sistema de diseño moderno, modular y ligero, con tiempos de carga inmediatos y sin sobrecarga de frameworks innecesarios. |
| **Iconografía** | **Iconos Vectoriales SVG (Lucide Icons)** | Trazo limpio de 1.5px, estilo monocromático profesional, sin emoticones en toda la plataforma. |
| **Bases de Datos** | **SQLite / PostgreSQL Aislados** | Un archivo o esquema dedicado por país (`licitaciones_co.db` y `licitaciones_pe.db`). |
| **Inteligencia Artificial** | **Google Gemini 3.8 Flash API** | Calificación de afinidad en menos de 2 segundos, comprensión profunda de términos de referencia y bajo costo operativo. |
| **Calendarios** | **Google Calendar API, MS Graph y feed iCal (.ics)** | Sincronización universal con dispositivos móviles y de escritorio. |
| **Mensajería** | **n8n / Evolution API / SMTP** | Disparo de alertas ejecutivas por WhatsApp y correo electrónico. |

---

## 4. Módulos del Sistema y Funcionamiento

### Módulo A: Ingesta y Conectores de Datos Públicos
* **Conector Colombia:** Conexión a la API Socrata de Colombia Compra Eficiente (`datos.gov.co` - dataset `p6dx-8zbt`).
* **Conector Perú:** Conexión a la API OCDS de Contrataciones Abiertas (`contratacionesabiertas.oece.gob.pe/api/v1/releases`) con cabecera `User-Agent` de navegador para validación del firewall estatal.
* **Modelo Canónico:** Ambos orígenes se homologan a una estructura común: `id_global`, `entidad`, `departamento`, `objeto`, `presupuesto`, `fecha_cierre` y `modalidad`.

### Módulo B: Motor de Filtros y Detección de Falsos Positivos
El Radar implementa filtros avanzados para separar el ruido de las oportunidades reales:
* **Filtro de Palabras Clave:** Categorías de turismo, branding, eventos, producción audiovisual, desarrollo web y consultoría.
* **Reglas Anti-Falsos Positivos:**
  * Búsquedas de *Marca* excluyen referencias al departamento de *Cundinamarca*.
  * Búsquedas de *Campaña* excluyen material militar, intendencia o campañas de vacunación.
* **Filtros Territoriales:** Agrupaciones por zonas de interés comercial (Caribe, Grandes Capitales, Destinos Clave en Colombia; Lima Metropolitana, Macro Sur, Norte y Selva en Perú).

### Módulo C: Control de Tiempos y Semáforo de Vencimiento
Cada licitación calcula su plazo en tiempo real mediante la fórmula:
`dias_restantes = fecha_cierre - fecha_actual`

Clasificación por insignias de color:
* **Crítico (< 48 horas):** Insignia roja tenue (`bg-rose-50 text-rose-700`). Muestra horas restantes en cuenta regresiva (ej: *Vence en 18 horas*).
* **Tiempo Justo (3 a 7 días):** Insignia ámbar tenue (`bg-amber-50 text-amber-700`). Indica cierre en la semana en curso.
* **Tiempo Ideal (> 7 días):** Insignia verde tenue (`bg-emerald-50 text-emerald-700`). Plazo suficiente para estructurar consorcios o propuestas extensas.
* **Recién Convocada (< 24 horas):** Insignia azul tenue (`bg-blue-50 text-blue-700`). Momento clave para formular preguntas y observaciones a las bases.

### Módulo D: Evaluador de Afinidad con Inteligencia Artificial (Gemini)
* **Perfil de Agencia:** Archivo `data/tornus_profile.json` que contiene áreas de especialidad, experiencia previa, montos objetivo y servicios no deseados.
* **Procesamiento Asíncrono:** La llamada a Gemini se ejecuta en segundo plano para no congelar la navegación del usuario.
* **Resultado Estructurado:**
  * Clasificación: `ALTA`, `MEDIA` o `BAJA`.
  * Puntaje numérico del 1 al 100.
  * Justificación ejecutiva de 2 párrafos explicativos.
  * Etiquetas de oportunidad identificadas.

### Módulo E: Tablero Comercial (Mi Cartera Kanban)
* Seguimiento visual del ciclo de vida de cada licitación mediante 5 columnas:
  1. *Identificada / En Estudio*
  2. *En Preparación de Oferta*
  3. *Propuesta Presentada*
  4. *Adjudicada (Ganada)*
  5. *Descartada*
* Notas internas compartidas por el equipo.
* Exportación a Microsoft Excel (`.xlsx`) en formato ejecutivo de 3 pestañas y exportación rápida en CSV.

### Módulo F: Sincronización Dinámica con Calendarios Corporativos
* **Suscripción Universal (.ics):** Enlace único para suscribir Google Calendar, Outlook o Apple Calendar a las licitaciones activas de Mi Cartera.
* **Alarmas Escalonadas:**
  * Alarma T-48 horas: Verificación final de firmas, garantías y pólizas.
  * Alarma T-3 horas: Subida obligatoria de la propuesta al portal estatal antes de horas pico.
* **Detección de Prórrogas (Self-Updating):** Si la entidad pública aplaza la fecha de cierre en SECOP o SEACE, el sistema actualiza la cita del calendario automáticamente sin duplicar eventos.

---

## 5. Hoja de Ruta de Implementación por Fases

### Fase 1: Arquitectura Base y Aislamiento (Días 1 a 3)
* [ ] Configuración del proyecto base con Laravel 11 y Tailwind CSS.
* [ ] Implementación de la pantalla de Login con selector de país (`Colombia` o `Perú`).
* [ ] Configuración de conexiones de base de datos aisladas (`licitaciones_co.db` y `licitaciones_pe.db`).
* [ ] Integración de la biblioteca de iconos SVG (Lucide Icons) sin emoticones.

### Fase 2: Ingesta de Datos y Radares (Días 4 a 6)
* [ ] Conector funcional de Colombia (SECOP II vía Socrata).
* [ ] Conector funcional de Perú (SEACE vía OCDS API).
* [ ] Normalización de campos y almacenamiento estructurado.
* [ ] Implementación del semáforo de vencimientos y cuenta regresiva de horas.

### Fase 3: Inteligencia Artificial y Gestión de Cartera (Días 7 a 9)
* [ ] Servicio de calificación de afinidad con Google Gemini 3.8 Flash.
* [ ] Tablero Kanban de Cartera con arrastre de tarjetas y notas del equipo.
* [ ] Generador de reportes en Microsoft Excel (`.xlsx`) y CSV.

### Fase 4: Calendarios Corporativos y Alertas (Días 10 a 12)
* [ ] Generador de feed iCal (`.ics`) y sincronización con Google Calendar / Outlook.
* [ ] Integración de alarmas escalonadas (48h y 3h previas).
* [ ] Integración de webhook a n8n para alertas ejecutivas por WhatsApp y correo.

### Fase 5: Despliegue en Servidor y Pruebas (Días 13 a 14)
* [ ] Despliegue en servidor de producción (Nginx / CloudPanel).
* [ ] Protección de seguridad en directorio `data/` (bloqueo de acceso con código 404).
* [ ] Configuración de tareas programadas (*Cron jobs*) para sincronización continua.

---

## 6. Reglas de Estilo y Comunicación

1. **Prohibición Total de Emoticones:** Queda vetado el uso de emojis en cualquier parte del sistema, interfaces, correos, mensajes de WhatsApp y reportes.
2. **Iconografía Vectorial:** Todo elemento visual debe emplear iconos SVG minimalistas de trazo fino (1.5px).
3. **Formato Editorial de Mensajes:** Los despachos por WhatsApp o correo deben utilizar separadores estándar (`|`, `·`, `—`) con estructura corporativa y sobria.
