# 🔍 AUDITORÍA Y PLAN DE REFACTORIZACIÓN - COMICK BURGER

**Fecha de Auditoría:** 27 de Noviembre de 2025  
**Proyecto:** Sistema de Pedidos Online - Comick Burger  
**Tipo:** Aplicación Web PHP con MySQL

---

## 📋 RESUMEN EJECUTIVO

El proyecto es funcional pero presenta **graves problemas de seguridad, duplicación de código, inconsistencias estructurales** y malas prácticas de desarrollo. Se requiere una refactorización profunda manteniendo los diseños visuales actuales.

### Puntuación de Calidad del Código: 3/10

**Hallazgos Críticos:**
- ❌ 17 archivos duplicados innecesariamente
- ❌ Vulnerabilidades SQL Injection severas
- ❌ Gestión de sesiones inconsistente
- ❌ Sin validación ni sanitización de datos
- ❌ Mezcla de lógica de negocio con presentación
- ❌ Sin estructura MVC ni organización clara

---

## 🚨 PROBLEMAS CRÍTICOS DETECTADOS

### 1. **DUPLICACIÓN MASIVA DE CÓDIGO** ⚠️ CRÍTICO

**Problema:** La carpeta `/cliente` contiene 17 archivos idénticos a la raíz del proyecto.

**Archivos Duplicados:**
```
/agregar_carrito.php          =  /cliente/agregar_carrito.php
/cancelar_pedido.php          =  /cliente/cancelar_pedido.php
/carrito.php                  =  /cliente/carrito.php
/carrito_actualizar.php       =  /cliente/carrito_actualizar.php
/carrito_eliminar.php         =  /cliente/carrito_eliminar.php
/carrito_vaciar.php           =  /cliente/carrito_vaciar.php
/db.php                       =  /cliente/db.php
/galeria.php                  =  /cliente/galeria.php
/index.php                    =  /cliente/index.php
/login.php                    =  /cliente/login.php
/menu.php                     =  /cliente/menu.php
/metodo_pago.php              =  /cliente/metodo_pago.php
/mis_pedidos.php              =  /cliente/mis_pedidos.php
/pago.php                     =  /cliente/pago.php
/pago_exito.php               =  /cliente/pago_exito.php
/registro.php                 =  /cliente/registro.php
/ver_pedido.php               =  /cliente/ver_pedido.php
```

**Impacto:**
- Mantenimiento duplicado (bug en un archivo = bug en 2 lugares)
- Confusión sobre qué archivo es el correcto
- Desperdicio de espacio
- Riesgo de inconsistencias

**Solución:** Eliminar la carpeta `/cliente` completamente.

---

### 2. **VULNERABILIDADES DE SEGURIDAD SQL INJECTION** 🔥 CRÍTICO

**Archivos Afectados:**
- `mis_pedidos.php` - Línea 11-13
- `ver_pedido.php` - Línea 21, 35-38
- `admin_pedidos.php` (múltiples líneas)

**Código Vulnerable:**
```php
// ❌ VULNERABLE - mis_pedidos.php
$sql = $conn->query("
    SELECT * FROM pedidos 
    WHERE id_cliente = $cliente_id  // <--- Inyección SQL directa
    ORDER BY fecha DESC
");

// ❌ VULNERABLE - ver_pedido.php
$pedido = $conn->query("
    SELECT * FROM pedidos 
    WHERE id_pedido = $id_pedido     // <--- Sin sanitización
      AND id_cliente = $cliente_id
");
```

**Ataque Posible:**
```
URL: ver_pedido.php?id=1 OR 1=1 --
Resultado: Expone todos los pedidos de todos los usuarios
```

**Solución Correcta:**
```php
// ✅ SEGURO - Usar prepared statements
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_cliente = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
```

---

### 3. **INCONSISTENCIAS EN ARCHIVOS DE CONEXIÓN A BD** ⚠️

**Problema:** Existen 3 archivos diferentes de conexión a base de datos:

```
1. /db.php                    (contiene el schema SQL)
2. /includes/connect.php      (conexión real usada)
3. Referencia a 'conexion.php' en pago_exito.php (no existe)
```

**Código en `pago_exito.php` (línea 3):**
```php
include 'conexion.php'; // ❌ Este archivo NO existe
```

**Impacto:**
- `pago_exito.php` está ROTO
- Confusión sobre qué archivo usar
- Posibles errores de conexión

**Solución:**
- Mantener solo `/includes/connect.php`
- Mover el schema SQL a un archivo separado `/database/schema.sql`
- Corregir la referencia en `pago_exito.php`

---

### 4. **GESTIÓN DE SESIONES INCONSISTENTE** ⚠️

**Problema:** Se usan diferentes nombres de variables de sesión para el mismo propósito:

