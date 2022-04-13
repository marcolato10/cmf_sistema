// JavaScript Document
$(document).ready(function(){inicioGeneralFramework()});
		 
function inicioGeneralFramework(){
	$(".datepicker").datepicker({
		showOn:'button', 
		buttonImage:'/biblioteca/images/ico_calendario.gif', 
		buttonImageOnly: true,
		dateFormat: 'dd/mm/yy',
		changeMonth: true,
		changeYear: true});
}

function isAutenticadoIntra(funcion){
	var dateString = new Date().getTime();
	
    $.post("index.php?isAutenticadoIntra",{},
           function(json){
               if(json.autenticado == 'N'){
		
				   if( $("body").length){
					   $("body").append("<div id='"+dateString+"' class='buscando'></div>");
				   }
				   $("#"+dateString).dialog(
				   { 
				   	width:450,
					height:300,
					maxHeight: 300,
					minHeight: 300,
					title:'Iniciar Sesion',
					modal:true, 
					stack: true,
					autoOpen:false,
					modal:true,
					close:function(){
						$(this).remove();
					},
					buttons:{
						'OK':function(){
							var v_usuario = $("#inputUsuario").val();
							var v_password = $("#inputPassword").val();
							$(this).dialog('close');
							$.post("/intranet/aplic/rrhh/portal_rhv/rhv_procesar_login.php?recuperar=SI",
								{
									clave:v_password,
									usuario:v_usuario
								},
								function(){
									
									isAutenticadoIntra(funcion);
								}
							)							
						}
					}
				});
				   
				   $("#"+dateString).html('<table>\
				   							<tr>\
											<td colspan="3" style="border:1px solid #CCC;font-size:12px">\
											<img src="/biblioteca/images/advertencia.png" /><i>Su sesion ha terminado, favor ingrese sus datos nuevamente</i>\
											</td>\
											</tr>\
											<tr><td colspan="3">&nbsp;</td></tr>\
				   							<tr>\
												<td><img src="/biblioteca/images/usuario.png" />Nombre de Usuario</td>\
												<td>:</td>\
												<td width="40px"><input id="inputUsuario" type="text" /></td>\
											</tr>\
											<tr>\
												<td><img src="/biblioteca/images/llave.png" />Contrase&ntilde;a</td>\
												<td>:</td>\
												<td><input id="inputPassword" type="password" /></td>\
											</tr>\
										  </table>');
				   $("#"+dateString).dialog("open");
                   //$("#divNoAutenticado").dialog({ title:'Sesion expirada',modal:true, stack: true,autoOpen:false,buttons:{'OK':function(){$(this).dialog('close')}},close: function(){window.top.close()}});
                   //$("#divNoAutenticado").html("Su tiempo de sesion expir&oacute;<br/><strong>Cerrando ...<strong>");
				   //$("#divNoAutenticado").dialog("open");
				   
				   
                   
				   
                   //setTimeout(function(){window.top.close()},3000);
               }else{
				  setTimeout(funcion,0);
               }

            },
    'json');
}