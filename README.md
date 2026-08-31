
# CRM Clínica

Sistema web integral para la gestión de una clínica, desarrollado con Laravel, Tailwind CSS, Alpine.js y MySQL. Incluye CRM, pacientes, tratamientos, procedimientos, finanzas, marketing, agenda, landing page y visor de revista digital.


## Tecnologías utilizadas

- **Backend:** Laravel / PHP
- **Frontend:** Blade, Tailwind CSS, JavaScript, Alpine.js
- **Base de datos:** MySQL
- **Tablas heredadas:** integración mediante Query Builder y consultas SQL
- **Visualización de documentos:** PDF.js
- **Generación de PDF:** mPDF
- **Almacenamiento de archivos:** sistema de almacenamiento compatible con CDN
- **Interactividad:** Fetch API, DataTables y componentes JavaScript

**Nota:** Esta versión publicada en GitHub corresponde a una adaptación para demostración y portafolio. Se han reemplazado nombres, logotipos, información sensible, archivos y configuraciones pertenecientes a la implementación original.


## Principales funcionalidades

### CRM y Ventas
- Gestión de leads y clientes.
- Seguimiento de prospectos.
- Reportes y filtros dinámicos.
- Gestión de propietarios de leads.
- Clasificación mediante semáforo comercial.
- Historial y seguimiento de pacientes.
- Cambio de estado y actualización de información directamente desde los reportes.

### Pacientes
- Perfil completo del paciente.
- Historial de valoraciones.
- Información de procedimientos.
- Historia clínica.
- Identificación y documentación.
- Laboratorios.
- Contratos y documentos.
- Galería de fotografías de valoración.

### Procedimientos y tratamientos
- Administración de procedimientos.
- Gestión de tratamientos con y sin expediente médico.
- Historial de aplicaciones.
- Fotografías asociadas a tratamientos.
- Notas médicas.
- Gestión de recibos.
- Soporte para diferentes sucursales.

### Administración y Finanzas
- Registro y administración de gastos.
- Control de ingresos.
- Cortes diarios.
- Presupuestos.
- Gestión de nómina.
- Reportes administrativos.

### Marketing
- Seguimiento de pacientes.
- Consulta de revisiones y tratamientos.
- Visualización de fotografías asociadas a pacientes.
- Filtros por fecha y sucursal.

### Agenda
- Gestión de citas y actividades.
- Seguimiento de eventos.
- Visualización de pacientes por fecha y sucursal.

### Landing Page
También se desarrolló una landing page enfocada en la presentación de los servicios de la clínica, incluyendo:

- Diseño responsive.
- Secciones informativas.
- Presentación de procedimientos y tratamientos.
- Elementos visuales y llamadas a la acción.
- Formularios de contacto.
- Optimización para dispositivos móviles.

### Revista Digital
Como parte del proyecto se desarrolló un visor de revista digital:

- Visualización de documentos PDF mediante PDF.js.
- Navegación por páginas.
- Zoom.
- Pantalla completa.
- Navegación mediante gestos en dispositivos móviles.
- Marcas de agua.
- Enlaces temporales firmados para controlar el acceso.
- Interfaz adaptada a escritorio y dispositivos móviles.

## Arquitectura

El proyecto utiliza una arquitectura basada en Laravel, separando responsabilidades entre:

- Controladores.
- Vistas Blade.
- Rutas.
- Consultas a base de datos.
- Endpoints AJAX/JSON.
- Servicios externos para almacenamiento de archivos.

Se realizó además la migración progresiva de funcionalidades provenientes de un CRM desarrollado originalmente en PHP hacia la nueva plataforma Laravel.

## Objetivo del proyecto

Modernizar y centralizar la operación digital de una clínica, sustituyendo procesos dispersos y funcionalidades heredadas por una plataforma web unificada, responsive y escalable.

## Screenshots

![App Screenshot]()
