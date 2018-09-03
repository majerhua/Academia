<?php

namespace AkademiaBundle\Repository;

/**
 * DepartamentoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DepartamentoRepository extends \Doctrine\ORM\EntityRepository
{

	public function getDepartmentsPublicGeneralByDisability($disability,$ageBeneficiario){

        $query = "  SELECT DISTINCT ubiDpto.ubidpto id ,
                                    ubiDpto.ubinombre nombre
                    FROM  ACADEMIA.horario AS hor 
                    INNER JOIN CATASTRO.edificacionDisciplina AS edi ON edi.edi_codigo = hor.edi_codigo
                    INNER JOIN CATASTRO.edificacionesdeportivas AS ede ON ede.ede_codigo = edi.ede_codigo
                    INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo
                    INNER JOIN grubigeo AS ubiDpto ON ubiDpto.ubidpto = ubi.ubidpto
                    WHERE
                    hor.estado = 1 AND 
                    hor.discapacitados = '$disability' AND
                    hor.vacantes <> 0 AND
                    hor.etapa = 1 AND
                    hor.convocatoria = 1 AND
                    ubiDpto.ubidpto <> '00' AND 
                    ubiDpto.ubidistrito ='00' AND
                    ubiDpto.ubiprovincia ='00' AND
                    '$ageBeneficiario' <= hor.edadMaxima AND 
                    '$ageBeneficiario' >= hor.edadMinima ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentos = $stmt->fetchAll();
        return $departamentos;
	
	}

    public function getDepartmentsPromotorByDisability($disability,$ageBeneficiario){
    
        $query = "  SELECT DISTINCT ubiDpto.ubidpto id ,
                                    ubiDpto.ubinombre nombre
                    FROM  ACADEMIA.horario AS hor 
                    INNER JOIN CATASTRO.edificacionDisciplina AS edi ON edi.edi_codigo = hor.edi_codigo
                    INNER JOIN CATASTRO.edificacionesdeportivas AS ede ON ede.ede_codigo = edi.ede_codigo
                    INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo
                    INNER JOIN grubigeo AS ubiDpto ON ubiDpto.ubidpto = ubi.ubidpto
                    WHERE
                    hor.estado = 1 AND 
                    hor.discapacitados = '$disability' AND
                    hor.vacantes <> 0 AND
                    ubiDpto.ubidpto <> '00' AND 
                    ubiDpto.ubidistrito ='00' AND
                    ubiDpto.ubiprovincia ='00'  AND
                    '$ageBeneficiario' <= hor.edadMaxima AND 
                    '$ageBeneficiario' >= hor.edadMinima  ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentos = $stmt->fetchAll();
        return $departamentos;
    }

    public function getDepartmentsLandingByDisability($disability){
    
        $query = "  SELECT DISTINCT ubiDpto.ubidpto id ,
                                    ubiDpto.ubinombre nombre
                    FROM  ACADEMIA.horario AS hor 
                    INNER JOIN CATASTRO.edificacionDisciplina AS edi ON edi.edi_codigo = hor.edi_codigo
                    INNER JOIN CATASTRO.edificacionesdeportivas AS ede ON ede.ede_codigo = edi.ede_codigo
                    INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo
                    INNER JOIN grubigeo AS ubiDpto ON ubiDpto.ubidpto = ubi.ubidpto
                    WHERE
                    hor.estado = 1 AND 
                    hor.discapacitados = '$disability' AND
                    hor.vacantes <> 0 AND
                    hor.convocatoria = 1 AND
                    ubiDpto.ubidpto <> '00' AND 
                    ubiDpto.ubidistrito ='00' AND
                    ubiDpto.ubiprovincia ='00' ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentos = $stmt->fetchAll();
        return $departamentos;
    }

    public function departamentosExport(){

        $query = "SELECT distinct ubiDpto.ubidpto idDepartamento
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
        $departamentos = $stmt->fetchAll();
        return $departamentos;
    }

    public function departamentosExportFind($id){

        $query = " SELECT distinct ubiDpto.ubidpto idDepartamento
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
        $departamentos = $stmt->fetchAll();
        return $departamentos;
    
    }



    public function departamentosExportFind2($id){

        $query = " SELECT distinct ubiDpto.ubidpto idDepartamento
                    FROM 
            grubigeo ubiDpto 
        inner join CATASTRO.edificacionesdeportivas ede on ede.ubicodigo = ubiDpto.ubicodigo
        WHERE  ede.ede_codigo='$id' ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentos = $stmt->fetchAll();
        return $departamentos;
    
    }

	public function departamentosAll(){

    $query = "SELECT ubidpto as id ,ubinombre as nombre from grubigeo where ubiprovincia='00' and ubidistrito='00' AND  ubidpto!='00';";
    $stmt = $this->getEntityManager()->getConnection()->prepare($query);
    $stmt->execute();
    $departamentos = $stmt->fetchAll();
    return $departamentos;
	
	}

}
