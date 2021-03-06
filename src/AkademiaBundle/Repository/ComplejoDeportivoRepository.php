<?php

namespace AkademiaBundle\Repository;

/**
 * ComplejoDeportivoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
use Doctrine\DBAL\DBALException;

class ComplejoDeportivoRepository extends \Doctrine\ORM\EntityRepository
{

	public function crearComplejoDeportivo($nombre,$ubicodigo,$tipo,$estado,$latitud,$longitud,$usuario){

		//try {

			$query = "  INSERT INTO CATASTRO.edificacionesdeportivas (	ede_nombre , 
																		ubicodigo,
																		ede_asociado,
																		ede_estado,
																		ede_coordenadax,
																		ede_coordenaday,
																		ede_usucrea,
																		ede_fechacrea )
					
						VALUES('$nombre',$ubicodigo,$tipo,$estado,$latitud,$longitud,$usuario,getDate() )";


			$stmt = $this->getEntityManager()->getConnection()->prepare($query);
			$stmt->execute();
			return "1";
		//}catch (DBALException $e) {

          //  $message = $e->getCode();
         //   return $message;
       //	}
	}

	public function editarComplejoDeportivo($codigo,$nombre,$ubicodigo,$tipo,$estado,$latitud,$longitud,$usuario){

		try {

			$query = "  UPDATE CATASTRO.edificacionesdeportivas SET 
								ede_nombre = '$nombre',
								ubicodigo = $ubicodigo,
								ede_asociado = $tipo,
								ede_estado = $estado,
								ede_coordenadax = $longitud,
								ede_coordenaday = $latitud,
								ede_usumodi = $usuario,
								ede_fechamodi = getDate()
						WHERE ede_codigo = $codigo; ";
			$stmt = $this->getEntityManager()->getConnection()->prepare($query);
			$stmt->execute();
			return "1";
		}catch (DBALException $e) {

            $message = $e->getCode();
            return $message;
       	}
	}

	public function getComplejosUsuario($usuarioId){

		$query = "  SELECT 
					ede.ede_codigo complejoId,
					ede.ede_nombre complejoNombre,
					ubi.ubidpto departamentoId,
					ubi.ubiprovincia provinciaId
					 FROM ACADEMIA.Usuario_Edificacion usuEdi
					INNER JOIN ACADEMIA.usuario usu ON usu.id = usuEdi.usuario_id
					INNER JOIN CATASTRO.edificacionesdeportivas ede ON ede.ede_codigo = usuEdi.ede_codigo
					INNER JOIN grubigeo ubi ON ubi.ubicodigo= ede.ubicodigo
					WHERE usu.id  = $usuarioId ";
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejos = $stmt->fetchAll();
		return $complejos;
	}

	public function getComplejosDisciplinaUsuario($usuarioId){

		$query = "  SELECT 
					usu.id usuarioId,
					ede.ede_codigo complejoId,
					ede.ede_nombre complejoNombre,
					ubi.ubidpto departamentoId,
					ubi.ubiprovincia provinciaId
					FROM ACADEMIA.usuario usu
					INNER JOIN  ACADEMIA.Usuario_EdificacionDisciplina usuEdi ON usuEdi.usuario_id = usu.id
					INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = usuEdi.edi_codigo
					INNER JOIN CATASTRO.edificacionesdeportivas ede ON ede.ede_codigo = edi.ede_codigo
					INNER JOIN grubigeo ubi ON ubi.ubicodigo= ede.ubicodigo
					WHERE 
					usu.id = $usuarioId ";
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejos = $stmt->fetchAll();
		return $complejos;
	}

	public function getDisciplinaComplejoUsuario($usuarioId){

		$query = "  SELECT 
					usu.id usuarioId,
					ede.ede_codigo complejoId,
					ede.ede_nombre complejoNombre,
					dis.dis_descripcion disciplinaNombre,
					dis.dis_codigo disciplinaId
					 FROM ACADEMIA.usuario usu
					INNER JOIN  ACADEMIA.Usuario_EdificacionDisciplina usuEdi ON usuEdi.usuario_id = usu.id
					INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = usuEdi.edi_codigo
					INNER JOIN CATASTRO.edificacionesdeportivas ede ON ede.ede_codigo = edi.ede_codigo
					INNER JOIN CATASTRO.disciplina dis ON dis.dis_codigo = edi.dis_codigo
					WHERE 
					usu.id = $usuarioId ";
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$disciplinas = $stmt->fetchAll();
		return $disciplinas;
	}

	public function getComplejoById($codigo){

		$query = "SELECT 
					ede.ede_codigo complejoId,
					ede.ede_nombre complejoNombre,
					ubi.ubidpto departamento,
					ubi.ubiprovincia provincia,
					ubi.ubidistrito distrito,
					ede.ede_asociado complejoTipo,
					ede.ede_estado complejoEstado,
					ede.ede_coordenadax longitud,
					ede.ede_coordenaday latitud
					FROM CATASTRO.edificacionesdeportivas ede
					INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo
					WHERE
					ubi.ubidistrito <> '00' AND 
					ubi.ubiprovincia <> '00' AND 
					ubi.ubiprovincia <> '00' AND
					ede.ede_codigo = $codigo; ";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejo = $stmt->fetchAll();
		return $complejo;
	}

	public function getComplejos(){

		$query = "SELECT 
					ede.ede_codigo complejoId,
					ede.ede_nombre complejoNombre,
					ubiDpto.ubinombre departamento,
					ubiProv.ubinombre provincia,
					ubi.ubinombre distrito,
					CASE ede.ede_asociado
					WHEN 1 THEN 'IPD'
					WHEN 2 THEN 'Asociado'
					WHEN 3 THEN 'No asociado'
					END
					AS complejoTipo,
					CASE ede_estado
					WHEN 0 THEN 'Desabilitado'
					WHEN 1 THEN 'Habilitado'
					END
					AS
					complejoEstado,
					ubi.ubidpto departamentoId,
					ubi.ubiprovincia provinciaId,
					ubi.ubidistrito distritoId
					 FROM CATASTRO.edificacionesdeportivas ede
					INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo
					INNER JOIN grubigeo AS ubiProv ON ubiProv.ubiprovincia = ubi.ubiprovincia
					INNER JOIN grubigeo AS ubiDpto ON ubiDpto.ubidpto = ubi.ubidpto
					WHERE
					ubiDpto.ubidpto <> '00' AND 
					ubiDpto.ubidistrito ='00' AND
					ubiDpto.ubiprovincia ='00' AND

					ubiProv.ubidpto = ubi.ubidpto AND
					ubiProv.ubidpto <> '00' AND
					ubiProv.ubiprovincia <> '00' AND
					ubiProv.ubidistrito = '00' AND

					ubi.ubidistrito <> '00' AND 
					ubi.ubiprovincia <> '00' AND 
					ubi.ubiprovincia <> '00'
					ORDER BY ede.ede_codigo DESC;";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejosDeportivos = $stmt->fetchAll();
		return $complejosDeportivos;
	}


	public function getComplexesPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada)
	{
		$query = "SELECT distinct edde.ede_codigo as id, edde.ede_nombre as nombre ,edde.ubicodigo ,edde.ede_direccion as direccion, edde.ede_estado as estado,edde.ede_discapacitado as discapacitado from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis, CATASTRO.edificacionesdeportivas AS edde where hor.discapacitados='$disability' and hor.estado=1 and hor.edi_codigo=eddis.edi_codigo and edde.ede_codigo=eddis.ede_codigo and hor.vacantes <> 0 and hor.convocatoria=1 and hor.etapa = 1 and '$ageBeneficiario'<=hor.edadMaxima and '$ageBeneficiario'>=hor.edadMinima and eddis.temporada_id = $idTemporada;";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejosDeportivos = $stmt->fetchAll();
		return $complejosDeportivos;
	}

	public function getComplexesPromotorByDisability($disability,$ageBeneficiario, $idTemporada)
	{
		$query = "SELECT distinct edde.ede_codigo AS id, edde.ede_nombre as nombre ,edde.ubicodigo ,edde.ede_direccion as direccion, edde.ede_estado as estado,edde.ede_discapacitado as discapacitado from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis, CATASTRO.edificacionesdeportivas AS edde where hor.discapacitados='$disability' and hor.estado=1 and hor.edi_codigo=eddis.edi_codigo and edde.ede_codigo=eddis.ede_codigo and hor.vacantes<>0 and '$ageBeneficiario'<=hor.edadMaxima and '$ageBeneficiario'>=hor.edadMinima and eddis.temporada_id = $idTemporada;";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejosDeportivos = $stmt->fetchAll();
		return $complejosDeportivos;
	}

	public function complejoDeportivoExport(){
       $query = "SELECT distinct ubiDpto.ubidpto idDepartamento ,ede.ede_codigo id, ede.ede_nombre nombre
    				FROM ACADEMIA.inscribete AS ins
    				inner join (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
				    GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
				    inner join ACADEMIA.movimientos mov on mov.id = ids.mov_id 
				    inner join ACADEMIA.participante par on par.id = ins.participante_id
				    inner join ACADEMIA.apoderado apod on apod.id = par.apoderado_id
				    inner join grpersona grApod on grApod.percodigo = apod.percodigo

					inner join ACADEMIA.horario hor on hor.id=ins.horario_id
					inner join CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
				    inner join CATASTRO.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo

				    inner join grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
					inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto

					WHERE ubiDpto.ubidistrito=0 AND ubiDpto.ubiprovincia=0 AND ubiDpto.ubidpto!=0;";
				           $stmt = $this->getEntityManager()->getConnection()->prepare($query);
           $stmt->execute();
           $complejosDeportivos = $stmt->fetchAll();
           return $complejosDeportivos;		
	}

	public function getComplexesLandingByDisability($disability,$idTemporada)
	{
		$query = "  SELECT distinct 
							edde.ede_codigo AS id, 
							edde.ede_nombre AS nombre ,
							edde.ubicodigo ,
							edde.ede_direccion AS direccion, 
							edde.ede_estado AS estado,
							edde.ede_discapacitado AS discapacitado 

							FROM ACADEMIA.horario AS hor , 
								CATASTRO.edificacionDisciplina AS eddis,
								CATASTRO.edificacionesdeportivas AS edde

							WHERE 	hor.discapacitados = '$disability' AND 
									hor.estado = 1 AND 
									hor.vacantes <> 0 AND
									hor.convocatoria=1 AND
									hor.etapa = 1 AND
									hor.edi_codigo = eddis.edi_codigo AND 

									eddis.temporada_id = '$idTemporada' AND
									
									edde.ede_codigo=eddis.ede_codigo ;";
									

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejosDeportivos = $stmt->fetchAll();
		return $complejosDeportivos;
	}

	public function complejoDeportivoExportFind($id){
       $query = "SELECT distinct ubiDpto.ubidpto idDepartamento ,ede.ede_codigo id, ede.ede_nombre nombre
    				FROM ACADEMIA.inscribete AS ins
    				inner join (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
				    GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
				    inner join ACADEMIA.movimientos mov on mov.id = ids.mov_id 
				    inner join ACADEMIA.participante par on par.id = ins.participante_id
				    inner join ACADEMIA.apoderado apod on apod.id = par.apoderado_id
				    inner join grpersona grApod on grApod.percodigo = apod.percodigo

					inner join ACADEMIA.horario hor on hor.id=ins.horario_id
					inner join CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
				    inner join CATASTRO.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo

				    inner join grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
					inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
				    
					WHERE ubiDpto.ubidistrito=0 AND ubiDpto.ubiprovincia=0 AND ubiDpto.ubidpto!=0 AND ede.ede_codigo='$id' ";
           $stmt = $this->getEntityManager()->getConnection()->prepare($query);
           $stmt->execute();
           $complejosDeportivos = $stmt->fetchAll();
           return $complejosDeportivos;		
	}



	public function complejoDeportivoExportFind2($id){
       $query = " SELECT distinct ubiDpto.ubidpto idDepartamento, ede.ede_codigo id , ede.ede_nombre nombre
        FROM 
            grubigeo ubiDpto 

        inner join CATASTRO.edificacionesdeportivas ede on ede.ubicodigo = ubiDpto.ubicodigo
        WHERE  ede.ede_codigo='$id';";

           $stmt = $this->getEntityManager()->getConnection()->prepare($query);
           $stmt->execute();
           $complejosDeportivos = $stmt->fetchAll();
           return $complejosDeportivos;		
	}

	public function getComplejosDeportivos()
	{

       $query = "SELECT ede_codigo as id, ede_nombre as nombre ,ubicodigo , ede_direccion as direccion, ede_estado as estado, ede_discapacitado as discapacitado from CATASTRO.edificacionesdeportivas;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $complejosDeportivos = $stmt->fetchAll();

        return $complejosDeportivos;        		
	}

	public function getComplejosDeportivosDiscapacitados()
	{
		$query = "SELECT ede_codigo as id, ede_nombre as nombre ,ubicodigo, ede_direccion as direccion, ede_estado as estado,ede_discapacitado as discapacidad from CATASTRO.edificacionesdeportivas where ede_discapacitado='1'";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejosDeportivos = $stmt->fetchAll();

		return $complejosDeportivos;
	}

		public function complejosDeportivosFlagAllLanding($flagDis)
	{
		$query = "SELECT distinct edde.ede_codigo as id, edde.ede_nombre as nombre ,edde.ubicodigo ,edde.ede_direccion as direccion, edde.ede_estado as estado,edde.ede_discapacitado as discapacitado from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis, CATASTRO.edificacionesdeportivas AS edde where hor.discapacitados='$flagDis' and hor.estado=1 and hor.edi_codigo=eddis.edi_codigo and edde.ede_codigo=eddis.ede_codigo and hor.vacantes <> 0 and hor.convocatoria=1;";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejosDeportivos = $stmt->fetchAll();
		return $complejosDeportivos;
	}



	public function getEditarDiscapacitado($idComplejo, $usuario){
	
		$query = "UPDATE catastro.edificacionesdeportivas set ede_discapacitado = 1, ede_usumodi = $usuario, ede_fechamodi = getDate() where ede_codigo= $idComplejo";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
	
	}


	public function nombreComplejo($idComplejo){
		$query = "SELECT b.ede_nombre as nombreComplejo from academia.usuario a inner join catastro.edificacionesdeportivas b on a.id_complejo = b.ede_codigo where a.id_complejo = $idComplejo";
		
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$nombre = $stmt->fetchAll();

		return $nombre;
	}


	public function getComplejosByTalentos($idTemporada){
		
		$query = "SELECT DISTINCT
					ubiDep.ubidpto departamentoId,
				   	ubiDep.ubinombre departamentoNombre,
				   	ede.ede_codigo complejoId,
				   	ede.ede_nombre complejoNombre		 
					FROM ACADEMIA.inscribete ins
					inner join (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
					FROM ACADEMIA.movimientos m
					GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
					inner join academia.movimientos mov on mov.id = ids.mov_id
					INNER JOIN ACADEMIA.horario hor On ins.horario_id = hor.id
					INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = hor.edi_codigo
					INNER JOIN CATASTRO.edificacionesdeportivas ede ON ede.ede_codigo = edi.ede_codigo
					INNER JOIN grubigeo ubi ON ubi.ubicodigo = ede.ubicodigo
					INNER JOIN grubigeo ubiDep ON ubiDep.ubidpto = ubi.ubidpto
					WHERE
					ubi.ubidpto <> '00' AND
					ubi.ubiprovincia <> '00' AND
					ubi.ubidistrito <> '00'  AND
					ubiDep.ubidpto <> '00' AND
					ubiDep.ubiprovincia = '00' AND
					ubiDep.ubidistrito = '00' AND
					ins.estado = 2 AND
					edi.temporada_id = '$idTemporada' AND
					mov.categoria_id = 4 AND 
					mov.asistencia_id != 3;";
		
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
		$stmt->execute();
		$complejos = $stmt->fetchAll();
		return $complejos;
	}

}
