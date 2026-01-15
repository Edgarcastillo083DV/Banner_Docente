<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Form for adding/editing the bannerdocente activity.
 *
 * @property stdClass $current Existing instance data
 * @property context $context Current context
 * @property MoodleQuickForm $_form The form instance
 * @method void standard_coursemodule_elements()
 * @method void add_action_buttons()
 */
class mod_bannerdocente_mod_form extends moodleform_mod
{
    /**
     * Prepare draft area for file manager
     */
    public function data_preprocessing(&$default_values)
    {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('bannerimage');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_bannerdocente', 'bannerimage', 0, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
            $default_values['bannerimage'] = $draftitemid;
        }
    }

    public function definition()
    {
        global $CFG;
        $mform = $this->_form;

        // --- Cabecera General ---
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // --- Nombre del Banner (Oculto por defecto) ---
        $mform->addElement('hidden', 'name', 'Banner Facilitador');
        $mform->setType('name', PARAM_TEXT);
        // $mform->addRule('name', null, 'required', null, 'client'); // No need for client validation if hidden and filled

        // --- Configuración Específica del Banner ---
        // (Sección unificada sin divisor visual por petición del usuario)

        // Periodo
        $periodos = array(
            '' => get_string('choosedots'),
            'enero-abril-2025-1' => 'Enero-Abril 2025-1',
            'mayo-agosto-2025-2' => 'Mayo-Agosto 2025-2',
            'septiembre-diciembre-2025-3' => 'Septiembre-Diciembre 2025-3'
        );
        $mform->addElement('select', 'periodo', get_string('periodo', 'mod_bannerdocente'), $periodos);
        $mform->addRule('periodo', null, 'required', null, 'client');

        // Cédula
        $mform->addElement('text', 'cedula', get_string('cedula', 'mod_bannerdocente'), array('placeholder' => 'Ej: 001-1234567-8'));
        $mform->setType('cedula', PARAM_TEXT);
        $mform->addRule('cedula', null, 'required', null, 'client');

        $mform->addElement('text', 'nombre_docente', get_string('nombre_docente', 'mod_bannerdocente'), array('size' => '64', 'placeholder' => 'Ej: Prof. Maria Garcia'));
        $mform->setType('nombre_docente', PARAM_TEXT);
        $mform->addRule('nombre_docente', null, 'required', null, 'client');

        // Escuela (Opciones específicas del usuario)
        $schools = array(
            '' => get_string('choosedots'),
            'ciencias-salud-psicologia' => 'Ciencias de la Salud y Psicología',
            'ciencias-politicas-juridicas' => 'Ciencias Políticas y Jurídicas',
            'ciencias-sociales-comunicacion' => 'Ciencias Sociales y Comunicación',
            'educacion-formacion-general' => 'Educación y Formación General',
            'ingenieria-tecnologia' => 'Ingeniería y Tecnología',
            'negocios-turismo' => 'Negocios y Turismo',
            'postgrado' => 'Postgrado'
        );
        $mform->addElement('select', 'escuela', get_string('escuela', 'mod_bannerdocente'), $schools);
        $mform->addRule('escuela', null, 'required', null, 'client');

        // Email
        $mform->addElement('text', 'email', get_string('email', 'mod_bannerdocente'));
        $mform->setType('email', PARAM_TEXT);
        $mform->addRule('email', null, 'required', null, 'client');

        // Teléfono
        $mform->addElement('text', 'telefono', get_string('telefono', 'mod_bannerdocente'));
        $mform->setType('telefono', PARAM_TEXT);
        $mform->addRule('telefono', null, 'required', null, 'client');

        // WhatsApp Personal
        $mform->addElement('text', 'whatsapp', get_string('whatsapp', 'mod_bannerdocente'));
        $mform->setType('whatsapp', PARAM_TEXT);
        $mform->addRule('whatsapp', null, 'required', null, 'client');

        // Link Grupo WhatsApp
        $mform->addElement('text', 'link_grupo', get_string('link_grupo', 'mod_bannerdocente'), array('size' => '64'));
        $mform->setType('link_grupo', PARAM_URL);
        $mform->addRule('link_grupo', null, 'required', null, 'client');

        // Info Adicional
        $mform->addElement('textarea', 'info_adicional', get_string('info_adicional', 'mod_bannerdocente'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('info_adicional', PARAM_TEXT);

        // Foto del Facilitador (bannerimage la reutilizamos o renombramos, mejor usar una nueva área o reutilizar la existente con etiqueta clara)
        // Reutilizamos 'bannerimage' pero le cambiamos el label visualmente
        $mform->addElement(
            'filemanager',
            'bannerimage',
            get_string('foto_perfil', 'mod_bannerdocente'),
            null,
            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('image'))
        );
        $mform->addHelpButton('bannerimage', 'foto_perfil', 'mod_bannerdocente');

        // Color de Fondo 
        // Eliminamos el campo de fondo simple si no es necesario, o lo mantenemos como "Color Primario"
        // El usuario usa: primaryColor y accentColor. Vamos a simplificar usando el backgroundcolor como "Tema" o Color Primario.
        // Pero el diseño pide GRADIENTE. Podríamos poner 2 selectores de color o un selector de tema.
        // Por ahora mantenemos backgroundcolor como hexadecimal simple para no complicar, o agregamos otro.
        $mform->addElement('text', 'backgroundcolor', get_string('color_primario', 'mod_bannerdocente'), array('placeholder' => '#ff6f00'));
        $mform->setType('backgroundcolor', PARAM_TEXT);
        $mform->setDefault('backgroundcolor', '#ff6f00');

        // --- Ajustes Comunes del Módulo ---
        $this->standard_coursemodule_elements();

        // --- Botones de Acción ---
        $this->add_action_buttons();
    }
}
