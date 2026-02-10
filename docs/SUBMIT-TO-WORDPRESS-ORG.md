# Cómo subir R2 Cloud Backup a WordPress.org

## 1. Zip listo

El paquete para enviar está en:

**`dist/r2-cloud-backup.zip`**

Contenido del zip:
- Carpeta **r2-cloud-backup/** con: `r2-wordpress-backup.php`, `uninstall.php`, `readme.txt`, `LICENSE`, `includes/`, `admin/`, `languages/`.
- No incluye: `docs/`, `.git`, ni archivos de desarrollo.

Para regenerar el zip desde la raíz del repo:

```bash
mkdir -p dist-build/r2-cloud-backup
cp r2-wordpress-backup.php uninstall.php readme.txt LICENSE dist-build/r2-cloud-backup/
cp -r includes admin languages dist-build/r2-cloud-backup/
(cd dist-build && zip -r ../dist/r2-cloud-backup.zip r2-cloud-backup)
rm -rf dist-build
```

---

## 2. Revisión del readme.txt

- **Stable tag:** 1.0.4 (debe coincidir con la versión en el archivo principal del plugin).
- **Tags:** 5 (máximo permitido): backup, cloudflare, s3, database, restore.
- **Donate link:** Enlace directo, sin afiliados ni redirecciones.
- **Tested up to:** 6.7 (actualiza cuando pruebes con una versión más nueva de WordPress).
- Sin enlaces de afiliados ni spam; texto orientado a usuarios.

Si cambias la versión del plugin, actualiza también **Stable tag** en `readme.txt` y la cabecera del plugin.

---

## 3. Pasos para subir a WordPress.org

### A. Cuenta y perfil

1. Entra en [wordpress.org](https://wordpress.org) y **inicia sesión** (o crea una cuenta).
2. Ve a tu **perfil** y revisa que tu **email** sea correcto y que no sea un auto-respondedor (el equipo de plugins lo usa para notificaciones).
3. Opcional: en el perfil puedes añadir tu web y enlace de donación.

### B. Enviar el plugin

1. Abre la página de **“Añadir tu plugin”**:  
   **https://wordpress.org/plugins/developers/add/**
2. Acepta los **Plugin Directory Guidelines** (checkbox).
3. **Sube el zip:** elige `dist/r2-cloud-backup.zip`.
4. **Plugin URL (opcional):** si tienes una página del plugin, puedes ponerla (por ejemplo la landing en GitHub Pages).
5. **Descripción (opcional):** breve resumen para el revisor (ej.: “Backups completos de WordPress a Cloudflare R2 (archivos + base de datos). Export, import, programación y ajustes.”).
6. Envía el formulario.

### C. Después del envío

- Recibirás un **email** en la cuenta de WordPress.org cuando lo revisen (puede tardar varios días).
- Si piden cambios, te indicarán qué ajustar; responde o corrige según las instrucciones.
- Si lo aprueban, te darán acceso al **repositorio SVN** del plugin en WordPress.org.

### D. Cuando esté aprobado: SVN

1. **URL del SVN** (te la envían por email; el slug puede ser el que ellos asignen, p. ej. `r2-cloud-backup`):
   ```
   https://plugins.svn.wordpress.org/r2-cloud-backup/
   ```

2. **Estructura típica en SVN:**
   - **`trunk/`** – código de la versión en desarrollo (lo que se descarga como “versión estable” si `Stable tag` en readme es `trunk`).
   - **`tags/1.0.4/`** – copia del código de cada versión (1.0.4, 1.0.5, etc.).
   - **`assets/`** – banners e iconos del plugin en el directorio (opcional).

3. **Primera subida tras la aprobación:**
   - Haz un **checkout** del repositorio SVN.
   - Copia el contenido de tu plugin (igual que en el zip: archivos dentro de la carpeta del plugin) a **`trunk/`**.
   - Crea **`tags/1.0.4/`** como copia de `trunk` para marcar la versión 1.0.4.
   - Haz **commit** de trunk y del tag.

4. **Para cada nueva versión (ej. 1.0.5):**
   - Actualiza la versión en el archivo principal del plugin y en `readme.txt` (cabecera + **Stable tag**).
   - Regenera el zip (o usa el mismo contenido que subirás a SVN).
   - Sube los cambios a **`trunk/`**.
   - Crea un nuevo tag: **`tags/1.0.5/`** como copia de `trunk`.
   - Haz commit; WordPress.org generará el zip de descarga y la ficha del plugin a partir del readme y del tag estable.

Documentación oficial de SVN para plugins:
- [How to Use Subversion](https://developer.wordpress.org/plugins/developers/how-to-use-subversion/)
- [Readme.txt and tagging](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/#readmetxt-and-tagging)

### E. Consejos

- **Stable tag:** En el `readme.txt` que está en **trunk**, el campo **Stable tag** debe tener el número de la última versión que exista en **tags/** (ej. `1.0.4`). Así el directorio ofrecerá esa versión para descarga.
- No hagas commits muy seguidos con mensajes vagos (“update”, “fix”); usa mensajes claros para cada cambio.
- Mantén el código en GitHub (o donde desarrolles) y usa SVN como “espejo” de lo que quieres publicar en WordPress.org.
- Si te piden cambios por las guidelines, aplica las correcciones y vuelve a subir o comenta en el ticket/email que te indiquen.

---

## 4. Resumen rápido

| Paso | Acción |
|------|--------|
| 1 | Zip en `dist/r2-cloud-backup.zip` |
| 2 | Cuenta WordPress.org con email válido |
| 3 | [wordpress.org/plugins/developers/add/](https://wordpress.org/plugins/developers/add/) → subir zip y enviar |
| 4 | Esperar email del equipo de plugins |
| 5 | Si aprueban: usar SVN para trunk y tags (p. ej. `tags/1.0.4`) |
| 6 | En readme en trunk: **Stable tag** = última versión en tags |

Si quieres, el siguiente paso puede ser preparar los comandos SVN concretos (checkout, add, commit) para tu máquina una vez te den la URL del repo.
