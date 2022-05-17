$(document).ready(function() {
    //|||inicio: Permite abrir modal
    $("#div_ver_version").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });

    $("#div_firmar_certificado").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });

    $("#div_respuesta_firma").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });

    $("#div_respuesta_mde").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });

});

//guarda los cambios para poder firmar
function guardarCambiosParaFirmar() {
    $("#errorTipoEnvio").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");

    var errores = 0;

    var dato_archivo = $('#archivoInput').prop("files")[0];
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();



    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();
    var padre = $("#padre").val();
    var wf = $("#wf").val();
    var tipo = $("#tipo").val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    //console.log("usa plantilla ?: " + usarPlantillaSINO);


    //destinatarios a eliminar
    var rutDestinatarioE = document.getElementsByClassName("miDestinatarioE"),
        arrayDestinatarioE = [];
    for (var x = 0; x < rutDestinatarioE.length; x++) {
        arrayDestinatarioE[x] = rutDestinatarioE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario: " + arrayDestinatarioE[x]);
    }
    //destinatario copia a eliminar
    var rutDestinatarioCE = document.getElementsByClassName("miDestinatarioCE"),
        arrayDestinatarioCE = [];
    for (var x = 0; x < rutDestinatarioCE.length; x++) {
        arrayDestinatarioCE[x] = rutDestinatarioCE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario Copia: " + arrayDestinatarioCE[x]);
    }



    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    var arrayMiMedioEnvio = [];

    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
        arrayMiMedioEnvio[i] = $("#miMedioEnvio_" + rutDestinatario[i].value).val() + '_';

        //console.log("destinatario:" + arrayDestinatario[i]);
        //console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("correoooo : " + arrayCorreo[i]);
    }



    var miCopiaCorreo = document.getElementsByClassName("miCopiaCorreo"),
        arrayCopiaCorreo = [];
    var direccionCopia = document.getElementsByClassName("miCopiaDireccion"),
        arrayCopiaDireccion = [];
    var rutCopia = document.getElementsByClassName("miCopia"),
        arrayCopia = [];
    var arrayCargoCopia = [];
    var arrayMiTipoCopia = [];
    var arrayMiNombreDesCopia = [];
    var arrayMiMedioEnvioCopia = [];

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        arrayMiMedioEnvioCopia[x] = $("#miMedioEnvioCopia_" + rutCopia[x].value).val() + '_';

        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }


    //validamos tipos de envios
    if (!tipo_envio) {
        $("#errorTipoEnvio").css("display", "block");
        errores++;
    }
    //validamos datos sensibles
    if (!datosensibleSINO) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorDatosSensibles").css("display", "block");
        errores++;
    }
    //validamos  seleccion de tipo privacidad    
    if (!privacidadTipo) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorPrivacidad").css("display", "block");
        errores++;
    }
    //validamos  seleccion de usa plantilla    
    /*if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }*/



    var formData = new FormData();

    formData.append("file", dato_archivo);
    formData.append("p_cuerpo", dato3);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_padre", padre);
    formData.append("p_wf", wf);
    formData.append("p_tipo", tipo);

    formData.append("p_tipo_envio", tipo_envio);

    //data destinatario
    formData.append("p_arrayDestinatario", arrayDestinatario);
    formData.append("p_arrayCargoDestinatario", arrayCargoDestinatario);
    formData.append("p_arrayDireccion", arrayDireccion);
    formData.append("p_arrayCorreo", arrayCorreo);
    formData.append("p_arrayMiTipo", arrayMiTipo);
    formData.append("p_arrayMiNombreDes", arrayMiNombreDes);


    //data destinatario copia
    formData.append("p_arrayCopia", arrayCopia);
    formData.append("p_arrayCargoCopia", arrayCargoCopia);
    formData.append("p_arrayCopiaCorreo", arrayCopiaCorreo);
    formData.append("p_arrayMiTipoCopia", arrayMiTipoCopia);
    formData.append("p_arrayCopiaDireccion", arrayCopiaDireccion);
    formData.append("p_arrayMiNombreDesCopia", arrayMiNombreDesCopia);


    //data destinatario a eliminar
    formData.append("p_arrayDestinatarioE", arrayDestinatarioE);
    formData.append("p_arrayDestinatarioCE", arrayDestinatarioCE);

    formData.append("p_arrayMiMedioEnvio", arrayMiMedioEnvio);
    formData.append("p_arrayMiMedioEnvioCopia", arrayMiMedioEnvioCopia);

    //accion modificar 
    if (errores > 0) {
        //alert("ERRORES : Usted tiene " + errores + " errores.");
        callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
            errores: errores,
            respuesta: 'NOK'
        });
        return "NOK";
    } else {
        $.ajax({
            data: formData,
            url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_modificar_certificado',
            type: 'post',
            success: function(html) {
                if (html == 'OK') {
                    console.log("RESPUESTA : OK");
                    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_firmar");
                } else {
                    alert("RESPUESTA :: NOK");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }


}




//ml: INICIAMOS ACCION DE FIRMAR DESDE EL FORMULARIO DE MODIFICAR CERTIFICADO
function accionFirmarCertificado() {
    console.log("INICIAMOS ACCION DE FIRMAR DESDE EL FORMULARIO DE MODIFICAR CERTIFICADO");

    $.ajax({
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_verificar_exiten_cambios',
        type: 'post',
        success: function(html) {
            //alert(html);
            if (html == 'SI') {
                console.log("existen cambios");

                var mensaje;
                var opcion = confirm("Usted tiene cambios pendientes , ¿Desea guardar los cambios?");
                if (opcion == true) {
                    console.log("Se guardan los cambio y se firma");
                    guardarCambiosParaFirmar();


                } else {
                    console.log("Se anulan los cambios y se firma");
                    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_firmar");
                }

            } else {
                console.log("no existen cambios , se firma");
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_firmar");
            }
        }
    });


}

//ml:quiero firmar el certificado desde el modal
function quieroFirmarCertificado() {
    console.log("Accionamos la Firma del certificado");
    var miPassword = document.getElementById('password_equipo').value;
    var miFirma = document.getElementById('tipo_firma').value;

    //console.log(miPassword + '//' + miFirma);

    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();
    var dato_archivo = $('#archivoInput').prop("files")[0];
    var formData = new FormData();
    formData.append("p_cuerpo", dato3);
    formData.append("file", dato_archivo);
    formData.append("password", miPassword);
    formData.append("firma", miFirma);


    $.ajax({
        data: formData,
        url: 'index.php?pagina=paginas.firmar_certificado&funcion=fun_accion_firmar_certificado',
        type: 'post',
        success: function(html) {
            console.log("IDENTIFICAMOS CERTIFICADO :: " + html);
            if (html == 'OK') {
                console.log("QUEREMOS FIRMAR CON CUERPO");
                fun_firmar_con_cuerpo();
            } else if (html == 'OK1') {
                console.log("QUEREMOS FIRMAR CON ADJUNTO");
                fun_firmar_con_adjunto();
            } else {
                alert("ERROR 001: PROBLEMAS AL TRATAR DE GENERAR LA FIRMA EN EL CERTIFICADO.");
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });


    /*
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_accion_firmar_certificado", {
        firma: miFirma,
        password: miPassword
    });
    */

}


//firmar certificado en caso que sea desde el cuerpo
function fun_firmar_con_cuerpo() {

    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    $.ajax({
        data: {
            medio_envio: tipo_envio
        },
        url: 'index.php?pagina=paginas.firmar_certificado&funcion=fun_firmar_con_cuerpo',
        type: 'post',
        success: function(html) {
            if (html == 'NOK') {
                $("#div_firmar_certificado").dialog('close');
                alert("ERROR AL TRATAR DE FIRMAR EL CERTIFICADO");
            } else {
                $("#div_firmar_certificado").dialog('close');
                callback_gral("index.php?pagina=paginas.firmar_certificado&funcion=fun_respuesta_firma_certificado", {
                    numero_sgd: html
                });
            }
        }
    });

}

//firmar certificado en caso que sea desde un adjunto
function fun_firmar_con_adjunto() {

    console.log("PASO :: ESTAMOS EN fun_firmar_con_adjunto");

    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    $.ajax({
        data: {
            medio_envio: tipo_envio
        },
        url: 'index.php?pagina=paginas.firmar_certificado&funcion=fun_firmar_con_adjunto',
        type: 'post',
        success: function(html) {
            alert("NUMERO DE SGD DE LA FIRMA :: " + html);
            if (html == 'NOK') {
                $("#div_firmar_certificado").dialog('close');
                alert("ERROR AL TRATAR DE FIRMAR EL CERTIFICADO");
            } else {
                $("#div_firmar_certificado").dialog('close');
                callback_gral("index.php?pagina=paginas.firmar_certificado&funcion=fun_respuesta_firma_certificado", {
                    numero_sgd: html
                });
            }

        }
    });

}


//



function cancelar_firmar() {
    $("#div_firmar_certificado").dialog('close');
}




function accionBtnFormEnviaraAVB() {
    //mostramos modal ENVIAR A VISTO BUENO
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_evb");
}


//enviamos a VB modificar MI UNIDAD
function accionEnviarVB() {

    $("#errorTipoEnvio").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");

    var errores = 0;

    var dato_archivo = $('#archivoInput').prop("files")[0];
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();



    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();
    var padre = $("#padre").val();
    var wf = $("#wf").val();
    var tipo = $("#tipo").val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    //console.log("usa plantilla ?: " + usarPlantillaSINO);


    //destinatarios a eliminar
    var rutDestinatarioE = document.getElementsByClassName("miDestinatarioE"),
        arrayDestinatarioE = [];
    for (var x = 0; x < rutDestinatarioE.length; x++) {
        arrayDestinatarioE[x] = rutDestinatarioE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario: " + arrayDestinatarioE[x]);
    }
    //destinatario copia a eliminar
    var rutDestinatarioCE = document.getElementsByClassName("miDestinatarioCE"),
        arrayDestinatarioCE = [];
    for (var x = 0; x < rutDestinatarioCE.length; x++) {
        arrayDestinatarioCE[x] = rutDestinatarioCE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario Copia: " + arrayDestinatarioCE[x]);
    }



    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    var arrayMiMedioEnvio = [];

    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
        arrayMiMedioEnvio[i] = $("#miMedioEnvio_" + rutDestinatario[i].value).val() + '_';

        //console.log("destinatario:" + arrayDestinatario[i]);
        //console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("cargo D: " + arrayCargoDestinatario[i]);
    }



    var miCopiaCorreo = document.getElementsByClassName("miCopiaCorreo"),
        arrayCopiaCorreo = [];
    var direccionCopia = document.getElementsByClassName("miCopiaDireccion"),
        arrayCopiaDireccion = [];
    var rutCopia = document.getElementsByClassName("miCopia"),
        arrayCopia = [];
    var arrayCargoCopia = [];
    var arrayMiTipoCopia = [];
    var arrayMiNombreDesCopia = [];
    var arrayMiMedioEnvioCopia = [];

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        arrayMiMedioEnvioCopia[x] = $("#miMedioEnvioCopia_" + rutCopia[x].value).val() + '_';

        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }


    //DATOS DEL MODAL ENVIAR VB
    var miCCertificado = $("#miCCertificado").val();
    var paraVB = document.getElementById('para_vb').value;
    //var copiaVB = document.getElementById("copia_vb").value;
    var copiaVB = $("#copia_vb").val();

    //console.log(copiaVB);

    var visacionVB = document.getElementById("visacion_vb").value;
    var comentarioVB = CKEDITOR.instances.comentario_vb.getData();
    var archivoVB = $('#archivo_vb').prop("files")[0];


    //validamos tipos de envios
    if (!tipo_envio) {
        $("#errorTipoEnvio").css("display", "block");
        errores++;
    }
    //validamos datos sensibles
    if (!datosensibleSINO) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorDatosSensibles").css("display", "block");
        errores++;
    }
    //validamos  seleccion de tipo privacidad    
    if (!privacidadTipo) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorPrivacidad").css("display", "block");
        errores++;
    }
    //validamos  seleccion de usa plantilla    
    /*if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }*/

    //validaciones Enviar VB
    if (!paraVB) {
        $("#errorParaVB").css("display", "block");
        errores++;
    }
    if (!visacionVB) {
        $("#errorVisacionVB").css("display", "block");
        errores++;
    }

    var formData = new FormData();

    formData.append("file", dato_archivo);
    formData.append("p_cuerpo", dato3);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_padre", padre);
    formData.append("p_wf", wf);
    formData.append("p_tipo", tipo);
    formData.append("p_tipo_envio", tipo_envio);


    //data destinatario
    formData.append("p_arrayDestinatario", arrayDestinatario);
    formData.append("p_arrayCargoDestinatario", arrayCargoDestinatario);
    formData.append("p_arrayDireccion", arrayDireccion);
    formData.append("p_arrayCorreo", arrayCorreo);
    formData.append("p_arrayMiTipo", arrayMiTipo);
    formData.append("p_arrayMiNombreDes", arrayMiNombreDes);


    //data destinatario copia
    formData.append("p_arrayCopia", arrayCopia);
    formData.append("p_arrayCargoCopia", arrayCargoCopia);
    formData.append("p_arrayCopiaCorreo", arrayCopiaCorreo);
    formData.append("p_arrayMiTipoCopia", arrayMiTipoCopia);
    formData.append("p_arrayCopiaDireccion", arrayCopiaDireccion);
    formData.append("p_arrayMiNombreDesCopia", arrayMiNombreDesCopia);


    //data destinatario a eliminar
    formData.append("p_arrayDestinatarioE", arrayDestinatarioE);
    formData.append("p_arrayDestinatarioCE", arrayDestinatarioCE);

    //enviar a vb
    formData.append("file2", archivoVB); //archivo adjunto EVB
    formData.append("p_paraVB", paraVB);
    formData.append("p_copiaVB", copiaVB);
    formData.append("p_visacionVB", visacionVB);
    formData.append("p_comentarioVB", comentarioVB);


    formData.append("p_arrayMiMedioEnvio", arrayMiMedioEnvio);
    formData.append("p_arrayMiMedioEnvioCopia", arrayMiMedioEnvioCopia);

    //validamos si existen errores 
    if (errores > 0) {
        console.log("ERRORES : Usted tiene " + errores + " errores.");
        /*callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
            errores: errores,
            respuesta: 'NOK'
        });*/
    } else {

        $.ajax({
            data: formData,
            //url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_enviar_visto_bueno',
            url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_agregar_enviar_vb',
            type: 'post',
            success: function(html) {
                //alert(html);
                if (html == 'OK') {
                    //console.log("respuesta: OK");
                    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_evb", {
                        errores: errores,
                        respuesta: html
                    });
                } else {
                    alert("ERROR: al intentar agregar el certificado");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

    }


}

function accionEVB_Todos() {

    var errores = 0;

    //limpiamos errores pestaña OTRAS
    $("#errorParaVBTodos").css("display", "none");
    $("#errorCopiaVBTodos").css("display", "none");
    $("#errorVisacionVBTodos").css("display", "none");

    //limpiamos errores formulario CERTIFICADO
    $("#errorTipoEnvio").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorUsaPlantilla").css("display", "none");
    $("#errorTipoPlantilla").css("display", "none");

    //limpiamos errores pestaña MI UNIDAD
    $("#errorParaVB").css("display", "none");
    $("#errorCopiaVB").css("display", "none");
    $("#errorVisacionVB").css("display", "none");


    //limpiamos errores pestaña OTRA UNIDAD
    $("#errorDivisionVB").css("display", "none");
    $("#errorOtraUnidadParaVB").css("display", "none");
    $("#errorOtraUnidadCopiaVB").css("display", "none");
    $("#errorOtraUnidadVisacionVB").css("display", "none");


    var dato_archivo = $('#archivoInput').prop("files")[0];
    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    var arrayMiMedioEnvio = [];

    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
        arrayMiMedioEnvio[i] = $("#miMedioEnvio_" + rutDestinatario[i].value).val() + '_';

        //console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("cargo D: " + arrayCargoDestinatario[i]);
        //console.log("DIRECCION :: " + arrayDireccion[i].value);
        //console.log("NOMBRE DESTINATARIO :: " + arrayMiNombreDes[i].value);
    }


    var miCopiaCorreo = document.getElementsByClassName("miCopiaCorreo"),
        arrayCopiaCorreo = [];
    var direccionCopia = document.getElementsByClassName("miCopiaDireccion"),
        arrayCopiaDireccion = [];
    var rutCopia = document.getElementsByClassName("miCopia"),
        arrayCopia = [];
    var arrayCargoCopia = [];
    var arrayMiTipoCopia = [];
    var arrayMiNombreDesCopia = [];
    var arrayMiMedioEnvioCopia = [];

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        arrayMiMedioEnvioCopia[x] = $("#miMedioEnvioCopia_" + rutCopia[x].value).val() + '_';

        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }


    var miCCertificado = $("#miCCertificado").val();
    var padre = $("#padre").val();
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();
    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();



    //parametros pestaña OTROS EVB 
    var ParaVBTodos = document.getElementById('paraVBTodos').value;
    var CopiaVBTodos = $("#copiaVBTodos").val();
    var VisacionVBTodos = document.getElementById("visacionVBTodos").value;
    var ComentarioVBTodos = CKEDITOR.instances.comentarioVBTodos.getData();
    var ArchivoVBTodos = $('#archivoVBTodos').prop("files")[0];


    var formData = new FormData();
    formData.append("file", dato_archivo);
    formData.append("file4", ArchivoVBTodos); //archivo adjunto EVB, OTRA UNIDAD

    formData.append("p_cuerpo", dato3);
    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_tipo_envio", tipo_envio);

    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_miccertificado", miCCertificado);
    formData.append("p_padre", padre);
    formData.append("p_arrayCopia", arrayCopia);
    formData.append("p_arrayDestinatario", arrayDestinatario);
    formData.append("p_arrayCargoCopia", arrayCargoCopia);
    formData.append("p_arrayCargoDestinatario", arrayCargoDestinatario);
    formData.append("p_arrayCopiaDireccion", arrayCopiaDireccion);
    formData.append("p_arrayCopiaCorreo", arrayCopiaCorreo);
    formData.append("p_arrayDireccion", arrayDireccion);
    formData.append("p_arrayCorreo", arrayCorreo);
    formData.append("p_arrayMiTipo", arrayMiTipo);
    formData.append("p_arrayMiTipoCopia", arrayMiTipoCopia);
    formData.append("p_arrayMiNombreDes", arrayMiNombreDes);
    formData.append("p_arrayMiNombreDesCopia", arrayMiNombreDesCopia);

    //parametros enviados de pestaña TODOS
    formData.append("p_paraVBTodos", ParaVBTodos);
    formData.append("p_copiaVBTodos", CopiaVBTodos);
    formData.append("p_visacionVBTodos", VisacionVBTodos);
    formData.append("p_comentarioVBTodos", ComentarioVBTodos);

    formData.append("p_arrayMiMedioEnvio", arrayMiMedioEnvio);
    formData.append("p_arrayMiMedioEnvioCopia", arrayMiMedioEnvioCopia);

    //console.log(otraUnidadParaVB + "//" + otraUnidadCopiaVB + "//" + otraUnidadVisacionVB + "//" + otraUnidadComentarioVB);




    //validamos tipos de envios
    if (!tipo_envio) {
        $("#errorTipoEnvio").css("display", "block");
        errores++;
    }
    //validamos datos sensibles
    if (!datosensibleSINO) {
        $("#errorDatosSensibles").css("display", "block");
        errores++;
    }
    //validamos  seleccion de tipo privacidad    
    if (!privacidadTipo) {
        $("#errorPrivacidad").css("display", "block");
        errores++;
    }
    //validamos  seleccion de usa plantilla    
    /*if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }*/

    //inicio validaciones form OTRA UNIDAD
    if (!ParaVBTodos) {
        $("#errorParaVBTodos").css("display", "block");
        errores++;
    }
    /*if (!CopiaVBTodos) {
        $("#errorCopiaVBTodos").css("display", "block");
        errores++;
    }*/
    if (!VisacionVBTodos) {
        $("#errorVisacionVBTodos").css("display", "block");
        errores++;
    }


    //validamos si existen errores
    if (errores > 0) {
        console.log("ERRORES : Usted tiene " + errores + " errores.");
        callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_evb", {
            errores: errores,
            respuesta: 'NOK'
        });
    } else {
        console.log("PASO:: NO EXISTEN ERRORES EVB DESDE TODAS");
        $.ajax({
            data: formData,
            url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_enviarvb_todas',
            type: 'post',
            success: function(html) {
                if (html == 'OK') {
                    console.log("RESPUESTA: OK , SE ENVIO A VB CORRECTAMENTE DESDE PESTAÑA TODAS");
                    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_evb", {
                        errores: errores,
                        respuesta: html
                    });
                } else {
                    alert("ERROR: al intentar agregar el certificado");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

    }



}



//enviamos a VB modificar OTRA UNIDAD
function accionOtraUnidadEVB() {


    var errores = 0;

    $("#errorTipoEnvio").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorUsaPlantilla").css("display", "none");
    $("#errorTipoPlantilla").css("display", "none");
    $("#errorParaVB").css("display", "none");
    $("#errorCopiaVB").css("display", "none");
    $("#errorVisacionVB").css("display", "none");

    $("#errorDivisionVB").css("display", "none");
    $("#errorOtraUnidadParaVB").css("display", "none");
    $("#errorOtraUnidadCopiaVB").css("display", "none");
    $("#errorOtraUnidadVisacionVB").css("display", "none");



    var dato_archivo = $('#archivoInput').prop("files")[0];
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();



    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();
    var padre = $("#padre").val();
    var wf = $("#wf").val();
    var tipo = $("#tipo").val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    //console.log("usa plantilla ?: " + usarPlantillaSINO);


    //destinatarios a eliminar
    var rutDestinatarioE = document.getElementsByClassName("miDestinatarioE"),
        arrayDestinatarioE = [];
    for (var x = 0; x < rutDestinatarioE.length; x++) {
        arrayDestinatarioE[x] = rutDestinatarioE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario: " + arrayDestinatarioE[x]);
    }
    //destinatario copia a eliminar
    var rutDestinatarioCE = document.getElementsByClassName("miDestinatarioCE"),
        arrayDestinatarioCE = [];
    for (var x = 0; x < rutDestinatarioCE.length; x++) {
        arrayDestinatarioCE[x] = rutDestinatarioCE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario Copia: " + arrayDestinatarioCE[x]);
    }



    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    var arrayMiMedioEnvio = [];


    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
        arrayMiMedioEnvio[i] = $("#miMedioEnvio_" + rutDestinatario[i].value).val() + '_';

        //console.log("destinatario:" + arrayDestinatario[i]);
        //console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("cargo D: " + arrayCargoDestinatario[i]);
    }



    var miCopiaCorreo = document.getElementsByClassName("miCopiaCorreo"),
        arrayCopiaCorreo = [];
    var direccionCopia = document.getElementsByClassName("miCopiaDireccion"),
        arrayCopiaDireccion = [];
    var rutCopia = document.getElementsByClassName("miCopia"),
        arrayCopia = [];
    var arrayCargoCopia = [];
    var arrayMiTipoCopia = [];
    var arrayMiNombreDesCopia = [];
    var arrayMiMedioEnvioCopia = [];

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        arrayMiMedioEnvioCopia[x] = $("#miMedioEnvioCopia_" + rutCopia[x].value).val() + '_';

        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }


    //DATOS DEL MODAL ENVIAR VB
    var miCCertificado = $("#miCCertificado").val();


    var otraUnidadParaVB = document.getElementById('OtraUnidadPara_vb').value;
    var otraUnidadCopiaVB = $("#OtraUnidadCopia_vb").val();
    var otraUnidadVisacionVB = document.getElementById("OtraUnidadVisacion_vb").value;
    var otraUnidadComentarioVB = CKEDITOR.instances.OtraUnidadComentario_vb.getData();
    var otraunidadArchivoVB = $('#OtraUnidadArchivo_vb').prop("files")[0];


    //validamos tipos de envios
    if (!tipo_envio) {
        $("#errorTipoEnvio").css("display", "block");
        errores++;
    }
    //validamos datos sensibles
    if (!datosensibleSINO) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorDatosSensibles").css("display", "block");
        errores++;
    }
    //validamos  seleccion de tipo privacidad    
    if (!privacidadTipo) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorPrivacidad").css("display", "block");
        errores++;
    }
    //validamos  seleccion de usa plantilla    
    /*if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }*/

    //inicio validaciones form OTRA UNIDAD
    if (!otraUnidadParaVB) {
        $("#errorOtraUnidadParaVB").css("display", "block");
        errores++;
    }
    /*
    if (!otraUnidadCopiaVB) {
        $("#errorOtraUnidadCopiaVB").css("display", "block");
        errores++;
    }*/
    if (!otraUnidadVisacionVB) {
        $("#errorOtraUnidadVisacionVB").css("display", "block");
        errores++;
    }


    var formData = new FormData();

    formData.append("file", dato_archivo);
    formData.append("p_cuerpo", dato3);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_padre", padre);
    formData.append("p_wf", wf);
    formData.append("p_tipo", tipo);

    formData.append("p_tipo_envio", tipo_envio);

    //data destinatario
    formData.append("p_arrayDestinatario", arrayDestinatario);
    formData.append("p_arrayCargoDestinatario", arrayCargoDestinatario);
    formData.append("p_arrayDireccion", arrayDireccion);
    formData.append("p_arrayCorreo", arrayCorreo);
    formData.append("p_arrayMiTipo", arrayMiTipo);
    formData.append("p_arrayMiNombreDes", arrayMiNombreDes);


    //data destinatario copia
    formData.append("p_arrayCopia", arrayCopia);
    formData.append("p_arrayCargoCopia", arrayCargoCopia);
    formData.append("p_arrayCopiaCorreo", arrayCopiaCorreo);
    formData.append("p_arrayMiTipoCopia", arrayMiTipoCopia);
    formData.append("p_arrayCopiaDireccion", arrayCopiaDireccion);
    formData.append("p_arrayMiNombreDesCopia", arrayMiNombreDesCopia);


    //data destinatario a eliminar
    formData.append("p_arrayDestinatarioE", arrayDestinatarioE);
    formData.append("p_arrayDestinatarioCE", arrayDestinatarioCE);

    //enviar a vb OTRA UNIDAD
    formData.append("file3", otraunidadArchivoVB); //archivo adjunto EVB, OTRA UNIDAD
    formData.append("p_otraUnidadParaVB", otraUnidadParaVB);
    formData.append("p_otraUnidadCopiaVB", otraUnidadCopiaVB);
    formData.append("p_otraUnidadVisacionVB", otraUnidadVisacionVB);
    formData.append("p_otraUnidadComentarioVB", otraUnidadComentarioVB);


    formData.append("p_arrayMiMedioEnvio", arrayMiMedioEnvio);
    formData.append("p_arrayMiMedioEnvioCopia", arrayMiMedioEnvioCopia);

    //validamos si existen errores 
    if (errores > 0) {
        alert("ERRORES : Usted tiene " + errores + " errores.");
        /*callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
            errores: errores,
            respuesta: 'NOK'
        });*/
    } else {

        $.ajax({
            data: formData,
            url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_agregar_otraunidad_evb',
            type: 'post',
            success: function(html) {
                if (html == 'OK') {
                    //console.log("respuesta: OK");
                    //callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
                    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_evb", {
                        errores: errores,
                        respuesta: html
                    });
                } else {
                    alert("ERROR: al intentar agregar el certificado");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

    }


}






function miDatoSensibleSINO(valor, cadena) {

    var arraycadena = cadena.split('_');
    for (z = 0; z < arraycadena.length; z++) {
        //alert(arraycadena[x]);
        $("#privacidadTipo_" + arraycadena[z]).attr('disabled', 'disabled');
    }

    console.log("ESRADO PRIVACIDAD CAMBIO ESTADO:" + valor);
    if (valor == 'SI') {
        opcion = 2; //cambiamos el estado para saber que se hizo un cambio en el cuerpo
        callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_cambia_estado_privacidad&estado=" + opcion);
        $("#privacidadTipo_publi").attr('disabled', 'disabled');
        $("#privacidadTipo_publi").attr('checked', false);
        for (x = 0; x < arraycadena.length; x++) {
            if (arraycadena[x] != 'publi') {
                var nombre = arraycadena[x];
                $("#privacidadTipo_" + nombre).removeAttr('disabled');
            }
        }
    } else {
        opcion = 2; //cambiamos el estado para saber que se hizo un cambio en el cuerpo
        callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_cambia_estado_privacidad&estado=" + opcion);
        $("#privacidadTipo_publi").removeAttr('disabled');
        $("#privacidadTipo_publi").attr('checked', true);
        for (y = 0; y < arraycadena.length; y++) {
            if (arraycadena[y] != 'publi') {
                var nombre2 = arraycadena[y];
                $("#privacidadTipo_" + nombre2).attr('disabled', 'disabled');
                $("#privacidadTipo_" + nombre2).attr('checked', false);
            }
        }
    }

}

function fun_cambiar_estado_privacidad(estado) {
    opcion = estado; //cambiamos el estado para saber que se hizo un cambio en la privacidad
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_cambia_estado_privacidad&estado=" + opcion);
}


function click_verExpediente(distribucion, padre) {
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_verExpediente", {
        distribucion: distribucion,
        padre: padre
    });
}


