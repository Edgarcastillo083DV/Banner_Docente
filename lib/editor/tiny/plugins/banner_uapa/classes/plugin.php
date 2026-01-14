<?php

namespace tiny_banner_uapa;

defined('MOODLE_INTERNAL') || die();

use editor_tiny\plugin as tiny_plugin;

class plugin extends tiny_plugin
{

    /**
     * Define the configuration for the editor side (JS).
     *
     * @param \context $context
     * @return array
     */
    public function get_plugin_configuration_for_editor(\context $context): array
    {
        return [
            'btnTitle' => get_string('header_name', 'tiny_banner_uapa'),
        ];
    }

    /**
     * Get the list of buttons provided by this plugin.
     *
     * @return array
     */
    public function get_buttons(): array
    {
        return [
            'tiny_banner_uapa',
        ];
    }

    /**
     * Get the list of menu items provided by this plugin.
     *
     * @return array
     */
    public function get_menu_items(): array
    {
        return [
            'tiny_banner_uapa',
        ];
    }

    /**
     * Get the list of available buttons (Static, for registration).
     *
     * @return array
     */
    public static function get_available_buttons(): array
    {
        return [
            'tiny_banner_uapa',
        ];
    }

    /**
     * Get the list of JS modules to include.
     *
     * @return array
     */
    public function get_js_modules(): array
    {
        return [
            'editor_tiny/banner_uapa/plugin',
        ];
    }
}
