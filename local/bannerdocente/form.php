<?php
require_once('../../config.php');

// Validar login
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/bannerdocente/form.php'));
$PAGE->set_pagelayout('embedded'); // Layout limpio sin headers de Moodle
$PAGE->set_title('Editor de Banner');

echo $OUTPUT->header();
?>

<style>
    /* Estilos base adaptados del proyecto original */
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f7fa; padding: 20px; }
    .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; }
    
    /* Layout */
    .layout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    @media (max-width: 800px) { .layout-grid { grid-template-columns: 1fr; } }
    
    /* Formulario */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
    .form-group input, .form-group select, .form-group textarea { 
        width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box;
    }
    textarea { resize: vertical; min-height: 80px; }

    /* Preview del Banner */
    .banner-preview-container { 
        position: sticky; top: 20px; 
        background: #eee; border-radius: 12px; padding: 20px; 
        display: flex; align-items: center; justify-content: center; min-height: 300px;
    }
    
    /* Estilos del Banner (Críticos para el renderizado final) */
    .banner-root { width: 800px; height: 420px; overflow: hidden; position: relative; background: #fff; font-family: 'Open Sans', sans-serif; transform-origin: top left; transform: scale(0.5); }
    /* Nota: El scale es para que quepa en el preview, al guardar guardaremos el HTML limpio */
    
    /* ... (Aquí se pueden agregar más estilos del index.html original) ... */
</style>

<div class="container">
    <div class="layout-grid">
        <!-- Columna Izquierda: Formulario -->
        <div class="form-section">
            <h2 style="margin-top:0;">Datos del Facilitador</h2>
            
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" id="name" placeholder="Ej: Prof. Maria Garcia">
            </div>

            <div class="form-group">
                <label>Escuela</label>
                <select id="school">
                    <option value="">Seleccionar Escuela</option>
                    <option value="Escuela de Educación">Escuela de Educación</option>
                    <option value="Escuela de Ciencia y Tecnología">Escuela de Ciencia y Tecnología</option>
                    <option value="Escuela de Humanidades">Escuela de Humanidades</option>
                    <option value="Escuela de Negocios">Escuela de Negocios</option>
                    <option value="Escuela de Salud">Escuela de Salud</option>
                    <option value="Escuela de Artes">Escuela de Artes</option>
                </select>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email">
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" id="phone">
            </div>

            <!-- ... (Más campos según necesidad) ... -->
        </div>

        <!-- Columna Derecha: Vista Previa -->
        <div class="preview-section">
            <h3>Vista Previa</h3>
            <div class="banner-preview-container">
                <div id="banner-render" class="banner-root">
                    <div style="padding: 40px;">
                        <h1 id="preview-name" style="color: #1a4d7e; font-size: 32px; margin: 0;">Prof. Maria Garcia</h1>
                        <h2 id="preview-school" style="color: #e53935; font-size: 24px; margin: 10px 0;">Escuela de Educación</h2>
                        <p id="preview-email" style="font-size: 18px; color: #555;">email@ejemplo.com</p>
                    </div>
                </div>
            </div>
            <p><small>El banner se guardará y mostrará en el curso tal como se ve aquí.</small></p>
        </div>
    </div>
</div>

<script>
    // Lógica básica de actualización en tiempo real
    const inputs = ['name', 'school', 'email', 'phone'];
    
    inputs.forEach(id => {
        document.getElementById(id).addEventListener('input', updatePreview);
    });

    function updatePreview() {
        const data = getFormData();
        document.getElementById('preview-name').textContent = data.name || 'Nombre del Facilitador';
        document.getElementById('preview-school').textContent = data.school || 'Escuela';
        document.getElementById('preview-email').textContent = data.email || 'email@ejemplo.com';
    }

    function getFormData() {
        return {
            name: document.getElementById('name').value,
            school: document.getElementById('school').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value
        };
    }

    // Esta función es llamada por el modal.js de Moodle
    window.getBannerData = function() {
        const data = getFormData();
        
        // Obtenemos el HTML del banner (limpiando el transform de escala para que se guarde a tamaño real)
        const bannerClone = document.getElementById('banner-render').cloneNode(true);
        bannerClone.style.transform = 'none'; 
        
        return {
            json_config: data,    // Datos crudos para rellenar el form al editar
            html: bannerClone.outerHTML  // HTML visual para el filtro
        };
    };

    // Inicializar
    updatePreview();
</script>

<?php
echo $OUTPUT->footer();
