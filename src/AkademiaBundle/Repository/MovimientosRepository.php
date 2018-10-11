<?php

namespace AkademiaBundle\Repository;

/**
 * MovimientosRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MovimientosRepository extends \Doctrine\ORM\EntityRepository
{

  	public function nuevoMovimiento($idCategoria, $idAsistencia, $idFicha, $usuario, $horarioActual,$horarioAMigrar){

        if(!empty($horarioAMigrar)){

            $query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id, usuario_valida,horario_id) values ($idCategoria,$idAsistencia,$idFicha,$usuario,$horarioAMigrar)";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();

            $query = "UPDATE ACADEMIA.inscribete SET horario_id=$horarioAMigrar WHERE id=$idFicha";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();

        }else{
            $query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id, usuario_valida,horario_id) values ($idCategoria,$idAsistencia,$idFicha,$usuario,$horarioActual)";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();

        }
    }

    public function RegistrarMovInicial($idFicha, $usuario,$idHorario){
    	
    	$query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id, usuario_valida,horario_id) values (1,1,$idFicha,$usuario , $idHorario )";
    	$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }

    public function getCantAsistencias($idAsistencia, $idHorario){

    	$query = "SELECT count(*)asistencias
                FROM 
                ACADEMIA.inscribete ins 
                inner join 
                (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
                FROM ACADEMIA.movimientos m
                GROUP BY m.inscribete_id) ids
                ON ins.id = ids.mov_ins_id
                inner join academia.horario hor on ins.horario_id = hor.id 
                inner join academia.participante par on ins.participante_id = par.id
                inner join academia.movimientos mov on mov.id = ids.mov_id
                WHERE hor.id = $idHorario and ins.estado = 2 and mov.asistencia_id=$idAsistencia";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $asistencias = $stmt->fetchAll();

        return $asistencias;
    }

    public function getCantInscritos($idHorario){

        $query = "SELECT COUNT(1) inscritos FROM ACADEMIA.inscribete WHERE horario_id = '$idHorario' AND estado = 2";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $inscritos = $stmt->fetchAll();

        return $inscritos;
    }

    public function getCantRetirados($idRetirado, $idHorario){

    	$query = "  SELECT count(*)retirados
                    FROM ACADEMIA.inscribete ins   
                    INNER JOIN 
                    (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
                        FROM ACADEMIA.movimientos m GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
                    INNER JOIN ACADEMIA.horario hor on ins.horario_id = hor.id 
                    INNER JOIN ACADEMIA.movimientos mov on mov.id = ids.mov_id
                    WHERE hor.id = $idHorario and ins.estado = 1 and mov.asistencia_id=$idRetirado";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $retirados = $stmt->fetchAll();

        return $retirados;
    }


    public function getCantSeleccionados($idCategoria, $idHorario){
    	$query = "SELECT count(*)seleccionados
                FROM 
                ACADEMIA.inscribete ins 
                inner join 
                (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
                FROM ACADEMIA.movimientos m
                GROUP BY m.inscribete_id) ids
                ON ins.id = ids.mov_ins_id
                inner join academia.horario hor on ins.horario_id = hor.id 
                inner join academia.movimientos mov on mov.id = ids.mov_id
                WHERE hor.id = $idHorario and ins.estado = 2 and mov.categoria_id=$idCategoria";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $seleccionados = $stmt->fetchAll();

        return $seleccionados;
    }


}
