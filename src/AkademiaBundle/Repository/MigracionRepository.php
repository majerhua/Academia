<?php

namespace AkademiaBundle\Repository;

/**
 * MigracionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MigracionRepository extends \Doctrine\ORM\EntityRepository
{
	public function getHorariosDisponibleMigracion($ediCodigo,$modalidad,$etapa,$edad,$horarioActual){


		if($etapa == 1){
			$query = "	SELECT DISTINCT
						hor.id id,
						CONVERT(VARCHAR(10),hor.edadMinima)+' - '+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad
						FROM ACADEMIA.horario hor
						INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = hor.edi_codigo
						WHERE
						 $edad >= hor.edadMinima AND $edad <= hor.edadMaxima 
						AND etapa = 1 AND edi.edi_codigo = $ediCodigo AND hor.estado = 1
						AND hor.discapacitados = $modalidad AND hor.id != $horarioActual
						";

		}else if($etapa == 0){
			$query = "	SELECT DISTINCT 
						hor.id id,
						CONVERT(VARCHAR(10),hor.edadMinima)+' - '+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad
						FROM ACADEMIA.horario hor
						INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = hor.edi_codigo
						WHERE
						$edad >= hor.edadMinima AND $edad <= hor.edadMaxima 
						AND etapa = 0 AND edi.edi_codigo = $ediCodigo AND hor.estado = 1 
						AND hor.discapacitados = $modalidad  AND hor.id != $horarioActual";
		}


	    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
	    $stmt->execute();
	    $horariosMigracion = $stmt->fetchAll();
	    
	    return $horariosMigracion;
	}

	public function getAsistenciaMensualInscribete($idHorario){

		$query = "	WITH asistencia_meses AS (
						SELECT TOP 10000000000 MAX(fecha_modificacion) maxFechaMovimiento ,inscribete_id
						FROM ACADEMIA.movimientos asis_movi
						INNER JOIN ACADEMIA.inscribete asis_ins ON asis_ins.id = asis_movi.inscribete_id
						INNER JOIN ACADEMIA.horario asis_hor ON asis_hor.id = asis_ins.horario_id
						WHERE asis_hor.id= $idHorario
						GROUP BY inscribete_id,MONTH(fecha_modificacion) 
						ORDER BY inscribete_id ASC
						)
						SELECT 
						MONTH(am.maxFechaMovimiento) mes,
						am.inscribete_id,

					 	(SELECT asistencia_id FROM ACADEMIA.movimientos mov 
					 	WHERE mov.fecha_modificacion = am.maxFechaMovimiento 
					 	AND mov.inscribete_id= am.inscribete_id) asistencia_id,

					 	(SELECT mov2.id  FROM ACADEMIA.movimientos mov2 
					 	WHERE mov2.fecha_modificacion = am.maxFechaMovimiento 
					 	AND mov2.inscribete_id= am.inscribete_id) idMovimiento

						FROM asistencia_meses  am";
	    
	    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
	    $stmt->execute();
	    $asistenciaMensualInscribete = $stmt->fetchAll();
	    
	    return $asistenciaMensualInscribete;
	}
}
