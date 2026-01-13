<?php

namespace tiny_bannerdocente;

defined('MOODLE_INTERNAL') || die();

use editor_tiny\plugin;
use editor_tiny\plugin_with_buttons;

class plugin_bannerdocente extends plugin implements plugin_with_buttons {
    public static function get_plugin_buttons(): array {
        return [
            'bannerdocente_button' => [
                'icon' => 'bannerdocente',
                'title' => 'tiny_bannerdocente:header_name',
            ],
        ];
    }

    public function get_plugin_configuration_for_editor(\context $context): array {
        return [
            'btnTitle' => get_string('header_name', 'tiny_bannerdocente'),
        ];
    }
}
