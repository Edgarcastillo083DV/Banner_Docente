<?php
define('AJAX_SCRIPT', true);
require_once('../../config.php');
require_once('./classes/db_helper.php');

// Validar sesión y login
require_login();
require_sesskey();

// Configurar encabezados JSON
header('Content-Type: application/json; charset=utf-8');

$action = optional_param('action', '', PARAM_ALPHA);
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$response = ['success' => false, 'message' => 'Acción inválida'];

try {
    // Conectar a BD Externa
    $db = \local_bannerdocente\db_helper::get_external_db();
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos externa.');
    }

    if ($action === 'save') {
        // Validar datos mínimos
        if (empty($data['html']) || empty($data['json_config'])) {
            throw new Exception('Faltan datos requeridos (html o configuración)');
        }

        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseid = $data['courseid'] ?? 0;
        $record->html_content = $data['html']; // El HTML renderizado para el filtro
        $record->config_data = json_encode($data['json_config']); // Configuración cruda (colores, foto url, etc)
        $record->timemodified = time();

        $banner_id = $data['id'] ?? 0;

        if ($banner_id > 0) {
            // Actualizar
            $record->id = $banner_id;
            $db->update_record('banners', $record);
            $response = ['success' => true, 'id' => $banner_id, 'message' => 'Banner actualizado'];
        } else {
            // Crear nuevo
            $record->timecreated = time();
            $new_id = $db->insert_record('banners', $record);
            $response = ['success' => true, 'id' => $new_id, 'message' => 'Banner creado'];
        }
    } elseif ($action === 'load') {
        $id = required_param('id', PARAM_INT);
        $record = $db->get_record('banners', ['id' => $id]);
        if ($record) {
            $response = ['success' => true, 'data' => $record];
        } else {
            throw new Exception('Banner no encontrado');
        }
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
