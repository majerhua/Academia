const $agregarTurnos = $('#agregar-turnos'),
			$horaInicio   = $("#hora-inicio"),
			$minInicio   = $("#min-inicio"),
			$horaFin   = $("#hora-fin"),
			$minFin   = $("#min-fin"),
			$turnosSeleccionado = $("#turnos-seleccionados");


var totalTurnosAcumulados = new Array(); 

const dias = {1:'Lun', 2:'Mar', 3:'Mie', 4:'Jue', 5:'Vie', 6:'Sab', 7:'Dom'}
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


const eventAgregarTurnos = ()=>{
	
	var turnosAcumulados = {'hora-inicio':$horaInicio.val(),
													'min-inicio':$minInicio.val(),
													'hora-fin':$horaFin.val(),
													'min-fin':$minFin.val(),
													'turno-dias':turno_dias
												};

	totalTurnosAcumulados.push(turnosAcumulados);



	$turnosSeleccionado.append("<div>"+turno_dias+"</div>");
}

$agregarTurnos.on('click',eventAgregarTurnos);