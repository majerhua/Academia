<?php

namespace AkademiaBundle\Repository;

/**
 * ParticipanteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ParticipanteRepository extends \Doctrine\ORM\EntityRepository
{
/****** INICIO PRUEBA ***/

    public function envioEmailIndividual($codigoInscripcion){

        $query = "SELECT 
                    ins.id as inscribeteId,
                    perApod.pernombres as nombreApoderado,
                    perApod.percorreo correoApoderado
                    FROM
                    ACADEMIA.inscribete ins 
                    INNER JOIN ACADEMIA.participante par ON par.id = ins.participante_id
                    INNER JOIN ACADEMIA.apoderado apod ON apod.id = par.apoderado_id
                    INNER JOIN grpersona as perApod ON perApod.percodigo = apod.percodigo
                    WHERE ins.id = '$codigoInscripcion';";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $participantes = $stmt->fetchAll();
        return $participantes;
    }

    public function envioEmailMasivo($inicio,$fin){

        $query = "WITH ParticipantesOrdenados AS(
                    SELECT 
                    ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                    ins.id as inscribeteId,
                    perApod.pernombres as nombreApoderado,
                    perApod.percorreo correoApoderado
                    FROM
                    ACADEMIA.inscribete ins 
                    INNER JOIN ACADEMIA.participante par ON par.id = ins.participante_id
                    INNER JOIN ACADEMIA.apoderado apod ON apod.id = par.apoderado_id
                    INNER JOIN grpersona as perApod ON perApod.percodigo = apod.percodigo
                    WHERE ins.id >= 37301 )

                    SELECT *FROM ParticipantesOrdenados
                    WHERE num_id BETWEEN '$inicio' AND '$fin';";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $participantes = $stmt->fetchAll();
        return $participantes;
    }
