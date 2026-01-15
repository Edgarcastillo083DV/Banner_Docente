<?php
// Script para forzar la configuración de la barra de herramientas de TinyMCE (Versión Agresiva)
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/local/bannerdocente/activar_toolbar.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Activar Banner UAPA');
$PAGE->set_heading('Configuración Forzada TinyMCE - Intento 2');

echo $OUTPUT->header();

$plugin = 'editor_tiny';
$config = get_config($plugin);

echo "<h2>Estado Actual de '$plugin'</h2>";
echo "<pre>" . print_r($config, true) . "</pre>";

$default_toolbar = 'undo redo | bold italic | link | tiny_banner_uapa';

if (optional_param('fix', 0, PARAM_BOOL)) {
    echo "<h3>Aplicando cambios...</h3>";

    // 1. Desactivar branding (Prueba de control)
    set_config('branding', '0', $plugin);
    echo "<li>branding => 0 (Si desaparece el logo 'Tiny', tenemos control)</li>";

    // 2. Intento via 'rows'
    set_config('rows', $default_toolbar, $plugin);
    echo "<li>rows => $default_toolbar</li>";

    // 3. Intento via 'editor_toolbar'
    set_config('editor_toolbar', $default_toolbar, $plugin);
    echo "<li>editor_toolbar => $default_toolbar</li>";

    // 4. Intento via 'json_config' (Formato JSON estricto)
    $json_val = json_encode(['toolbar' => $default_toolbar]);
    set_config('json_config', $json_val, $plugin);
    echo "<li>json_config => $json_val</li>";

    echo "<div class='alert alert-success'>✅ ¡Todas las configuraciones inyectadas!</div>";
    echo "<p><strong>Siguientes pasos:</strong></p>";
    echo "<ol><li>Purgue todas las cachés.</li><li>Recargue el editor.</li></ol>";
    echo "<p><strong>¿Qué buscar?</strong><br>1. ¿Desapareció el logo 'Powered by TinyMCE'?<br>2. ¿Cambió la barra?</p>";
    echo $OUTPUT->single_button(new moodle_url('/admin/purgecaches.php'), 'Ir a Purgar Cachés');
} else {
    echo "<div class='alert alert-info'>ℹ️ La prueba anterior falló. Vamos a intentar inyectar la configuración en TODOS los lugares posibles y desactivar el branding para confirmar control.</div>";

    $url = new moodle_url('/local/bannerdocente/activar_toolbar.php', ['fix' => 1]);
    echo $OUTPUT->single_button($url, 'FIX: Inyectar configuración agresiva');
}

echo $OUTPUT->footer();
