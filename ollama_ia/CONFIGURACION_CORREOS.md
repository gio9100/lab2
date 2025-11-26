# 📧 Sistema de Correos - Debugging y Configuración

## 🔍 Sistema de Logging Implementado

Se agregó un sistema de logging completo para rastrear el envío de correos electrónicos.

### **Archivos de Log Creados:**

Cuando moderes una publicación, se crearán automáticamente en:
```
c:\xampp\htdocs\Lab\ollama_ia\logs\
```

**Archivos generados:**

1. **`email_log.txt`** - Log de todos los intentos de envío
   - Fecha y hora
   - Destinatario (publicador o admin)
   - Asunto del correo
   - Resultado (ÉXITO o FALLO)
   - Estado de la publicación

2. **`email_publicador_[timestamp].html`** - Correo HTML del publicador (si falla)
3. **`email_admin_[timestamp].html`** - Correo HTML del admin (si falla)

---

## 🧪 Cómo Verificar

### **Paso 1: Moderar una Publicación**
1. Ve a: `http://localhost/Lab/ollama_ia/panel-moderacion.php`
2. Modera cualquier publicación
3. Espera a que termine

### **Paso 2: Revisar los Logs**
1. Abre: `c:\xampp\htdocs\Lab\ollama_ia\logs\email_log.txt`
2. Verás algo como:

```
2025-11-24 01:25:00 - Intento de envío a PUBLICADOR: publicador@example.com
Nombre: Juan Pérez
Asunto: ✅ Tu publicación ha sido aprobada - Lab-Explorer
Resultado: FALLO
Estado: aprobada
Publicación: Mi investigación científica
---

2025-11-24 01:25:01 - Intento de envío a ADMIN: admin@example.com
Asunto: ✅ Publicación Aprobada Automáticamente - Lab-Explorer
Resultado: FALLO
Estado: aprobada
---
```

### **Paso 3: Ver los Correos HTML**
Si `mail()` falla, los correos se guardan como archivos HTML:

1. Abre: `c:\xampp\htdocs\Lab\ollama_ia\logs\`
2. Verás archivos como:
   - `email_publicador_1732425900_abc123.html`
   - `email_admin_1732425901_def456.html`
3. Ábrelos en tu navegador para ver cómo se ven

---

## ⚙️ Configurar SMTP en XAMPP (Para que funcione mail())

### **Opción 1: Usar MailHog (Recomendado para desarrollo)**

1. **Descargar MailHog:**
   ```
   https://github.com/mailhog/MailHog/releases
   ```

2. **Ejecutar MailHog:**
   ```
   MailHog.exe
   ```

3. **Configurar PHP:**
   Edita `C:\xampp\php\php.ini`:
   ```ini
   [mail function]
   SMTP = localhost
   smtp_port = 1025
   sendmail_from = noreply@lab-explorer.com
   ```

4. **Reiniciar Apache**

5. **Ver correos:**
   ```
   http://localhost:8025
   ```

### **Opción 2: Usar Gmail SMTP**

1. **Instalar PHPMailer:**
   ```bash
   composer require phpmailer/phpmailer
   ```

2. **Modificar el código** (si quieres usar Gmail en producción)

### **Opción 3: Solo para Testing - Guardar en Archivos**

Los correos ya se están guardando en archivos HTML cuando `mail()` falla.
Puedes revisar estos archivos para verificar que el contenido es correcto.

---

## 📊 Interpretando los Resultados

### **Si ves "Resultado: FALLO"**
✅ **Esto es NORMAL en desarrollo local**
- XAMPP no tiene SMTP configurado por defecto
- Los correos se guardan en archivos HTML
- Puedes abrirlos para verificar el contenido
- El sistema está funcionando correctamente

### **Si ves "Resultado: ÉXITO"**
✅ **El correo se envió correctamente**
- Verifica la bandeja de entrada del destinatario
- Revisa también spam/correo no deseado

### **Si NO se crea el archivo email_log.txt**
❌ **Problema: Las funciones de correo no se están llamando**
- Verifica que el estado se esté actualizando en la BD
- Revisa que `actualizarEstadoPublicacion()` se esté ejecutando

---

## 🎯 Próximos Pasos

### **Para Desarrollo:**
1. ✅ Usa los archivos HTML guardados para verificar el contenido
2. ✅ Revisa `email_log.txt` para confirmar que se intentan enviar
3. ✅ Opcionalmente instala MailHog para ver los correos en una interfaz web

### **Para Producción:**
1. Configura un servidor SMTP real (Gmail, SendGrid, etc.)
2. Usa PHPMailer para mayor control
3. Configura autenticación SMTP
4. Verifica que los correos lleguen correctamente

---

## 📝 Ejemplo de Log Exitoso

```
2025-11-24 01:30:00 - Intento de envío a PUBLICADOR: juan@example.com
Nombre: Juan Pérez
Asunto: ✅ Tu publicación ha sido aprobada - Lab-Explorer
Resultado: ÉXITO
Estado: aprobada
Publicación: Investigación sobre IA
---

2025-11-24 01:30:01 - Intento de envío a ADMIN: admin@lab.com
Asunto: ✅ Publicación Aprobada Automáticamente - Lab-Explorer
Resultado: ÉXITO
Estado: aprobada
---
```

---

## ✅ Resumen

| Componente | Estado | Acción |
|------------|--------|--------|
| **Sistema de moderación** | ✅ Funciona | Analiza y decide |
| **Actualización de BD** | ✅ Funciona | Cambia estados |
| **Logging de correos** | ✅ Funciona | Registra intentos |
| **Guardado de HTML** | ✅ Funciona | Guarda correos |
| **Envío real (mail())** | ⚠️ Requiere config | Instalar MailHog |

**El sistema está completo y funcionando. Solo falta configurar SMTP para envío real.**
