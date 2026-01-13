define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str', 'core/ajax', 'core/notification'],
    function ($, ModalFactory, ModalEvents, Str, Ajax, Notification) {

        return {
            open: function (editor) {
                // Crear el modal
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: Str.get_string('header_name', 'tiny_bannerdocente'),
                    body: '<div class="bannerdocente-loading"><i class="fa fa-spinner fa-pulse"></i> Cargando formulario...</div>',
                    large: true
                }).then(function (modal) {

                    // Mostrar modal
                    modal.show();

                    // Cargar el contenido del formulario (desde el plugin local)
                    // Usamos un iframe para aislamiento de estilos CSS del banner
                    var iframeUrl = M.cfg.wwwroot + '/local/bannerdocente/form.php';
                    var iframeHtml = '<iframe src="' + iframeUrl + '" style="width:100%; height:600px; border:none;" id="bannerdocente_iframe"></iframe>';

                    modal.setBody(iframeHtml);

                    // Manejar evento Guardar
                    modal.getRoot().on(ModalEvents.save, function () {
                        var iframe = document.getElementById('bannerdocente_iframe');
                        if (iframe && iframe.contentWindow && iframe.contentWindow.getBannerData) {
                            try {
                                // Obtener datos desde el iframe
                                var data = iframe.contentWindow.getBannerData();

                                // Guardar en BD vía AJAX
                                Ajax.call([{
                                    methodname: 'local_bannerdocente_save_banner', // TODO: Implementar External Lib o usar ajax.php directo
                                    args: { json: JSON.stringify(data) }
                                }])[0].then(function (response) {
                                    // Insertar shortcode en editor
                                    var shortcode = '[bannerdocente id="' + response.id + '"]';
                                    editor.insertContent(shortcode);
                                    modal.destroy();
                                }).fail(Notification.exception);

                                // Por ahora, como paso intermedio usaremos el ajax.php directo si el WS no está listo
                                // Este bloque es provisional hasta tener el Service Web
                                /*
                                $.ajax({
                                    url: M.cfg.wwwroot + '/local/bannerdocente/ajax.php?action=save',
                                    type: 'POST',
                                    data: JSON.stringify(data),
                                    contentType: 'application/json',
                                    success: function(res) {
                                        if(res.success) {
                                            var shortcode = '[bannerdocente id="' + res.id + '"]';
                                            editor.insertContent(shortcode);
                                            modal.destroy();
                                        } else {
                                            Notification.alert('Error', res.message);
                                        }
                                    }
                                });
                                */

                            } catch (e) {
                                console.error(e);
                            }
                        }
                        // Prevenir cierre automático hasta confirmar guardado
                        return false;
                    });

                    // Escuchar mensajes del iframe (postMessage) para comunicación cruzada
                    window.addEventListener('message', function (e) {
                        if (e.data && e.data.type === 'BANNER_SAVED') {
                            var shortcode = '[bannerdocente id="' + e.data.id + '"]';
                            editor.insertContent(shortcode);
                            modal.destroy();
                        }
                    });

                });
            }
        };
    });