function click_verVersion(id, version) {
    console.log("usted quiere ver la version " + version + " del documento " + id);
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_ver_version", {
        version: version,
        id: id
    });

}

//ml: inicamos la actualizacion de certificado
function accionActualizar() {

    $("#errorTipoEnvio").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");

    var errores = 0;

    var dato_archivo = $('#archivoInput').prop("files")[0];
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();



    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();
    var padre = $("#padre").val();
    var wf = $("#wf").val();
    var tipo = $("#tipo").val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    console.log("usa plantilla ?: " + usarPlantillaSINO);


    //destinatarios a eliminar
    var rutDestinatarioE = document.getElementsByClassName("miDestinatarioE"),
        arrayDestinatarioE = [];
    for (var x = 0; x < rutDestinatarioE.length; x++) {
        arrayDestinatarioE[x] = rutDestinatarioE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario: " + arrayDestinatarioE[x]);
    }
    //destinatario copia a eliminar
    var rutDestinatarioCE = document.getElementsByClassName("miDestinatarioCE"),
        arrayDestinatarioCE = [];
    for (var x = 0; x < rutDestinatarioCE.length; x++) {
        arrayDestinatarioCE[x] = rutDestinatarioCE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario Copia: " + arrayDestinatarioCE[x]);
    }



    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    var arrayMiMedioEnvio = [];


    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
        arrayMiMedioEnvio[i] = $("#miMedioEnvio_" + rutDestinatario[i].value).val() + '_';

        //console.log("destinatario:" + arrayDestinatario[i]);
        //console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("correoooo : " + arrayCorreo[i]);
    }



    var miCopiaCorreo = document.getElementsByClassName("miCopiaCorreo"),
        arrayCopiaCorreo = [];
    var direccionCopia = document.getElementsByClassName("miCopiaDireccion"),
        arrayCopiaDireccion = [];
    var rutCopia = document.getElementsByClassName("miCopia"),
        arrayCopia = [];
    var arrayCargoCopia = [];
    var arrayMiTipoCopia = [];
    var arrayMiNombreDesCopia = [];
    var arrayMiMedioEnvioCopia = [];

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        arrayMiMedioEnvioCopia[x] = $("#miMedioEnvioCopia_" + rutCopia[x].value).val() + '_';

        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }


    //validamos tipos de envios
    if (!tipo_envio) {
        $("#errorTipoEnvio").css("display", "block");
        errores++;
    }
    //validamos datos sensibles
    if (!datosensibleSINO) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorDatosSensibles").css("display", "block");
        errores++;
    }
    //validamos  seleccion de tipo privacidad    
    if (!privacidadTipo) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorPrivacidad").css("display", "block");
        errores++;
    }
    //validamos  seleccion de usa plantilla    
    /*if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }*/



    var formData = new FormData();

    formData.append("file", dato_archivo);
    formData.append("p_cuerpo", dato3);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_padre", padre);
    formData.append("p_wf", wf);
    formData.append("p_tipo", tipo);

    formData.append("p_tipo_envio", tipo_envio);

    //data destinatario
    formData.append("p_arrayDestinatario", arrayDestinatario);
    formData.append("p_arrayCargoDestinatario", arrayCargoDestinatario);
    formData.append("p_arrayDireccion", arrayDireccion);
    formData.append("p_arrayCorreo", arrayCorreo);
    formData.append("p_arrayMiTipo", arrayMiTipo);
    formData.append("p_arrayMiNombreDes", arrayMiNombreDes);
    formData.append("p_arrayMiMedioEnvio", arrayMiMedioEnvio);


    //data destinatario copia
    formData.append("p_arrayCopia", arrayCopia);
    formData.append("p_arrayCargoCopia", arrayCargoCopia);
    formData.append("p_arrayCopiaCorreo", arrayCopiaCorreo);
    formData.append("p_arrayMiTipoCopia", arrayMiTipoCopia);
    formData.append("p_arrayCopiaDireccion", arrayCopiaDireccion);
    formData.append("p_arrayMiNombreDesCopia", arrayMiNombreDesCopia);
    formData.append("p_arrayMiMedioEnvioCopia", arrayMiMedioEnvioCopia);

    //data destinatario a eliminar
    formData.append("p_arrayDestinatarioE", arrayDestinatarioE);
    formData.append("p_arrayDestinatarioCE", arrayDestinatarioCE);




    //accion modificar 
    if (errores > 0) {
        //alert("ERRORES : Usted tiene " + errores + " errores.");
        callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
            errores: errores,
            respuesta: 'NOK'
        });
    } else {
        $.ajax({
            data: formData,
            url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_modificar_certificado',
            type: 'post',
            success: function(html) {
                console.log("RESPUESTA ACCION ACTUALIZAR: " + html);
                if (html == 'OK') {
                    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
                        errores: errores,
                        respuesta: html
                    });
                } else {
                    alert("ERROR: al intentar agregar el certificado");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }



}

function fun_eliminarSeleccionadoDestinatarioM(rut, copia) {

    console.log("ELIMINAR RUT ::: " + rut);

    //controlamos el destinatario eliminado 
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_controlar_eliminados", { rut: rut });

    opcion = 2; //cambiamos el estado para saber que se hizo un cambio en destinatarios
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_cambia_estado_destinatario&estado=" + opcion);

    var micapa = document.getElementById('div_fiscalizadosAeliminar');
    if (copia == 'NO') {
        micapa.innerHTML += '<input type="hidden" class="miDestinatarioE" name="miDestinatarioE[]" id="miDestinatarioE_' + rut + '" value="' + rut + '">';
        $("#tr_" + rut).css("display", "none");
    } else {
        micapa.innerHTML += '<input type="hidden" class="miDestinatarioCE" name="miDestinatarioCE[]" id="miDestinatarioCE_' + rut + '" value="' + rut + '">';
        $("#trc_" + rut).css("display", "none");
    }



    /*
        1.- hay que ocultar el registro
        2.- hay que guardarlo en un array porque pueden ser muchos
        3.- hay que tener en cuenta si es una nueva version o la misma
        4.- eso depende de el campo genera b¿version y que el usuario sea el mismo 

        observaciones
        1.- no puedo eliminarlo directo porque si es una nueva version 
            al momento de guardar los registro se vuelve un kilombo
    */




    /*
    $.ajax({
        data: {
            rut: rut,
            version: version,
            wf: wf
        },
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_eliminar_distribucion',
        type: 'post',
        success: function(html) {
            if (html == 'OK') {
                $("#tr_" + rut).css("display", "none");
            } else {
                alert("ERROR: al intentar eliminar al destinatario");
            }
        }
    });
    */
}


//ml: iniciamos la accion de eliminar
function accionBtnFormEliminar() {
    //console.log("INICIAMOS LA ELIMINACION DESDE EL MODIFICAR");
    callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_eliminar");


}

//ml: elimina certificado 
function eliminarCertificado() {
    console.log("PASO :: ELIMINAR CERTIFICADO");
    //var motivo = document.getElementsByClassName("motivo_eliminar");
    var motivo = document.getElementById("motivo_eliminar").value;
    //console.log("MOTIVO :: " + motivo);

    $.ajax({
        data: {
            motivo: motivo
        },
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_eliminar_certificado',
        type: 'post',
        success: function(html) {
            if (html == 'OK') {
                $("#div_eliminar_certificado").dialog('close');
                console.log("ENVIAMOS LA RESPUESTA DE LA ELIMINACION ..");
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_eliminar");


            } else {
                alert("ERROR: al intentar eliminar el caso");
            }
        }
    });

}



//||||||||||||||||||||||||||||||||||||||||||||||||||||||||| MEDIO DE ENVIO 

//iniciamos la firma del certificado
function accionInicioFirmar() {

    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();
    if (tipo_envio == 'noen') { //para medio de envio NO SE ENVIA
        //console.log("USTED SELECCIONO LA OPCION NO SE ENVIA como medio de envio, por lo tanto solo se firma");
        accionFirmarCertificado(); //firmamos certificado sin envio  
    } else {
        //validamos medio de envio antes de firmar certificado 
        //console.log("IDENTIFICAMOS EL OTRO MEDIO DE ENVIO, distinto a NO SE ENVIA");
        if (tipo_envio == 'elect') {
            //console.log("USTED SELECCIONO LA OPCION MEDIO DE ENVIO ELECTRONICO");
            medioEnvioElectronico();
        } else {
            //console.log("USTED SELECCIONO LA OPCION MEDIO DE ENVIO MANUAL");
            medioEnvioManual();
        }
    }
}



//iniciamos la validacion para el medio de envio manual
function medioEnvioManual() {
    console.log("INICIAMOS VALIDACION MEDIO DE ENVIO MANUAL");

    $.ajax({
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_verificar_exiten_cambios',
        type: 'post',
        success: function(html) {

            errores = validarErroresFormulario();
            if (errores > 0) {
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
                    errores: errores,
                    respuesta: 'NOK'
                });
            } else {

                //alert(html);
                if (html == 'SI') {
                    //console.log("PASO 2 :: EXISTEN CAMBIOS, se procede a guardar los cambios");
                    var opcion = confirm("Usted tiene cambios pendientes , ¿Desea guardar los cambios?");
                    if (opcion == true) {
                        /*
                          1.- Aqui hay que guardar los nuevos cambios [OK]
                          2.- Hay que validar el tipo de envio electronico [OK]
                          3.- Hay que firmar [OK]
                    
                         */

                        formData = obtenerDataParaFirmar();
                        inicioGuardarCambiosRealizados(formData, 'manual'); //guardamos los cambios y chequeamos medio de envio

                    } else {
                        /*
                          1. -No se toman en cuenta los cambios
                          2.- Hay que validar el tipo de envio electronico 
                          3.- Hay que firmar
                         */

                        //console.log("PASO 3 :: no se toma en cuenta los cambios , se valida envio electronico y se procede a firmar");
                        chequearMedioManual();
                    }

                } else {
                    /*  
                        1.- Hay que validar el tipo de envio electronico 
                        2.- Hay que firmar
                        */
                    //console.log("PASO 2 :: NO EXISTEN CAMBIOS , se valida envio electronico , se firma");
                    chequearMedioManual();
                }

            }


        }
    });


}



