/*
autor: Mlatorre
archivo: utiles_comun.js
observacion:  Contiene funciones utiles que son comun tanto para el CREAR certificado como par el MODIFICAR certificado 
*/


function callback_gral(url, data, fn_callback) {
    $("#contentLoading").show();

    $('#boton_guardar').fadeTo('fast', .4);
    $('#boton_guardar').append('<div id="div_block_boton_guardar" style="position: absolute;top:0;left:0;width: 100%;height:100%;z-index:2;opacity:1;filter: alpha(opacity = 50)"></div>');
    $.ajax({
        url: url,
        data: data,
        type: "POST",
        dataType: "JSON"
    }).done(function(data) {
        $("#contentLoading").fadeOut("fast");
        if (data.RESULTADO == 'OK') {
            if (data.MENSAJES) {
                $.each(data.MENSAJES, function(key, value) {
                    //msg(value);
                    console.log(value);
                });
            }

            if (data.CAMBIA) {
                $.each(data.CAMBIA, function(key, value) {
                    $(key).html(value);
                    if (typeof inicializacion === 'function') {
                        inicializacion($(key));
                    }
                });
            }

            if (data.OPEN) {
                $.each(data.OPEN, function(key, value) {
                    /*if(value.position){
                        alert(value.position)
                        $(key).dialog( "option", "position", value.position);
                        
                    }*/
                    $(key).dialog('open');
                    //$(key).dialog( "option", "position", value.position);
                });
            }
            if (data.HIDE) {
                $.each(data.HIDE, function(key, value) {
                    $(key).hide();
                });
            }
            if (data.SHOW) {
                $.each(data.SHOW, function(key, value) {
                    $(key).show();
                });
            }
            if (data.CLOSE) {
                $.each(data.CLOSE, function(key, value) {
                    $(key).dialog('close');
                });
            }

            if (data.ALERT) {
                $.each(data.ALERT, function(key, value) {
                    if (typeof value === 'object') {
                        var tipo = (value.tipo) ? value.tipo : undefined;
                        var title = (value.title) ? value.title : undefined;
                        var callback = (value.callback) ? value.callback : undefined;
                        var icono = (value.icono) ? value.icono : undefined;
                        var html = (value.html) ? value.html : undefined;

                        jAlert(html, title, function() { eval(callback) });
                    } else {
                        if (!isNaN(parseFloat(key)) && isFinite(key)) {
                            key = 'Aviso'
                        }
                        jAlert(value, key);
                    }

                });
            }





            if (data.CLASS_ADD) {
                $.each(data.CLASS_ADD, function(key, value) {
                    $(key).addClass(value);
                });
            }

            if (data.CLASS_REMOVE) {
                $.each(data.CLASS_REMOVE, function(key, value) {
                    $(key).removeClass(value);
                });
            }

            if (data.CALLBACK) {
                $.each(data.CALLBACK, function(key, value) {
                    eval(value);
                });
            }

            if (data.VAL) {
                $.each(data.VAL, function(key, value) {
                    $(key).val(value);
                });
            }



        }

        if (data.RESULTADO == 'ERROR') {
            alert(data.ERROR);
        }
        if (data.RESULTADO == 'MSG') {
            alert(data.MSG);
        }
        if (fn_callback) {
            console.log("[ALERT] fn_callback : " + fn_callback);
            //fn_callback();
        }
        $('#div_block_boton_guardar').remove();
        $('#boton_guardar').fadeTo('fast', 1);

    }).fail(function(a, b, c) {
        $("#div_errorAjax").dialog('option', 'width', '350px');
        $("#div_errorAjax").html("<strong>" + b + "</strong><br/><br/><img src='Sistema/img/hss-alert-icon.png' width='40' />" + c + "<br><br><strong style='cursor:pointer' onclick='fun_verDetalleErrorAjax()'>Detalle</strong><div id='div_detalleErrorAjax' style='display:none'>" + a.responseText + "</div>");
        //$("#div_errorAjax").parent().addClass('ui-state-error');
        $("#div_errorAjax").parent().find('.ui-dialog-titlebar').addClass('ui-state-error');
        $("#div_errorAjax").dialog('open');
        $("#contentLoading").fadeOut("fast");

        $('#div_block_boton_guardar').remove();
        $('#boton_guardar').fadeTo('fast', 1);
    });

}

function fun_dropSubirAdjunto(tipo, id) {
    opcion = 2; //cambiamos el estado para saber que se hizo un cambio en expedientes
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_cambia_estado_expediente&estado=" + opcion);
    callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_dropSubirAdjunto", {
        'v_tipo': tipo,
        'v_id': id
    });
}




function click_eliminarAdjunto(id) {
    opcion = 2; //cambiamos el estado para saber que se hizo un cambio en expedientes
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_cambia_estado_expediente&estado=" + opcion);
    callback_gral("index.php?pagina=paginas.generar_docto&funcion=click_eliminarAdjunto", {
        'id': id
    });
}

function accionCerrarCertificado() {
    var mensaje = confirm("¿Desea salir y descartar los cambios?");
    if (mensaje) {
        //clearTempDocs();
        window.close();
    }
}


//usado en el grabar del crear
function accionBtnFormCerrar() {

    $("#div_respuesta").dialog('close');
    window.opener.href = "/intranet/aplic/wf/bandejaEntradaV2.php?p_procesos=0";
    window.close();
}

//cerrar el grabar modificacion 
function accionBtnFormCerrarM() {

    $("#div_respuesta").dialog('close');

}

function accionBtnCerrarEVB() {

    $("#div_respuesta").dialog('close');
    window.opener.href = "/intranet/aplic/wf/bandejaEntradaV2.php?p_procesos=0";
    window.close();

}