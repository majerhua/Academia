jQuery('#dniApoderado, #dni-hijo').on('keypress', function (e) {

	    if (e.keyCode == 101 || e.keyCode == 45 || e.keyCode == 46 || e.keyCode == 43 || e.keyCode == 44 || e.keyCode == 47 || e.keyCode == 69 ) {

	        return false;
	    }
	    soloNumeros(e.keyCode);
	});
	
	
	// validar filtro de caracteres especiales

	jQuery('#correo').on('keypress', function(e){

		if(e.keyCode >= 33  && e.keyCode <= 45 || e.keyCode >=58 && e.keyCode <= 63  || e.keyCode >= 123 && e.keyCode <= 255 ){
			//showAlertMessage('#alerta-digitar', "No se permiten caracteres especiales");
			return false;
		}

		noCaracteresEspeciales(e.keyCode);
	});

	jQuery('#apellidoPaterno,#apellidoMaterno,#nombre,#apellidoPaterno-hijo,#apellidoMaterno-hijo,#nombre-hijo,#direccion,#correo').on('keydown',function(e){

		if(e.keyCode==86 && e.ctrlKey){
			return false;
		}

	});


	function modificarTexbox(){
	
	    var tipoDoc = $("#tipo_documento").val();

	    switch(tipoDoc) {
	        case "30":         
	            document.getElementById("dniApoderado").setAttribute("maxlength", "8");
	            $("#dniApoderado" ).show();
	            $("#dniApoderado").attr("placeholder", "Ingresar DNI ...");
	            $("#dniApoderado").focus();
	        break;

	        case "31":
	            document.getElementById("dniApoderado").setAttribute("maxlength", "15");
	            $("#dniApoderado" ).show();
	            $("#dniApoderado").attr("placeholder", "Ingresar Carnet extranjería ...");
	            $("#dniApoderado").focus();
	        break;

        	default:
        		$("#dniApoderado").hide();
        		alertify.error('<p style="color:white">Seleccione el tipo de documento</p>'); 
	    }
	}

	function noCaracteresEspeciales(e){
		var key = window.Event ? e.which : e.keyCode
	        return (key >= 64 && key <= 90)
	}


	$(document).ready(function () {
		disablePaste('#dniApoderado');
		disablePaste('#apellidoPaterno');
		disablePaste('#apellidoMaterno'); 
		disablePaste('#nombre');
		disablePaste('#dni-hijo');
		disablePaste('#apellidoPaterno-hijo');
		disablePaste('#apellidoMaterno-hijo'); 
		disablePaste('#nombre-hijo'); 
		disablePaste('#direccion'); 
		disablePaste('#telefono'); 
		disablePaste('#correo'); 
	});

	function disablePaste(input){
		$(input).bind('copy paste', function (e) {
	       e.preventDefault();
	    });
	}


	function filtro(string, tipo){

		var filtro = '';

		switch(tipo){
			case 'letras': 
			filtro = 'abcdefghijklmñnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÑ';
			break;
			case 'letrasNumeros': 
			filtro = 'abcdefghijklmnopqrstuvwxñyzABCDEFGHIÑJKLMNOPQRSTUVWXYZ 0123456789';
			break;
			case 'numeros': 
			filtro = '0123456789';
			break;
		}
	    
	    var out = '';
		
	    for (var i=0; i<string.length; i++)
	       if (filtro.indexOf(string.charAt(i)) != -1) 
	         out += string.charAt(i);
		
	    return out.toUpperCase();;
	}


	function modificarTexboxHijo(){
	   
	   	var tipoDoc = $("#tipo_documentoHijo").val();

	    switch(tipoDoc) {
	        case "30":         
	            document.getElementById("dni-hijo").setAttribute("maxlength", "8");
	            $("#dni-hijo").attr("placeholder", "Ingresar DNI ...");
	            $("#dni-hijo").show();
	            $("#dni-hijo").focus();

	        break;
	        case "31":
	            document.getElementById("dni-hijo").setAttribute("maxlength", "15");
	            $("#dni-hijo").attr("placeholder", "Ingresar Carnet extranjería ...");
	            $("#dni-hijo").show();
	            $("#dni-hijo").focus();
	        break;
	        default:
	        	$("#dni-hijo").hide();
	        	alertify.error('<p style="color:white">Seleccione el tipo de documento</p>'); 
    
	    }
	}	

	function isValidDate(day,month,year){
					
		var dteDate;
		month=month-1;
		dteDate=new Date(year,month,day);
					 		    
		return ((day==dteDate.getDate()) && (month==dteDate.getMonth()) && (year==dteDate.getFullYear()));
	}
			 
	function validate_fecha(fecha){
		
		var patron=new RegExp("^(19|20)+([0-9]{2})([-])([0-9]{1,2})([-])([0-9]{1,2})$");
			 
		if(fecha.search(patron)==0){
			    
			var values=fecha.split("-");
			if(isValidDate(values[2],values[1],values[0])){
			        
			    return true;
			}
		}
		return false;
	}
	
	function calcularEdad(fecha){
	
		if(validate_fecha(fecha)==true){
		       
			var values=fecha.split("-");
			var dia = values[2];
			var mes = values[1];
			var ano = values[0];
		 
	        // cogemos los valores actuales
	        var fecha_hoy = new Date();
	        var ahora_ano = fecha_hoy.getYear();
	        var ahora_mes = fecha_hoy.getMonth()+1;
	        var ahora_dia = fecha_hoy.getDate();
		 
	        // realizamos el calculo
	        var edad = (ahora_ano + 1900) - ano;
	        if ( ahora_mes < mes ){
	            edad--;
	        }

	        if ((mes == ahora_mes) && (ahora_dia < dia)){
	            edad--;
	        }

	        if (edad > 1900){
	            edad -= 1900;
	        }
		        // calculamos los meses
		        var meses=0;
		        if(ahora_mes>mes)
		            meses=ahora_mes-mes;
		        if(ahora_mes<mes)
		            meses=12-(mes-ahora_mes);
		        if(ahora_mes==mes && dia>ahora_dia)
		            meses=11;
		 
		        // calculamos los dias
		        var dias=0;
		        if(ahora_dia>dia)
		            dias=ahora_dia-dia;
		        if(ahora_dia<dia){
		            ultimoDiaMes=new Date(ahora_ano, ahora_mes, 0);
		            dias=ultimoDiaMes.getDate()-(dia-ahora_dia);
		        }
		    }
		    return edad;
		}	

function soloNumeros(e) {
    var key = window.Event ? e.which : e.keyCode
        return (key >= 48 && key <= 57)
}

function mayus(e) {
    e.value = e.value.toUpperCase();
}


/*Muestra mensaje de alerta*/
function showAlertMessage(alert, mensaje){
	$(alert).text(mensaje).show('fade');
	setTimeout(function(){
		hideAlertMessage(alert);	
	}, 2000);
}

/*Oculta mensaje de alerta*/
function hideAlertMessage(alert){
	$(alert).hide('fade');		
}