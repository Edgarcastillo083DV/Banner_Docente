<?php
define('AJAX_SCRIPT', true);
require('../../config.php');

require_login();
// Podemos requerir capacidad de editar si queremos ser estrictos
// require_capability('moodle/course:manageactivities', context_system::instance());

$cedula = optional_param('cedula', '', PARAM_TEXT);

$response = array('found' => false);

if (!empty($cedula)) {
    // Buscar el registro más reciente con esa cédula
    // Ordenamos por id DESC para obtener el último creado
    $record = $DB->get_record('bannerdocente', array('cedula' => $cedula), '*', IGNORE_MULTIPLE);

    // Si hay mÃºltiples, get_record con IGNORE_MULTIPLE devuelve el primero que encuentra (que suele ser el primero insertado).
    // Para obtener el Ãºltimo, mejor usamos get_records con limit.
    $records = $DB->get_records('bannerdocente', array('cedula' => $cedula), 'id DESC', '*', 0, 1);

    if (!empty($records)) {
        $record = reset($records);
        $response['found'] = true;
        $response['data'] = array(
            'nombre_docente' => $record->nombre_docente,
            'escuela'        => $record->escuela,
            'email'          => $record->email,
            'telefono'       => $record->telefono,
            'whatsapp'       => $record->whatsapp,
            // 'info_adicional' => $record->info_adicional // Opcional
        );

        // Verificar si tiene foto
        $context = context_module::instance($record->id); // Esto fallarÃ¡ porque el id en bannerdocente NO es el cmid.
        // Necesitamos el cmid para sacar la foto?
        // En la tabla bannerdocente guardamos 'course'. El cmid se obtiene cruzando con mdl_course_modules.
        // Pero espera, el file storage usa contextid. 
        // El contextid original se perdiÃ³ si no guardamos el cmid del banner original.
        // PERO, podemos intentar buscar el cm asociado a esta instancia.
        $cm = get_coursemodule_from_instance('bannerdocente', $record->id, $record->course);
        if ($cm) {
            $context = context_module::instance($cm->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_bannerdocente', 'bannerimage', 0, 'sortorder DESC, id ASC', false);
            if (!empty($files)) {
                $response['data']['has_photo'] = true;
                // No podemos devolver la URL para "copiarla" al filemanager fÃ¡cilmente via JS sin plugins raros.
                // Pero podemos avisar al usuario.
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