//iniciamos la validacion para el medio de envio electronico
function medioEnvioElectronico() {
    //console.log("PASO 1 :: INICIAMOS VALIDACION MEDIO DE ENVIO ELECTRONICO");
    //validamos si hay cambios 
    $.ajax({
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_verificar_exiten_cambios',
        type: 'post',
        success: function(html) {

            errores = validarErroresFormulario();
            if (errores > 0) {
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta", {
                    errores: errores,
                    respuesta: 'NOK'
                });
            } else {

                //console.log(html);
                if (html == 'SI') {
                    //console.log("PASO 2 :: EXISTEN CAMBIOS, se procede a guardar los cambios");
                    var opcion = confirm("Usted tiene cambios pendientes , ¿Desea guardar los cambios?");
                    if (opcion == true) {
                        //console.log("PASO :: GUARDAMOS LOS CAMBIOS Y CHEQUEAMOS MEDIO ELECTRONICO PARA FIRMAR");
                        formData = obtenerDataParaFirmar();
                        inicioGuardarCambiosRealizados(formData, 'electronico'); //guardamos los cambios y chequeamos medio de envio

                    } else {
                        //console.log("PASO 3 :: no se toma en cuenta los cambios , se valida envio electronico y se procede a firmar");
                        chequearMedioElectronico();
                    }

                } else {
                    //console.log("PASO 2 :: NO EXISTEN CAMBIOS , se valida envio electronico , se firma");
                    chequearMedioElectronico();
                }

            }


        }
    });


}

