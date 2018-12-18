
const $agregarTurnos = $('#agregar-turnos'),
			$horaInicio   = $("#hora-inicio"),
			$minInicio   = $("#min-inicio"),
			$horaFin   = $("#hora-fin"),
			$minFin   = $("#min-fin"),
			$edadMin = $("#edad-minima");
			$edadMax = $("#edad-maxima");
			$containerTurnosSeleccionados = $("#container-turnos-seleccionados"),
			$turnosSeleccionado = $("#turnos-seleccionados"),
			$modalidad = $("#modalidad-horario"),
			$etapa = $("#etapa-horario"),
			$vacantes = $("#vacantes-horario"),
			$preinscripciones = $("#preinscripciones-horario"),
			$btnCrearHorario = $("#btn-crear-horario");

const dias = {1:'Lun', 2:'Mar', 3:'Mie', 4:'Jue', 5:'Vie', 6:'Sab', 7:'Dom'}


var totalTurnosAcumulados = new Object(); 
var diasElegidos = [];
var elemento = [];
var turno_dias;

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

const elegirDias = (element)=>{

  if (element.checked) {
    diasElegidos.push(element.value);

  }else{
    diasElegidos.splice( diasElegidos.indexOf(element.value), 1);
  }
  	
	turno_dias=ordenarCheckbox(diasElegidos);
}

const convertArrayToString = (array)=>{

	let sizeArray = array.length;
	var stringToArray = '';

	if( sizeArray == 1 && sizeArray > 0 ){
		stringToArray = stringToArray+Object.values(array[0]).toString();

	}else {

		for (var i = 0; i <sizeArray ; i++) {

			if(i == (sizeArray-1) ) {
				stringToArray = stringToArray+Object.values(array[i]).toString();
			}else{
				stringToArray = stringToArray+Object.values(array[i]).toString()+',';
			}
		}
	}


	return stringToArray;
}

const convertirTimeToInt = (time) =>{
	let arrayTime = String(time).split(':');
	let number = parseInt(arrayTime[0]);
	return number;
}

const ordenarArrayTurnosTotales = (array)=>{

	let sizeArray = array.length;

	for (var i = 0; i <sizeArray ; i++) {
		
		for (var j = i; j < sizeArray; j++) {
			
			if( convertirTimeToInt(array[i]['hora-inicio']) > convertirTimeToInt(array[j]['hora-inicio']) ){

				var aux = array[j];
				array[j] = array[i];
				array[i] = aux;
			}
		}
	}

	return array;
}

const generateKeyArrayTotalTurnos = ()=>{
	let numberRandom = Math.floor(Math.random() * 10000000) + 1 ;
	return numberRandom;
}


const eliminarTurno = (keyArray)=>{

	delete  totalTurnosAcumulados[parseInt(keyArray)];

	var divContTurno = document.querySelectorAll('.div-cont-turno');

	console.log(divContTurno.length)

	for (let i =0; i < divContTurno.length; i++) {
		
		if(  keyArray == parseInt( $(divContTurno[i]).attr('key-div') )  ){
			$(divContTurno[i]).empty();
			break;
		}
	}

	cont--;
}


var cont=0;
var flag=null;

const eventAgregarTurnos = ()=>{

	flag=0;	
	var timeHoraInicio='';
	var timeHoraFin='';

	if($horaInicio.val()!='' && $minInicio.val()!='' && $horaFin.val()!='' && $minFin.val()!='' && turno_dias!=''){

		var turMinInicio = '';
		var intHoraInicio = parseInt($horaInicio.val());
		var intMinInicio = parseInt($minInicio.val());

		var turMinFin = '';
		var intHoraFin = parseInt($horaFin.val());
		var intMinFin = parseInt($minFin.val());

		if( intMinInicio >= 0 && intMinInicio <= 9  ){
			turMinInicio = '0'+intMinInicio
		}else{
			turMinInicio = $minInicio.val();
		}

		if( intMinFin >= 0 && intMinFin <= 9  ){
			turMinFin = '0'+intMinFin
		}else{
			turMinFin = $minFin.val();
		}

		var turnosAcumulados = {	
									'hora-inicio':intHoraInicio+':'+turMinInicio,
									'hora-fin':intHoraFin+':'+turMinFin,
									'turno-dias':turno_dias
								};
		console.log("Turnos Acumulados =>",turnosAcumulados);

		var keyArray = generateKeyArrayTotalTurnos();

		if(cont==0){

			totalTurnosAcumulados[keyArray]= turnosAcumulados;			
			cont++;

			$turnosSeleccionado.append("<div class=' row div-cont-turno' key-div="+keyArray+"  ><div class='col-md-6'  >*"+turno_dias+"</div>"+"<div class='col-md-5'> de "+intHoraInicio+":"+turMinInicio+" a "+intHoraFin+":"+turMinFin+"</div><div class='col-md-1'><i title='eliminar turno' style='cursor:pointer;' class='icon-trash'  onclick=eliminarTurno("+keyArray+")></i></div></div>");
			$containerTurnosSeleccionados.css('background-color','#ffffcc');
		}else{


			var iterKeyArray = Object.keys(totalTurnosAcumulados);

			for (let i = 0; i < iterKeyArray.length; i++) {

				if( totalTurnosAcumulados[iterKeyArray[i]]['hora-inicio']==turnosAcumulados['hora-inicio'] &&  totalTurnosAcumulados[iterKeyArray[i]]['hora-fin']==turnosAcumulados['hora-fin'] && totalTurnosAcumulados[iterKeyArray[i]]['turno-dias']==turnosAcumulados['turno-dias'] ){
					flag=1;
					break;	
				}
			}

			if( flag == 1){
				alertify.error("Los turno deben ser diferentes!");

			}else{

				totalTurnosAcumulados[keyArray]= turnosAcumulados;		
				$turnosSeleccionado.append("<div class=' row div-cont-turno' key-div="+keyArray+"><div class='col-md-6' >*"+turno_dias+"</div>"+"<div class='col-md-5'> de "+intHoraInicio+":"+turMinInicio+" a "+intHoraFin+":"+turMinFin+"</div><div class='col-md-1'><i title='eliminar turno' style='cursor:pointer;' class='icon-trash'  onclick=eliminarTurno("+keyArray+")></i></div></div>" );
				$containerTurnosSeleccionados.css('background-color','#ffffcc');
			
			}
		}

	}else{
		alertify.error('Hay campos que faltan llenar para poder crear un nuevo turno!',5); 
	}


			$("#hora-inicio").val("");
			$("#min-inicio").val("");
			$("#hora-fin").val("");
			$("#min-fin").val("");
			$("#Lun").prop('checked', false);
			$("#Mar").prop('checked', false); 
			$("#Mie").prop('checked', false);
			$("#Jue").prop('checked', false); 
			$("#Vie").prop('checked', false);
			$("#Sab").prop('checked', false); 
			$("#Dom").prop('checked', false);
			turno_dias='';
			diasElegidos = [];
}


