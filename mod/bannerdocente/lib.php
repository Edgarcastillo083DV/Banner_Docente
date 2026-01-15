<?php

defined('MOODLE_INTERNAL') || die();

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

    // Manejar archivos (imagen) si es necesario
    // completion_info logic...

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

    $data->timemodified = time();
    $data->id = $data->instance;

    // Actualizar registro
    $DB->update_record('bannerdocente', $data);

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
            return false;
        case FEATURE_IDNUMBER:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}