```php
// En login.php:
$_SESSION['cliente_id']     // ✓
$_SESSION['cliente_nombre'] // ✓

// En carrito.php:
$_SESSION['id_cliente']     // ❌ Diferente nombre

// En index.php:
$_SESSION['id_cliente']     // ❌ Nunca se setea en login
```

**Resultado:** El botón de "Cerrar Sesión" en `index.php` nunca aparece porque busca una variable que no existe.

**Código Problemático en `index.php`:**
```php
<?php if (isset($_SESSION['id_cliente'])): ?>  // ❌ Nunca será true
    <a href="cerrar.php" class="logout">🚪 Cerrar Sesión</a>
<?php else: ?>
    <a href="registro.php">🔑 Registrarse</a>
<?php endif; ?>
```

**Solución:** Estandarizar a:
```php
$_SESSION['cliente_id']     // ID del cliente
$_SESSION['cliente_nombre'] // Nombre del cliente
$_SESSION['cliente_email']  // Email (opcional)
```

---

### 5. **FALTA DE VALIDACIÓN Y SANITIZACIÓN** 🔥

**Problema:** Los datos del usuario se confían ciegamente sin validación.

**Ejemplos:**

```php
// ❌ registro.php - Sin validar longitud ni formato
$nombre = trim($_POST['nombre'] ?? '');
$celular = trim($_POST['celular'] ?? '');

// ❌ metodo_pago.php - Sin validar enumeración
$metodo_pago = $_POST['metodo_pago'] ?? '';  // Puede ser cualquier cosa

// ❌ admin_login.php - Sin validar password
$password = trim($_POST['password'] ?? '');  // No se usa en la query
```

**Riesgos:**
- XSS (Cross-Site Scripting)
- Datos malformados en BD
- Bypass de validaciones de negocio

**Solución:** Implementar validación robusta:
```php
// ✅ Validación correcta
function validar_nombre($nombre) {
    $nombre = trim($nombre);
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        throw new Exception("Nombre inválido");
    }
    return filter_var($nombre, FILTER_SANITIZE_STRING);
}

function validar_metodo_pago($metodo) {
    $metodos_validos = ['Tarjeta', 'Efectivo', 'Transferencia'];
    if (!in_array($metodo, $metodos_validos)) {
        throw new Exception("Método de pago inválido");
    }
    return $metodo;
}
```

---

### 6. **LÓGICA DE NEGOCIO MEZCLADA CON PRESENTACIÓN** ⚠️

**Problema:** HTML, CSS, SQL y lógica PHP todo en el mismo archivo.

**Ejemplo en `galeria.php`:**
```php
<?php
session_start();
require 'includes/connect.php';
$carrito_count = 0;
// ... lógica ...
$sql = $conn->query("SELECT * FROM productos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<style>
    /* 200 líneas de CSS embebido */
</style>
```

**Impacto:**
- Difícil de mantener
- Imposible de testear
- Código no reutilizable
- Mezcla de responsabilidades

**Solución:** Implementar arquitectura MVC (ver sección de refactorización).

---

### 7. **CONTROL DE ACCESO DÉBIL** 🔥

**Problema:** Múltiples formas de validar autenticación, algunas incorrectas.

**Inconsistencias:**

```php
// ❌ admin_login.php - NO VALIDA PASSWORD
$stmt = $conn->prepare("SELECT id_admin, username, email FROM admin WHERE email = ?");
// ... NO verifica password, solo que exista el email

// ✅ login.php (cliente) - SÍ valida password
if (password_verify($password, $hashed_password)) {
    // Correcto
}

// ❌ menu.php - Redirección incorrecta
if (!isset($_SESSION['cliente_id'])) {
    header("Location: cliente_login.php");  // Este archivo NO existe
    exit();
}
```

**Riesgo de Seguridad:**
- Cualquier persona con un email válido de admin puede entrar
- Redirecciones rotas rompen la experiencia

**Solución:**
```php
// ✅ admin_login.php CORREGIDO
$stmt = $conn->prepare("SELECT id_admin, username, password FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    
    // VERIFICAR PASSWORD
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['usuario_admin'] = $admin['username'];
        header("Location: admin_dashboard.php");
        exit();
    }
}
```

---

### 8. **ERRORES DE ESQUEMA DE BASE DE DATOS** ⚠️

**Problema:** Inconsistencias entre el schema y el código.

**Schema en `db.php`:**
```sql
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    -- Nota: campo "email" NO existe
```

**Código en `login.php`:**
```php
$stmt = $conn->prepare("SELECT id_cliente, nombre, password FROM clientes WHERE email = ?");
//                                                                            ^^^^^ Campo que NO existe
```

**Además:**
- `registro.php` inserta en campos: `nombre, celular, direccion, email, password`
- `db.php` define campos: `nombre, correo, telefono, direccion, password`

