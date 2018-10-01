<?php

namespace AkademiaBundle\Repository;

/**
 * TemporadaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
use Doctrine\DBAL\DBALException;
class TemporadaRepository extends \Doctrine\ORM\EntityRepository
{


	public function temporadaProxima(){
		try {
				$query = "	SELECT  TOP 1 CONVERT(VARCHAR,pre_inscripciones,103) fecha_preinscripcion,
										anio,
										cic.descripcion ciclo
							FROM ACADEMIA.temporada temp
							INNER JOIN ACADEMIA.ciclo cic ON cic.id = temp.ciclo_id
							WHERE GETDATE() < pre_inscripciones
							ORDER BY pre_inscripciones ASC
						";

			    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
			    $stmt->execute();
			    $temporadaProxima = $stmt->fetchAll();
           		return $temporadaProxima;

        }catch (DBALException $e) {
          $message = $e->getCode();
        }

        return $message;
	}

	public function faseTemporadaActiva($id){

        try {
				$query = "	SELECT 
								CASE 
							    WHEN  GETDATE() >= apertura  AND GETDATE() < pre_inscripciones THEN 10
							    WHEN  GETDATE()  >= pre_inscripciones  AND GETDATE() < inicio_clases THEN 20
							    WHEN  GETDATE()  >= inicio_clases AND GETDATE() < cierre_clases THEN 30
							    ELSE
							    40
								END AS fase 
								FROM ACADEMIA.temporada WHERE id = '$id' AND estado=1;";

			    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
			    $stmt->execute();
			    $faseTemporadaActiva = $stmt->fetchAll();
           		return $faseTemporadaActiva;

        }catch (DBALException $e) {
          $message = $e->getCode();
        }

        return $message;
	}

	public function getFechaPreInscripcion($idTemproada){

		try {
				$query = "	SELECT 
							CONVERT(VARCHAR,pre_inscripciones,103) fecha_preinscripcion,
							anio,
							cic.descripcion ciclo
							FROM ACADEMIA.temporada temp
							INNER JOIN ACADEMIA.ciclo cic ON cic.id = temp.ciclo_id
							WHERE temp.id = '$idTemproada' AND temp.estado = 1";
			    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
			    $stmt->execute();
			    $fechaPreInscripcion = $stmt->fetchAll();
           		return $fechaPreInscripcion;

        }catch (DBALException $e) {
          $message = $e->getCode();
        }

        return $message;
	}

	public function getTemporadaActiva(){

        try {
				$query = "	SELECT temp.id temporadaId
							FROM ACADEMIA.temporada temp WHERE estado = 1;";

			    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
			    $stmt->execute();
			    $temporadaActiva = $stmt->fetchAll();
           		return $temporadaActiva;

        }catch (DBALException $e) {
          $message = $e->getCode();
        }

        return $message;
	}

	public function getTemporadasHabilitadas(){

        try {
				$query = "	SELECT temp.id temporadaId, cic.descripcion ciclo, temp.anio anio
							FROM ACADEMIA.temporada temp
							INNER JOIN ACADEMIA.ciclo cic ON cic.id = temp.ciclo_id
							WHERE
							GETDATE() BETWEEN apertura AND fecha_subsanacion ORDER BY temp.id DESC";

			    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
			    $stmt->execute();
			    $temporadas = $stmt->fetchAll();
           		return $temporadas;

        }catch (DBALException $e) {
          $message = $e->getCode();
        }

        return $message;
	}


	public function crearTemporada($crearAnio,$crearCiclo,$crearApertura,$crearPreInscripcion,$crearInicioClases,$crearCierreClases,$crearFechaSubsanacion){

            try {
					$query = "	EXEC ACADEMIA.crearTemporada $crearAnio,$crearCiclo,'$crearApertura','$crearPreInscripcion','$crearInicioClases','$crearCierreClases','$crearFechaSubsanacion' ";
				    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
				    $stmt->execute();
               		$temporadas = $stmt->fetchAll();
               		return $temporadas[0]['estadoCrear'];

            }catch (DBALException $e) {
              $message = $e->getCode();
            }

            return $message;
	}


	public function updateTemporada($editarAnio,$editarCiclo,$editarApertura,$editarPreInscripcion,$editarInicioClases,$editarCierreClases,$editarFechaSubsanacion,$idTemporada){

            try {
					$query = "	EXEC ACADEMIA.updateTemporada $editarAnio,$editarCiclo,'$editarApertura','$editarPreInscripcion','$editarInicioClases','$editarCierreClases','$editarFechaSubsanacion',$idTemporada ";
				    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
				    $stmt->execute();
				    $temporadas = $stmt->fetchAll();
               		return $temporadas[0]['estadoModificar'];

            }catch (DBALException $e) {
              $message = $e->getCode();
            }

            return $message;
	}

	public function getTemporadas(){

		$query = "	SELECT	temp.id,
							temp.anio,
							cic.descripcion ciclo,
							temp.ciclo_id cicloId,
							CONVERT(varchar,temp.apertura,105) apertura,
							CONVERT(varchar,temp.pre_inscripciones,105) pre_inscripciones,
							CONVERT(varchar,temp.inicio_clases,105) inicio_clases,
							CONVERT(varchar,temp.cierre_clases,105) cierre_clases,
							CONVERT(varchar,temp.fecha_subsanacion,105) subsanacion,
							CONVERT(VARCHAR,CONVERT(int,CONVERT( VARCHAR,DATEDIFF(month,temp.apertura,temp.cierre_clases) ))+1)+' meses' duracion,
							temp.estado
					FROM ACADEMIA.temporada temp
					INNER JOIN ACADEMIA.ciclo cic ON cic.id = temp.ciclo_id ORDER BY temp.id DESC";
	    
	    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
	    $stmt->execute();
	    $temporadas = $stmt->fetchAll();
	    
	    return $temporadas;
	}

	public function getCantidadMesesTemporadaEnCurso($idTemporada){

		$query = "	SELECT	
					MONTH(temp.inicio_clases) mes_inicio_temporada,
					MONTH(temp.cierre_clases) mes_fin_temporada 
					FROM ACADEMIA.temporada temp
					INNER JOIN ACADEMIA.ciclo cic ON cic.id = temp.ciclo_id
					WHERE temp.id = $idTemporada; ";
	    
	    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
	    $stmt->execute();
	    $mesesTemporada = $stmt->fetchAll();
	    
	    return $mesesTemporada;
	}
}
