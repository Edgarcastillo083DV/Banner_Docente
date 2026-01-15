<?php

defined('MOODLE_INTERNAL') || die();

use context_module;
use moodle_url;
use stdClass;
use cached_cm_info;

/**
 * Agrega una nueva instancia de bannerdocente.
 *
 * @param stdClass $data Datos del formulario
 * @param mod_bannerdocente_mod_form $mform El formulario
 * @return int ID de la nueva instancia
 */
function bannerdocente_add_instance($data, $mform = null)
{
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Guardar registro principal
    $data->id = $DB->insert_record('bannerdocente', $data);

    // NOTA: No podemos guardar la imagen aquí porque el Contexto del Módulo (CM) 
    // aún no existe en este punto. Moodle crea el CM después de que retornamos el ID.
    // La imagen se guardará si el usuario edita la actividad después.

    return $data->id;
}

/**
 * Actualiza una instancia de bannerdocente.
 *
 * @param stdClass $data Datos del formulario
 * @param mod_bannerdocente_mod_form $mform El formulario
 * @return bool True si tuvo éxito
 */
function bannerdocente_update_instance($data, $mform = null)
{
    global $DB;

    // Actualizar registro
    // Aseguramos que el ID sea el de la instancia (registro en la tabla)
    $data->id = $data->instance;
    $DB->update_record('bannerdocente', $data);

    // Actualizar imagen
    $context = \context_module::instance($data->coursemodule);
    if (isset($data->bannerimage)) {
        file_save_draft_area_files($data->bannerimage, $context->id, 'mod_bannerdocente', 'bannerimage', 0, array('subdirs' => 0, 'maxfiles' => 1));
    }

    return true;
}

/**
 * Elimina una instancia de bannerdocente.
 *
 * @param int $id ID de la instancia
 * @return bool True si tuvo éxito
 */
