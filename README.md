# 🛡️ Click Seguro — Sistema Web de Análisis y Prevención de Phishing

Plataforma web de ciberseguridad desarrollada bajo la metodología ágil **Programación Extrema (XP)** para la detección temprana de enlaces maliciosos, códigos QR fraudulentos, enlaces ocultos en documentos PDF/TXT, reputación de dominios/IPs (RDAP), auditoría de contraseñas filtradas (k-anonimato con Have I Been Pwned) y verificación privada de archivos mediante huella criptográfica **SHA-256**.

* **Equipo de Desarrollo:** COB
* **Metodología:** Programación Extrema (XP)
* **Versión actual del corte:** `v0.6.0-beta` (Mitad de la 6ta Iteración)

---

## 👥 Integrantes del Equipo (Developers)

| Integrante | Rol Principal | Usuario GitHub |
| :--- | :--- | :--- |
| **José Alejandro Salazar Quiroga** | Líder de Proyecto / QA & Testing | `@DarkStar303` |
| **José Martín Leaño Mercado** | Administrador de BD (DBA) / Backend | `@katfipol` |
| **Julio Mateo Bustillos Serrano** | Desarrollador Backend / Seguridad | `@goemon043` |
| **Melani Quintela Aguilar** | Diseñadora UX/UI / Frontend Principal / Documentación | `@MelaniQuintelaAguilar` |

---

## 📋 Alcance Actual del Sistema (`v0.6.0-beta`)

El proyecto consta de **18 Historias de Usuario** planificadas en **8 iteraciones**. Hasta el presente corte se encuentran implementadas y cerradas **13 Historias de Usuario**:

* **Iteración 1:** `[HU-01]` Autenticación con Sanctum, `[HU-03]` Clasificación de nivel de riesgo (VirusTotal API v3), `[HU-04]` Expansión de enlaces acortados.
* **Iteración 2:** `[HU-02]` Captura validada y pegado rápido de URLs, `[HU-05]` Desglose de razones de riesgo por motor de seguridad.
* **Iteración 3:** `[HU-08]` Decodificación de enlaces en códigos QR, `[HU-13]` Extracción masiva de enlaces en documentos PDF y TXT.
* **Iteración 4:** `[HU-06]` Historial personal y eliminación con confirmación modal, `[HU-07]` Reanálisis actualizado de URLs.
* **Iteración 5:** `[HU-09]` Reporte comunitario de URLs sospechosas, `[HU-12]` Ficha técnica de dominio, IP y antigüedad (RDAP).
* **Iteración 6 (Primera Mitad):** `[HU-11]` Verificación local de archivos por huella SHA-256 (Web Crypto API), `[HU-15]` Validación de contraseñas filtradas con k-anonimato (HIBP).
* **En progreso / Backlog (Iteraciones 6.2, 7 y 8):** `[HU-14]` Evaluador y generador de contraseñas, `[HU-10]` Estadísticas personales, `[HU-16]` Estadísticas globales, `[HU-17]` Exportación de informes PDF y `[HU-18]` Procesamiento asíncrono con colas.

---

## 🏗️ Arquitectura y Estructura del Proyecto

El sistema implementa una arquitectura desacoplada en **3 capas** bajo principios **SOLID**:

* **`app/` (Capa 1 - Backend API REST `/api/v1`):**
  * `Contracts/` y `Services/`: Desacoplamiento mediante `EscaneoSeguridadInterface`, `VirusTotalService`, `UrlService` y `FichaDominioService`.
  * `Http/Controllers/`, `Requests/` y `Resources/`: Controladores RESTful, validaciones `FormRequest` y serializadores JSON.
  * `Models/` y `Rules/`: 11 modelos Eloquent normalizados y regla `ContrasenaNoFiltrada` (k-anonimato).
* **`database/` (Capa 2 - Persistencia MySQL):**
  * `migrations/` y `seeders/`: Esquema relacional 3FN con estándar de prefijos `id_` y catálogos base.
  * `ddl_schema.sql` y `seeds.sql`: Respaldos físicos estandarizados de estructura y datos semilla.
* **`Front_Click_Seguro/` y `docs/` (Capa 3 - Cliente Web y Documentación):**
  * `Front_Click_Seguro/`: Interfaz responsiva en HTML5, CSS3, módulos ES6 (`js/api.js`) y Web Crypto API.
  * `docs/`: Especificación de Requisitos y Backlog XP, Manual de Estándares de Desarrollo y Manual de Usuario Inicial.

---

## 🌿 Flujo de Ramas (Git Flow) y Convención de Commits

* **Ramas de 3 niveles:**
  * `main`: Versión estable de producción auditada al cierre de cada corte.
  * `develop`: Rama troncal de integración continua del equipo.
  * `feature/HU-XX-descripcion`: Ramas aisladas por Historia de Usuario integradas mediante *Pull Requests*.
* **Estructura semántica de Commits (`tipo(módulo): descripción en presente`):**
  * `feat(modulo):` Nueva funcionalidad de Historia de Usuario.
  * `database(migraciones/seeders):` Esquema SQL, migraciones y catálogos.
  * `style(ui):` Diseño responsivo e interfaz visual.
  * `refactor(excepciones):` Mejora interna de código sin alterar comportamiento.
  * `docs(proyecto):` Documentación técnica, manuales y guías.

---

## ⚙️ Requisitos del Entorno

* **PHP:** `>= 8.1` (extensiones `curl`, `mbstring`, `openssl`, `pdo_mysql`)
* **Base de Datos:** MySQL `>= 8.0` / MariaDB (XAMPP, WAMP o Docker)
* **Gestor de Dependencias:** Composer `>= 2.x`
* **Servicios Externos:** API Key de **VirusTotal v3** (`VT_API_KEY`)

---

## 🚀 Guía Paso a Paso de Instalación y Ejecución

1. **Clonar el repositorio:**
   `git clone https://github.com/katfipol/ClickSeguroEstadisticas.git`

2. **Instalar dependencias de PHP con Composer:**
   `composer install`

3. **Configurar variables de entorno:**
   Copiar el archivo `.env.example` a `.env`, configurar la base de datos `click_seguro` y agregar la clave `VT_API_KEY`. Luego generar la llave de aplicación:
   `php artisan key:generate`

4. **Ejecutar migraciones y cargar catálogos iniciales (Seeders):**
   `php artisan migrate --seed`

5. **Iniciar el servidor Backend (API REST en `http://127.0.0.1:8000`):**
   `php artisan serve`

6. **Abrir el cliente Frontend:**
   Ejecutar `Front_Click_Seguro/index.html` desde el servidor local asegurándose de que `API_BASE_URL` en `Front_Click_Seguro/js/api.js` apunte a `http://127.0.0.1:8000/api/v1`.

---

## 🔑 Credenciales de Prueba

* **Correo electrónico:** `demo@clickseguro.bo` *(o registrar una cuenta nueva desde `register.html`)*
* **Contraseña:** `ClickSeguro#2026`

---

## 📚 Documentación Oficial en `/docs`

1. 📄 `docs/01_Especificacion_Requisitos_y_Backlog_XP.pdf`: Requisitos, Historias de Usuario y Modelo Entidad-Relación.
2. 📄 `docs/02_Manual_Estandares_Desarrollo_XP.pdf`: Estándares de código, base de datos, ramas y commits.
3. 📘 `docs/03_Manual_Usuario_Inicial.pdf`: Manual de Usuario Inicial paso a paso con capturas de la versión `v0.6.0-beta`.