//guardamos los cambios y validamos medio electronico
function inicioGuardarCambiosRealizados(formData, medioEnvio) {

    $.ajax({
        data: formData,
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_modificar_certificado',
        type: 'post',
        success: function(html) {
            console.log("RESPUESTA ACCION ACTUALIZAR: " + html);
            if (html == 'OK') {

                console.log("PASO :: Se guardaron los cambios ahora hay que validar medio electronico");
                if (medioEnvio == 'electronico') {
                    chequearMedioElectronico();
                } else if (medioEnvio == 'manual') {
                    chequearMedioManual();
                } else {
                    alert("ERROR: EL MEDIO DE ENVIO NO EXISTE.");
                }


            } else {
                alert("ERROR: Al intentar guardar los nuevos cambios.");
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });


}


function chequearMedioManual() {

    //alert("AQUI CHEQUEAMOS EL MEDIO DE ENVIO MANUAL");

    $.ajax({
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_validar_medio_manual',
        type: 'post',
        success: function(html) {

            if (html == 'OK') {
                //aqui procedemos a firmar , mostramos el modal para firmar
                //console.log("SE VALIDARON LOS CAMBIOS SE PROCEDE A FIRMAR");
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_firmar");
            } else {

                //console.log("ERROR :: No puede firmar, no paso la validacion para el medio de envio manual.");
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_mde", {
                    medio_envio: 'manual'
                });
            }
        }
    });

}



