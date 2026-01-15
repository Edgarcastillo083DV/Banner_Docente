<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_bannerdocente_mod_form extends moodleform_mod
{

    public function definition()
    {
        $mform = $this->_form;

        // --- Cabecera General ---
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // --- Nombre del Banner ---
        $mform->addElement('text', 'name', get_string('bannername', 'mod_bannerdocente'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // --- Configuración Específica del Banner ---
        $mform->addElement('header', 'banner_content', get_string('banner_content', 'mod_bannerdocente'));

        // Imagen
        $mform->addElement(
            'filemanager',
            'bannerimage',
            get_string('bannerimage', 'mod_bannerdocente'),
            null,
            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('image'))
        );
        $mform->addHelpButton('bannerimage', 'bannerimage', 'mod_bannerdocente');

        // Color de Fondo
        $mform->addElement('colorpicker', 'backgroundcolor', get_string('backgroundcolor', 'mod_bannerdocente'));
        $mform->setDefault('backgroundcolor', '#003366'); // Color UAPA por defecto?

        // Texto del Banner (si es diferente al nombre)
        $mform->addElement('text', 'bannertext', get_string('bannertext', 'mod_bannerdocente'), array('size' => '64'));
        $mform->setType('bannertext', PARAM_TEXT);

        // --- Ajustes Comunes del Módulo ---
        $this->standard_coursemodule_elements();

        // --- Botones de Acción ---
        $this->add_action_buttons();
    }
}
