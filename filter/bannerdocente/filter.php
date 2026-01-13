<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/bannerdocente/classes/db_helper.php');

class filter_bannerdocente extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        if (!is_string($text) or empty($text)) {
            return $text;
        }

        // Regex para encontrar [bannerdocente id="123"]
        $pattern = '/\[bannerdocente\s+id="(\d+)"\]/';

        return preg_replace_callback($pattern, array($this, 'get_banner_content'), $text);
    }

    private function get_banner_content($matches) {
        global $USER, $DB;
        
        $banner_id = $matches[1];

        // 1. Obtener conexión a BD externa
        $ext_db = \local_bannerdocente\db_helper::get_external_db();
        if (!$ext_db) {
            return '<!-- Error de conexión Banner Docente -->';
        }

        // 2. Buscar el registro
        try {
            $record = $ext_db->get_record('banners', array('id' => $banner_id));
        } catch (Exception $e) {
            return '<!-- Error al cargar banner ID ' . $banner_id . ' -->';
        }

        if (!$record) {
            return '<div class="alert alert-warning">Banner no encontrado (ID: ' . $banner_id . ')</div>';
        }

        // 3. Renderizar
        // Decodificamos el HTML
        $html = $record->html_content;

        // 4. Lógica de Roles: ¿Mostrar botón de edición?
        // En un plugin real, verificaríamos $options['context'] y has_capability('moodle/course:manageactivities')
        // Por simplificación: Si el usuario actual es el dueño del banner, mostramos botón
        $edit_btn = '';
        if (isloggedin() && $USER->id == $record->userid) {
             // Este botón podría abrir el mismo modal que el editor TinyMCE
            $edit_btn = '<button class="btn btn-sm btn-light banner-edit-trigger" data-id="'.$banner_id.'"><i class="fa fa-pencil"></i> Editar Banner</button>';
        }

        return '<div class="bannerdocente-container" style="position:relative;">' . $html . $edit_btn . '</div>';
    }
}