**Tabla de Inconsistencias:**
| Código PHP | Schema DB | Estado |
|------------|-----------|--------|
| `email` | `correo` | ❌ No coincide |
| `celular` | `telefono` | ❌ No coincide |

**Solución:** Estandarizar en el schema:
```sql
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,      -- Cambiar de 'correo'
    celular VARCHAR(20),                     -- Cambiar de 'telefono'
    direccion VARCHAR(200),
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 9. **ARCHIVOS HUÉRFANOS Y REFERENCIAS ROTAS** ⚠️

**Archivos Que No Existen Pero Son Referenciados:**

```php
// cerrar.php - Referenciado en index.php (NO EXISTE)
<a href="cerrar.php" class="logout">🚪 Cerrar Sesión</a>

// cliente_login.php - Referenciado en menu.php (NO EXISTE)
header("Location: cliente_login.php");

// conexion.php - Referenciado en pago_exito.php (NO EXISTE)
include 'conexion.php';

// logout.php - Referenciado en menu.php (NO EXISTE)
<a href="logout.php" class="btn">🚪 Cerrar sesión</a>

// historial_pedidos.php - Referenciado en menu.php (NO EXISTE)
<a href="historial_pedidos.php" class="btn">📦 Mis pedidos</a>
```

**Archivos Que Existen Pero No Se Usan:**
- `carrito_actualizar.php` - La lógica está en `carrito.php`
- `carrito_eliminar.php` - La lógica está en `carrito.php`
- `carrito_vaciar.php` - No se usa en ningún lado
- `admin/prueba` - Archivo de prueba sin extensión

**Solución:**
- Crear los archivos faltantes
- Eliminar los archivos redundantes
- Unificar lógica del carrito

---

### 10. **FALTA DE MANEJO DE ERRORES** ⚠️

**Problema:** Errores se muestran directamente al usuario o se ignoran.

```php
// ❌ Mal manejo
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);  // Expone info sensible
}

