<?php

namespace AkademiaBundle\Repository;

/**
 * DisciplinaDeportivaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
use Doctrine\DBAL\DBALException;

class DisciplinaDeportivaRepository extends \Doctrine\ORM\EntityRepository
{


    public function updateDisciplina($idDisciplina,$convencionalEdadMinima,$convencionalEdadMaxima,$discapacitadoEdadMinima,$discapacitadoEdadMaxima,$estado)
    {
        try {

            $query = " exec ACADEMIA.actualizarConfiguracionDisciplina $convencionalEdadMinima ,$convencionalEdadMaxima ,$discapacitadoEdadMinima ,$discapacitadoEdadMaxima,$idDisciplina,$estado";
        
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();

            $message = 1;

        }catch (DBALException $e) {
            $message = $e->getCode();
        }

        return $message;
    }

public function getDisciplinasTotales()
    {
        $query = "  SELECT dis.dis_codigo codigo,
                    dis.dis_descripcion disciplina,
                    CONVERT(VARCHAR(10),ced.edad_min_discapacitado)+' a '+CONVERT(VARCHAR(10),ced.edad_max_discapacitado)+' años' AS rango_edad_discapacitado,
                    CONVERT(VARCHAR(10),ced.edad_min_convencional)+' a '+CONVERT(VARCHAR(10),ced.edad_max_convencional)+' años' AS rango_edad_convencional,
                    dis.dis_estado estado
                    FROM CATASTRO.disciplina dis
                    INNER JOIN ACADEMIA.ConfiguracionEdadesDisciplina ced ON ced.disciplina_id=dis.dis_codigo";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasActivas = $stmt->fetchAll();
        return $disciplinasActivas;
    }

    public function getDisciplinaConfiguracionById($idDisciplina)
    {
        $query = "  SELECT dis.dis_codigo codigo,
                    dis.dis_descripcion disciplina,
                    ced.edad_min_discapacitado,
                    ced.edad_max_discapacitado,
                    ced.edad_min_convencional,
                    ced.edad_max_convencional ,
                    dis.dis_estado estado
                    FROM CATASTRO.disciplina dis
                    INNER JOIN ACADEMIA.ConfiguracionEdadesDisciplina ced ON ced.disciplina_id=dis.dis_codigo
                    WHERE  dis.dis_codigo = '$idDisciplina' ";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasActivas = $stmt->fetchAll();
        return $disciplinasActivas;
    }

    public function getDisciplinesPublicGeneralByDisability($disability,$ageBeneficiario)
    {
        $query = "SELECT  distinct 
                            eddis.edi_codigo AS id, 
                            eddis.ede_codigo AS idComplejoDeportivo, 
                            rtrim(dis.dis_descripcion) AS nombreDisciplina, 
                            dis.dis_codigo AS idDisciplina ,
                            edde.ede_discapacitado AS discapacidad
                    FROM    ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina AS eddis, CATASTRO.edificacionesdeportivas AS         edde,   CATASTRO.disciplina AS dis 
                    WHERE 
                            
                            hor.edi_codigo = eddis.edi_codigo AND 
                            edde.ede_codigo=eddis.ede_codigo AND 
                            dis.dis_codigo=eddis.dis_codigo AND 
                            hor.etapa = 1 AND

                            hor.estado=1 AND 
                            hor.vacantes<> 0 AND
                            hor.convocatoria = 1 AND
                            hor.discapacitados='$disability'  AND 
                            dis_estado = 1 AND 


                            '$ageBeneficiario' <= hor.edadMaxima and 
                            '$ageBeneficiario' >= hor.edadMinima

                    ORDER BY nombreDisciplina  ASC, id, idComplejoDeportivo, idDisciplina, discapacidad;";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasDeporivas = $stmt->fetchAll();
        return $disciplinasDeporivas;
    }

    public function getDisciplinesPromotorByDisability($disability,$ageBeneficiario)
    {
        $query = "  SELECT  distinct 
                            eddis.edi_codigo AS id, 
                            eddis.ede_codigo AS idComplejoDeportivo, 
                            rtrim(dis.dis_descripcion) AS nombreDisciplina, 
                            dis.dis_codigo AS idDisciplina ,
                            edde.ede_discapacitado AS discapacidad
                    FROM    ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina AS eddis, CATASTRO.edificacionesdeportivas AS         edde,   CATASTRO.disciplina AS dis 
                    WHERE 
                            hor.discapacitados='$disability'  AND 
                            dis_estado = 1 AND 
                            hor.edi_codigo = eddis.edi_codigo AND 
                            edde.ede_codigo=eddis.ede_codigo AND 
                            dis.dis_codigo=eddis.dis_codigo AND 
                            hor.estado=1 AND 
                            hor.vacantes<> 0 AND
                            
                            '$ageBeneficiario' <= hor.edadMaxima and 
                            '$ageBeneficiario' >= hor.edadMinima

                    ORDER BY nombreDisciplina  ASC, id, idComplejoDeportivo, idDisciplina, discapacidad;";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasDeporivas = $stmt->fetchAll();
        return $disciplinasDeporivas;
    }

    public function getDisciplinesLandingByDisability($disability)
    {
        $query = "SELECT  distinct 
                            eddis.edi_codigo AS id, 
                            eddis.ede_codigo AS idComplejoDeportivo, 
                            rtrim(dis.dis_descripcion) AS nombreDisciplina, 
                            dis.dis_codigo AS idDisciplina ,
                            edde.ede_discapacitado AS discapacidad
                    FROM    ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina AS eddis, CATASTRO.edificacionesdeportivas AS         edde,   CATASTRO.disciplina AS dis 
                    WHERE 
                            
                            hor.edi_codigo = eddis.edi_codigo AND 
                            edde.ede_codigo=eddis.ede_codigo AND 
                            dis.dis_codigo=eddis.dis_codigo AND 

                            hor.estado=1 AND 
                            hor.vacantes<> 0 AND
                            hor.convocatoria = 1 AND
                            hor.discapacitados='$disability'  AND 
                            dis_estado = 1 

                    ORDER BY nombreDisciplina  ASC, id, idComplejoDeportivo, idDisciplina, discapacidad;";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasDeporivas = $stmt->fetchAll();
        return $disciplinasDeporivas;
    }

   public function getDisciplinasDiferentes($idComplejo){
            
        $query = "  SELECT  dis.dis_codigo,
                            dis.dis_descripcion 
                    FROM catastro.disciplina dis left join 
                    (SELECT d.dis_codigo, d.dis_descripcion, edi.edi_estado 
                    from catastro.edificacionDisciplina edi 
                    inner join catastro.disciplina d on edi.dis_codigo = d.dis_codigo 
                    inner join catastro.edificacionesdeportivas ede on edi.ede_codigo = ede.ede_codigo
                    where ede.ede_codigo = $idComplejo and edi.edi_estado=1) t2
                    on dis.dis_codigo = t2.dis_codigo 
                    where t2.dis_codigo IS NULL and dis.dis_estado = 1
                    ORDER BY dis.dis_descripcion ASC";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinas = $stmt->fetchAll();

        return $disciplinas;
    }

    public function getMostrarCambios($idEdiDisciplina){

        $query = "SELECT rtrim(b.dis_descripcion) as nombreDisciplina,b.dis_codigo as idDisciplina ,c.ede_discapacitado as discapacidad from CATASTRO.edificacionDisciplina as a inner join CATASTRO.disciplina as b on a.dis_codigo = b.dis_codigo inner join CATASTRO.edificacionesdeportivas as c on a.ede_codigo=c.ede_codigo where a.edi_codigo = $idEdiDisciplina";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinas = $stmt->fetchAll();

        return $disciplinas;
    }


    public function disciplinasDeportivasFlagAllLanding($flagDis)
    {
        $query = "  SELECT distinct eddis.edi_codigo as id, eddis.ede_codigo as idComplejoDeportivo,
                    rtrim(dis.dis_descripcion) as nombreDisciplina, dis.dis_codigo as idDisciplina ,
                    edde.ede_discapacitado as discapacidad
                    from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis,
                    CATASTRO.edificacionesdeportivas AS edde, CATASTRO.disciplina as dis 
                    where hor.discapacitados='$flagDis'  and dis_estado=1 and hor.edi_codigo=eddis.edi_codigo 
                    and edde.ede_codigo=eddis.ede_codigo and dis.dis_codigo=eddis.dis_codigo and hor.estado=1 
                    and hor.vacantes <>0 and hor.convocatoria=1";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasDeporivas = $stmt->fetchAll();
        return $disciplinasDeporivas;
    }

    public function disciplinaDeportivaExport(){

        $query = "SELECT distinct ede.ede_codigo as idComplejoDeportivo,dis.dis_codigo idDisciplina , rtrim(dis.dis_descripcion) as nombreDisciplina
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

            inner join CATASTRO.disciplina dis on dis.dis_codigo = edi.dis_codigo
            WHERE ubiDpto.ubidistrito=0 AND ubiDpto.ubiprovincia=0 AND ubiDpto.ubidpto!=0
            ORDER BY nombreDisciplina  ASC, idComplejoDeportivo, idDisciplina ;";
            
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasDeporivas = $stmt->fetchAll();
        return $disciplinasDeporivas;
    }

    public function getEditarDiscapacitado($idDisciplina, $usuario){

        $query = "UPDATE catastro.disciplina set dis_discapacitado = 1, dis_usumodi = $usuario, dis_fechamodi = getDate() where dis_codigo= $idDisciplina";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }
}
