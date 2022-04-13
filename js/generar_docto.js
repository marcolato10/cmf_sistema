$(document).ready(function() {

    $(".js-example-basic-single").select2();

    //|||inicio: Permite abrir modal
    $("#div_verExpediente").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });
    $("#div_respuesta").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });
    $("#div_enviarvb").dialog({
        autoOpen: false,
        modal: true,
        width: '650px'
    });
    //|||fin: Permite abrir modal



});

//$(".js-example-basic-single").select2();

function mostrar_opciones_todos() {
    console.log("MOSTRAMOS LAS OPCIONES DISPONIBLES");

    $("#div_copia_todos").css("display", "block");
    $("#div_visacion_todos").css("display", "block");
    $("#div_comentario_todos").css("display", "block");
    $("#div_archivo_todos").css("display", "block");
    $("#div_btn_enviar_todos").css("display", "block");

}

function agregarOtroDestinatario() {
    alert("agregamos otro destinatario");
}

function agregarOtroConCopia() {
    alert("agregamos con copia a...");
}

function verExpedienteModal() {
    //$("#modalVerExpediente").html(html);
    //alert("llegue aqui");
    callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_verExpediente");

}

function eliminar() {
    alert("eliminar lo seleccionado");
}

function verVersion(id, version) {
    alert("usted quiere ver la version " + version + " del documento " + id);
}







//inicio de enviar a vb :: abrimos modal y mostramos formulario
function accionBtnFormEnviaraAVB() {
    callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_enviar_vb");
}



function accionBtnFormFirmar() {
    alert("aqui Firmar");
}

function accionBtnFormEliminar() {
    alert("aqui Eliminar");
}



