// JavaScript Document
$(document).ready(function(){inicio_propiedades()});

function inicio_propiedades(){
	//$("#texttareaComentario").cleditor({width:'100%',styles:[['background-color',' yellow']]});
	/*$("#oculto").stickynote({
				size 			 : 'large',
				containment		 : 'posit',
				event			 : 'click',
				ontop			: true,
				cerrar			: false,
				ok				: false,
				id_textarea		: 'textarea_comentario',
				width: "500px",
				height: "400px"
			});*/
	$( "#tabs_enviar" ).tabs();
	change_selectParaOtraUnidad();
	$("#selectParaOtraUnidad").change(function(){change_selectParaOtraUnidad();});
	//$("#oculto").click();
	init_contadorTa("textarea_comentario","contadorTaComentario", 1000);
	
}

function change_selectParaOtraUnidad(){
	$.ajax({
  		url: "index.php?pagina=paginas.enviar&funcion=change_selectParaOtraUnidad",
  		context: document.body,
		data:{'id_division':$("#selectParaOtraUnidad").val()},
  		success: function(html){		
    		$("#selectParaOtraUnidadPersona").html(html);
			//$("#modalEnviar").dialog('open');
  		}
	});
	
}



function init_contadorTa(idtextarea, idcontador,max)
{
    $("#"+idtextarea).keyup(function()
            {
                updateContadorTa(idtextarea, idcontador,max);
            });
    
    $("#"+idtextarea).change(function()
    {
            updateContadorTa(idtextarea, idcontador,max);
    });
    
}

function updateContadorTa(idtextarea, idcontador,max)
{
    var contador = $("#"+idcontador);
    var ta =     $("#"+idtextarea);
    contador.html("0/"+max);
    
    contador.html(ta.val().length+"/"+max);
    if(parseInt(ta.val().length)>max)
    {
        ta.val(ta.val().substring(0,max-1));
        contador.html(max+"/"+max);
    }

}