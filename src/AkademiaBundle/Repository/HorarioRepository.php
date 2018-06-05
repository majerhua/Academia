<?php

namespace AkademiaBundle\Repository;

/**
 * HorarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class HorarioRepository extends \Doctrine\ORM\EntityRepository
{

    public function horariosFlagAll($flagDis,$edadBeneficiario)
    {
        $query = "SELECT * from ACADEMIA.horario where convocatoria= 1  and vacantes <> 0 and estado = 1 and discapacitados='$flagDis' and '$edadBeneficiario'<=edadMaxima and '$edadBeneficiario'>=edadMinima;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();
        return $horarios;
    }

    public function getHorariosPromotores($flagDis){
            
        $query = "SELECT * from ACADEMIA.horario where vacantes <> 0 and estado = 1 and discapacitados='$flagDis';";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();

        return $horarios;
    }

    public function getHorariosVacantes($idHorario){
        $query = "SELECT vacantes from ACADEMIA.horario where id = '$idHorario' ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();

        return $horarios;
    }

    public function getHorariosDiscapacitados(){
    
            $query = "SELECT * from ACADEMIA.horario where discapacitados = 1";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $horarios = $stmt->fetchAll();

            return $horarios;

    }

    public function getHorariosComplejos($idcomplejo){
    
            $query ="SELECT rtrim(dis.dis_descripcion) as nombreDisciplina,
                    dis.dis_codigo as idDisciplina,
                    hor.id as idHorario,
                    hor.turno as turno,
                    hor.inscritos as inscritos,
                    hor.estado as estadoHorario,
                    hor.discapacitados as discapacidad,
                    hor.edadMinima as edadMinima,
                    hor.edadMaxima as edadMaxima,
                    hor.horaInicio as horaInicio, 
                    hor.horaFin as horaFin, 
                    hor.vacantes as vacantes,
                    hor.convocatoria as convocatoria,
                    edi.edi_codigo as edi_codigo
                    from 
                    ACADEMIA.horario as hor inner join CATASTRO.edificacionDisciplina as edi on hor.edi_codigo = edi.edi_codigo
                    inner join CATASTRO.disciplina as dis on edi.dis_codigo = dis.dis_codigo
                    inner join CATASTRO.edificacionesdeportivas as ede on edi.ede_codigo = ede.ede_codigo
                    where ede.ede_codigo =$idcomplejo and dis.dis_estado = 1 and hor.estado = 1";

            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $horarios = $stmt->fetchAll();

            return $horarios;
    }

    public function getHorariosIndividual($idHorario, $idDisciplina){
            
        $query = "SELECT rtrim(dis.dis_descripcion) as nombreDisciplina,
                dis.dis_codigo as idDisciplina,
                hor.id as idHorario,
                hor.turno as turno,
                hor.discapacitados as discapacidad,
                hor.edadMinima as edadMinima,
                hor.edadMaxima as edadMaxima,
                hor.horaInicio as horaInicio, 
                hor.horaFin as horaFin, 
                hor.vacantes as vacantes,
                hor.convocatoria as convocatoria,
                edi.edi_codigo as edi_codigo
                from 
                ACADEMIA.horario as hor inner join CATASTRO.edificacionDisciplina as edi on hor.edi_codigo = edi.edi_codigo
                inner join CATASTRO.disciplina as dis on edi.dis_codigo = dis.dis_codigo
                inner join CATASTRO.edificacionesdeportivas as ede on edi.ede_codigo = ede.ede_codigo
                where dis.dis_codigo =$idDisciplina and hor.id = $idHorario";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();

        return $horarios;
    }

    public function getCapturarEdiCodigo($idComplejo, $idDisciplina){

        $query="SELECT edi_codigo from catastro.edificacionDisciplina where ede_codigo = $idComplejo and dis_codigo = $idDisciplina";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();

        return $horarios;
    }

        public function getActualizarHorarios($idHorario, $vacantes, $convocatoria, $usuario){

                $query = "UPDATE academia.horario set convocatoria = $convocatoria, vacantes = $vacantes, usuario_modif = $usuario,fecha_modif = getDate() from academia.horario where id = $idHorario";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();

        }

        public function getOcultarHorario($idHorario, $usuario){
                $query = "UPDATE academia.horario set estado = 0, usuario_elimina = $usuario, fecha_eliminacion = getDate() where id= $idHorario";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();
        }

        public function getMostrarCambios($idHorario){

                $query = "SELECT * from academia.horario where id='$idHorario'";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();
                $horarios = $stmt->fetchAll();

                return $horarios;

        }
        public function getActualizarVacantesHorarios($idHorario){

                $query = "UPDATE academia.horario set vacantes = (vacantes - 1) where id = $idHorario and vacantes > 0";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();

        }

        public function getAcumularInscritos($idHorario){

                $query = "UPDATE academia.horario set inscritos = (inscritos + 1) where id = $idHorario ";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();
        
        }

        public function getHorarioBeneficiario($idHorario){

                $query = "SELECT rtrim(dis.dis_descripcion) as nombreDisciplina,
                        dis.dis_codigo as idDisciplina,
                        hor.id as idHorario,
                        hor.turno as turno,
                        hor.inscritos as inscritos,
                        hor.discapacitados as discapacidad,
                        hor.edadMinima as edadMinima,
                        hor.edadMaxima as edadMaxima,
                        hor.horaInicio as horaInicio, 
                        hor.horaFin as horaFin, 
                        hor.vacantes as vacantes,
                        hor.convocatoria as convocatoria,
                        edi.edi_codigo as edi_codigo,
                        ede.ede_nombre as nombreComplejo
                        from 
                        ACADEMIA.horario as hor inner join CATASTRO.edificacionDisciplina as edi on hor.edi_codigo = edi.edi_codigo
                        inner join CATASTRO.disciplina as dis on edi.dis_codigo = dis.dis_codigo
                        inner join CATASTRO.edificacionesdeportivas as ede on edi.ede_codigo = ede.ede_codigo
                        where hor.id = $idHorario";

                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();
                $horarios = $stmt->fetchAll();

                return $horarios;
        
        }

        public function getBeneficiarios($idHorario){

                $query ="SELECT (per.perapepaterno+' '+per.perapematerno+' '+per.pernombres) as nombre,
                        par.id as idParticipante,
                        per.perdni as dni, (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                        per.persexo as sexo,
                        hor.id as idHorario, 
                        ins.id as idInscribete,
                        ins.estado as estadoInscribete,
                        mov.categoria_id as idCategoria,
                        mov.asistencia_id as idAsistencia,
                        mov.fecha_modificacion as fechita,
                        mov.asistencia_id as TipoAsistencia
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
                        inner join grpersona per on per.percodigo = par.percodigo
                        WHERE hor.id = $idHorario and ins.estado = 2";
                

                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();
                $beneficiarios = $stmt->fetchAll();

                return $beneficiarios;
        }

        public function getDiferenciarHorarios($turno,$edadMinima,$edadMaxima,$horaInicio,$horaFin,$discapacitados,$codigoEdi){
               
                $query="SELECT turno from academia.horario where turno ='$turno' and edadMinima=$edadMinima and edadMaxima=$edadMaxima and horaInicio='$horaInicio' and horaFin='$horaFin' and discapacitados=$discapacitados and edi_codigo=$codigoEdi and estado <> 0";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();
                $horarios = $stmt->fetchAll();

                return $horarios;

        }

        public function cantidadInscritos($idHorario){
          
            $query = "SELECT count(*) cantInscritos from academia.inscribete where horario_id = $idHorario and estado = 2";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cantidad = $stmt->fetchAll();

            return $cantidad;

        }

        public function horarioActivos($idHorario){

            $query = "SELECT estado from academia.horario where id = $idHorario AND estado = 1";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();

            return $data;
        }


}