//chequeamos medio electronico para poder firmar
function chequearMedioElectronico() {

    var rutDestinatarioE = document.getElementsByClassName("miDestinatarioE"),
        arrayDestinatarioE = [];
    for (var x = 0; x < rutDestinatarioE.length; x++) {
        arrayDestinatarioE[x] = rutDestinatarioE[x].value + '_';
        //console.log("Usted quiere eliminar este Destinatario: " + arrayDestinatarioE[x]);
    }
    //destinatario copia a eliminar
    var rutDestinatarioCE = document.getElementsByClassName("miDestinatarioCE"),
        arrayDestinatarioCE = [];
    for (var x = 0; x < rutDestinatarioCE.length; x++) {
        arrayDestinatarioCE[x] = rutDestinatarioCE[x].value + '_';
        //console.log("Usted quiere eliminar este Destinatario Copia: " + arrayDestinatarioCE[x]);
    }



    //realizamos la validacion medio de envio electronico
    $.ajax({
        data: {
            p_arrayDestinatarioE: arrayDestinatarioE,
            p_arrayDestinatarioCE: arrayDestinatarioCE
        },
        url: 'index.php?pagina=paginas.modificar_docto&funcion=fun_validar_medio_electronico',
        type: 'post',
        success: function(html) {

            if (html == 'OK') {
                //alert("SE VALIDARON LOS CAMBIOS SE PROCEDE A FIRMAR");
                //aqui procedemos a firmar , mostramos el modal para firmar
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_mostrar_modal_firmar");
            } else {
                callback_gral("index.php?pagina=paginas.modificar_docto&funcion=fun_respuesta_mde", {
                    medio_envio: 'electronico'
                });
            }
        }
    });

}



