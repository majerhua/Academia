<?php

namespace AkademiaBundle\Repository;

/**
 * DistritoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DistritoRepository extends \Doctrine\ORM\EntityRepository
{


	public function getDepartamentos(){

        $query = "SELECT ubidpto as id ,ubinombre as nombre 
                FROM grubigeo WHERE ubiprovincia='00' and ubidistrito='00' AND ubidpto!='00'
                ORDER BY ubinombre ASC;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentos = $stmt->fetchAll();
        return $departamentos;
	}


	public function getProvincias(){

        $query = " SELECT ubidpto as idDepartamento,ubiprovincia as idProvincia , ubinombre as nombreProvincia 
                FROM grubigeo where ubidistrito='00' AND ubidpto!='00' AND ubiprovincia!='00'
                ORDER BY ubinombre ASC;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $provincias = $stmt->fetchAll();

        return $provincias; 		
	}

	public function getDistritos(){

       $query = "SELECT ubidpto as idDepartamento,ubiprovincia as idProvincia, ubicodigo as idDistrito ,ubinombre as nombreDistrito
                FROM grubigeo where ubidistrito!='00' AND ubidpto!='00' AND ubiprovincia!='00'
                ORDER BY ubinombre ASC;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();

        return $distritos;		
	}


    public function getDitritosCD(){

       $query = " SELECT  DISTINCT grubigeo.ubidpto as idDepartamento, grubigeo.ubiprovincia as idProvincia,grubigeo.ubicodigo as idDistrito,grubigeo.ubinombre as nombreDistrito
                FROM CATASTRO.edificacionesdeportivas as edificacionesdeportivas,grubigeo 
                WHERE edificacionesdeportivas.ubicodigo = grubigeo.ubicodigo and edificacionesdeportivas.ede_estado = 1
                ORDER BY grubigeo.ubinombre ASC;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritosCD = $stmt->fetchAll();

        return $distritosCD;  

    }

    public function getProvinciasCD(){

        $query = "SELECT  ubidpto as idDepartamento,ubiprovincia as idProvincia , ubinombre as nombreProvincia 
                FROM grubigeo where ubidistrito='00' AND ubidpto!='00' AND ubiprovincia!='00' and (ubidpto = 24
                and ubiprovincia = 01) or (ubidpto = 14 and ubiprovincia = 01) and ubidistrito = 0
                ORDER BY ubinombre ASC;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $provinciasCD = $stmt->fetchAll();

        return $provinciasCD;         
    }

    public function getDepartamentosCD(){

        $query = "SELECT ubidpto as id ,ubinombre as nombre FROM grubigeo 
                WHERE ubiprovincia='00' and ubidistrito='00' AND ubidpto!='00' and ubicodigo=24 or ubicodigo=14
                ORDER BY ubinombre ASC; ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentosCD = $stmt->fetchAll();

        return $departamentosCD;

    }

    public function distritosFlagAll($flagDis){

        $query = "SELECT distinct ubi.ubidpto as idDepartamento,ubi.ubiprovincia as 
                idProvincia, ubi.ubidistrito as idDistrito, ubi.ubicodigo as identDistrito ,ubinombre as nombreDistrito
                from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis, 
                CATASTRO.edificacionesdeportivas AS edde, grubigeo as ubi where hor.discapacitados='$flagDis' and hor.estado=1
                and hor.edi_codigo=eddis.edi_codigo and edde.ede_codigo=eddis.ede_codigo and ubi.ubicodigo=edde.ubicodigo
                and ubidistrito!='00' AND ubidpto!='00' AND ubiprovincia!='00' and hor.vacantes<>0 and hor.convocatoria=1";
       
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    }    

    public function distritosPromotor($flagDis){

        $query = "SELECT distinct ubi.ubidpto as idDepartamento,ubi.ubiprovincia as 
                idProvincia, ubi.ubidistrito as idDistrito, ubi.ubicodigo as identDistrito ,ubinombre as nombreDistrito
                from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis, 
                CATASTRO.edificacionesdeportivas AS edde, grubigeo as ubi where hor.discapacitados='$flagDis' and hor.estado=1
                and hor.edi_codigo=eddis.edi_codigo and edde.ede_codigo=eddis.ede_codigo and ubi.ubicodigo=edde.ubicodigo
                and ubidistrito!='00' AND ubidpto!='00' AND ubiprovincia!='00' and hor.vacantes<>0";
       
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    
    } 

    
    public function distritosAll(){

        $query = "SELECT ubidpto as idDepartamento,ubiprovincia as idProvincia, ubidistrito as idDistrito ,ubinombre as nombreDistrito from grubigeo where ubidistrito!='00' AND ubidpto!='00' AND ubiprovincia!='00';";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    }

}
