# 📋 Guía de Instalación - Sistema 2FA Lab-Explora

## 🎯 Archivos SQL Disponibles

### 1. **setup_2fa.sql** (Instalación Completa)
**Usar si:** Estás configurando el sistema 2FA por primera vez.

**Incluye:**
- ✅ Columnas `two_factor_enabled` en tablas de usuarios
- ✅ Columnas `blocked_until` para bloqueos temporales
- ✅ Tabla `two_factor_codes` con columna `code VARCHAR(255)` para hashes bcrypt
- ✅ Evento automático de limpieza cada hora
- ✅ Índices optimizados para búsquedas rápidas

**Ejecutar:**
```bash
C:\xampp\mysql\bin\mysql.exe -u root lab_exp_db < setup_2fa.sql
```

O desde **phpMyAdmin**: Importar archivo `setup_2fa.sql`

---

### 2. **fix_2fa_column.sql** (Actualización de Tabla Existente)
**Usar si:** Ya tienes la tabla `two_factor_codes` pero con `code VARCHAR(6)`.

**Hace:**
- ✅ Expande columna `code` de VARCHAR(6) a VARCHAR(255)
- ✅ Permite almacenar hashes bcrypt (~60 caracteres)

**Ejecutar:**
```bash
C:\xampp\mysql\bin\mysql.exe -u root lab_exp_db < fix_2fa_column.sql
```

---

### 3. **setup_contactos.sql** (Sistema de Contacto)
**Tabla:** `contactos_legales`

**Incluye:**
- Formulario de contacto para términos/privacidad
- Campos para nombre, email, asunto, mensaje
- Sistema de estados (pendiente, en_revision, respondido)

**Ejecutar:**
```bash
C:\xampp\mysql\bin\mysql.exe -u root lab_exp_db < setup_contactos.sql
```

---

## 🚀 Instalación Rápida (Todo desde Cero)

Si estás configurando todo el sistema por primera vez:

```bash
cd C:\xampp\htdocs\lab2

# 1. Sistema 2FA
C:\xampp\mysql\bin\mysql.exe -u root lab_exp_db < setup_2fa.sql

# 2. Sistema de Contacto
C:\xampp\mysql\bin\mysql.exe -u root lab_exp_db < setup_contactos.sql
```

---

## 🔧 Verificación Post-Instalación

### Verificar tabla 2FA:
```sql
DESCRIBE two_factor_codes;
-- Debe mostrar: code VARCHAR(255)
```

### Verificar evento de limpieza:
```sql
SHOW EVENTS;
-- Debe aparecer: cleanup_expired_2fa_codes
```

### Verificar tabla de contactos:
```sql
DESCRIBE contactos_legales;
```

---

## ⚠️ Solución de Problemas

### Error: "Unknown database 'lab2'"
**Solución:** Usar nombre correcto de BD: `lab_exp_db`

### Error: "Table already exists"
**Si usas setup_2fa.sql:** Normal, usa `IF NOT EXISTS`
**Si ya existe con VARCHAR(6):** Ejecutar `fix_2fa_column.sql`

### Códigos 2FA no se guardan
**Diagnóstico:**
```sql
SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'two_factor_codes' AND COLUMN_NAME = 'code';
```
**Debe devolver:** `varchar(255)`
**Si devuelve** `varchar(6)`: Ejecutar `fix_2fa_column.sql`

---

## 📊 Estructura Final de Tablas

### `two_factor_codes`
```
- id: INT (PK)
- user_type: ENUM('usuario','publicador','admin')
- user_id: INT
- code: VARCHAR(255) ← CRÍTICO: 255 para bcrypt
- created_at: DATETIME
- expires_at: DATETIME
- used: TINYINT(1)
- ip_address: VARCHAR(45)
```

### `contactos_legales`
```
- id: INT (PK)
- nombre: VARCHAR(255)
- email: VARCHAR(255)
- telefono: VARCHAR(50)
- asunto: VARCHAR(255)
- mensaje: TEXT
- fecha_envio: DATETIME
- ip_origen: VARCHAR(45)
- estado: ENUM
- fecha_respuesta: DATETIME
- notas_admin: TEXT
```

---

## ✅ Estado del Sistema

- **2FA:** ✅ Encriptación bcrypt implementada
- **Contacto:** ✅ Formulario funcional
- **Términos/Privacidad:** ✅ Actualizados y formales
- **Limpieza automática:** ✅ Evento programado cada hora

---

## 📝 Notas Importantes

1. **Códigos encriptados:** Todos los nuevos códigos se guardan con `password_hash()`
2. **Retrocompatibilidad:** Sistema acepta códigos antiguos en texto plano (expiran en 10 min)
3. **Seguridad:** Imposible recuperar código original de la base de datos
4. **Eventos:** MySQL debe tener `event_scheduler = ON` (ya configurado en el script)

---

**Última actualización:** 7 de diciembre de 2025