// no se esta ocupando aun
//obtener data para firmar
function obtenerDataParaFirmar() {

    var dato_archivo = $('#archivoInput').prop("files")[0];
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();



    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();
    var padre = $("#padre").val();
    var wf = $("#wf").val();
    var tipo = $("#tipo").val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();

    //console.log("usa plantilla ?: " + usarPlantillaSINO);


    //destinatarios a eliminar
    var rutDestinatarioE = document.getElementsByClassName("miDestinatarioE"),
        arrayDestinatarioE = [];
    for (var x = 0; x < rutDestinatarioE.length; x++) {
        arrayDestinatarioE[x] = rutDestinatarioE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario: " + arrayDestinatarioE[x]);
    }
    //destinatario copia a eliminar
    var rutDestinatarioCE = document.getElementsByClassName("miDestinatarioCE"),
        arrayDestinatarioCE = [];
    for (var x = 0; x < rutDestinatarioCE.length; x++) {
        arrayDestinatarioCE[x] = rutDestinatarioCE[x].value + '_';
        console.log("Usted quiere eliminar este Destinatario Copia: " + arrayDestinatarioCE[x]);
    }



    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    var arrayMiMedioEnvio = [];

    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
        arrayMiMedioEnvio[i] = $("#miMedioEnvio_" + rutDestinatario[i].value).val() + '_';

        //console.log("destinatario:" + arrayDestinatario[i]);
        //console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("correoooo : " + arrayCorreo[i]);
    }



    var miCopiaCorreo = document.getElementsByClassName("miCopiaCorreo"),
        arrayCopiaCorreo = [];
    var direccionCopia = document.getElementsByClassName("miCopiaDireccion"),
        arrayCopiaDireccion = [];
    var rutCopia = document.getElementsByClassName("miCopia"),
        arrayCopia = [];
    var arrayCargoCopia = [];
    var arrayMiTipoCopia = [];
    var arrayMiNombreDesCopia = [];
    var arrayMiMedioEnvioCopia = [];

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        arrayMiMedioEnvioCopia[x] = $("#miMedioEnvioCopia_" + rutCopia[x].value).val() + '_';

        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }



    var formData = new FormData();

    formData.append("file", dato_archivo);
    formData.append("p_cuerpo", dato3);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_padre", padre);
    formData.append("p_wf", wf);
    formData.append("p_tipo", tipo);

    formData.append("p_tipo_envio", tipo_envio);

    //data destinatario
    formData.append("p_arrayDestinatario", arrayDestinatario);
    formData.append("p_arrayCargoDestinatario", arrayCargoDestinatario);
    formData.append("p_arrayDireccion", arrayDireccion);
    formData.append("p_arrayCorreo", arrayCorreo);
    formData.append("p_arrayMiTipo", arrayMiTipo);
    formData.append("p_arrayMiNombreDes", arrayMiNombreDes);


    //data destinatario copia
    formData.append("p_arrayCopia", arrayCopia);
    formData.append("p_arrayCargoCopia", arrayCargoCopia);
    formData.append("p_arrayCopiaCorreo", arrayCopiaCorreo);
    formData.append("p_arrayMiTipoCopia", arrayMiTipoCopia);
    formData.append("p_arrayCopiaDireccion", arrayCopiaDireccion);
    formData.append("p_arrayMiNombreDesCopia", arrayMiNombreDesCopia);


    //data destinatario a eliminar
    formData.append("p_arrayDestinatarioE", arrayDestinatarioE);
    formData.append("p_arrayDestinatarioCE", arrayDestinatarioCE);

    formData.append("p_arrayMiMedioEnvio", arrayMiMedioEnvio);
    formData.append("p_arrayMiMedioEnvioCopia", arrayMiMedioEnvioCopia);



    return formData;



}


