<?php

namespace AkademiaBundle\Repository;

/**
 * DisciplinaDeportivaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DisciplinaDeportivaRepository extends \Doctrine\ORM\EntityRepository
{
	   public function getDisciplinasDiferentes($idComplejo){
                
                $query = "SELECT dis.dis_codigo, dis.dis_descripcion 
                        from catastro.disciplina dis left join 
                        (select d.dis_codigo, d.dis_descripcion, edi.edi_estado 
                        from catastro.edificacionDisciplina edi 
                        inner join catastro.disciplina d on edi.dis_codigo = d.dis_codigo 
                        inner join catastro.edificacionesdeportivas ede on edi.ede_codigo = ede.ede_codigo
                        where ede.ede_codigo = 11 and edi.edi_estado=1) t2
                        on dis.dis_codigo = t2.dis_codigo 
                        where t2.dis_codigo IS NULL;";

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

    public function disciplinasDeportivasFlagAll($flagDis)
    {
        $query = "select distinct eddis.edi_codigo as id, eddis.ede_codigo as idComplejoDeportivo, rtrim(dis.dis_descripcion) as nombreDisciplina, dis.dis_codigo as idDisciplina ,edde.ede_discapacitado as discapacidad
            from ACADEMIA.horario AS hor , CATASTRO.edificacionDisciplina as eddis, CATASTRO.edificacionesdeportivas AS edde, CATASTRO.disciplina as dis where hor.discapacitados='$flagDis'  and dis_estado=1 and hor.edi_codigo=eddis.edi_codigo and edde.ede_codigo=eddis.ede_codigo and dis.dis_codigo=eddis.dis_codigo and hor.estado=1 and hor.vacantes<>0 and hor.convocatoria=1;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinasDeporivas = $stmt->fetchAll();
        return $disciplinasDeporivas;
    }

}
