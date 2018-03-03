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

  	public function nuevoMovimiento($idCategoria, $idAsistencia, $idFicha, $usuario){

        $query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id, usuario_valida) values ($idCategoria,$idAsistencia,$idFicha,$usuario)";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }

    public function RegistrarMovInicial($idFicha){
    	
    	$query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id) values (5,4,$idFicha)";
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

    public function getCantRetirados($idRetirado, $idHorario){
    	$query = "SELECT count(*)retirados
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
                WHERE hor.id = $idHorario and ins.estado = 1 and mov.asistencia_id=$idRetirado";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $retirados = $stmt->fetchAll();

        return $retirados;
    }


    public function getCantEvaluados($idCategoria, $idHorario){
    	$query = "SELECT count(*)seleccionados
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
                WHERE hor.id = $idHorario and ins.estado = 2 and mov.categoria_id=$idCategoria";

		$stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $seleccionados = $stmt->fetchAll();

        return $seleccionados;
    }


}