function bannerdocente_delete_instance($id)
{
    global $DB;

    if (!$record = $DB->get_record('bannerdocente', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('bannerdocente', array('id' => $id));

    return true;
}

/**
 * Sirve los archivos del módulo (imágenes del banner).
 *
 * @param stdClass $course Curso
 * @param stdClass $cm Course Module
 * @param context $context Contexto
 * @param string $filearea Área (bannerimage)
 * @param array $args Argumentos
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function bannerdocente_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea !== 'bannerimage') {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_bannerdocente/$filearea/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Soporte para características (Feature support)
 * @param string $feature CARACTERÍSTICA_CONSTANTE
 * @return mixed
 */
function bannerdocente_supports($feature)
{
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_NO_VIEW_LINK:
            return true;
        case FEATURE_IDNUMBER:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Permite que el banner se muestre DIRECTAMENTE en la página del curso (estilo Etiqueta).
 * 
 * @param object $coursemodule
 * @return cached_cm_info
 */
function bannerdocente_get_coursemodule_info($coursemodule)
{
    global $DB;

    // Obtener registro completo con los nuevos campos
    $db_record = $DB->get_record('bannerdocente', array('id' => $coursemodule->instance), '*', MUST_EXIST);
    $result = new cached_cm_info();
    $result->name = $db_record->name;

    // --- Lógica de Imágenes ---
    $context = \context_module::instance($coursemodule->id);
    $fs = get_file_storage();

    // 1. Foto de Perfil (usamos el area 'bannerimage' que definimos en el form)
    // NOTA: En mod_form usamos 'bannerimage' para la foto del perfil ahora.
    $files = $fs->get_area_files($context->id, 'mod_bannerdocente', 'bannerimage', 0, 'sortorder DESC, id ASC', false);
    $photoUrl = '';
    if (!empty($files)) {
        $file = reset($files);
        // Usamos moodle_url para generar una URL absoluta correcta
        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        $photoUrl = $url->out();
    }

    // --- Datos del Docente ---
    $nombreDocente = !empty($db_record->nombre_docente) ? $db_record->nombre_docente : $db_record->name;
    $escuelaKey = $db_record->escuela;
    // Mapeo manual de Escuelas si no usamos get_string para todo (Para coincidir con el JS del usuario)
    $escuelasMap = [
        'ciencias-salud-psicologia' => 'Ciencias de la Salud y Psicología',
        'ciencias-politicas-juridicas' => 'Ciencias Políticas y Jurídicas',
        'ciencias-sociales-comunicacion' => 'Ciencias Sociales y Comunicación',
        'educacion-formacion-general' => 'Educación y Formación General',
        'ingenieria-tecnologia' => 'Ingeniería y Tecnología',
        'negocios-turismo' => 'Negocios y Turismo',
        'postgrado' => 'Postgrado'
    ];
    $escuelaLabel = isset($escuelasMap[$escuelaKey]) ? $escuelasMap[$escuelaKey] : $escuelaKey;

    $email = $db_record->email;
    $telefono = $db_record->telefono;
    $whatsapp = $db_record->whatsapp;
    $linkGrupo = $db_record->link_grupo;
    $infoAdicional = $db_record->info_adicional;

    // --- Colores ---
    // El usuario usa gradientes. Usaremos el 'backgroundcolor' como primario y generaremos un secundario o usaremos un default.
    $primaryColor = !empty($db_record->backgroundcolor) ? $db_record->backgroundcolor : '#ff6f00'; // Sunset default
    $accentColor = '#ff1744'; // Default accent for Sunset
    // Si el usuario eligió azul (ej. #1a4d7e), cambiamos el acento.
    // Lógica simple: Si es azulado, acento rojo. Si es naranja, acento rojo/rosa.
    // Por ahora para fidelidad, usaremos el gradiente hardcodeado del tema "Sunset" si no se especifica otro,
    // o el color elegido como start y un derivado como end.

    // Simplificación: Usar el backgroundcolor como Start y un tono más oscuro o rotado como End.
    $bgStyle = "background: linear-gradient(135deg, $primaryColor 0%, $accentColor 100%);";

    // --- QR Code ---
    // Usamos API pública rápida y segura para generar imagen QR del link del grupo
    $qrImgUrl = '';
    if (!empty($linkGrupo)) {
        $qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($linkGrupo);
    }

    // --- Construcción del HTML (Replicando editor-unificado.html) ---
    // Usamos clases CSS definidas en styles.css (bannerdocente-*)

    // Iconos SVG inline (Optimizados y con tamaño fijo)
    // Se fuerza width/height y se asegura que la clase CSS controle el resto
    $iconMail = '<svg class="bannerdocente-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>';
    $iconPhone = '<svg class="bannerdocente-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
    $iconWhatsapp = '<svg class="bannerdocente-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"></path><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"></path></svg>'; // Simplified WA or keep original if valid

    // Restauramos el icono de WhatsApp original que es más detallado, pero con atributos correctos
    $iconWhatsapp = '<svg class="bannerdocente-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>';


    // Logo UAPA: Usar la imagen local copiada en pix/logo-uapa.png
    // Usamos moodle_url para generar la ruta correcta al directorio de imágenes del módulo
    $logoUrl = new moodle_url('/mod/bannerdocente/pix/logo-uapa.png');
    $logoUrl = $logoUrl->out();

    $initial = mb_substr($nombreDocente, 0, 1);

    ob_start();
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
                    <a href="mailto:<?php echo s($email); ?>"><?php echo s($email); ?></a>
                </div>
                <div class="bannerdocente-contact-item">
                    <?php echo $iconPhone; ?>
                    <a href="tel:<?php echo s($telefono); ?>"><?php echo s($telefono); ?></a>
                </div>

                <?php if ($whatsapp):
                    $cleanWa = preg_replace('/[^0-9]/', '', $whatsapp);
                ?>
                    <a href="https://wa.me/<?php echo $cleanWa; ?>" target="_blank" class="bannerdocente-button bannerdocente-btn-whatsapp-personal">
                        Chat Personal <?php echo $iconWhatsapp; ?>
                    </a>
                <?php endif; ?>

                <?php if ($linkGrupo): ?>
                    <a href="<?php echo s($linkGrupo); ?>" target="_blank" class="bannerdocente-button bannerdocente-btn-whatsapp-group">
                        Unirse al Grupo <?php echo $iconWhatsapp; ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Additional Info & QR -->
            <?php if (!empty($infoAdicional) || !empty($qrImgUrl)): ?>
                <div class="bannerdocente-additional">
                    <div class="bannerdocente-extra-row">
                        <div class="bannerdocente-extra-text">
                            <?php if (!empty($infoAdicional)): ?>
                                <h4>Información Adicional</h4>
                                <p><?php echo nl2br(s($infoAdicional)); ?></p>
                            <?php elseif (!empty($linkGrupo)): ?>
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
    $content = ob_get_clean();

    $result->content = $content;
    return $result;
}