// no se esta ocupando aun
//verificamos si existen errores
function validarErroresFormulario() {

    $("#errorTipoEnvio").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");


    var errores = 0;
    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();


    //validamos tipos de envios
    if (!tipo_envio) {
        $("#errorTipoEnvio").css("display", "block");
        errores++;
    }
    //validamos datos sensibles
    if (!datosensibleSINO) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorDatosSensibles").css("display", "block");
        errores++;
    }
    //validamos  seleccion de tipo privacidad    
    if (!privacidadTipo) {
        //console.log("NO definido //" + datosensibleSINO + "//" + privacidadTipo);
        $("#errorPrivacidad").css("display", "block");
        errores++;
    }


    return errores;

}









// ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
// ||||||||||||||||||||||||||||||||||  MODAL VARIOS |||||||||||||||||||||||||||||||||||||||
// ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


function cerrar_modal_eliminar() {
    $("#div_eliminar_certificado").dialog('close');
}

//ml: cerramos modal de respuesta eliminar y volvemos a grilla 
function cerrar_modal_eliminarOK() {
    //cerramos el modal
    $("#div_respuesta_eliminar").dialog('close');

    //volvemos a la grilla
    window.opener.href = "/intranet/aplic/wf/bandejaEntradaV2.php?p_procesos=0";
    window.close();


}

//ml: cerramos modal de respuesta eliminar y volvemos a grilla 
function cerrar_modal_MensajeErrorMedioEnvio() {
    //cerramos el modal
    $("#div_respuesta_mde").dialog('close');

}