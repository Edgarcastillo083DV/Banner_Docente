<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/bannerdocente/classes/db_helper.php');

/**
 * Clase que define la lógica de restauración para el plugin local.
 * Se encarga de duplicar los banners externos cuando se restaura un curso.
 */
class restore_local_bannerdocente_plugin extends restore_plugin {

    /**
     * Define the decoder rules to detect and process the shortcodes.
     */
    public function define_decode_contents() {
        $contents = array();

        // Regla: Buscar [bannerdocente id="XXX"]
        // Moodle ejecutará process_bannerdocente_shortcode por cada coincidencia
        $contents[] = new restore_decode_rule(
            'BANNERDOCENTE', 
            '/\[bannerdocente\s+id="(\d+)"\]/', 
            'local_bannerdocente_restore_handler' 
        );

        return $contents;
    }

    // Nota: Moodle espera que la lógica de transformación esté en una función global o estática definida en lib.php
    // Pero para mantenerlo limpio, usaremos un método estático helper que llamaremos desde lib.php
}

/**
 * Función helper (usualmente en lib.php, pero la simularé aquí para la demostración de lógica).
 * En la implementación real, esto debe ir en local/bannerdocente/lib.php
 */
function local_bannerdocente_restore_handler($content, $matches) {
    // $matches[1] es el ID antiguo
    $old_id = $matches[1];
    
    // Obtener conexión externa
    $db = \local_bannerdocente\db_helper::get_external_db();
    if (!$db) return $content; // Si falla, dejamos el contenido igual (mejor fallo silencioso que romper restore)

    try {
        // 1. Leer el banner original
        $old_record = $db->get_record('banners', array('id' => $old_id));
        if (!$old_record) return $content;

        // 2. Crear copia
        $new_record = clone $old_record;
        unset($new_record->id); // Quitamos ID para que sea nuevo
        $new_record->timecreated = time();
        $new_record->timemodified = time();
        // IMPORTANTE: Aquí deberíamos tener acceso al mapping de course IDs, 
        // pero en un 'decode_content' simple a veces es limitado.
        // Asumiremos que el contexto de curso se actualiza luego o se deja genérico
        // si la BD externa solo guarda datos visuales.
        
        $new_id = $db->insert_record('banners', $new_record);

        // 3. Retornar el nuevo shortcode
        return '[bannerdocente id="' . $new_id . '"]';

    } catch (Exception $e) {
        return $content;
    }
}
