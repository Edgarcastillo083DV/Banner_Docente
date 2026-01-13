export default (editor, options) => {
    // Registrar el icono (SVG simple)
    editor.ui.registry.addIcon('bannerdocente', '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/><path d="M7 7h10v2H7zm0 4h10v2H7zm0 4h7v2H7z"/></svg>');

    // Registrar el botón
    editor.ui.registry.addButton('bannerdocente_button', {
        icon: 'bannerdocente',
        tooltip: options.btnTitle,
        onAction: () => {
            // Cargar dinámicamente el módulo AMD para manejar el modal
            require(['tiny_bannerdocente/modal'], function(Modal) {
                Modal.open(editor);
            });
        }
    });

    // Registrar un comando por si se quiere llamar programáticamente
    editor.addCommand('mceBannerDocente', () => {
        require(['tiny_bannerdocente/modal'], function(Modal) {
            Modal.open(editor);
        });
    });
};
