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

    // Preparar objeto limpio para actualizar la base de datos
    // Esto evita errores de "campo desconocido" si $data trae campos extra del formulario (ej. submitbutton, files)
    $updateData = new stdClass();
    $updateData->id = $data->instance;
    $updateData->name = $data->name;
    $updateData->periodo = $data->periodo;
    $updateData->cedula = $data->cedula;
    $updateData->nombre_docente = $data->nombre_docente;
    $updateData->escuela = $data->escuela;
    $updateData->email = $data->email;
    $updateData->telefono = $data->telefono;
    $updateData->whatsapp = $data->whatsapp;
    $updateData->link_grupo = $data->link_grupo;
    $updateData->info_adicional = $data->info_adicional;
    $updateData->backgroundcolor = $data->backgroundcolor;
    $updateData->timemodified = time();

    // Actualizar registro en DB
    $DB->update_record('bannerdocente', $updateData);

    // Actualizar imagen (gestionada por separado como borrador)
    $context = \context_module::instance($data->coursemodule);
    if (isset($data->bannerimage)) {
        // file_save_draft_area_files maneja la lógica de mover del área draft a la área final
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
    $qrUrl = '';
    if (!empty($linkGrupo)) {
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($linkGrupo);
    }

    // --- Logo URL ---
    // Intentamos buscarlo en la carpeta pix del plugin
    $logoUrl = $CFG->wwwroot . '/mod/bannerdocente/pix/logo-uapa.png';

    // --- Construcción del HTML (Replicando editor-unificado.html) ---
    // Usamos clases CSS definidas en styles.css (bannerdocente-*)

    // Iconos SVG inline (Optimizados y con tamaño fijo)
    // --- GENERACIÓN DEL CONTENIDO HTML (Diseño V4 - Estilos Inline para Garantía Visual) ---

    // Iconos SVG (Uso de comillas simples para evitar conflictos)
    $iconMail = '<svg class="icon" aria-hidden="true" style="width:24px;height:24px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z"></path><path d="m4 7 8 5 8-5"></path></svg>';
    $iconPhone = '<svg class="icon" aria-hidden="true" style="width:24px;height:24px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 3h2.7c.2 0 .4.15.45.35l.7 3a.5.5 0 0 1-.14.47L8.6 8.87a9.5 9.5 0 0 0 6.53 6.53l1.05-1.61a.5.5 0 0 1 .47-.14l3 .7c.2.05.35.25.35.46v2.7a1 1 0 0 1-1 1A12.5 12.5 0 0 1 5.5 4a1 1 0 0 1 1-1z"></path></svg>';
    // WhatsApp Icon Base64 (Feather Phone Icon - Standard 24px)
    $iconWhatsappBase64 = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTIyIDE2LjkydjNhMiAyIDAgMCAxLTIuMTggMiAxOS43OSAxOS43OSAwIDAgMS04LjYzLTMuMDcgMTkuNSAxOS41IDAgMCAxLTYtNiAxOS43OSAxOS43OSAwIDAgMSAxIDYgMTkuNzkgMTkuNzkgMCAwIDEgMS02IDMuMDcgOS43NCAxOS43NCAwIDAgMSAyLTIuMTggMiAyIDAgMCAxIDIgMi4xOHYuOGEyIDIgMCAwIDEgMi4xOCAyIDkgOSAwIDAgMSAxIDZ6Ii8+PC9zdmc+';
    $iconWhatsapp = '<img src="' . $iconWhatsappBase64 . '" alt="WhatsApp" style="width:24px;height:24px;object-fit:contain;vertical-align:middle;" />';

    // Mapa de Escuelas
    $schoolLabels = [
        'ciencias-salud-psicologia' => 'Ciencias de la Salud y Psicología',
        'ciencias-politicas-juridicas' => 'Ciencias Políticas y Jurídicas',
        'ciencias-sociales-comunicacion' => 'Ciencias Sociales y Comunicación',
        'educacion-formacion-general' => 'Educación y Formación General',
        'ingenieria-tecnologia' => 'Ingeniería y Tecnología',
        'negocios-turismo' => 'Negocios y Turismo',
        'postgrado' => 'Postgrado'
    ];
    $displaySchool = isset($schoolLabels[$db_record->escuela]) ? $schoolLabels[$db_record->escuela] : $db_record->escuela;

    $initial = !empty($db_record->nombre_docente) ? mb_substr($db_record->nombre_docente, 0, 1) : '?';

    // Construcción del HTML con ESTILOS INLINE para asegurar visualización sin dependencia de CSS externo
    $content = '';

    // Contenedor Gradient
    $content .= '<div class="banner-content" style="background: linear-gradient(135deg, ' . $primaryColor . ' 0%, ' . $accentColor . ' 100%); width: 100%; color: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); position: relative; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; margin-bottom: 20px;">';

    // --- Header ---
    $content .= '<div class="banner-header" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">';
    $content .= '<div style="display:flex; align-items:center; gap:16px;">';

    // Foto
    $content .= '<div class="banner-photo-wrapper" style="position: relative; width: 80px; height: 80px; flex-shrink: 0;">';
    if ($photoUrl) {
        $content .= '<img src="' . $photoUrl . '" alt="Foto Docente" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);" />';
    } else {
        $content .= '<div style="width: 100%; height: 100%; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: white; border: 4px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);">' . $initial . '</div>';
    }
    // Status dot
    $content .= '<span style="position: absolute; bottom: 0; right: 0; width: 16px; height: 16px; border-radius: 50%; background: #25d366; border: 2px solid white; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);"></span>';
    $content .= '</div>';

    // Texto Nombre
    $content .= '<div>';
    $content .= '<div style="font-size: 24px; font-weight: bold; margin-bottom: 4px; color: white; line-height: 1.2;">' . s($db_record->nombre_docente) . '</div>';
    $content .= '<div style="font-size: 18px; opacity: 0.95; color: white;">' . s($displaySchool) . '</div>';
    $content .= '</div>';
    $content .= '</div>'; // Fin izq

    // Logo (Con altura forzada)
    $content .= '<img src="' . $logoUrl . '" alt="Logo UAPA" style="height: 80px; max-width: 200px; object-fit: contain;" />';
    $content .= '</div>'; // Fin Header

    // --- Contacto ---
    $content .= '<div class="banner-contact" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 20px; margin-bottom: 15px; border: 1px solid rgba(255, 255, 255, 0.2);">';

    // Email
    if (!empty($db_record->email)) {
        $content .= '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 18px; color: white;">';
        $content .= $iconMail;
        $content .= '<a href="mailto:' . s($db_record->email) . '" style="color: white; text-decoration: none;">' . s($db_record->email) . '</a>';
        $content .= '</div>';
    }

    // Teléfono
    if (!empty($db_record->telefono)) {
        $phoneLink = preg_replace('/[^0-9+]/', '', $db_record->telefono);
        $content .= '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 18px; color: white;">';
        $content .= $iconPhone;
        $content .= '<a href="tel:' . $phoneLink . '" style="color: white; text-decoration: none;">' . s($db_record->telefono) . '</a>';
        $content .= '</div>';
    }

    // Botones WhatsApp
    // Chat Personal (Botón Verde)
    if (!empty($db_record->whatsapp)) {
        $waNum = preg_replace('/[^0-9]/', '', $db_record->whatsapp);
        $waUrl = 'https://wa.me/' . $waNum;
        $content .= '<a href="' . $waUrl . '" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 10px; font-size: 16px; text-decoration: none; color: white; background: #25d366;">';
        $content .= '<span>Chat Personal</span> ';
        $content .= $iconWhatsapp;
        $content .= '</a>';
    }

    // Grupo (Botón Transparente)
    if (!empty($db_record->link_grupo)) {
        $content .= '<a href="' . s($db_record->link_grupo) . '" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 10px; font-size: 16px; text-decoration: none; color: white; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5);">';
        $content .= '<span>Unirse al Grupo</span> ';
        $content .= $iconWhatsapp;
        $content .= '</a>';
    }

    $content .= '</div>'; // Fin contact

    // --- Info Adicional / QR ---
    $hasComments = !empty($db_record->info_adicional) && trim($db_record->info_adicional) !== '';
    if ($hasComments || !empty($qrUrl)) {
        $content .= '<div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 15px; border: 1px solid rgba(255, 255, 255, 0.2); color: white;">';
        $content .= '<div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;">';

        $content .= '<div style="flex: 1;">';
        if ($hasComments) {
            $content .= '<h4 style="font-size: 18px; font-weight: 600; margin-bottom: 10px; color: white; margin-top: 0;">Información Adicional</h4>';
            $content .= '<p style="font-size: 16px; line-height: 1.5; opacity: 0.95; margin-bottom: 0; color: white;">' . nl2br(s($db_record->info_adicional)) . '</p>';
        } else {
            $content .= '<h4 style="font-size: 18px; font-weight: 600; margin-bottom: 10px; color: white; margin-top: 0;">Grupo de WhatsApp</h4>';
            $content .= '<p style="font-size: 16px; line-height: 1.5; opacity: 0.95; margin-bottom: 0; color: white;">Escanea el código para unirte al grupo.</p>';
        }
        $content .= '</div>';

        if (!empty($qrUrl)) {
            $content .= '<div style="background: rgba(255, 255, 255, 0.9); border-radius: 10px; padding: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); flex-shrink: 0;">';
            $content .= '<img src="' . $qrUrl . '" alt="QR Grupo" style="display: block; width: 96px; height: 96px; border-radius: 6px;" />';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';
    }

    $content .= '</div>'; // Fin banner-content

    $result->content = $content;
    return $result;
}