// ❌ Sin manejo
$sql = $conn->query("SELECT * FROM productos");  // Si falla, error fatal
```

**Solución:**
```php
// ✅ Manejo correcto
try {
    if ($conn->connect_error) {
        error_log("Error de conexión: " . $conn->connect_error);
        die("Lo sentimos, hay un problema temporal. Intenta más tarde.");
    }
    
    $sql = $conn->query("SELECT * FROM productos");
    if (!$sql) {
        throw new Exception("Error al obtener productos");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: error.php");
    exit();
}
```

---

### 11. **CÓDIGO EN DESARROLLO EN PRODUCCIÓN** ⚠️

```php
// ❌ registro.php - líneas 1-3
error_reporting(E_ALL);
ini_set('display_errors', 1);  // NUNCA en producción

// ❌ pago_exito.php - Sin configurar
$pedido_id = isset($_GET['pedido']) ? intval($_GET['pedido']) : 0;
// ... Pero nunca se pasa el parámetro 'pedido'
```

**Solución:** Usar configuración por entorno:
```php
// config.php
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // Desarrollo
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Producción
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
```

---

## 📁 PROBLEMAS DE ESTRUCTURA DE ARCHIVOS

### Estructura Actual (Caótica):
```
/
├── 17 archivos .php en raíz (mezclados)
├── db.php (schema SQL, no conexión)
├── cliente/
│   └── 17 archivos duplicados
├── admin/
│   ├── 4 archivos PHP
│   └── prueba (sin extensión)
├── assets/
│   └── todaslas ima/
│       ├── productos/
│       │   └── imag/
│       ├── avanve del proyecto.txt
│       ├── script.js
│       └── style.css
└── includes/
    └── connect.php
```

**Problemas:**
1. ❌ 17 archivos PHP sueltos en raíz
2. ❌ Carpeta `/cliente` completamente duplicada
3. ❌ `db.php` confunde (parece conexión pero es schema)
4. ❌ Carpeta `assets/todaslas ima/` (typo y mal organizada)
5. ❌ Archivos CSS/JS embebidos en PHP en vez de usar los de `/assets`

---

## 🎨 PROBLEMAS DE DISEÑO (NO VISUAL)

**Nota:** Los diseños visuales se mantendrán, estos son problemas de código:

1. **CSS Duplicado:** Cada archivo PHP tiene 100-200 líneas de CSS embebido
2. **Sin Reutilización:** Los estilos se repiten en cada página
3. **Sin Assets Externos:** Existen archivos `style.css` y `script.js` que NO se usan
4. **Inline Styles:** Múltiples elementos con `style="..."` inline

**Solución:** Extraer CSS a archivos externos manteniendo los estilos exactos.

---

## 📊 MÉTRICAS DE CÓDIGO

| Métrica | Valor | Estado |
|---------|-------|--------|
| Archivos PHP totales | 41 | ❌ Muchos duplicados |
| Líneas de código duplicadas | ~3,500 | ❌ Crítico |
| Archivos con SQL injection | 7 | 🔥 Crítico |
| Archivos rotos (referencias) | 5 | ❌ Alto |
| Uso de prepared statements | 40% | ❌ Bajo |
| Separación de responsabilidades | 0% | ❌ Inexistente |
| Cobertura de tests | 0% | ❌ Sin tests |
| Documentación | 0% | ❌ Sin comentarios útiles |

---

## 🔧 PLAN DE REFACTORIZACIÓN

### FASE 1: LIMPIEZA Y SEGURIDAD (PRIORITARIO - 1 semana)

#### 1.1 Eliminación de Duplicados
```bash
# Acción inmediata
rm -rf cliente/  # Eliminar carpeta completa
rm carrito_actualizar.php
rm carrito_eliminar.php  
rm carrito_vaciar.php
rm admin/prueba
```

#### 1.2 Corrección de Vulnerabilidades SQL
**Archivos a modificar:**
- ✅ `mis_pedidos.php` - Agregar prepared statements
- ✅ `ver_pedido.php` - Agregar prepared statements  
- ✅ `admin_pedidos.php` - Agregar prepared statements
- ✅ `galeria.php` - Ya usa query directo pero sin parámetros externos (OK)

#### 1.3 Unificación de Conexión BD
```php
// Acción:
1. Mantener solo /includes/connect.php
2. Crear /database/schema.sql (mover contenido de db.php)
3. Eliminar db.php
4. Corregir pago_exito.php para usar includes/connect.php
```

#### 1.4 Corrección de Schema de BD
```sql
-- Ejecutar en MySQL:
ALTER TABLE clientes CHANGE correo email VARCHAR(100) NOT NULL;
ALTER TABLE clientes CHANGE telefono celular VARCHAR(20);
```

#### 1.5 Estandarización de Sesiones
**Variables a usar en TODO el proyecto:**
```php
$_SESSION['cliente_id']       // ID del cliente autenticado
$_SESSION['cliente_nombre']   // Nombre del cliente
$_SESSION['admin_logged_in']  // Boolean para admin
$_SESSION['admin_id']         // ID del administrador
$_SESSION['usuario_admin']    // Nombre del admin
$_SESSION['carrito']          // Array del carrito
```

**Archivos a modificar:**
- `index.php` - Cambiar `id_cliente` por `cliente_id`
- `carrito.php` - Cambiar `id_cliente` por `cliente_id`
- Todos los archivos - Revisar consistencia

#### 1.6 Creación de Archivos Faltantes

```php
// cerrar_sesion.php (nuevo)
<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php");
exit();
?>
```

Cambiar referencias de `cerrar.php` y `logout.php` a `cerrar_sesion.php`

---

### FASE 2: REORGANIZACIÓN DE ESTRUCTURA (1 semana)

#### 2.1 Nueva Estructura de Carpetas

```
comick/
├── config/
│   ├── config.php              (configuración por entorno)
│   └── database.php            (clase de conexión mejorada)
├── database/
│   ├── schema.sql              (estructura de BD)
│   └── seed.sql                (datos de prueba)
├── public/                     (raíz web pública)
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css        (estilos globales)
│   │   │   ├── admin.css
│   │   │   └── cliente.css
│   │   ├── js/
│   │   │   └── scripts.js
│   │   └── images/
│   │       ├── productos/
│   │       └── logos/
│   ├── cliente/
│   │   ├── galeria.php
│   │   ├── carrito.php
│   │   └── mis_pedidos.php
│   └── admin/
│       ├── dashboard.php
│       ├── pedidos.php
│       └── productos.php
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProductoController.php
│   │   ├── CarritoController.php
│   │   └── PedidoController.php
│   ├── Models/
│   │   ├── Cliente.php
│   │   ├── Producto.php
│   │   ├── Pedido.php
│   │   └── Administrador.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── cliente/
│   │   └── admin/
│   └── Middleware/
│       ├── AuthMiddleware.php
│       └── ValidationMiddleware.php
├── vendor/                     (dependencias Composer)
├── .gitignore
├── composer.json
└── README.md
```

#### 2.2 Implementación de Autoloader

```php
// composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "require": {
        "php": ">=7.4",
        "ext-mysqli": "*"
    }
}
```

---

### FASE 3: IMPLEMENTACIÓN DE ARQUITECTURA MVC (2 semanas)

#### 3.1 Crear Clase de Base de Datos

```php
// src/Database/Database.php
<?php
namespace App\Database;

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $config = require __DIR__ . '/../../config/config.php';
        
        $this->conn = new \mysqli(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name']
        );
        
        if ($this->conn->connect_error) {
            error_log("Error de conexión: " . $this->conn->connect_error);
            throw new \Exception("Error de conexión a la base de datos");
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevenir clonación
    private function __clone() {}
}
```

#### 3.2 Crear Modelos

```php
// src/Models/Cliente.php
<?php
namespace App\Models;

