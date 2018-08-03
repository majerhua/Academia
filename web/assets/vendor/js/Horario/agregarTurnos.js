const $agregarTurnos = $('#agregar-turnos'),
			$horaInicio   = $("#hora-inicio"),
			$minInicio   = $("#min-inicio"),
			$horaFin   = $("#hora-fin"),
			$minFin   = $("#min-fin"),
			$turnosSeleccionado = $("#turnos-seleccionados"),
			$modalidad = $("#modalidad-horario"),
			$tipo = $("#tipo-horario"),
			$vacantes = $("#vacantes-horario"),
			$preinscripciones = $("#preinscripciones-horario"),
			$btnCrearHorario = $("#btn-crear-horario");

const dias = {1:'Lun', 2:'Mar', 3:'Mie', 4:'Jue', 5:'Vie', 6:'Sab', 7:'Dom'}


var totalTurnosAcumulados = new Array(); 
var diasElegidos = [];
var elemento = [];
var turno_dias;

const elegirDias = (element)=>{

  if (element.checked) {
    diasElegidos.push(element.value);

  }else{
    diasElegidos.splice( diasElegidos.indexOf(element.value), 1);
  }
		    
	turno_dias=ordenarCheckbox(diasElegidos);
}

const ordenarCheckbox = (arreglo)=>{

	var cadenaHorario = '';
	var tamArreglo = arreglo.length;

	arreglo = arreglo.sort();


 	for(var i = 0; i < tamArreglo; i++){	

 		if( i!= (tamArreglo-1) )
 			cadenaHorario+=dias[arreglo[i]]+',';
 		else 
 			cadenaHorario+=dias[arreglo[i]];
 	}
 	return cadenaHorario;
}

var cont=0;
var flag=null;
const eventAgregarTurnos = ()=>{
			flag=0;	
	var turnosAcumulados = {'hora-inicio':$horaInicio.val(),
													'min-inicio':$minInicio.val(),
													'hora-fin':$horaFin.val(),
													'min-fin':$minFin.val(),
													'turno-dias':turno_dias
												};
		if(cont==0){
			totalTurnosAcumulados.push(turnosAcumulados);			
			cont++;
			$turnosSeleccionado.append("<div class='col-md-6' >"+turno_dias+"</div>"+"<div class='col-md-6'> de "+$horaInicio.val()+":"+$minInicio.val()+" a "+$horaFin.val()+":"+$minFin.val()+"</div>" );
		}else{

			for (let i = 0; i < totalTurnosAcumulados.length; i++) {

				if( totalTurnosAcumulados[i]['hora-inicio']==turnosAcumulados['hora-inicio'] && totalTurnosAcumulados[i]['min-inicio']==turnosAcumulados['min-inicio'] && totalTurnosAcumulados[i]['hora-fin']==turnosAcumulados['hora-fin'] && totalTurnosAcumulados[i]['turno-dias']==turnosAcumulados['turno-dias'] ){
					flag=1;
					break;	
				}
			}

			if( flag==1){
				console.log("Los turno deben ser diferentes!");
			}else{
				totalTurnosAcumulados.push(turnosAcumulados);		
				$turnosSeleccionado.append("<div class='col-md-6' >"+turno_dias+"</div>"+"<div class='col-md-6'> de "+$horaInicio.val()+":"+$minInicio.val()+" a "+$horaFin.val()+":"+$minFin.val()+"</div>" );				
			}

		}
}

var crearHorario = ()=>{

	var datos = {
		'vacantes-horario':$vacantes.val(),
		'preinscripciones-horario':$preinscripciones.val(),
		'modalidad-horario':$modalidad.val(),
		'tipo-horario':$tipo.val(),
		'vacantes': $vacantes.val(),
		'preinscripciones':$preinscripciones.val(),
		'turnos-seleccionados':totalTurnosAcumulados
	}

	$.ajax({

		type:'POST',
		url:'ajax/crearHorario',
		data:datos,
		beforeSend:function(){
			console.log("Datos a enviar =>",datos);
		},
		success:function(data){
			console.log("Datos => ",data);
		}

	});

}

$agregarTurnos.on('click',eventAgregarTurnos);
$btnCrearHorario.on('click',crearHorario);





