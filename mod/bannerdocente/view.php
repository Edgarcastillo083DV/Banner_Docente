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

// --- Lógica del Banner Cards (Copiada de lib.php para consistencia) ---

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
$qrImgUrl = '';
if (!empty($bannerdocente->link_grupo)) {
    $qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($bannerdocente->link_grupo);
}

// Logo (Fallback)
$logoUrl = "https://www.uapa.edu.do/wp-content/uploads/2023/10/logo-uapa-blanco.png";

// Iconos
$iconMail = '<svg class="bannerdocente-icon" viewBox="0 0 24 24"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z"></path><path d="m4 7 8 5 8-5"></path></svg>';
$iconPhone = '<svg class="bannerdocente-icon" viewBox="0 0 24 24"><path d="M6.5 3h2.7c.2 0 .4.15.45.35l.7 3a.5.5 0 0 1-.14.47L8.6 8.87a9.5 9.5 0 0 0 6.53 6.53l1.05-1.61a.5.5 0 0 1 .47-.14l3 .7c.2.05.35.25.35.46v2.7a1 1 0 0 1-1 1A12.5 12.5 0 0 1 5.5 4a1 1 0 0 1 1-1z"></path></svg>';
$iconWhatsapp = '<svg class="bannerdocente-icon" viewBox="0 0 24 24"><path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.7-1.2A9 9 0 1 0 12 3Z" fill="none"></path><path d="M9.5 8.8c.2-.3.3-.5.5-.5.1 0 .3-.1.5.1.1.1.9 1 .9 1.1 0 .1.1.3 0 .5-.2.2-.3.3-.5.5-.1.1-.3.2-.2.4.1.2.5.9 1.2 1.5.8.6 1.4.8 1.6.9.1.1.3 0 .4-.1s.5-.6.7-.8.3-.1.5-.1.3 0 .4 0 .4.2.4.3.4 1.1.4 1.3-.1.3-.3.4c-.1.1-.7.4-1.4.4-.3 0-.9-.1-1.6-.4-.9-.3-1.8-1-2.5-1.8-.3-.4-.8-1-1-1.6-.4-.8-.3-1.4-.2-1.5z" fill="none"></path></svg>';

$initial = mb_substr($nombreDocente, 0, 1);

// --- Renderizado HTML ---
echo '<div class="bannerdocente-container" style="padding: 20px;">';
?>
<div class="bannerdocente-card" style="<?php echo $bgStyle; ?>">
    <div class="bannerdocente-content">
        <!-- Header -->
        <div class="bannerdocente-header">
            <div style="display:flex; align-items:center; gap:16px;">
                <div class="bannerdocente-photo-wrapper">
                    <?php if ($photoUrl): ?>
                        <img src="<?php echo $photoUrl; ?>" alt="<?php echo s($nombreDocente); ?>" class="bannerdocente-photo" />
                    <?php else: ?>
                        <div class="bannerdocente-photo-placeholder"><?php echo $initial; ?></div>
                    <?php endif; ?>
                    <span class="bannerdocente-status-dot" title="Activo"></span>
                </div>
                <div>
                    <div class="bannerdocente-title"><?php echo s($nombreDocente); ?></div>
                    <div class="bannerdocente-school"><?php echo s($escuelaLabel); ?></div>
                </div>
            </div>
            <img class="bannerdocente-logo" src="<?php echo $logoUrl; ?>" alt="Logo UAPA" />
        </div>

        <!-- Contact -->
        <div class="bannerdocente-contact">
            <div class="bannerdocente-contact-item">
                <?php echo $iconMail; ?>
                <a href="mailto:<?php echo s($bannerdocente->email); ?>"><?php echo s($bannerdocente->email); ?></a>
            </div>
            <div class="bannerdocente-contact-item">
                <?php echo $iconPhone; ?>
                <a href="tel:<?php echo s($bannerdocente->telefono); ?>"><?php echo s($bannerdocente->telefono); ?></a>
            </div>

            <?php if ($bannerdocente->whatsapp):
                $cleanWa = preg_replace('/[^0-9]/', '', $bannerdocente->whatsapp);
            ?>
                <a href="https://wa.me/<?php echo $cleanWa; ?>" target="_blank" class="bannerdocente-button bannerdocente-btn-whatsapp-personal">
                    Chat Personal <?php echo $iconWhatsapp; ?>
                </a>
            <?php endif; ?>

            <?php if ($bannerdocente->link_grupo): ?>
                <a href="<?php echo s($bannerdocente->link_grupo); ?>" target="_blank" class="bannerdocente-button bannerdocente-btn-whatsapp-group">
                    Unirse al Grupo <?php echo $iconWhatsapp; ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Additional Info & QR -->
        <?php if (!empty($bannerdocente->info_adicional) || !empty($qrImgUrl)): ?>
            <div class="bannerdocente-additional">
                <div class="bannerdocente-extra-row">
                    <div class="bannerdocente-extra-text">
                        <?php if (!empty($bannerdocente->info_adicional)): ?>
                            <h4>Información Adicional</h4>
                            <p><?php echo nl2br(s($bannerdocente->info_adicional)); ?></p>
                        <?php elseif (!empty($bannerdocente->link_grupo)): ?>
                            <h4>Grupo de WhatsApp</h4>
                            <p>Escanea el código para unirte al grupo.</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($qrImgUrl): ?>
                        <div class="bannerdocente-qr">
                            <img src="<?php echo $qrImgUrl; ?>" alt="QR Grupo" />
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php
echo '</div>';

echo $OUTPUT->footer();
if ($bannerdocente->intro) {
    echo '<div class="bannerdocente-intro box py-3">';
    echo format_module_intro('bannerdocente', $bannerdocente, $cm->id);
    echo '</div>';
}
echo $OUTPUT->footer();
