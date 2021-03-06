<?php

namespace AkademiaBundle\Repository;

/**
 * ComplejoDisciplinaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */


use Doctrine\DBAL\DBALException;

class ComplejoDisciplinaRepository extends \Doctrine\ORM\EntityRepository
{

	public function crearComplejoDisciplina($idComplejo,$idDisciplina,$usuario,$idTemporada){

		
        try {
              $query="EXEC crearComplejoDisciplina $idComplejo,$idDisciplina,$usuario,$idTemporada";
              $stmt = $this->getEntityManager()->getConnection()->prepare($query);
              $stmt->execute();
               $message = 1;

            }catch (DBALException $e) {
              $message = $e->getCode();
            }
       	
       	return $message;
	}


	public function getComplejoDisciplinas(){

       $query = "SELECT  edi_codigo as id, edidis.ede_codigo as idComplejoDeportivo, rtrim(dis.dis_descripcion) as nombreDisciplina, dis.dis_codigo as idDisciplina ,ediComplejo.ede_discapacitado as discapacidad
			from CATASTRO.edificacionDisciplina as edidis inner join CATASTRO.disciplina as dis on edidis.dis_codigo = dis.dis_codigo inner join CATASTRO.edificacionesdeportivas as ediComplejo on edidis.ede_codigo=ediComplejo.ede_codigo";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $complejosDeportivos = $stmt->fetchAll();

        return $complejosDeportivos;

	}

	public function getComplejosDisciplinasDiscaspacitados(){
		$query = "SELECT  edi_codigo as id, edidis.ede_codigo as idComplejoDeportivo, rtrim(dis.dis_descripcion) as nombreDisciplina, dis.dis_codigo as idDisciplina ,ediComplejo.ede_discapacitado as discapacidad
			from CATASTRO.edificacionDisciplina as edidis inner join CATASTRO.disciplina as dis on edidis.dis_codigo = dis.dis_codigo inner join CATASTRO.edificacionesdeportivas as ediComplejo 
			on edidis.ede_codigo=ediComplejo.ede_codigo where ediComplejo.ede_discapacitado = 1";
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $complejosDeportivos = $stmt->fetchAll();

        return $complejosDeportivos;
	}

	public function getComplejosDisciplinasHorarios($idComplejo , $idTemporada){

		$query = "	SELECT	edi.edi_codigo as idDistrito,
					ede.ede_nombre as nombreComplejo,
					edi.ede_codigo as idComplejoDeportivo, 
					rtrim(dis.dis_descripcion) as nombreDisciplina,
					dis.dis_codigo as idDisciplina ,
					ede.ede_discapacitado as discapacidad 
					FROM CATASTRO.edificacionDisciplina as edi 
					INNER JOIN CATASTRO.disciplina as dis on edi.dis_codigo = dis.dis_codigo 
					INNER JOIN CATASTRO.edificacionesdeportivas as ede on edi.ede_codigo=ede.ede_codigo 
					WHERE 
					edi.ede_codigo = $idComplejo AND 
					edi.edi_estado = 1 AND 
					edi.temporada_id = $idTemporada 
					ORDER BY dis.dis_descripcion ASC";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $complejosDeportivos = $stmt->fetchAll();

        return $complejosDeportivos;

	}

	public function getComplejosDisciplinasHorariosByDisciplinaId($idComplejo , $idTemporada, $idDisciplina){

		$query = "	SELECT	edi.edi_codigo as idDistrito,
					ede.ede_nombre as nombreComplejo,
					edi.ede_codigo as idComplejoDeportivo, 
					rtrim(dis.dis_descripcion) as nombreDisciplina,
					dis.dis_codigo as idDisciplina ,
					ede.ede_discapacitado as discapacidad 
					FROM CATASTRO.edificacionDisciplina as edi 
					INNER JOIN CATASTRO.disciplina as dis on edi.dis_codigo = dis.dis_codigo 
					INNER JOIN CATASTRO.edificacionesdeportivas as ede on edi.ede_codigo=ede.ede_codigo 
					WHERE 
					edi.ede_codigo = $idComplejo AND 
					edi.edi_estado = 1 AND 
					edi.temporada_id = $idTemporada AND
					edi.dis_codigo = $idDisciplina
					ORDER BY dis.dis_descripcion ASC";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $complejosDeportivos = $stmt->fetchAll();

        return $complejosDeportivos;

	}

	public function vertificarEdificacionDisciplina($idComplejo, $idDisciplina , $idTemporada){
		
		$query = "SELECT edi_estado AS estado FROM catastro.edificacionDisciplina WHERE ede_codigo = $idComplejo AND dis_codigo = $idDisciplina AND temporada_id = $idTemporada ";
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $estado = $stmt->fetchAll();

        return $estado;

	}

	public function habilitarEdificacionDisciplina($idComplejo, $idDisciplina){
		
  		try {
  			$query = "UPDATE catastro.edificacionDisciplina set edi_estado = 1 where ede_codigo = $idComplejo and dis_codigo = $idDisciplina";
			$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        	$stmt->execute();
        	$message ="1";
 		}catch (DBALException $e) {
            $message = $e->getCode();
        }
       	
       	return $message;
	}

	public function getEdiCodDisciplina($idComplejo, $idDisciplina){
		$query = "SELECT edi_codigo from catastro.edificacionDisciplina where ede_codigo = $idComplejo and dis_codigo = $idDisciplina";
		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $codigo = $stmt->fetchAll();

        return $codigo;

	}


}
