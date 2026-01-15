<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Función de actualización del módulo bannerdocente.
 *
 * @param int $oldversion Versión anterior instalada.
 * @return bool True si la actualización fue exitosa.
 */
function xmldb_bannerdocente_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026011502) {
        $table = new xmldb_table('bannerdocente');

        // Definir campos a agregar
        $fields = [
            new xmldb_field('periodo', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'timemodified'),
            new xmldb_field('cedula', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'periodo'),
            new xmldb_field('nombre_docente', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'cedula'), // Usamos nombre_docente para no confundir con 'name' (nombre instancia)
            new xmldb_field('escuela', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'nombre_docente'),
            new xmldb_field('email', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'escuela'),
            new xmldb_field('telefono', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'email'),
            new xmldb_field('whatsapp', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'telefono'),
            new xmldb_field('link_grupo', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'whatsapp'),
            new xmldb_field('info_adicional', XMLDB_TYPE_TEXT, null, null, null, null, null, 'link_grupo'),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2026011502, 'bannerdocente');
    }

    return true;
}
