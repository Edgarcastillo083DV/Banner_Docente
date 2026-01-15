<?php

require('../../config.php');
require_once('lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$b  = optional_param('b', 0, PARAM_INT);  // Banner instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('bannerdocente', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $bannerdocente  = $DB->get_record('bannerdocente', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($b) {
    $bannerdocente  = $DB->get_record('bannerdocente', array('id' => $b), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $bannerdocente->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('bannerdocente', $bannerdocente->id, $course->id, false, MUST_EXIST);
} else {
    print_error('mustspecifycourse', 'bannerdocente');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Trigger view event
// $event = \mod_bannerdocente\event\course_module_viewed::create(array(
//     'objectid' => $bannerdocente->id,
//     'context' => $context,
// ));
// $event->add_record_snapshot('course', $course);
// $event->add_record_snapshot('bannerdocente', $bannerdocente);
// $event->trigger();

$PAGE->set_url('/mod/bannerdocente/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bannerdocente->name));
$PAGE->set_heading(format_string($course->fullname));

// --- Output ---
echo $OUTPUT->header();

// --- Lógica del Banner Cards (Visual Fidelity V4 - Inline Styles) ---

// 1. Imagen Promocional / Foto Perfil
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_bannerdocente', 'bannerimage', 0, 'sortorder DESC, id ASC', false);
$photoUrl = '';
if (!empty($files)) {
    $file = reset($files);
    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
    $photoUrl = $url->out();
}

// 2. Datos
$nombreDocente = !empty($bannerdocente->nombre_docente) ? $bannerdocente->nombre_docente : $bannerdocente->name;
$escuelasMap = [
    'ciencias-salud-psicologia' => 'Ciencias de la Salud y Psicología',
    'ciencias-politicas-juridicas' => 'Ciencias Políticas y Jurídicas',
    'ciencias-sociales-comunicacion' => 'Ciencias Sociales y Comunicación',
    'educacion-formacion-general' => 'Educación y Formación General',
    'ingenieria-tecnologia' => 'Ingeniería y Tecnología',
    'negocios-turismo' => 'Negocios y Turismo',
    'postgrado' => 'Postgrado'
];
$escuelaLabel = isset($escuelasMap[$bannerdocente->escuela]) ? $escuelasMap[$bannerdocente->escuela] : $bannerdocente->escuela;

// 3. Colores
$primaryColor = !empty($bannerdocente->backgroundcolor) ? $bannerdocente->backgroundcolor : '#ff6f00';
$accentColor = '#ff1744';
$bgStyle = "background: linear-gradient(135deg, $primaryColor 0%, $accentColor 100%);";

// 4. QR
$qrUrl = '';
if (!empty($bannerdocente->link_grupo)) {
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($bannerdocente->link_grupo);
}

// Logo (Fallback)
$logoUrl = $CFG->wwwroot . '/mod/bannerdocente/pix/logo-uapa.png';

// Iconos SVG inline (Actualizados 24px)
$iconMail = '<svg class="icon" aria-hidden="true" style="width:24px;height:24px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z"></path><path d="m4 7 8 5 8-5"></path></svg>';
$iconPhone = '<svg class="icon" aria-hidden="true" style="width:24px;height:24px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 3h2.7c.2 0 .4.15.45.35l.7 3a.5.5 0 0 1-.14.47L8.6 8.87a9.5 9.5 0 0 0 6.53 6.53l1.05-1.61a.5.5 0 0 1 .47-.14l3 .7c.2.05.35.25.35.46v2.7a1 1 0 0 1-1 1A12.5 12.5 0 0 1 5.5 4a1 1 0 0 1 1-1z"></path></svg>';
// WhatsApp Icon Base64 (Feather Phone Icon - Standard 24px)
$iconWhatsappBase64 = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTIyIDE2LjkydjNhMiAyIDAgMCAxLTIuMTggMiAxOS43OSAxOS43OSAwIDAgMS04LjYzLTMuMDcgMTkuNSAxOS41IDAgMCAxLTYtNiAxOS43OSAxOS43OSAwIDAgMSAxIDYgMTkuNzkgMTkuNzkgMCAwIDEgMS02IDMuMDcgOS43NCAxOS43NCAwIDAgMSAyLTIuMTggMiAyIDAgMCAxIDIgMi4xOHYuOGEyIDIgMCAwIDEgMi4xOCAyIDkgOSAwIDAgMSAxIDZ6Ii8+PC9zdmc+';
$iconWhatsapp = '<img src="' . $iconWhatsappBase64 . '" alt="WhatsApp" style="width:24px;height:24px;object-fit:contain;vertical-align:middle;" />';

$initial = mb_substr($nombreDocente, 0, 1);

// --- Construcción del HTML con ESTILOS INLINE ---
// Contenedor Gradient
echo '<div class="banner-content" style="background: linear-gradient(135deg, ' . $primaryColor . ' 0%, ' . $accentColor . ' 100%); width: 100%; color: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); position: relative; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; margin-bottom: 20px;">';

// --- Header ---
echo '<div class="banner-header" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">';
echo '<div style="display:flex; align-items:center; gap:16px;">';

// Foto
echo '<div class="banner-photo-wrapper" style="position: relative; width: 80px; height: 80px; flex-shrink: 0;">';
if ($photoUrl) {
    echo '<img src="' . $photoUrl . '" alt="Foto Docente" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);" />';
} else {
    echo '<div style="width: 100%; height: 100%; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: white; border: 4px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">' . $initial . '</div>';
}
// Status dot
echo '<span style="position: absolute; bottom: 0; right: 0; width: 16px; height: 16px; border-radius: 50%; background: #25d366; border: 2px solid white; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"></span>';
echo '</div>';

// Texto Nombre
echo '<div>';
echo '<div style="font-size: 24px; font-weight: bold; margin-bottom: 4px; color: white; line-height: 1.2;">' . s($nombreDocente) . '</div>';
echo '<div style="font-size: 18px; opacity: 0.95; color: white;">' . s($escuelaLabel) . '</div>';
echo '</div>';
echo '</div>'; // Fin izq

// Logo
echo '<img src="' . $logoUrl . '" alt="Logo UAPA" style="height: 80px; max-width: 200px; object-fit: contain;" />';
echo '</div>'; // Fin Header

// --- Contacto ---
echo '<div class="banner-contact" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 20px; margin-bottom: 15px; border: 1px solid rgba(255, 255, 255, 0.2);">';

// Email
if (!empty($bannerdocente->email)) {
    echo '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 18px; color: white;">';
    echo $iconMail;
    echo '<a href="mailto:' . s($bannerdocente->email) . '" style="color: white; text-decoration: none;">' . s($bannerdocente->email) . '</a>';
    echo '</div>';
}

// Teléfono
if (!empty($bannerdocente->telefono)) {
    $phoneLink = preg_replace('/[^0-9+]/', '', $bannerdocente->telefono);
    echo '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 18px; color: white;">';
    echo $iconPhone;
    echo '<a href="tel:' . $phoneLink . '" style="color: white; text-decoration: none;">' . s($bannerdocente->telefono) . '</a>';
    echo '</div>';
}

// Botones WhatsApp
// Chat Personal
if (!empty($bannerdocente->whatsapp)) {
    $waNum = preg_replace('/[^0-9]/', '', $bannerdocente->whatsapp);
    $waUrl = 'https://wa.me/' . $waNum;
    echo '<a href="' . $waUrl . '" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 10px; font-size: 16px; text-decoration: none; color: white; background: #25d366;">';
    echo '<span>Chat Personal</span> ';
    echo $iconWhatsapp;
    echo '</a>';
}

// Grupo
if (!empty($bannerdocente->link_grupo)) {
    echo '<a href="' . s($bannerdocente->link_grupo) . '" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 10px; font-size: 16px; text-decoration: none; color: white; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5);">';
    echo '<span>Unirse al Grupo</span> ';
    echo $iconWhatsapp;
    echo '</a>';
}

echo '</div>'; // Fin contact

// --- Info Adicional / QR ---
$hasComments = !empty($bannerdocente->info_adicional) && trim($bannerdocente->info_adicional) !== '';
if ($hasComments || !empty($qrUrl)) {
    echo '<div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 15px; border: 1px solid rgba(255, 255, 255, 0.2); color: white;">';
    echo '<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;">';

    echo '<div style="flex: 1;">';
    if ($hasComments) {
        echo '<h4 style="font-size: 18px; font-weight: 600; margin-bottom: 10px; color: white; margin-top: 0;">Información Adicional</h4>';
        echo '<p style="font-size: 16px; line-height: 1.5; opacity: 0.95; margin-bottom: 0; color: white;">' . nl2br(s($bannerdocente->info_adicional)) . '</p>';
    } else {
        echo '<h4 style="font-size: 18px; font-weight: 600; margin-bottom: 10px; color: white; margin-top: 0;">Grupo de WhatsApp</h4>';
        echo '<p style="font-size: 16px; line-height: 1.5; opacity: 0.95; margin-bottom: 0; color: white;">Escanea el código para unirte al grupo.</p>';
    }
    echo '</div>';

    if (!empty($qrUrl)) {
        echo '<div style="background: rgba(255, 255, 255, 0.9); border-radius: 10px; padding: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); flex-shrink: 0;">';
        echo '<img src="' . $qrUrl . '" alt="QR Grupo" style="display: block; width: 96px; height: 96px; border-radius: 6px;" />';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}

echo '</div>'; // Fin banner-content

echo $OUTPUT->footer();
if ($bannerdocente->intro) {
    echo '<div class="bannerdocente-intro box py-3">';
    echo format_module_intro('bannerdocente', $bannerdocente, $cm->id);
    echo '</div>';
}
echo $OUTPUT->footer();
