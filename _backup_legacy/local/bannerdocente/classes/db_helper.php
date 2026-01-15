<?php
namespace local_bannerdocente;

defined('MOODLE_INTERNAL') || die();

class db_helper {

    /**
     * Obtiene una instancia de conexión a la base de datos externa.
     * 
     * @return \moodle_database La instancia de conexión o null si falla.
     */
    public static function get_external_db() {
        global $CFG;

        $dbhost = get_config('local_bannerdocente', 'dbhost');
        $dbname = get_config('local_bannerdocente', 'dbname');
        $dbuser = get_config('local_bannerdocente', 'dbuser');
        $dbpass = get_config('local_bannerdocente', 'dbpass');
        $dbprefix = get_config('local_bannerdocente', 'dbprefix');

        // Si falta configuración crítica, abortar
        if (empty($dbhost) || empty($dbname) || empty($dbuser)) {
            return null;
        }

        try {
            // Instanciar el driver MySQL nativo de Moodle
            $ext_db = \moodle_database::get_driver_instance('mysqli', 'native');
            
            // Opciones de conexión estándar
            $dboptions = array(
                'dbpersist' => false,
                'dbport' => '',
                'dbsocket' => '',
            );

            // Intentar conectar
            $ext_db->connect($dbhost, $dbuser, $dbpass, $dbname, $dbprefix, $dboptions);
            
            return $ext_db;
        } catch (\Exception $e) {
            debugging('Error al conectar con BD externa Banner Docente: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si la conexión externa está funcionando.
     */
    public static function check_connection() {
        $db = self::get_external_db();
        return ($db !== null);
    }
}