/****FIN PRUEBA ***/

    public function getPreInscripcionUnica($dniParticipante,$idTemporada){
        $query = "EXEC ACADEMIA.preInscripcionUnica '$dniParticipante',$idTemporada ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $idInscribeteOrDataParticipante = $stmt->fetchAll();
        return $idInscribeteOrDataParticipante;
    }

    public function getParticipantePrueba(){
        $query = "SELECT id,dni FROM ACADEMIA.participante ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $participante = $stmt->fetchAll();
        return $participante;
    }

	public function getbuscarParticipante($dni){

        $query = "SELECT id from ACADEMIA.participante where dni='$dni'";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dni = $stmt->fetchAll();

    	return $dni;
	}

    public function getbuscarParticipantePersona($dni){

        $query ="SELECT percodigo as id from grpersona where perdni='$dni'";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dni = $stmt->fetchAll();

        return $dni;
    }

    public function getbuscarParticipanteApoderado($dni){

        $query = "SELECT top 1 id,apoderado_id from ACADEMIA.participante where dni='$dni' and estado=0 order by id DESC";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dni = $stmt->fetchAll();

        return $dni;
    }


    public function maxDniAcademiaPart($dni){

        $query = "SELECT MAX(id) as id from academia.participante where dni = '$dni' group by(dni)";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $codigo = $stmt->fetchAll();

        return $codigo;
    }

    public function getActualizarApoderado($idApod, $idParticipante){
        $query = "UPDATE ACADEMIA.participante SET apoderado_id ='$idApod'  WHERE id='$idParticipante'; ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }

    
    public function getActualizarParticipanteFicha($idParticipante, $ficha){
        $query = "UPDATE ACADEMIA.inscribete SET participante_id ='$idParticipante'  WHERE id='$ficha'; ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }

    public function getMostrarSeleccionados($idTemporada){
        
        $query = "SELECT 
                (per.perapepaterno+' '+per.perapematerno+' '+per.pernombres) as nombre,
                par.id as idParticipante,
                per.perdni as dni, 
                (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                per.persexo as sexo,
                ins.id as idInscribete,
                mov.categoria_id as idCategoria,
                mov.asistencia_id as idAsistencia,
                mov.fecha_modificacion as fechita,
                dis.dis_descripcion as nombreDisciplina,
                ede.ede_nombre as nombreComplejo,
                ubiDpto.ubinombre as departamento
                FROM 
                ACADEMIA.inscribete ins 
                inner join (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
                FROM ACADEMIA.movimientos m
                GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
                inner join academia.horario hor on ins.horario_id = hor.id
                inner join catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                inner join catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                inner join catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                inner join grubigeo ubi on ede.ubicodigo = ubi.ubicodigo
                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                inner join academia.participante par on ins.participante_id = par.id
                inner join grpersona per on per.percodigo = par.percodigo
                inner join academia.movimientos mov on mov.id = ids.mov_id
                WHERE 
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubiDpto.ubidistrito = '00' AND 
                ubiDpto.ubiprovincia = '00' AND 
                ubiDpto.ubidpto <> '00' AND
                ins.estado = 2 AND
                edi.temporada_id= $idTemporada AND
                mov.asistencia_id != 3   AND (  mov.categoria_id = 2 OR mov.categoria_id= 3 )";
  
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $seleccionado = $stmt->fetchAll();

        return $seleccionado;

    }

    public function getMostrarTalento($idParticipante){

         $query = " SELECT 
                (per.perapepaterno+' '+per.perapematerno+' '+per.pernombres) as nombre,
                par.id as idParticipante,
                par.dni as dni, 
                par.link as link,
                par.ficha_ruta as ficha,
                par.foto_ruta as foto,
                par.comentarios as comentarios,
                par.visible_app as visible,
                par.discapacitado as discapacidad,
                (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                per.persexo as sexo,
                dis.dis_descripcion as nombreDisciplina,
                ede.ede_nombre as nombreComplejo,
                ins.id as idInscribete
                FROM 
                ACADEMIA.inscribete ins 
                inner join academia.horario hor on ins.horario_id = hor.id
                inner join catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                inner join catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                inner join catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                inner join academia.participante par on ins.participante_id = par.id
                inner join grpersona per on per.percodigo = par.percodigo
                WHERE par.id = $idParticipante and ins.estado= 2";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $talento = $stmt->fetchAll();

        return $talento;

    }

    public function getMostrarControles($idParticipante){

        $query = "EXEC dbo.mostrarIndicadoresControl $idParticipante";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $controles = $stmt->fetchAll();

        return $controles;

    }

    public function getNumeroControl($idParticipante){
       
        $query = "SELECT distinct(control_id) as id, co.fecha as fecha FROM ACADEMIA.indicador_control ic
                  INNER JOIN ACADEMIA.control co on ic.control_id = co.id where co.id_participante = $idParticipante";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $num = $stmt->fetchAll();

        return $num;

    }

    public function guardarTalento($idParticipante, $link, $ficha,$foto, $comentarios){

        $query = "UPDATE academia.participante  SET link='$link',ficha_ruta='$ficha',comentarios='$comentarios', foto_ruta='$foto' WHERE id=$idParticipante";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();

    }

    public function cantidadControl($idParticipante){

        $query = "SELECT count(id) as cantidad from academia.control where id_participante = $idParticipante";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $datos = $stmt->fetchAll();

        return $datos;
    }

    public function registrarMovEva($idInscribete, $usuario){

        $query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id,fecha_modificacion, usuario_valida)
                  values(3,2,$idInscribete,getdate(),$usuario)";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }

    public function registrarMovTal($idInscribete, $usuario){

        $query = "INSERT into academia.movimientos(categoria_id, asistencia_id, inscribete_id, fecha_modificacion, usuario_valida)
                 values(4,2,$idInscribete, getdate(),$usuario)";

        $stmt =$this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }

    public function nuevoControl($fecha,$idParticipante,$usuario){
        
        $query = "EXEC dbo.InsertarControl '$fecha',$idParticipante,1,$usuario";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $control = 1;
        return $control;
        
    }

    public function retornoIdControl($idParticipante){

        $query = "SELECT max(id) as id from academia.control where id_participante = $idParticipante";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $control = $stmt->fetchAll();
        return $control;

    }


    public function nuevoControlIndicador($peso,$talla,$ind30mt,$saltoLargo,$lanzPelota,$saltoV,$abdominales,$agCubitoDorsal,$idNewControl,$usuario){

        $query = "EXEC dbo.InsertarIndicadoresControl $peso,$talla,$ind30mt,$saltoLargo,$lanzPelota,$saltoV,$abdominales,$agCubitoDorsal,$idNewControl,$usuario";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute(); 

    }
    
    public function actualizarControlIndicador($fechaDato,$peso,$talla,$ind30mt,$saltoLargo,$lanzPelota,$saltoV,$abdominales,$agCubitoDorsal,$idControl){

        $query = "EXEC dbo.actualizarIndicadorControl '$fechaDato',$peso,$talla,$ind30mt,$saltoLargo,$lanzPelota,$saltoV,$abdominales,$agCubitoDorsal,$idControl";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $control = 1;
        return $control;
    }

    public function listarControlInd($idParticipante, $idControl){
        
        $query = "SELECT ic.indicador_id, ic.control_id, ic.valor,co.id_participante,CONVERT(date,co.fecha,111) as fecha FROM ACADEMIA.indicador_control ic INNER JOIN ACADEMIA.control co on ic.control_id = co.id where co.id_participante = $idParticipante and ic.control_id = $idControl";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $datos = $stmt->fetchAll();
    
        return $datos;    
    }

    public function actualizarVisibilidad($idParticipante, $visibilidad){
        
        $query = "UPDATE academia.participante set visible_app = $visibilidad where id = $idParticipante";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
    }


    public function listarTalentos($idTemporada){

        $query ="SELECT 
                (per.perapepaterno+' '+per.perapematerno+' '+per.pernombres) as nombre,
                par.id as idParticipante,
                par.visible_app as visibilidad,
                per.perdni as dni, 
                (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                per.persexo as sexo,
                ins.id as idInscribete,
                mov.categoria_id as idCategoria,
                mov.asistencia_id as idAsistencia,
                mov.fecha_modificacion as fechita,
                dis.dis_descripcion as nombreDisciplina,
                ede.ede_nombre as nombreComplejo,
                ubiDpto.ubinombre as departamento
                FROM 
                ACADEMIA.inscribete ins 
                inner join (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
                FROM ACADEMIA.movimientos m
                GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
                inner join academia.horario hor on ins.horario_id = hor.id
                inner join catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                inner join catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                inner join catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo   
                inner join academia.participante par on ins.participante_id = par.id
               
                inner join academia.movimientos mov on mov.id = ids.mov_id
                inner join grpersona per on per.percodigo = par.percodigo
                inner join grubigeo ubi on ede.ubicodigo = ubi.ubicodigo
                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                WHERE 
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubiDpto.ubidistrito = '00' AND 
                ubiDpto.ubiprovincia = '00' AND 
                ubiDpto.ubidpto <> '00' AND
                ins.estado = 2 AND 
                edi.temporada_id = '$idTemporada' AND
                mov.categoria_id = 4 AND 
                mov.asistencia_id != 3;";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $datos = $stmt->fetchAll();

        return $datos;
    }


    public function listarTalentosByDepCompDis($idTemporada,$departamentoId,$complejoId,$disciplinaId){

        $arrayLetter = [" AND dis.dis_codigo = $disciplinaId"," AND ede.ede_codigo = 
                $complejoId", " AND ubiDpto.ubidpto = $departamentoId"];
        $arrayNum = [$disciplinaId,$complejoId,$departamentoId];
        $arrayAux = [];

        for($i =0 ;$i<count($arrayNum);$i++){
            
            if($arrayNum[$i]!= "")
                array_push($arrayAux,$arrayLetter[$i]);
        }

        $subQuery = implode(" ", $arrayAux);


        $query ="SELECT 
                (per.perapepaterno+' '+per.perapematerno+' '+per.pernombres) as nombre,
                per.pernombres as nombreTalento,
                per.perapepaterno as apellidoPaterno,
                per.perapematerno as apellidoMaterno,
                per.perfecnacimiento as fechaNacimiento,
                par.id as idParticipante,
                par.visible_app as visibilidad,
                per.perdni as dni, 
                (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                per.persexo as sexo,
                ins.id as idInscribete,
                mov.categoria_id as idCategoria,
                mov.asistencia_id as idAsistencia,
                mov.fecha_modificacion as fechita,
                dis.dis_descripcion as nombreDisciplina,
                ede.ede_nombre as nombreComplejo,
                ubiDpto.ubinombre as departamento
                FROM 
                ACADEMIA.inscribete ins 
                inner join (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id
                FROM ACADEMIA.movimientos m
                GROUP BY m.inscribete_id) ids ON ins.id = ids.mov_ins_id
                inner join academia.horario hor on ins.horario_id = hor.id
                inner join catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                inner join catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                inner join catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo   
                inner join academia.participante par on ins.participante_id = par.id
               
                inner join academia.movimientos mov on mov.id = ids.mov_id
                inner join grpersona per on per.percodigo = par.percodigo
                inner join grubigeo ubi on ede.ubicodigo = ubi.ubicodigo
                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                WHERE 
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubiDpto.ubidistrito = '00' AND 
                ubiDpto.ubiprovincia = '00' AND 
                ubiDpto.ubidpto <> '00' AND
                ins.estado = 2 AND 
                edi.temporada_id = '$idTemporada' AND
                mov.categoria_id = 4 AND 
                mov.asistencia_id != 3 ".$subQuery;

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $datos = $stmt->fetchAll();

        return $datos;
    }

}

