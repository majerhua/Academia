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

    public function getDistrictsPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada){

        $query = "SELECT DISTINCT ubi.ubidpto idDepartamento, 
                ubi.ubiprovincia idProvincia,
                ubi.ubidistrito idDistrito,
                ubi.ubicodigo ubigeoDistrito,
                ubi.ubinombre nombreDistrito

                FROM  ACADEMIA.horario AS hor 
                INNER JOIN CATASTRO.edificacionDisciplina AS edi ON edi.edi_codigo = hor.edi_codigo
                INNER JOIN CATASTRO.edificacionesdeportivas AS ede ON ede.ede_codigo = edi.ede_codigo
                INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo

                WHERE

                hor.estado = 1 AND 
                hor.discapacitados = '$disability' AND
                hor.vacantes <> 0 AND
                hor.convocatoria = 1 AND
                hor.etapa = 1 AND
                edi.temporada_id =  $idTemporada AND
                
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubi.ubiprovincia <> '00' AND

                '$ageBeneficiario' <= hor.edadMaxima AND 
                '$ageBeneficiario' >= hor.edadMinima 
                ";
       
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    }    

    public function getDistrictsPromotorByDisability($disability,$ageBeneficiario,$idTemporada){

        $query = "
                SELECT DISTINCT ubi.ubidpto idDepartamento, 
                ubi.ubiprovincia idProvincia,
                ubi.ubidistrito idDistrito,
                ubi.ubicodigo ubigeoDistrito,
                ubi.ubinombre nombreDistrito
                FROM  ACADEMIA.horario AS hor 
                INNER JOIN CATASTRO.edificacionDisciplina AS edi ON edi.edi_codigo = hor.edi_codigo
                INNER JOIN CATASTRO.edificacionesdeportivas AS ede ON ede.ede_codigo = edi.ede_codigo
                INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo

                WHERE

                hor.estado = 1 AND 
                hor.discapacitados = '$disability' AND
                hor.vacantes <> 0 AND
                
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubi.ubiprovincia <> '00' AND

                edi.temporada_id =  $idTemporada AND
                
                '$ageBeneficiario' <= hor.edadMaxima AND 
                '$ageBeneficiario' >= hor.edadMinima 
                ";
       
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    } 

    public function getDistrictsLandingByDisability($disability,$idTemporada){

        $query = "SELECT DISTINCT ubi.ubidpto idDepartamento, 
                ubi.ubiprovincia idProvincia,
                ubi.ubidistrito idDistrito,
                ubi.ubicodigo ubigeoDistrito,
                ubi.ubinombre nombreDistrito

                FROM  ACADEMIA.horario AS hor 
                INNER JOIN CATASTRO.edificacionDisciplina AS edi ON edi.edi_codigo = hor.edi_codigo
                INNER JOIN CATASTRO.edificacionesdeportivas AS ede ON ede.ede_codigo = edi.ede_codigo
                INNER JOIN grubigeo AS ubi ON ubi.ubicodigo = ede.ubicodigo

                WHERE

                hor.estado = 1 AND 
                hor.discapacitados = '$disability' AND
                hor.vacantes <> 0 AND
                hor.convocatoria = 1 AND
                hor.etapa = 1 AND
                
                edi.temporada_id = '$idTemporada' AND
                
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubi.ubiprovincia <> '00'
                ";
       
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    }    

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

       $query = "SELECT ubidpto as idDepartamento,ubiprovincia as idProvincia, ubicodigo as idDistrito ,ubinombre as nombreDistrito, ubidistrito idDistrito_2
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

    public function distritosAll(){

        $query = "SELECT ubidpto as idDepartamento,ubiprovincia as idProvincia, ubidistrito as idDistrito ,ubinombre as nombreDistrito from grubigeo where ubidistrito!='00' AND ubidpto!='00' AND ubiprovincia!='00';";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;
    }

}
