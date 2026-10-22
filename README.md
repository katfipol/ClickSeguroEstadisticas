# 🛡️ Click Seguro — Sistema Web de Análisis y Prevención de Phishing

Plataforma web de ciberseguridad desarrollada bajo la metodología ágil **Programación Extrema (XP)** para la detección temprana de enlaces maliciosos, códigos QR fraudulentos, enlaces ocultos en documentos PDF/TXT, reputación de dominios/IPs, auditoría de contraseñas filtradas (k-anonimato) y verificación privada de archivos mediante huella criptográfica **SHA-256**.

* **Equipo de Desarrollo:** COB
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

## 🏗️ Arquitectura y Estructura del Proyecto

El sistema implementa una arquitectura desacoplada en **3 capas** bajo principios **SOLID**:

```text
click-seguro/
├── app/
│   ├── Contracts/          # Interfaces de desacoplamiento (EscaneoSeguridadInterface)
│   ├── Exceptions/         # Manejo centralizado de errores JSON
│   ├── Http/
│   │   ├── Controllers/    # Controladores REST (Auth, Analisis, Archivo, Documento, Ficha, Reporte)
│   │   ├── Requests/       # Validaciones de entrada (FormRequests)
│   │   └── Resources/      # Serializadores JSON estandarizados
│   ├── Models/             # Modelos Eloquent (11 tablas normalizadas con prefijo id_)
│   ├── Rules/              # Regla NoFiltradaRule (k-anonimato con Have I Been Pwned)
│   └── Services/           # Lógica de negocio (VirusTotalService, UrlService, FichaDominioService)
├── database/
│   ├── migrations/         # Migraciones secuenciales del esquema relacional
│   ├── seeders/            # Catálogos iniciales y usuario de prueba
│   ├── ddl_schema.sql      # Script físico DDL del esquema normalizado
│   └── seeds.sql           # Script físico DML de datos iniciales
├── Front_Click_Seguro/     # Cliente Frontend independiente (HTML5, CSS3, ES6 Modules, Web Crypto API)
├── docs/                   # Documentación técnica XP, Estándares y Manual de Usuario Inicial
└── routes/
    └── api.php             # Endpoints RESTful prefijados bajo /api/v1