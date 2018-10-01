<?php

namespace AkademiaBundle\Repository;

/**
 * HorarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */

use Doctrine\DBAL\DBALException;
use AkademiaBundle\Entity\Horario;

class HorarioRepository extends \Doctrine\ORM\EntityRepository
{
    
    public function getScheduleDisciplineLanding($idCompleDis,$flagDiscapacitado){
        $query = "  SELECT  hor.id,
                            CONVERT(VARCHAR(10),hor.edadMinima)+' - '+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad
                    FROM ACADEMIA.horario hor
                    WHERE 
                    convocatoria= 1  AND
                    vacantes <> 0 AND
                    estado = 1 AND
                    etapa = 1 AND
                    discapacitados = $flagDiscapacitado AND
                    edi_codigo = $idCompleDis ORDER BY hor.edadMinima ASC
                    ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horariosComplejoDisciplina = $stmt->fetchAll();
        return $horariosComplejoDisciplina;
    }

    public function getScheduleDisciplinePublicGeneral($idCompleDis,$edadBeneficiario,$flagDiscapacitado){
        $query = "  SELECT  hor.id,
                            CONVERT(VARCHAR(10),hor.edadMinima)+' - '+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad
                    FROM ACADEMIA.horario hor
                    WHERE 
                    convocatoria= 1  AND
                    vacantes <> 0 AND estado = 1 AND etapa=1 AND edi_codigo = $idCompleDis AND '$edadBeneficiario'<=edadMaxima AND '$edadBeneficiario'>=edadMinima AND hor.discapacitados = $flagDiscapacitado ORDER BY hor.edadMinima ASC";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horariosComplejoDisciplina = $stmt->fetchAll();
        return $horariosComplejoDisciplina;
    }


