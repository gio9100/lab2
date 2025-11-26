# 📦 Respaldos de Base de Datos - Lab Explorer

## 📋 Información General

Esta carpeta contiene los respaldos de la base de datos `lab_exp_db` del proyecto Lab Explorer.

## 📁 Archivos en esta carpeta

- **lab_exp_db_backup_YYYYMMDD_HHMMSS.sql**: Respaldo completo (estructura + datos)
- **lab_exp_db_estructura_YYYYMMDD_HHMMSS.sql**: Solo estructura de tablas (sin datos)

## 🔄 Cómo restaurar un respaldo

### Opción 1: Usando phpMyAdmin
1. Abre `http://localhost/phpmyadmin`
2. Selecciona la base de datos `lab_exp_db` (o créala si no existe)
3. Ve a la pestaña "Importar"
4. Haz clic en "Seleccionar archivo" y elige el archivo `.sql`
5. Haz clic en "Continuar"

### Opción 2: Usando línea de comandos
```bash
# Navega a la carpeta del proyecto
cd C:\xampp\htdocs\Lab\db_actualizadas

# Restaura el respaldo (reemplaza NOMBRE_ARCHIVO.sql con el nombre real)
C:\xampp\mysql\bin\mysql.exe -u root lab_exp_db < NOMBRE_ARCHIVO.sql
```

## ⚠️ Notas Importantes

- **Siempre haz un respaldo antes de hacer cambios importantes** en la base de datos
- Los archivos `.sql` contienen TODA la estructura y datos de la base de datos
- Restaurar un respaldo **sobrescribirá** todos los datos actuales
- Guarda estos archivos en un lugar seguro (considera hacer copias en la nube o en un disco externo)