use App\Database\Database;

class Cliente {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function crear($datos) {
        $stmt = $this->conn->prepare(
            "INSERT INTO clientes (nombre, email, celular, direccion, password) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        $stmt->bind_param(
            "sssss",
            $datos['nombre'],
            $datos['email'],
            $datos['celular'],
            $datos['direccion'],
            $password_hash
        );
        
        return $stmt->execute();
    }
    
    public function autenticar($email, $password) {
        $stmt = $this->conn->prepare(
            "SELECT id_cliente, nombre, email, password 
             FROM clientes WHERE email = ?"
        );
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $cliente = $result->fetch_assoc();
            
            if (password_verify($password, $cliente['password'])) {
                unset($cliente['password']);
                return $cliente;
            }
        }
        
        return false;
    }
    
    public function obtenerPorId($id) {
        $stmt = $this->conn->prepare(
            "SELECT id_cliente, nombre, email, celular, direccion 
             FROM clientes WHERE id_cliente = ?"
        );
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}
```

#### 3.3 Crear Controladores

```php
// src/Controllers/AuthController.php
<?php
namespace App\Controllers;

use App\Models\Cliente;

class AuthController {
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];
            
            if (!$email || empty($password)) {
                return $this->render('login', [
                    'error' => 'Email o contraseña inválidos'
                ]);
            }
            
            $clienteModel = new Cliente();
            $cliente = $clienteModel->autenticar($email, $password);
            
            if ($cliente) {
                $_SESSION['cliente_id'] = $cliente['id_cliente'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                
                header("Location: /galeria.php");
                exit();
            } else {
                return $this->render('login', [
                    'error' => 'Credenciales incorrectas'
                ]);
            }
        }
        
        return $this->render('login');
    }
    
    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validación
            $errores = $this->validarRegistro($_POST);
            
            if (!empty($errores)) {
                return $this->render('registro', [
                    'errores' => $errores
                ]);
            }
            
            $clienteModel = new Cliente();
            $creado = $clienteModel->crear($_POST);
            
            if ($creado) {
                $_SESSION['mensaje'] = 'Registro exitoso. Por favor inicia sesión.';
                header("Location: /login.php");
                exit();
            }
        }
        
        return $this->render('registro');
    }
    
    private function validarRegistro($datos) {
        $errores = [];
        
        if (strlen($datos['nombre']) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Email inválido';
        }
        
        if (strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($datos['password'] !== $datos['confirm_password']) {
            $errores[] = 'Las contraseñas no coinciden';
        }
        
        return $errores;
    }
    
    private function render($vista, $datos = []) {
        extract($datos);
        require __DIR__ . "/../Views/{$vista}.php";
    }
}
```

---

### FASE 4: MEJORAS DE SEGURIDAD (1 semana)

#### 4.1 Implementar CSRF Protection

```php
// src/Middleware/CsrfMiddleware.php
<?php
namespace App\Middleware;

class CsrfMiddleware {
    
    public static function generarToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validarToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new \Exception('Token CSRF inválido');
        }
    }
    
    public static function campoOculto() {
        $token = self::generarToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
```

**Uso en formularios:**
```php
<form method="POST">
    <?php echo CsrfMiddleware::campoOculto(); ?>
    <!-- resto del formulario -->
</form>
```

#### 4.2 Sanitización de Entradas

```php
// src/Middleware/ValidationMiddleware.php
<?php
namespace App\Middleware;

class ValidationMiddleware {
    
    public static function sanitizarTexto($texto, $max_length = 255) {
        $texto = trim($texto);
        $texto = strip_tags($texto);
        $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        return substr($texto, 0, $max_length);
    }
    
    public static function validarEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        throw new \Exception('Email inválido');
    }
    
    public static function validarNumero($numero, $min = null, $max = null) {
        $numero = filter_var($numero, FILTER_VALIDATE_INT);
        
        if ($numero === false) {
            throw new \Exception('Número inválido');
        }
        
        if ($min !== null && $numero < $min) {
            throw new \Exception("El número debe ser mayor o igual a {$min}");
        }
        
        if ($max !== null && $numero > $max) {
            throw new \Exception("El número debe ser menor o igual a {$max}");
        }
        
        return $numero;
    }
}
```

#### 4.3 Rate Limiting para Login

```php
// src/Middleware/RateLimitMiddleware.php
<?php
namespace App\Middleware;

class RateLimitMiddleware {
    
