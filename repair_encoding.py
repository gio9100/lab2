import os

# Define replacements as strings and encode them
replacements_str = {
    'Administraci?n': 'Administración',
    'Informaci?n': 'Información',
    'Configuraci?n': 'Configuración',
    'Publicaci?n': 'Publicación',
    'Acci?n': 'Acción',
    'Sesi?n': 'Sesión',
    'Descripci?n': 'Descripción',
    'Creaci?n': 'Creación',
    'Edici?n': 'Edición',
    'Eliminaci?n': 'Eliminación',
    'Moderaci?n': 'Moderación',
    'P?gina': 'Página',
    'Tel?fono': 'Teléfono',
    'B?squeda': 'Búsqueda',
    'M?s': 'Más',
    'Est?': 'Está',
    'Tambi?n': 'También',
    'Despu?s': 'Después',
    'Adem?s': 'Además',
    'Est?n': 'Están',
    'd?as': 'días',
    'fr?o': 'frío',
    't?tulo': 'título',
    'autom?tica': 'automática',
    'electr?nico': 'electrónico',
    'com?n': 'común',
    'ingl?s': 'inglés',
    'secci?n': 'sección',
    'bot?n': 'botón',
    'im?gen': 'imágen',
    'im?genes': 'imágenes',
    'aqu?': 'aquí',
    's?': 'sí',
    'tr?fico': 'tráfico',
    'an?lisis': 'análisis',
    'categor?a': 'categoría',
    '?xito': 'éxito',
    'c?digo': 'código',
    'n?mero': 'número',
    'contrase?a': 'contraseña',
    'Espa?ol': 'Español',
    'espa?ol': 'español',
    'A?o': 'Año',
    'a?o': 'año',
    'Dise?o': 'Diseño',
}

# Convert keys and values to bytes using utf-8
replacements = {k.encode('utf-8'): v.encode('utf-8') for k, v in replacements_str.items()}

target_dir = r"c:\xampp\htdocs\lab2\forms\admins"

for root, dirs, files in os.walk(target_dir):
    for file in files:
        if file.endswith(".php"):
            path = os.path.join(root, file)
            try:
                with open(path, 'rb') as f:
                    content = f.read()

                # Perform replacements
                new_content = content
                for wrong, right in replacements.items():
                    new_content = new_content.replace(wrong, right)
                
                # Force replace sidebarWrapper properly
                new_content = new_content.replace(b'id="sidebarWrapper"', b'id="sidebar-wrapper"')
                
                # Write back
                with open(path, 'wb') as f:
                    f.write(new_content)
                    
                print(f"Repaired {file}")
            except Exception as e:
                print(f"Error repairing {file}: {e}")