    public function getTurnsDiscipline($idCompleDis){
        $query = "  SELECT 
                    tur.horario_id as idHorario,
                    CASE 
                    WHEN tur.horaInicio <CONVERT(time, '10:00:00')
                     THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5),2,4)
                    ELSE LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5)
                    END
                    +' - '+
                    CASE 
                    WHEN tur.horaFin <CONVERT(time, '10:00:00')
                     THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5),2,4)
                    ELSE LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5)
                    END as hora,
                    tur.dias as frecuencia 
                    FROM
                    ACADEMIA.turno tur
                    INNER JOIN ACADEMIA.horario hor ON hor.id = tur.horario_id
                    INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = hor.edi_codigo
                    WHERE hor.edi_codigo=$idCompleDis";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $turnosComplejoDisciplina = $stmt->fetchAll();
        return $turnosComplejoDisciplina;
    }

    public function getScheduleDisciplinePromotor($idCompleDis,$edadBeneficiario,$flagDiscapacitado){
        $query = "  SELECT  hor.id,
                            CONVERT(VARCHAR(10),hor.edadMinima)+'-'+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad,
                            CASE hor.etapa 
                            WHEN 0 THEN 'Formación'
                            WHEN 1 THEN 'Masificación'
                            END AS etapa
                    FROM ACADEMIA.horario hor
                    WHERE 
                    vacantes <> 0 AND estado = 1 AND edi_codigo = $idCompleDis AND '$edadBeneficiario'<=edadMaxima AND  '$edadBeneficiario'>=edadMinima  AND hor.discapacitados= $flagDiscapacitado ORDER BY hor.edadMinima ASC";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horariosComplejoDisciplina = $stmt->fetchAll();
        return $horariosComplejoDisciplina;
    }



    public function horariosFlagAll($flagDis,$edadBeneficiario)
    {
        $query = "SELECT * from ACADEMIA.horario where convocatoria= 1  and preinscripciones<>0 and preinscripciones <> 0 and estado = 1 and discapacitados='$flagDis' and '$edadBeneficiario'<=edadMaxima and '$edadBeneficiario'>=edadMinima;";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $turnos = $stmt->fetchAll();
        return $turnos;
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

     public function getHorariosPreinscripciones($idHorario){
        $query = "SELECT preinscripciones from ACADEMIA.horario where id = '$idHorario' ";
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
                    (SELECT COUNT(1) cantInscritos FROM ACADEMIA.inscribete WHERE horario_id=hor.id AND estado=2) AS inscritos,
                    hor.estado as estadoHorario,
                    hor.discapacitados as discapacidad,
                    hor.edadMinima as edadMinima,
                    hor.edadMaxima as edadMaxima,
                    hor.horaInicio as horaInicio, 
                    hor.horaFin as horaFin, 
                    hor.vacantes as vacantes,
                    hor.preinscripciones as preinscripciones,
                    hor.convocatoria as convocatoria,
                    edi.edi_codigo as edi_codigo,
                    hor.etapa
                    from 
                    ACADEMIA.horario as hor inner join CATASTRO.edificacionDisciplina as edi on hor.edi_codigo = edi.edi_codigo
                    inner join CATASTRO.disciplina as dis on edi.dis_codigo = dis.dis_codigo
                    inner join CATASTRO.edificacionesdeportivas as ede on edi.ede_codigo = ede.ede_codigo
                    where ede.ede_codigo =$idcomplejo and dis.dis_estado = 1 and hor.estado = 1
                    ORDER BY  hor.id DESC; ";

            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $horarios = $stmt->fetchAll();
            return $horarios;
    }

    public function getTurnosComplejos($idcomplejo){
    
            $query =" SELECT tur.id,

                            tur.horario_id,
                            CASE 
                            WHEN tur.horaInicio <CONVERT(time, '10:00:00')
                             THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5),2,4)
                            ELSE LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5)
                            END
                            +' - '+
                            CASE 
                            WHEN tur.horaFin <CONVERT(time, '10:00:00')
                             THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5),2,4)
                            ELSE LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5)
                            END as hora,

                            tur.dias 

                            FROM ACADEMIA.turno tur
                            INNER JOIN ACADEMIA.horario hor ON hor.id = tur.horario_id
                            INNER JOIN CATASTRO.edificacionDisciplina edi ON edi.edi_codigo = hor.edi_codigo
                            INNER JOIN CATASTRO.edificacionesdeportivas ede ON ede.ede_codigo = edi.ede_codigo
                            WHERE ede.ede_codigo='$idcomplejo' ";

            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $turnos = $stmt->fetchAll();
            return $turnos;
    }


    public function getHorariosIndividual($idHorario, $idDisciplina){
            
        $query = "SELECT rtrim(dis.dis_descripcion) AS nombreDisciplina,
                    dis.dis_codigo AS idDisciplina,
                    hor.id AS idHorario,
                    CASE hor.discapacitados 
                    WHEN 0 THEN 'Convencional'
                    WHEN 1 THEN 'Per con Discapacidad'
                    END AS modalidad,
                    CONVERT(VARCHAR(10),hor.edadMinima)+' - '+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad,
                    CASE hor.etapa 
                    WHEN 0 THEN 'Formación'
                    WHEN 1 THEN 'Masificación'
                    END AS etapa,
                    hor.vacantes AS vacantes,
                    hor.preinscripciones AS preinscripciones,
                    rtrim(hor.convocatoria) AS convocatoria,
                    edi.edi_codigo AS edi_codigo
                    from 
                    ACADEMIA.horario AS hor inner join CATASTRO.edificacionDisciplina AS edi on hor.edi_codigo = edi.edi_codigo
                    inner join CATASTRO.disciplina AS dis on edi.dis_codigo = dis.dis_codigo
                    inner join CATASTRO.edificacionesdeportivas AS ede on edi.ede_codigo = ede.ede_codigo
                    where  hor.id = $idHorario AND dis.dis_codigo = $idDisciplina ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();

        return $horarios;
    }


    public function getTurnosIndividual($idHorario){
            
        $query = "SELECT 
                    tur.horario_id as idHorario,

                    CASE 
                    WHEN tur.horaInicio <CONVERT(time, '10:00:00')
                     THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5),2,4)
                    ELSE LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5)
                    END
                    +' - '+
                    CASE 
                    WHEN tur.horaFin <CONVERT(time, '10:00:00')
                     THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5),2,4)
                    ELSE LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5)
                    END as hora,
                    tur.dias as frecuencia 
                    FROM
                    ACADEMIA.turno tur
                    WHERE  tur.horario_id=$idHorario";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $turnos = $stmt->fetchAll();

        return $turnos;
    }

    public function getCapturarEdiCodigo($idComplejo, $idDisciplina){

        $query="SELECT edi_codigo from catastro.edificacionDisciplina where ede_codigo = $idComplejo and dis_codigo = $idDisciplina";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $horarios = $stmt->fetchAll();

        return $horarios;
    }

        public function getActualizarHorarios($idHorario, $vacantes, $convocatoria, $usuario, $preinscripciones,$etapa){

                $query = "UPDATE academia.horario SET convocatoria = $convocatoria, vacantes = $vacantes, preinscripciones = $preinscripciones, etapa=$etapa , usuario_modif = $usuario,fecha_modif = getDate() from academia.horario where id = $idHorario";
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

        public function getActualizarPreinscripcionesHorarios($idHorario){
                $query = "UPDATE academia.horario set preinscripciones = (preinscripciones - 1) where id = $idHorario and preinscripciones > 0";
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $stmt->execute();

        }

        public function getActualizaConv($idHorario){
               
                $query = "UPDATE academia.horario set convocatoria = 0 where id = $idHorario";
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
                        CASE hor.discapacitados 
                        WHEN '0' THEN 'Convencional'
                        WHEN '1' THEN 'Persona con Discapacidad' 
                        END AS modalidad,
                        CONVERT(VARCHAR(10),hor.edadMinima)+' - '+CONVERT(VARCHAR(10),hor.edadMaxima)+' años' AS edad,
                        CASE hor.etapa 
                        WHEN 0 THEN 'Formación'
                        WHEN 1 THEN 'Masificación'
                        END AS etapa,
                        hor.vacantes as vacantes,
                        CASE hor.convocatoria 
                        WHEN '0' THEN 'Cerrada' 
                        WHEN '1' THEN 'Abierta'
                        END AS convocatoria,
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


        public function guardarTurnoHorario($turnos,$idHorarioNuevo){
        
          foreach ($turnos as $key => $value) {
            
            $horaInicio = $value['hora-inicio'];
            $horaFin = $value['hora-fin'];
            $dias = $value['turno-dias'];

            try {
              $query="INSERT INTO ACADEMIA.turno(horario_id,horaInicio,horaFin,dias) 
                      VALUES('$idHorarioNuevo','$horaInicio','$horaFin','$dias')";
              $stmt = $this->getEntityManager()->getConnection()->prepare($query);
              $stmt->execute();

               $message = 2;

            }catch (DBALException $e) {
              $message = $e->getCode();
            }

          }

          return $message;
                       
        }

        public function getDiferenciarHorarios($modalidad,$etapa,$edadMinima,$edadMaxima,$codigoEdi,$turnos){
               
                /*$query="SELECT ISNULL(COUNT(1),0) cantHorario FROM academia.horario WHERE discapacitados ='$modalidad' AND edadMinima='$edadMinima' AND edadMaxima = '$edadMaxima' AND edi_codigo = '$codigoEdi' AND etapa = '$etapa' AND estado <> 0";*/
                $query = "  WITH Horarios AS(    
                                        SELECT  hor.id,
                                        hor.edi_codigo,
                                        hor.etapa,

                                        CONVERT(VARCHAR(100),STUFF(( SELECT  ',' +
                                            CASE 
                                                WHEN tur.horaInicio <CONVERT(time, '10:00:00')
                                                THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5),2,4)
                                                      ELSE LEFT(CONVERT(VARCHAR(10),tur.horaInicio,108),5)
                                                 END 
                                            +
                                            ','+
                                             CASE 
                                                WHEN tur.horaFin <CONVERT(time, '10:00:00')
                                                THEN SUBSTRING(LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5),2,4)
                                                ELSE LEFT(CONVERT(VARCHAR(10),tur.horaFin,108),5)
                                                END 
                                            +
                                            ','+tur.dias
                                                FROM    ACADEMIA.turno AS tur
                                                WHERE   tur.horario_id = hor.id
                                                FOR
                                                XML PATH('')
                                            ), 1, 1, '')) AS turno
                                                  ,
                                            hor.discapacitados,
                                            hor.edadMinima,
                                            hor.edadMaxima
                                            FROM    ACADEMIA.horario AS hor)

                            SELECT ISNULL(COUNT(1),0) cantHorario FROM Horarios thor
                            WHERE thor.edi_codigo=$codigoEdi 
                            AND thor.etapa = $etapa AND thor.discapacitados = $modalidad
                            AND thor.edadMinima = $edadMinima AND thor.edadMaxima = $edadMaxima 
                            AND thor.turno = '$turnos' ";

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

        public function cantHorarioDisciplina($ediCodigo){

            $query = "SELECT count(1) cantHorarios from academia.horario where edi_codigo = $ediCodigo and estado!=0;";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            $cantidad = $stmt->fetch();

            return $cantidad;

        }

        public function eliminarDisciplina($ediCodigo){
           
            $query = " UPDATE  catastro.edificacionDisciplina SET edi_estado=0 where edi_codigo = $ediCodigo";
            $stmt = $this->getEntityManager()->getConnection()->prepare($query);
            $stmt->execute();
            
        }


}