function datoSensibleSINO(valor, cadena) {


    var arraycadena = cadena.split('_');
    for (z = 0; z < arraycadena.length; z++) {
        //alert(arraycadena[x]);
        $("#privacidadTipo_" + arraycadena[z]).attr('disabled', 'disabled');
    }
    //alert(valor);


    if (valor == 'SI') {

        $("#privacidadTipo_publi").attr('disabled', 'disabled');
        $("#privacidadTipo_publi").attr('checked', false);

        for (x = 0; x < arraycadena.length; x++) {
            if (arraycadena[x] != 'publi') {
                var nombre = arraycadena[x];
                $("#privacidadTipo_" + nombre).removeAttr('disabled');
            }
        }
    } else {
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

//ml: mostramos la lista de plantillas en la vista si seleccionamos la opcion SI
// en caso contrario se oculta
function datoTipoPlantilla(valor) {

    //$("#tipoPlantilla").attr('checked', false);
    document.querySelectorAll('[name=tipoPlantilla]').forEach((x) => x.checked = false);
    if (valor == 'SI') {
        //console.log("debemos mostrar las plantillas disponibles");
        document.getElementById('visorArchivo').innerHTML = ''; //limpiamos div vista previa
        $("#div_plantillas_disponibles").css("display", "block");
        $("#divCuerpo1").css("display", "none"); //ocultamos el cuerpo
        $("#divCuerpo2").css("display", "none"); //ocultamos el cuerpo
        $("#divSeleccionaPdf").css("display", "none"); //ocultamos la seleccion de pdf 
        document.getElementById("archivoInput").value = ""; //reseteamos el input file
    } else {
        //console.log("debemos ocultar las plantillas disponibles");
        CKEDITOR.instances.certificado_divCuerpo.setData('');
        $("#div_plantillas_disponibles").css("display", "none");
        $("#divCuerpo1").css("display", "block"); //ocultamos el cuerpo
        $("#divCuerpo2").css("display", "block"); //ocultamos el cuerpo
        $("#divSeleccionaPdf").css("display", "block");
        //document.getElementById("archivoInput").value = ""; //reseteamos el input file
    }




}

function validarArchivo(file) {
    console.log("enviados el archivo que queremos cargar");
    $.ajax({
        data: {
            p_file: file
        },
        url: 'index.php?pagina=paginas.generar_docto&funcion=fun_validar_archivo',
        type: 'post',
        success: function(html) {
            if (html == 'OK') {
                alert("el formato es  el correcto , su pdf se cargo al cuerpo");
                CKEDITOR.instances.certificado_divCuerpo.setData('');
                $("#divCuerpo1").css("display", "none"); //ocultamos el cuerpo
                $("#divCuerpo2").css("display", "none"); //ocultamos el cuerpo
            } else {
                alert("ERROR: El formato del archivo debe ser solo PDF.");
                document.getElementById("agregarPdf").value = ""; //reseteamos el input file
                $("#divCuerpo1").css("display", "block"); //mostramos el cuerpo
                $("#divCuerpo2").css("display", "block"); //mostramos el cuerpo
            }
        }
    });

}

function validarSeleccion() {
    alert("validamos seleccion de privacidad");
}


//accion de cambiar pestañas en modal de enviar VB
function cambiarPestanna(pestannas, pestanna, valor) {

    // Obtiene los elementos con los identificadores pasados.
    pestanna = document.getElementById(pestanna.id);
    listaPestannas = document.getElementById(pestannas.id);

    // Obtiene las divisiones que tienen el contenido de las pestañas.
    cpestanna = document.getElementById('c' + pestanna.id);
    listacPestannas = document.getElementById('contenido' + pestannas.id);

    i = 0;
    // Recorre la lista ocultando todas las pestañas y restaurando el fondo 
    // y el padding de las pestañas.
    while (typeof listacPestannas.getElementsByTagName('div')[i] != 'undefined') {
        $(document).ready(function() {
            if (valor == 1) {
                $("#cpestana2").css('display', 'none');
                $("#cpestana3").css('display', 'none');
            } else if (valor == 2) {
                $("#cpestana1").css('display', 'none');
                $("#cpestana3").css('display', 'none');
            } else {
                $("#cpestana1").css('display', 'none');
                $("#cpestana2").css('display', 'none');
            }
            $(listaPestannas.getElementsByTagName('li')[i]).css('background', '');
            $(listaPestannas.getElementsByTagName('li')[i]).css('padding-bottom', '');
        });
        i += 1;
    }

    $(document).ready(function() {
        // Muestra el contenido de la pestaña pasada como parametro a la funcion,
        // cambia el color de la pestaña y aumenta el padding para que tape el  
        // borde superior del contenido que esta juesto debajo y se vea de este 
        // modo que esta seleccionada.
        $(cpestanna).css('display', '');
        $(pestanna).css('background', 'dimgray');
        $(pestanna).css('padding-bottom', '2px');
    });

}

//ml: accion envia a visto bueno desde la pestaña TODOS en formulario CREAR CERTIFICADO
function accionEVB_Todos() {

    console.log("INICIAMOS ACCION EVB DESDE LA PESTAÑA TODOS");
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

    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';

        console.log("rut destinatario: " + rutDestinatario[i].value);
        //console.log("cargo D: " + arrayCargoDestinatario[i]);
        console.log("DIRECCION :: " + arrayDireccion[i].value);
        console.log("NOMBRE DESTINATARIO :: " + arrayMiNombreDes[i].value);
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

    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
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

    formData.append("p_paraVBTodos", ParaVBTodos);
    formData.append("p_copiaVBTodos", CopiaVBTodos);
    formData.append("p_visacionVBTodos", VisacionVBTodos);
    formData.append("p_comentarioVBTodos", ComentarioVBTodos);


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
    if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }

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
        callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
            errores: errores,
            respuesta: 'NOK'
        });
    } else {
        console.log("PASO:: NO EXISTEN ERRORES EVB DESDE TODAS");
        $.ajax({
            data: formData,
            url: 'index.php?pagina=paginas.generar_docto&funcion=fun_enviarvb_todas',
            type: 'post',
            success: function(html) {
                if (html == 'OK') {
                    console.log("RESPUESTA: OK , SE ENVIO A VB CORRECTAMENTE DESDE PESTAÑA TODAS");
                    callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
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


//accion Enviar a VB
function accionEnviarVB() {


    var errores = 0;
    $("#errorTipoEnvio").css("display", "none");
    $("#errorDatosSensibles").css("display", "none");
    $("#errorPrivacidad").css("display", "none");
    $("#errorUsaPlantilla").css("display", "none");
    $("#errorTipoPlantilla").css("display", "none");
    $("#errorParaVB").css("display", "none");
    $("#errorCopiaVB").css("display", "none");
    $("#errorVisacionVB").css("display", "none");




    var dato_archivo = $('#archivoInput').prop("files")[0];
    var arrayCorreo = [];
    var arrayDireccion = [];
    var rutDestinatario = document.getElementsByClassName("miDestinatario"),
        arrayDestinatario = [];
    var arrayCargoDestinatario = [];
    var arrayMiTipo = [];
    var arrayMiNombreDes = [];
    for (var i = 0; i < rutDestinatario.length; i++) {
        arrayDestinatario[i] = rutDestinatario[i].value + '_';
        arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
        arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
        arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
        arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
        arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';
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
    for (var x = 0; x < rutCopia.length; x++) {
        arrayCopia[x] = rutCopia[x].value + '_';
        arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
        arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
        arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
        arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
        arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
        //console.log("rut copia: " + rutCopia[x].value);
        //console.log("Cargo CC: " + arrayCargoCopia[x]);
    }



    var padre = $("#padre").val();
    var dato3 = CKEDITOR.instances.certificado_divCuerpo.getData();
    var datosensibleSINO = $('input:radio[name=datosensibleSINO]:checked').val();
    var usarPlantillaSINO = $('input:radio[name=usarPlantillaSINO]:checked').val();
    var tipo_envio = $('input:radio[name=tipo_envio]:checked').val();
    var tipoPlantilla = $('input:radio[name=tipoPlantilla]:checked').val();
    var privacidadTipo = $('input:radio[name=privacidadTipo]:checked').val();


    //DATOS DEL MODAL ENVIAR VB
    var miCCertificado = $("#miCCertificado").val();
    var paraVB = document.getElementById('para_vb').value;
    //var copiaVB = document.getElementById("copia_vb").value;
    var copiaVB = $("#copia_vb").val();

    var visacionVB = document.getElementById("visacion_vb").value;
    var comentarioVB = CKEDITOR.instances.comentario_vb.getData();
    var archivoVB = $('#archivo_vb').prop("files")[0];





    var formData = new FormData();
    formData.append("file", dato_archivo);
    formData.append("file2", archivoVB); //archivo adjunto EVB

    formData.append("p_cuerpo", dato3);
    formData.append("p_datosensibleSINO", datosensibleSINO);
    formData.append("p_usarPlantillaSINO", usarPlantillaSINO);

    formData.append("p_tipo_envio", tipo_envio);

    formData.append("p_privacidadTipo", privacidadTipo);
    formData.append("p_padre", padre);
    formData.append("p_miccertificado", miCCertificado);

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


    formData.append("p_paraVB", paraVB);
    formData.append("p_copiaVB", copiaVB);
    formData.append("p_visacionVB", visacionVB);
    formData.append("p_comentarioVB", comentarioVB);



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
    if (!usarPlantillaSINO) {
        $("#errorUsaPlantilla").css("display", "block");
        errores++;
    } else {
        if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
            $("#errorTipoPlantilla").css("display", "block");
            errores++;
        }
    }

    //validaciones Enviar VB
    if (!paraVB) {
        $("#errorParaVB").css("display", "block");
        errores++;
    }
    if (!visacionVB) {
        $("#errorVisacionVB").css("display", "block");
        errores++;
    }
    /*no es obligatorio agregar una copia 
    if (!copiaVB) {$("#errorCopiaVB").css("display", "block");errores++;}
    */


    //validamos si existen errores 
    if (errores > 0) {
        //console.log("ERRORES : Usted tiene " + errores + " errores.");
        callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
            errores: errores,
            respuesta: 'NOK'
        });
    } else {

        $.ajax({
            data: formData,
            url: 'index.php?pagina=paginas.generar_docto&funcion=fun_agregar_enviar_vb',
            type: 'post',
            success: function(html) {
                if (html == 'OK') {
                    //console.log("respuesta: OK");
                    callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
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

//cargamos el combo PARA en el formulario de Otra Unidad
function cargarParaOtraUnidad(miUnidad) {
    if (miUnidad) {
        $.ajax({
            data: {
                unidad: miUnidad
            },
            url: 'index.php?pagina=paginas.generar_docto&funcion=fun_cargar_para_otra_unidad',
            type: 'post',
            success: function(html) {
                var html = JSON.parse(html);
                if (html['RESULTADO'] == 'OK') {
                    console.log(html);
                    document.getElementById("otraUnidadParaVB").innerHTML =
                        '<div style="display: table-cell; text-align: right;" ><label class="seleccionaPdf" for="OtraUnidadPara_vb">&nbsp;&nbsp;&nbsp;&nbsp;Para:</label></div><div style="display: table-cell"><select style="margin-left:20px !important;" class="form-control js-example-basic-single" name="OtraUnidadPara_vb" id="OtraUnidadPara_vb"><option value>seleccione una opción</option>' + html["LISTADO"] + '</select><spam class="errorModificar" id="errorOtraUnidadParaVB" style="display:none;">(*) Usted debe seleccionar una opción</spam></div>';
                    document.getElementById("otraUnidadCopiaVB").innerHTML =
                        '<div style="display: table-cell;"><label class="seleccionaPdf" for="OtraUnidadCopia_vb">Con copia:</label></div><div style="display: table-cell"><select class="form-control js-example-basic-multiple" name="OtraUnidadCopia_vb" id="OtraUnidadCopia_vb" multiple="multiple" ><option value>seleccione una opción</option>' + html["LISTADO_COPIA"] + '</select><spn class="errorModificar" id="errorOtraUnidadCopiaVB" style="display:none;">(*) Usted debe seleccionar una opción</span></div>';
                } else {
                    console.log("ERROR");
                }

            },
        });


    } else {
        console.log("la unidad no existe");
    }

}

//accion de enviar a vb otra unidad
function accionOtraUnidadEVB() {
    console.log("INICIO ACCION OTRA UNIDAD EVB");
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


    var division_vb = document.getElementById('division_vb').value;
    if (division_vb) {
        console.log("paso1");


        var dato_archivo = $('#archivoInput').prop("files")[0];
        var arrayCorreo = [];
        var arrayDireccion = [];
        var rutDestinatario = document.getElementsByClassName("miDestinatario"),
            arrayDestinatario = [];
        var arrayCargoDestinatario = [];
        var arrayMiTipo = [];
        var arrayMiNombreDes = [];
        for (var i = 0; i < rutDestinatario.length; i++) {
            arrayDestinatario[i] = rutDestinatario[i].value + '_';
            arrayCargoDestinatario[i] = $("#input_cargoFiscalizadoLista_" + rutDestinatario[i].value).val() + '_';
            arrayDireccion[i] = $("#miDireccion_" + rutDestinatario[i].value).val() + '_';
            arrayCorreo[i] = $("#miCorreo_" + rutDestinatario[i].value).val() + '_';
            arrayMiTipo[i] = $("#miTipo_" + rutDestinatario[i].value).val() + '_';
            arrayMiNombreDes[i] = $("#miNombreDes_" + rutDestinatario[i].value).val() + '_';

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
        for (var x = 0; x < rutCopia.length; x++) {
            arrayCopia[x] = rutCopia[x].value + '_';
            arrayCargoCopia[x] = $("#input_cargoFiscalizadoLista_" + rutCopia[x].value).val() + '_';
            arrayCopiaDireccion[x] = $("#miCopiaDireccion_" + rutCopia[x].value).val() + '_';
            arrayCopiaCorreo[x] = $("#miCopiaCorreo_" + rutCopia[x].value).val() + '_';
            arrayMiTipoCopia[x] = $("#miTipoCopia_" + rutCopia[x].value).val() + '_';
            arrayMiNombreDesCopia[x] = $("#miNombreDesCopia_" + rutCopia[x].value).val() + '_';
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




        var otraUnidadParaVB = document.getElementById('OtraUnidadPara_vb').value;
        //var otraUnidadCopiaVB = document.getElementById("OtraUnidadCopia_vb").value;
        var otraUnidadCopiaVB = $("#OtraUnidadCopia_vb").val();
        var otraUnidadVisacionVB = document.getElementById("OtraUnidadVisacion_vb").value;
        var otraUnidadComentarioVB = CKEDITOR.instances.OtraUnidadComentario_vb.getData();
        var otraunidadArchivoVB = $('#OtraUnidadArchivo_vb').prop("files")[0];



        var formData = new FormData();
        formData.append("file", dato_archivo);
        formData.append("file3", otraunidadArchivoVB); //archivo adjunto EVB, OTRA UNIDAD

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



        formData.append("p_otraUnidadParaVB", otraUnidadParaVB);
        formData.append("p_otraUnidadCopiaVB", otraUnidadCopiaVB);
        formData.append("p_otraUnidadVisacionVB", otraUnidadVisacionVB);
        formData.append("p_otraUnidadComentarioVB", otraUnidadComentarioVB);


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
        if (!usarPlantillaSINO) {
            $("#errorUsaPlantilla").css("display", "block");
            errores++;
        } else {
            if (!tipoPlantilla && usarPlantillaSINO == 'SI') {
                $("#errorTipoPlantilla").css("display", "block");
                errores++;
            }
        }

        //inicio validaciones form OTRA UNIDAD
        if (!otraUnidadParaVB) {
            $("#errorOtraUnidadParaVB").css("display", "block");
            errores++;
        }
        /*if (!otraUnidadCopiaVB) {
            $("#errorOtraUnidadCopiaVB").css("display", "block");
            errores++;
        }*/
        if (!otraUnidadVisacionVB) {
            $("#errorOtraUnidadVisacionVB").css("display", "block");
            errores++;
        }



        //validamos si existen errores
        if (errores > 0) {
            console.log("ERRORES : Usted tiene " + errores + " errores.");
            callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
                errores: errores,
                respuesta: 'NOK'
            });
        } else {
            console.log("paso2");
            $.ajax({
                data: formData,
                url: 'index.php?pagina=paginas.generar_docto&funcion=fun_agregar_otraunidad_evb',
                type: 'post',
                success: function(html) {
                    if (html == 'OK') {
                        console.log("RESPUESTA: OK");
                        callback_gral("index.php?pagina=paginas.generar_docto&funcion=fun_respuesta", {
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




    } else {
        console.log("ERROR:la division seleccionada no existe.");
        $("#errorDivisionVB").css("display", "block");
    }



}