var crearHorario = ()=>{
	
	$flagRegistroHorario=-1;

	var edadMinima = parseInt( $("#edad-minima").val() );
	var edadMaxima = parseInt($("#edad-maxima").val() );

	var edad_min_convencional = parseInt( sessionStorage.getItem('edad_min_convencional') );
	var edad_max_convencional = parseInt( sessionStorage.getItem('edad_max_convencional') );

	var edad_min_discapacitado = parseInt( sessionStorage.getItem('edad_min_discapacitado') );
	var edad_max_discapacitado = parseInt( sessionStorage.getItem('edad_max_discapacitado') );

	if( $("#modalidad-horario").text() != "" && $("#etapa-horario").val() != "" && $("#edad-minima").val() != "" && $("#edad-maxima").val() != "" && $("#vacantes-horario").val() != "" && $("#preinscripciones-horario").val() != "" &&  Object.values(totalTurnosAcumulados).length!=0 ){

		if( ( edadMinima < edad_min_convencional || edadMaxima > edad_max_convencional ) && $("#modalidad-horario").val() == 0 ){

			alertify.error('Verifique la Edad Minima y Maxima para la Modalidad Convencional esten en el rango establecido.',3);
		}

		else if( ( edadMinima < edad_min_discapacitado || edadMaxima  > edad_max_discapacitado ) && $("#modalidad-horario").val() == 1){

			alertify.error('Verifique la Edad Minima y Maxima para la Modalidad Personas Discapacitadas esten en el rango establecido.',3);

		}else if( parseInt($("#edad-minima").val())  > parseInt($("#edad-maxima").val()) ){
			alertify.error('Verifique que la Edad Minima debe ser menor a la Edad Máxima.',3);

		}else if( parseInt($vacantes.val()) > parseInt($preinscripciones.val()) ){
			alertify.error('"El N° de Límite de pre - inscritos debe ser igual o mayor que el N° de vacantes disponibles.',3);
		}

		else{
			
			var arrayTurnoOrdenado =  ordenarArrayTurnosTotales(Object.values(totalTurnosAcumulados));

			var datos = {

				'vacantes-horario':$vacantes.val(),
				'preinscripciones-horario':$preinscripciones.val(),
				'modalidad-horario':$modalidad.val(),
				'etapa-horario':$etapa.val(),
				'edadMinima':$edadMin.val(),
				'edadMaxima':$edadMax.val(),
				'preinscripciones':$preinscripciones.val(),
				'turnos-seleccionados':arrayTurnoOrdenado,
				'turnos-seleccionados-string':convertArrayToString(arrayTurnoOrdenado),
				'idDisciplina': $("#idDisciplinaNewHorario").val(),
				'idTemporada': $("#inputHiddenIdTemporada").val(),
				'idComplejo': $("#input-hidden-id-complejo").val()
			}

			var inputIdDisciplina = $("#idDisciplinaNewHorario").val();

			$.ajax({

				type:'POST',
				url:'/academia/web/ajax/crearHorario',
				data:datos,
				beforeSend:function(){
					
				},
				success:function(data){

					if(data == 1){
						alertify.error('El horario que usted intenta crear ya existe!',4);
					}
					else {
						alertify.success('Felicidades!,Horario Creado Correctamente!',4);
						$("#1"+inputIdDisciplina).html(data);
					}
				}
			});
		}

	}else{

		if( Object.values(totalTurnosAcumulados).length==0 ) {
			$containerTurnosSeleccionados.css('background-color','rgba(215, 42, 43, 0.6)');
		}

			alertify.error('Hay campos que faltan llenar para poder crear un nuevo horario',5);
	}
}


//Si el Modal Crear Horario se cierra
 $('#modal-crear').on('hide.bs.modal', function () { 

 	$turnosSeleccionado.empty();
 	cont=0;
	flag=null;
	totalTurnosAcumulados=[];
	clearDataCrear();

 }); 

$agregarTurnos.on('click',eventAgregarTurnos);
$btnCrearHorario.on('click',crearHorario);





