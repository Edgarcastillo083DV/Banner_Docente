<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Función callback llamada por el motor de restauración de Moodle.
 * Busca coincidencias de [bannerdocente id="X"] y crea copias en la BD externa.
 *
 * @param string $content El contenido textual completo donde se encontró el match
 * @param array $matches Las coincidencias de regex (id antiguo)
 * @return string El nuevo contenido con el shortcode actualizado
 */
function local_bannerdocente_restore_handler($content, $matches) {
    // Esta función envuelve la lógica real. 
    // Moodle pasa el contenido completo, así que debemos reemplazar SOLO el ID.
    // Pero la función decode_content de Moodle espera que transformemos el match.
    
    $old_id = $matches[1];
    
    // Obtener conexión
    $db = \local_bannerdocente\db_helper::get_external_db();
    if (!$db) return $content;

    try {
        $old_record = $db->get_record('banners', array('id' => $old_id));
        if ($old_record) {
            $new_record = clone $old_record;
            unset($new_record->id);
            $new_record->timecreated = time();
            $new_record->timemodified = time();
            
            // Insertar copia
            $new_id = $db->insert_record('banners', $new_record);
            
            // Reemplazar el shortcode viejo por el nuevo
            return str_replace($old_id, $new_id, $content); 
        }
    } catch (Exception $e) {
        // Log error
    }
    
    return $content;
}
