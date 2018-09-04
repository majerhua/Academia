	$codigoDisciplina = $("#codigo-disciplina");
	$nombreDisciplina = $("#nombre-disciplina");
	$convencionalEdadMinima = $("#convencional-edad-minima");
	$convencionalEdadMaxima = $("#convencional-edad-maxima");
	$discapacitadoEdadMinima = $("#discapacitado-edad-minima");
	$discapacitadoEdadMaxima = $("#discapacitado-edad-maxima");
	$btnActualizarDisciplina = $("#btn-actualizarDisciplina");

	var getDisciplinaConfiguracionById = (idDisciplina)=> {

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

				for (let i = 0; i < disciplinaSize; i++) {

					$codigoDisciplina.text(disciplina[0].codigo);
					$nombreDisciplina.text(disciplina[0].disciplina);
					$convencionalEdadMinima.val(disciplina[0].edad_min_convencional);
					$convencionalEdadMaxima.val(disciplina[0].edad_max_convencional);
					$discapacitadoEdadMinima.val(disciplina[0].edad_min_discapacitado);
					$discapacitadoEdadMaxima.val(disciplina[0].edad_max_discapacitado);
				}

			},
			error:function(error){
				alertify.error('<p style="color:white">Se produjo un error.</p>');
			}

		});
	}

	var actualizarDisciplina = ()=>{

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
				console.log(data);
			},
			error:function(error){
				alertify.error('<p style="color:white">Se produjo un error.</p>');
			}

		});
	}

	$btnActualizarDisciplina.on('click',actualizarDisciplina);