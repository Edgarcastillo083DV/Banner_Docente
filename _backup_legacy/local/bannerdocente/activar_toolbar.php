<?php
// Script para forzar la configuración de la barra de herramientas de TinyMCE (Versión 3: Reemplazo Quirúrgico)
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/local/bannerdocente/activar_toolbar.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Activar Banner UAPA');
$PAGE->set_heading('Configuración Forzada TinyMCE - Intento 3');

echo $OUTPUT->header();

$plugin = 'editor_tiny';
$config = get_config($plugin);

echo "<h2>Estado Actual de '$plugin'</h2>";
echo "<pre>" . print_r($config, true) . "</pre>";

// LA PRUEBA DEFINITIVA:
// Vamos a definir una barra MINIMALISTA.
// Solo: Negrita, Cursiva, y NUESTRO BOTÓN.
// Quitamos la calculadora y todo lo demás.
// Si esto funciona, veremos una barra muy corta.

$toolbar_minimal = 'bold italic | tiny_banner_uapa';

if (optional_param('fix', 0, PARAM_BOOL)) {
    echo "<h3>Aplicando Cirugía a la Barra...</h3>";

    // 1. Branding fuera (Confirmado que funciona)
    set_config('branding', '0', $plugin);

    // 2. Inyectar barra minimalista en 'rows'
    set_config('rows', $toolbar_minimal, $plugin);
    echo "<li>rows => <code>$toolbar_minimal</code></li>";

    // 3. Inyectar barra minimalista en 'editor_toolbar' (por si acaso)
    set_config('editor_toolbar', $toolbar_minimal, $plugin);
    echo "<li>editor_toolbar => <code>$toolbar_minimal</code></li>";

    // 4. Limpiar json_config para evitar conflictos
    unset_config('json_config', $plugin);
    echo "<li>json_config => ELIMINADO (para forzar uso de 'rows')</li>";

    echo "<div class='alert alert-success'>✅ ¡Configuración Minimalista Aplicada!</div>";
    echo "<p><strong>QUÉ DEBE PASAR AHORA:</strong></p>";
    echo "<ul>";
    echo "<li>La calculadora DEBE DESAPARECER.</li>";
    echo "<li>Toda la barra debe ser muy corta (solo B y I).</li>";
    echo "<li><strong>Nuestro botón debería ser el tercero.</strong></li>";
    echo "</ul>";

    echo $OUTPUT->single_button(new moodle_url('/admin/purgecaches.php'), 'Ir a Purgar Cachés');
} else {
    echo "<div class='alert alert-info'>ℹ️ Intento 3: Usted tiene razón. Si controlamos el logo, controlamos la barra. Vamos a quitar la calculadora y dejar solo lo esencial + nuestro botón.</div>";

    $url = new moodle_url('/local/bannerdocente/activar_toolbar.php', ['fix' => 1]);
    echo $OUTPUT->single_button($url, 'FIX: Aplicar Barra Minimalista');
}

echo $OUTPUT->footer();
