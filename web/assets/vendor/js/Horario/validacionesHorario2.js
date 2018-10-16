
/*VALIDAR NEGATIVOS*/
jQuery('#hora-inicio, #hora-fin, #min-inicio, #min-fin, #edad-minima, #edad-maxima, #vacantes-horario, #editar_vacantes_horario, #preinscripciones-horario, #editar_preinscripciones_horario').on('keypress', function (e) {

    if (e.keyCode == 101 || e.keyCode == 45 || e.keyCode == 46 || e.keyCode == 43 || e.keyCode == 44 || e.keyCode == 47 || e.keyCode == 69 ) {
        return false;
    }
    soloNumeros(e.keyCode);
});

function soloNumeros(e) {
    var key = window.Event ? e.which : e.keyCode
        return (key >= 48 && key <= 57)
}

/*EDADES MÁXIMAS*/
var edad = document.getElementById('textEdad');

function validar(val){
  switch (val){
   case "0":
   	edad.innerHTML = 'De 6 a 17 años.' ;
   break;
   case "1":
   edad.innerHTML = 'De 6 a 59 años.';
   break;
   default : edad.innerHTML = '';
  }
}

var edadGimnasia = document.getElementById('textEdad');

function validarGimnasia(val) {
  switch (val){
   case "0":
   	edadGimnasia.innerHTML = 'De 4 a 17 años.' ;
   break;
   case "1":
   edadGimnasia.innerHTML = 'De 4 a 59 años.';
   break;
   default : edadGimnasia.innerHTML = '';
  }	
}

		
/* CARGAR ALERTAS */
$(document).ready(function(){
	
	$('#btn-close-alert-info').click(function(){
		hideAlertMessage('#alert-info');
	});	

	$('#btn-close-alert-editar-info').click(function(){
		hideAlertMessage('#alert-editar-info');
	});	

	$('#btn-close-alert-crear-info').click(function(){
		hideAlertMessage('#alert-crear-info');
	});	
});

/*FILTROS */
$(document).ready(function () {

    (function ($) {

    	var jsFiltrar = document.querySelectorAll('.filtrar');
    	var jsBuscar = document.querySelectorAll('.buscar');

    	for (var i = 0; i < jsFiltrar.length; i++) {
    		$(jsFiltrar[i]).keyup(function () {
    			var idfil = $(this).attr('ident-buscar');
                var rex = new RegExp($(this).val(),'i');
                $(jsBuscar[idfil]).children('tr').hide();
                $(jsBuscar[idfil]).children('tr').filter(function () {
                    return rex.test($(this).text());
                }).show();
        	})
    	}  

    }(jQuery));

});


		/*LIMPIAR CAJAS */

		function clearData(){
			$("#editar_codigo_horario").empty();
			$("#editar_disciplina_horario").empty();
			$("#editar_modalidad_horario").empty();
			$("#editar_edades_horario").empty();

			$("#editar_discapacidad_horario").empty();
			$("#editar_frecuencia_horario").empty();
			$("#editar_dias_horario").empty();

			$("#editar_etapa_horario").val("");
			$("#editar_vacantes_horario").val("");
			$("#editar_preinscripciones_horario").val("");
			$("#editar_convocatoria_horario").val("");
			$("#turno").empty();
			$("#textEdad").empty();
		}

		function clearDataCrear(){

			$("#textEdad").empty();
			$("#turno").empty();
			$("#hora-inicio").val("");
			$("#min-inicio").val("");
			$("#hora-fin").val("");
			$("#min-fin").val("");
			$("#edad-minima").val("");
			$("#edad-maxima").val("");
			$("#vacantes-horario").val("");
			$("#modalidad-horario").val("");
			$("#etapa-horario").val("");
			$("#preinscripciones-horario").val("");
			$("#crear_discapacidad").val("");
			$("#Lun").prop('checked', false);
			$("#Mar").prop('checked', false); 
			$("#Mie").prop('checked', false);
			$("#Jue").prop('checked', false); 
			$("#Vie").prop('checked', false);
			$("#Sab").prop('checked', false); 
			$("#Dom").prop('checked', false);
			
		}