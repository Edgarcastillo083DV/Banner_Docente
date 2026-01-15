<?php
// Script para forzar la configuración de la barra de herramientas de TinyMCE
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/local/bannerdocente/activar_toolbar.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Activar Banner UAPA');
$PAGE->set_heading('Configuración Forzada TinyMCE');

echo $OUTPUT->header();

$plugin = 'editor_tiny';
$config = get_config($plugin);

echo "<h2>Configuración Actual de '$plugin'</h2>";

$toolbar_keys = ['rows', 'toolbar', 'editor_toolbar', 'customtoolbar', 'json_config'];
$found = false;

echo "<pre>";
print_r($config);
echo "</pre>";

// Estrategia: Si no hay configuración explícita, MOODLE USA DEFAULTS.
// Para agregar nuestro botón, debemos CREAR una configuración que anule los defaults.
// Probaremos inyectando en 'rows' (común en Tiny 6) y 'branding' (para probar escritura).

$default_toolbar = 'undo redo | blocks | bold italic | link | tiny_banner_uapa';

if (optional_param('fix', 0, PARAM_BOOL)) {
    // Forzar creación de configuración
    set_config('rows', $default_toolbar, $plugin);
    // Algunos Moodle 4 usan json_config para override
    // set_config('json_config', '{"toolbar": "'.$default_toolbar.'"}', $plugin); 

    echo "<div class='alert alert-success'>✅ ¡Configuración 'rows' creada forzosamente!</div>";
    echo "<p>Valor inyectado: <code>$default_toolbar</code></p>";
    echo "<p><strong>Siguientes pasos:</strong></p>";
    echo "<ol><li>Purgue todas las cachés.</li><li>Verifique si el editor cambió (debería ver menos botones, pero INCLUYENDO EL NUESTRO).</li></ol>";
    echo "<p>Si esto funciona pero faltan botones, luego podemos editar la barra en la administración para agregar los que faltan.</p>";
    echo $OUTPUT->single_button(new moodle_url('/admin/purgecaches.php'), 'Ir a Purgar Cachés');
} else {
    echo "<div class='alert alert-warning'>⚠️ No se detectó configuración de barra personalizada. Se están usando los valores predeterminados de Moodle.</div>";
    echo "<p>Para ver el botón, debemos <strong>sobreescribir</strong> los valores predeterminados con una barra personalizada que incluya nuestro botón.</p>";

    $url = new moodle_url('/local/bannerdocente/activar_toolbar.php', ['fix' => 1]);
    echo $OUTPUT->single_button($url, 'FIX: Forzar creación de barra personalizada');
}

echo $OUTPUT->footer();
