	$codigoDisciplina = $("#codigo-disciplina");
	$nombreDisciplina = $("#nombre-disciplina");
	$convencionalEdadMinima = $("#convencional-edad-minima");
	$convencionalEdadMaxima = $("#convencional-edad-maxima");
	$discapacitadoEdadMinima = $("#discapacitado-edad-minima");
	$discapacitadoEdadMaxima = $("#discapacitado-edad-maxima");

	$btnActualizarDisciplina = $("#btn-actualizarDisciplina");
	$btnOcultarDisciplina = $("#btn-ocultar-disciplina");

	$containerTableDisciplina = $("#container-table-disciplina");

	const getDisciplinaConfiguracionById = (idDisciplina)=> {

		

		var datos = {
					'idDisciplina': idDisciplina
					};

		$.ajax({

			url: 'get/disciplina',
			data: datos,
			beforeSend:function(){
				console.log(datos);
			},
			success:function(data){

				var disciplina = JSON.parse(data);
				var disciplinaSize = disciplina.length;

				console.log(disciplina);

				for (let i = 0; i < disciplinaSize; i++) {

					$codigoDisciplina.text(disciplina[0].codigo);
					$nombreDisciplina.text(disciplina[0].disciplina);
					$convencionalEdadMinima.val(disciplina[0].edad_min_convencional);
					$convencionalEdadMaxima.val(disciplina[0].edad_max_convencional);
					$discapacitadoEdadMinima.val(disciplina[0].edad_min_discapacitado);
					$discapacitadoEdadMaxima.val(disciplina[0].edad_max_discapacitado);
				}

				/*change_select_estado_modal_editar();*/
			},
			error:function(error){
				alertify.error('<p style="color:white">Se produjo un error.</p>');
			}

		});
	}

	const actualizarDisciplina = ()=>{

		var datos = {
						'idDisciplina': $codigoDisciplina.text(),
						'convencional-edad-minima':$convencionalEdadMinima.val(),
						'convencional-edad-maxima': $convencionalEdadMaxima.val(),
						'discapacitado-edad-minima': $discapacitadoEdadMinima.val(),
						'discapacitado-edad-maxima': $discapacitadoEdadMaxima.val()
			};

			$.ajax({

				url: 'update/disciplina',
				data: datos,
				beforeSend:function(){
					console.log(datos);
				},
				success:function(data){
					$containerTableDisciplina.html(data);
				},
				error:function(error){
					alertify.error('<p style="color:white">Se produjo un error.</p>');
				}

			});

/*
		if( $estadoDisciplina.val() == 0 ){
			$('#confirmarOcultarDisciplina').modal('show');

		}else if( $estadoDisciplina.val() == 1 ) {

			
		}*/

	}

/*
	const ocultarDisciplina = ()=>{

		var datos = {
					'idDisciplina': $codigoDisciplina.text(),
					'convencional-edad-minima':$convencionalEdadMinima.val(),
					'convencional-edad-maxima': $convencionalEdadMaxima.val(),
					'discapacitado-edad-minima': $discapacitadoEdadMinima.val(),
					'discapacitado-edad-maxima': $discapacitadoEdadMaxima.val(),
					'estado-disciplina': $estadoDisciplina.val()
		};
		
		$.ajax({

				url: 'update/disciplina',
				data: datos,
				beforeSend:function(){
					console.log(datos);
				},
				success:function(data){
					$containerTableDisciplina.html(data);
				},
				error:function(error){
					alertify.error('<p style="color:white">Se produjo un error.</p>');
				}

			});
	}

	const change_select_estado_modal_editar = ()=>{
		$estadoDisciplina.removeAttr('class');
		if($estadoDisciplina.val() == 1){
			$estadoDisciplina.attr('class','estado-visible form-control');
		}else{
			$estadoDisciplina.attr('class','estado-oculto form-control');
		}
	}
*/
	/*change_select_estado_modal_editar();*/

	$btnActualizarDisciplina.on('click',actualizarDisciplina);
	/*$estadoDisciplina.on('change',change_select_estado_modal_editar);*/
	/*$btnOcultarDisciplina.on('click',ocultarDisciplina);*/