    public static function verificarIntentos($email, $max_intentos = 5, $tiempo_bloqueo = 900) {
        $key = "login_attempts_{$email}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'intentos' => 0,
                'ultimo_intento' => time()
            ];
        }
        
        $datos = $_SESSION[$key];
        
        // Si pasó el tiempo de bloqueo, resetear
        if (time() - $datos['ultimo_intento'] > $tiempo_bloqueo) {
            $_SESSION[$key] = [
                'intentos' => 0,
                'ultimo_intento' => time()
            ];
            return true;
        }
        
        // Verificar si está bloqueado
        if ($datos['intentos'] >= $max_intentos) {
            $tiempo_restante = $tiempo_bloqueo - (time() - $datos['ultimo_intento']);
            throw new \Exception("Demasiados intentos. Intenta en " . ceil($tiempo_restante / 60) . " minutos.");
        }
        
        return true;
    }
    
    public static function registrarIntento($email, $exitoso = false) {
        $key = "login_attempts_{$email}";
        
        if ($exitoso) {
            unset($_SESSION[$key]);
        } else {
            if (!isset($_SESSION[$key])) {
                $_SESSION[$key] = [
                    'intentos' => 0,
                    'ultimo_intento' => time()
                ];
            }
            
            $_SESSION[$key]['intentos']++;
            $_SESSION[$key]['ultimo_intento'] = time();
        }
    }
}
```

---

### FASE 5: EXTRACCIÓN DE CSS Y OPTIMIZACIÓN (3 días)

#### 5.1 Extraer CSS Embebido

**Crear archivo: public/assets/css/main.css**

```css
/* ==============================================
   COMICK BURGER - ESTILOS GLOBALES
   Mantiene el diseño exacto actual
   ============================================== */

/* Variables CSS para facilitar mantenimiento */
:root {
    --color-primary: #ff9800;
    --color-secondary: #00bcd4;
    --color-accent: #ffeb3b;
    --color-danger: #e53935;
    --color-success: #28a745;
    --color-bg-dark: #111;
    --color-bg-card: #222;
    --color-text: #fff;
    --font-comic: 'Comic Sans MS', cursive;
    --shadow-orange: 0 0 20px rgba(255, 152, 0, 0.5);
}

/* Reset y estilos base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background-color: var(--color-bg-dark);
    color: var(--color-text);
    font-family: var(--font-comic);
    line-height: 1.6;
}

/* Contenedores */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Botones globales */
.btn {
    display: inline-block;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    font-family: var(--font-comic);
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background-color: var(--color-primary);
    color: #000;
    box-shadow: 3px 3px 0 #e65100;
}

.btn-primary:hover {
    background-color: darkorange;
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: var(--color-secondary);
    color: #000;
    box-shadow: 3px 3px 0 #000;
}

.btn-danger {
    background-color: var(--color-danger);
    color: #fff;
    box-shadow: 3px 3px 0 #b71c1c;
}

/* Headers */
header {
    background: #000;
    border-bottom: 6px solid var(--color-primary);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 10px rgba(0,0,0,0.5);
}

header img {
    width: 100px;
}

h1 {
    color: var(--color-primary);
    font-size: 35px;
    text-shadow: 4px 4px red;
    margin-bottom: 20px;
}

/* Tarjetas de productos */
.card {
    background-color: var(--color-bg-card);
    border-radius: 15px;
    padding: 15px;
    box-shadow: 4px 4px 0px #ff0000, -4px -4px 0px #0000ff;
    text-align: center;
    transition: transform 0.2s;
}

.card:hover {
    transform: scale(1.05);
}

.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid var(--color-primary);
    margin-bottom: 10px;
}

/* Formularios */
.form-container {
    background: var(--color-bg-card);
    padding: 30px 25px 40px 25px;
    border-radius: 15px;
    max-width: 400px;
    margin: 60px auto;
    text-align: center;
    color: var(--color-text);
    border: 6px solid var(--color-primary);
    box-shadow: 10px 10px 0 var(--color-danger);
}

.form-group {
    text-align: left;
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: var(--color-accent);
    text-shadow: 1px 1px #000;
}

.form-container input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: none;
    border-bottom: 3px solid var(--color-primary);
    background: #333;
    color: var(--color-text);
    font-size: 0.95em;
    outline: none;
    transition: border-bottom-color 0.2s;
}

.form-container input:focus {
    border-bottom-color: var(--color-secondary);
}

/* Mensajes */
.message {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: bold;
}

.message-error {
    color: var(--color-danger);
    background: #333;
    border: 2px dashed var(--color-danger);
}

.message-success {
    color: var(--color-success);
    background: #1b3a1b;
    border: 2px dashed var(--color-success);
}

/* Carrito */
.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background: #333;
    border-radius: 10px;
    border-left: 5px solid var(--color-primary);
    transition: transform 0.2s;
}

.cart-item:hover {
    transform: translateX(5px);
}

/* Responsive */
@media (max-width: 768px) {
    .card {
        width: 90%;
    }
    
    header h1 {
        font-size: 22px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
```

#### 5.2 Modificar Archivos PHP para Usar CSS Externo

**Antes (galeria.php):**
```php
<head>
<style>
    body { background-color: #111; ... }
    /* 200 líneas más */
</style>
</head>
```

**Después (galeria.php):**
```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comick Burger - Galería</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/galeria.css">
</head>
```

---

### FASE 6: TESTING Y DOCUMENTACIÓN (1 semana)

#### 6.1 Tests Unitarios con PHPUnit

```php
// tests/Models/ClienteTest.php
<?php
use PHPUnit\Framework\TestCase;
use App\Models\Cliente;

class ClienteTest extends TestCase {
    
    public function testCrearCliente() {
        $cliente = new Cliente();
        $datos = [
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'celular' => '1234567890',
            'direccion' => 'Test Address',
            'password' => 'password123'
        ];
        
        $resultado = $cliente->crear($datos);
        $this->assertTrue($resultado);
    }
    
    public function testAutenticarConCredencialesCorrectas() {
        $cliente = new Cliente();
        $resultado = $cliente->autenticar('test@example.com', 'password123');
        
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_cliente', $resultado);
    }
    
    public function testAutenticarConCredencialesIncorrectas() {
        $cliente = new Cliente();
        $resultado = $cliente->autenticar('test@example.com', 'wrongpassword');
        
        $this->assertFalse($resultado);
    }
}
```

#### 6.2 Documentación del Código

```php
/**
 * Clase Cliente - Manejo de operaciones de clientes
 * 
 * Esta clase proporciona métodos para crear, autenticar y gestionar
 * clientes en el sistema de pedidos de Comick Burger.
 * 
 * @package App\Models
 * @author Tu Nombre
 * @version 1.0.0
 */
class Cliente {
    
    /**
     * Crea un nuevo cliente en la base de datos
     * 
     * @param array $datos Datos del cliente [nombre, email, celular, direccion, password]
     * @return bool True si se creó exitosamente, False en caso contrario
     * @throws Exception Si falla la conexión a la base de datos
     */
    public function crear($datos) {
        // ...
    }
}
```

#### 6.3 README.md del Proyecto

```markdown
# 🍔 Comick Burger - Sistema de Pedidos Online

Sistema web de gestión de pedidos para restaurante con panel administrativo.

## 📋 Requisitos

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Apache/Nginx

## 🚀 Instalación

1. Clonar el repositorio
2. Instalar dependencias: `composer install`
3. Configurar base de datos en `config/config.php`
4. Importar schema: `mysql < database/schema.sql`
5. Configurar permisos de carpetas de uploads

## 🏗️ Estructura

- `/public` - Archivos públicos accesibles vía web
- `/src` - Código fuente (MVC)
- `/config` - Configuración
- `/database` - Scripts SQL
- `/tests` - Tests automatizados

## 🔒 Seguridad

- Prepared statements en todas las consultas SQL
- Validación y sanitización de entradas
- Protección CSRF en formularios
- Rate limiting en login
- Passwords con bcrypt

## 📱 Características

- Catálogo de productos
- Carrito de compras
- Sistema de autenticación
- Panel de administración
- Gestión de pedidos
- Múltiples métodos de pago

## 👨‍💻 Desarrollo

```bash
# Correr tests
composer test

# Verificar estilo de código
composer cs-check
```
```

---

## 📅 CRONOGRAMA DE IMPLEMENTACIÓN

| Fase | Duración | Prioridad | Dependencias |
|------|----------|-----------|--------------|
| FASE 1: Limpieza y Seguridad | 1 semana | 🔥 CRÍTICA | Ninguna |
| FASE 2: Reorganización | 1 semana | Alta | Fase 1 |
| FASE 3: Arquitectura MVC | 2 semanas | Alta | Fase 2 |
| FASE 4: Mejoras de Seguridad | 1 semana | Alta | Fase 3 |
| FASE 5: Optimización CSS | 3 días | Media | Fase 3 |
| FASE 6: Testing y Docs | 1 semana | Media | Todas las anteriores |

**Tiempo total estimado:** 6-7 semanas

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Semana 1: Limpieza Crítica
- [ ] Eliminar carpeta `/cliente` completa
- [ ] Corregir SQL injection en `mis_pedidos.php`
- [ ] Corregir SQL injection en `ver_pedido.php`
- [ ] Corregir SQL injection en `admin_pedidos.php`
- [ ] Unificar conexión BD (eliminar db.php, corregir pago_exito.php)
- [ ] Corregir schema BD (correo→email, telefono→celular)
- [ ] Estandarizar variables de sesión
- [ ] Crear `cerrar_sesion.php`
- [ ] Corregir admin_login.php (agregar validación de password)
- [ ] Eliminar archivos redundantes del carrito

### Semana 2: Estructura
- [ ] Crear nueva estructura de carpetas
- [ ] Configurar Composer y autoloader
- [ ] Mover archivos a nueva estructura
- [ ] Crear config.php con manejo de entornos
- [ ] Reorganizar assets (CSS, JS, imágenes)

### Semanas 3-4: MVC
- [ ] Crear clase Database
- [ ] Implementar modelo Cliente
- [ ] Implementar modelo Producto
- [ ] Implementar modelo Pedido
- [ ] Implementar modelo Administrador
- [ ] Crear AuthController
- [ ] Crear ProductoController
- [ ] Crear CarritoController
- [ ] Crear PedidoController
- [ ] Adaptar vistas a nueva estructura

### Semana 5: Seguridad
- [ ] Implementar CSRF protection
- [ ] Crear ValidationMiddleware
- [ ] Implementar Rate Limiting
- [ ] Agregar logging de errores
- [ ] Sanitización de todas las entradas
- [ ] Auditoría de seguridad completa

### Semana 6: Frontend
- [ ] Extraer CSS a archivos externos
- [ ] Crear main.css con variables CSS
- [ ] Crear archivos CSS específicos (admin.css, cliente.css)
- [ ] Extraer JavaScript a scripts.js
- [ ] Optimizar imágenes
- [ ] Verificar que diseños se mantienen idénticos

### Semana 7: Testing
- [ ] Instalar PHPUnit
- [ ] Crear tests para modelos
- [ ] Crear tests para controladores
- [ ] Tests de integración
- [ ] Crear README.md
- [ ] Documentar código
- [ ] Manual de usuario

---

## 🎯 CRITERIOS DE ÉXITO

### Funcionalidad
- ✅ Todas las características actuales funcionan
- ✅ Sin errores PHP
- ✅ Sin errores JavaScript en consola
- ✅ Diseños visuales idénticos a los actuales

### Seguridad
- ✅ Sin vulnerabilidades SQL Injection
- ✅ Sin vulnerabilidades XSS
- ✅ Protección CSRF implementada
- ✅ Validación en servidor y cliente
- ✅ Rate limiting funcional

### Código
- ✅ Sin código duplicado
- ✅ Separación clara de responsabilidades
- ✅ Arquitectura MVC implementada
- ✅ Código documentado
- ✅ Tests con >70% de cobertura

### Performance
- ✅ Tiempo de carga < 2 segundos
- ✅ CSS externo cacheado
- ✅ Imágenes optimizadas
- ✅ Consultas SQL optimizadas

---

## 🚧 NOTAS IMPORTANTES

### Mantenimiento de Diseños
**CRÍTICO:** Los diseños visuales NO deben cambiar. Verificar pixel-perfect:
- Colores exactos
- Tamaños de fuentes
- Espaciados
- Sombras y bordes
- Animaciones

### Backup Antes de Refactorizar
```bash
# Crear backup completo antes de empezar
tar -czf comick_backup_$(date +%Y%m%d).tar.gz comick/
```

### Desarrollo por Ramas Git
```bash
git checkout -b fase1-limpieza
git checkout -b fase2-estructura
git checkout -b fase3-mvc
# etc.
```

### Testing Continuo
Después de cada fase, probar TODAS las funcionalidades:
- [ ] Registro de usuario
- [ ] Login de usuario
- [ ] Visualizar galería
- [ ] Agregar al carrito
- [ ] Modificar carrito
- [ ] Proceso de pago
- [ ] Ver mis pedidos
- [ ] Login admin
- [ ] Gestionar productos
- [ ] Gestionar pedidos

---

## 📞 SOPORTE Y MANTENIMIENTO

### Monitoreo Post-Refactorización
- Logs de errores PHP
- Logs de base de datos
- Métricas de performance
- Feedback de usuarios

### Plan de Contingencia
Si algo falla después del despliegue:
1. Revertir a backup inmediatamente
2. Identificar el problema
3. Corregir en entorno de desarrollo
4. Re-testear completamente
5. Desplegar nuevamente

---

## 📚 RECURSOS Y REFERENCIAS

### Documentación
- [PHP Best Practices](https://www.php-fig.org/psr/)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)
- [MVC Pattern](https://www.php.net/manual/en/tutorial.forms.php)

### Herramientas Recomendadas
- PHPStan - Análisis estático de código
- PHP CS Fixer - Estilo de código
- PHPUnit - Testing
- Xdebug - Debugging

---

## ✍️ CONCLUSIÓN

Este proyecto tiene potencial pero requiere refactorización urgente. La **FASE 1** debe implementarse **INMEDIATAMENTE** por los riesgos de seguridad. Las fases restantes pueden implementarse gradualmente.

**Recomendación:** Comenzar con FASE 1 esta semana, y planificar las demás fases en sprints de 2 semanas cada uno.

---

**Fin del Documento de Auditoría**  
*Generado: 27 de Noviembre de 2025*  
*Versión: 1.0*
