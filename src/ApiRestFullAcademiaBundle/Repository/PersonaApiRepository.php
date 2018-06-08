<?php

namespace ApiRestFullAcademiaBundle\Repository;

/**
 * PersonaApiRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonaApiRepository extends \Doctrine\ORM\EntityRepository
{

    public function beneficiarioAllFind($inicio,$fin,$idDisciplina){

        $query =    "WITH ParticipantesOrdenados AS  
                    (  
                    SELECT
                    ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                    ins.id idInscribete,
                    per.pernombres as nombre,
                    per.perapepaterno as apellidoPaterno,
                    per.perapematerno as apellidoMaterno,
                    (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                    per.persexo as sexo,
                    ede.ede_nombre as nombreComplejo,
                    dis.dis_descripcion as nombreDisciplina,
                    ('http://appweb.ipd.gob.pe/academia/web/' + par.ficha_ruta) as ficha_tecnica,
                    ('http://appweb.ipd.gob.pe/academia/web/' + par.foto_ruta) as foto,
                    par.link as link,
                    par.visible_app as visibilidad,
                    par.comentarios as comentarios,
                    par.id as idParticipante,
                    hor.discapacitados as discapacidad      
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND dis.dis_codigo='$idDisciplina'
                    )  
                    SELECT idInscribete inscribeteId,
                            nombre talentoNombre,
                            apellidoMaterno talentoApellidoMaterno,
                            apellidoPaterno talentoApellidoPaterno,
                            edad talentoEdad,
                            sexo talentoSexo, 
                            nombreComplejo complejoDeportivoNombre,
                            nombreDisciplina disciplinaDeportivaNombre,
                            foto talentoFotoPerfil,
                            ficha_tecnica talentoFotoFicha,
                            link talentoVideo,
                            visibilidad,
                            comentarios talentoComentarios,
                            idParticipante participanteId,
                            discapacidad      
                        FROM ParticipantesOrdenados   
                        WHERE num_id  BETWEEN '$inicio' AND '$fin' AND visibilidad = 1;";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $persona = $stmt->fetchAll();
        return $persona;
    }

    public function anioAll(){
        
        $query="SELECT DISTINCT YEAR(fecha_modificacion) AS fecha FROM academia.movimientos 
                WHERE asistencia_id = 2 AND categoria_id = 4";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $anio = $stmt->fetchAll();
        return $anio;
    
    }

    public function disciplinaAllGeneral(){
        $query = "        
                    WITH ParticipantesOrdenados AS  
                    (  
                    SELECT
                    dis.dis_descripcion as disciplinaNombre,
                    dis.dis_codigo disciplinaId
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND par.visible_app = 1
                    )  
                    SELECT distinct 
                            disciplinaId,
                            disciplinaNombre  
                        FROM ParticipantesOrdenados ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinas = $stmt->fetchAll();
        return $disciplinas;
    }

    public function departamentosSinFiltro($disciplinaId){
        
         $query = " WITH DepartamentosTalentos AS  
                    (  
                    SELECT
                    COUNT(ubi.ubidpto) departamentoTalentos,
                    ubi.ubidpto departamentoId
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    INNER JOIN  grubigeo as ubi ON ubi.ubicodigo = ede.ubicodigo
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND dis.dis_codigo = $disciplinaId GROUP BY ubi.ubidpto
                    )  
                    SELECT grubi.ubidpto departamentoId,grubi.ubinombre departamentoNombre ,
                    ISNULL(departamentoTalentos,0) cantidadTalentos FROM
                    DepartamentosTalentos po  
                    FULL OUTER JOIN grubigeo grubi ON grubi.ubidpto = po.departamentoId
                    WHERE 
                    ubidistrito = '00' AND ubidpto != '00' AND ubiprovincia = '00'";
      

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamento = $stmt->fetchAll();
        return $departamento;

    }

    public function departamentoAll(){

        $query = "        
                    WITH ParticipantesOrdenados AS  
                    (  
                    SELECT
                    ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                    ubi.ubidpto departamentoId

                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    INNER JOIN grubigeo as ubi ON ubi.ubicodigo = ede.ubicodigo
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4
                    )  
                        SELECT distinct departamentoId, grubi.ubinombre departamentoNombre
                        FROM ParticipantesOrdenados po
                        INNER JOIN grubigeo grubi ON grubi.ubidpto = po.departamentoId     
                        WHERE 
                        ubidistrito = '00' AND ubidpto != '00' AND ubiprovincia = '00'";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentos = $stmt->fetchAll();
        return $departamentos;  
    }

    public function provinciaAll($departamentoId){

        $query = "        
                    WITH ParticipantesOrdenados AS  
                    (  
                    SELECT
                    ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                    ubi.ubidpto departamentoId,
                    ubi.ubiprovincia provinciaId

                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    INNER JOIN grubigeo as ubi ON ubi.ubicodigo = ede.ubicodigo
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4
                    )  
                    SELECT  distinct departamentoId,provinciaId ,grubi.ubinombre provinciaNombre   
                    FROM ParticipantesOrdenados po
                    INNER JOIN grubigeo grubi ON grubi.ubidpto = po.departamentoId
                    WHERE  
                    ubidistrito ='00' AND ubidpto != '00' AND ubiprovincia !='00' 
                    AND grubi.ubiprovincia = po.provinciaId
                    AND ubidpto = '$departamentoId' ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $provincias = $stmt->fetchAll();
        return $provincias;  
    }


    public function distritoAll($departamentoId,$provinciaId){

        $query = "        
                   WITH ParticipantesOrdenados AS  
                    (  
                    SELECT
                    ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                    ubi.ubidpto idDepartamento,
                    ubi.ubiprovincia idProvincia,
                    ubi.ubidistrito idDistrito,
                    ubi.ubicodigo disUbicodigo,
                    ede.ede_codigo idComplejo  
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    INNER JOIN grubigeo as ubi ON ubi.ubicodigo = ede.ubicodigo
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4
                    )  
                    SELECT distinct idDepartamento departamentoId ,idProvincia provinciaId ,idDistrito distritoId,
                      disUbicodigo ubicodigo ,  grubi.ubinombre distritoNombre   
                    FROM ParticipantesOrdenados po
                    INNER JOIN grubigeo grubi ON grubi.ubidpto= po.idDepartamento
                    WHERE  
                    ubidistrito != '00' AND ubidpto != '00' AND ubiprovincia !='00' 
                    AND grubi.ubiprovincia = po.idProvincia
                    AND grubi.ubidistrito = po.idDistrito AND ubidpto='$departamentoId' AND ubiprovincia='$provinciaId'";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $distritos = $stmt->fetchAll();
        return $distritos;  
    }



    public function complejoDeportivoAll($ubicodigo){

        $query = "        
                  SELECT distinct
                    ede.ede_codigo complejoId,  
                    ede.ede_nombre complejoNombre,
                    ubi.ubicodigo ubicodigo
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                    INNER JOIN grubigeo ubi on ede.ubicodigo = ubi.ubicodigo 
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND ubi.ubicodigo = '$ubicodigo' ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $complejos = $stmt->fetchAll();
        return $complejos;  
    }


    public function disciplinaAll($complejoId){
        $query = "        
                SELECT distinct
                ede.ede_codigo complejoId,  
                dis.dis_codigo disciplinaId,
                dis.dis_descripcion disciplinaNombre
                FROM  ACADEMIA.movimientos AS mov
                INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                
                INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                INNER JOIN academia.horario hor on ins.horario_id = hor.id
                INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                INNER JOIN grubigeo ubi on ede.ubicodigo = ubi.ubicodigo 
                WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND ede.ede_codigo='$complejoId' ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $disciplinas = $stmt->fetchAll();
        return $disciplinas;  
    }

    public function indicadoresTalento($idParticipante){

        $query = "WITH controlMaximo AS ( 
                    SELECT  MAX(ic.control_id) as maximoControl
                    FROM   
                    ACADEMIA.indicador_control ic INNER JOIN ACADEMIA.control co on ic.control_id = co.id 
                    WHERE co.id_participante = $idParticipante
                )

                SELECT ic.indicador_id, ind.descripcion,ind.unidad_medida,dim.descripcion as dimension, ic.control_id, ic.valor,co.id_participante,CONVERT(varchar,co.fecha,105) fecha 
                FROM 
                ACADEMIA.indicador_control ic INNER JOIN ACADEMIA.control co on ic.control_id = co.id 
                INNER JOIN controlMaximo cm on ic.control_id = cm.maximoControl
                INNER JOIN ACADEMIA.indicador ind on ind.id = ic.indicador_id 
                INNER JOIN ACADEMIA.dimension dim on dim.id = ind.id_dimension
                WHERE co.id_participante = $idParticipante ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $controles = $stmt->fetchAll();

        return $controles;

    }

    public function controlesTalento($participanteId, $indicadorId){

        $query= "SELECT ic.indicador_id, ind.descripcion,ind.unidad_medida,dim.descripcion as dimension, ic.control_id, ic.valor,co.id_participante,CONVERT(varchar,co.fecha,105) fecha 
                FROM 
                ACADEMIA.indicador_control ic INNER JOIN ACADEMIA.control co on ic.control_id = co.id 
                INNER JOIN ACADEMIA.indicador ind on ind.id = ic.indicador_id 
                INNER JOIN ACADEMIA.dimension dim on dim.id = ind.id_dimension
                WHERE co.id_participante = $participanteId AND ic.indicador_id = $indicadorId";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $indicadores = $stmt->fetchAll();

        return $indicadores;
    }


    public function departamentoTalento($disciplinaId)
    {
        $query = " WITH DepartamentosTalentos AS  
                    (  
                    SELECT
                    COUNT(ubi.ubidpto) departamentoTalentos,
                    ubi.ubidpto departamentoId
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    INNER JOIN  grubigeo as ubi ON ubi.ubicodigo = ede.ubicodigo
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND dis.dis_codigo = $disciplinaId GROUP BY ubi.ubidpto
                    )  
                    SELECT grubi.ubidpto departamentoId,grubi.ubinombre departamentoNombre ,
                    ISNULL(departamentoTalentos,0) cantidadTalentos FROM
                    DepartamentosTalentos po  
                    FULL OUTER JOIN grubigeo grubi ON grubi.ubidpto = po.departamentoId
                    WHERE 
                    ubidistrito = '00' AND ubidpto != '00' AND ubiprovincia = '00'";

        $stmt =  $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamento = $stmt->fetchAll();

        return $departamento;
    }
    
    public function departamentoDisciplina($departamentoId,$disciplinaId,$inicio,$fin){
        
        $query = "  WITH ParticipantesOrdenados AS  
                   (  
                   SELECT
                   ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                   ubi.ubidpto departamentoId,
                   ubi.ubiprovincia provinciaId,
                   ubi.ubidistrito distritoId,
                   ubi.ubicodigo ubicodigo,
                   ede.ede_codigo complejoId,
                   dis.dis_codigo disciplinaId,
                   ins.id idInscribete,
                   per.pernombres as nombre,
                   per.perapepaterno as apellidoPaterno,
                   per.perapematerno as apellidoMaterno,
                   (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                   per.perfecnacimiento as fechaNacimiento,
                   per.persexo as sexo,
                   ede.ede_nombre as nombreComplejo,
                   dis.dis_descripcion as nombreDisciplina,
                   ('http://appweb.ipd.gob.pe/academia/web/' + par.ficha_ruta) as ficha_tecnica,
                   ('http://appweb.ipd.gob.pe/academia/web/' + par.foto_ruta) as foto,
                   par.link as link,
                   par.visible_app as visibilidad,
                   par.comentarios as comentarios ,
                   par.id as idParticipante ,
                   hor.discapacitados as discapacidad,    
                   YEAR(mov.fecha_modificacion) as anio  
                   FROM  ACADEMIA.movimientos AS mov
                   INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                   GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                   
                   INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                   INNER JOIN academia.horario hor on ins.horario_id = hor.id
                   INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                   INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                   INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                   INNER JOIN academia.participante par on ins.participante_id = par.id
                   INNER JOIN grpersona per on per.percodigo = par.percodigo
                   INNER JOIN grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
                   
                   WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 AND dis.dis_codigo= $disciplinaId and ubi.ubidpto = $departamentoId AND par.visible_app = 1
                   )  
                   SELECT 
                    num_id,
                    departamentoId,
                   nombre talentoNombre,
                   apellidoMaterno talentoApellidoMaterno,
                   apellidoPaterno talentoApellidoPaterno,
                   fechaNacimiento talentoFechaNacimiento,
                   edad talentoEdad,
                   sexo talentoSexo,
                   nombreComplejo complejoDeportivoNombre,
                   nombreDisciplina disciplinaDeportivaNombre,
                   foto talentoFotoPerfil,
                   ficha_tecnica talentoFotoFicha,
                   link talentoVideo,
                   visibilidad,
                   comentarios talentoComentarios,
                   idParticipante participanteId,
                   discapacidad  
                   FROM ParticipantesOrdenados  
                   WHERE num_id between $inicio and $fin ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $departamentoDis = $stmt->fetchAll();
        return $departamentoDis; 

    }


  /*  public function beneficiarioAllFilter($anio,$departamentoId,$provinciaId,$distritoId,$complejoId,$disciplinaId,$inicio,$fin){
        

        $departamento = " AND departamentoId = '$departamentoId' ";
        $provincia = " AND provinciaId = '$provinciaId' ";
        $distrito = " AND ubicodigo = '$distritoId' ";
        $complejo = " AND complejoId='$complejoId' ";
        $disciplina = " AND disciplinaId='$disciplinaId' ";

        $querys = array( $departamento, $provincia, $distrito, $complejo, $disciplina);
        $datos = array($departamentoId,$provinciaId, $distritoId,$complejoId,$disciplinaId);

        $querynew = array();
        $queryComplemento = "";


        for ($i =0; $i < 5 ; $i ++) { 
            if( $datos[$i] != ""){

                array_push($querynew, $querys[$i]);                
            }           
        }

        $queryComplemento = implode(" ", $querynew);
          
        $queryBase = "WITH ParticipantesOrdenados AS  
                    (  
                    SELECT
                    ROW_NUMBER() OVER(ORDER BY par.id ASC) AS num_id,
                    ubi.ubidpto departamentoId,
                    ubi.ubiprovincia provinciaId,
                    ubi.ubidistrito distritoId,
                    ubi.ubicodigo ubicodigo,
                    ede.ede_codigo complejoId,
                    dis.dis_codigo disciplinaId,
                    ins.id idInscribete,
                    per.pernombres as nombre,
                    per.perapepaterno as apellidoPaterno,
                    per.perapematerno as apellidoMaterno,
                    (cast(datediff(dd,per.perfecnacimiento,GETDATE()) / 365.25 as int)) as edad,
                    per.persexo as sexo,
                    ede.ede_nombre as nombreComplejo,
                    dis.dis_descripcion as nombreDisciplina,
                    par.foto_ruta as foto,
                    par.ficha_ruta as ficha_tecnica,
                    par.link as link,
                    par.visible_app as visibilidad,
                    par.comentarios as comentarios ,
                    par.id as idParticipante ,
                    hor.discapacitados as discapacidad,    
                    YEAR(mov.fecha_modificacion) as anio   
                    FROM  ACADEMIA.movimientos AS mov
                    INNER JOIN (SELECT m.inscribete_id as mov_ins_id, MAX(m.id) mov_id FROM ACADEMIA.movimientos m
                    GROUP BY m.inscribete_id) ids ON mov.id = ids.mov_id
                    
                    INNER JOIN ACADEMIA.inscribete ins ON ins.id = ids.mov_ins_id
                    INNER JOIN academia.horario hor on ins.horario_id = hor.id
                    INNER JOIN catastro.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                    INNER JOIN catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                    INNER JOIN catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo 
                    INNER JOIN academia.participante par on ins.participante_id = par.id
                    INNER JOIN grpersona per on per.percodigo = par.percodigo 
                    INNER JOIN grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
                    
                    WHERE mov.asistencia_id=2 AND mov.categoria_id = 4 
                    )  
                    SELECT 
                    nombre talentoNombre,
                    apellidoMaterno talentoApellidoMaterno,
                    apellidoPaterno talentoApellidoPaterno,
                    edad talentoEdad,
                    sexo talentoSexo, 
                    nombreComplejo complejoDeportivoNombre,
                    nombreDisciplina disciplinaDeportivaNombre,
                    foto talentoFotoPerfil,
                    ficha_tecnica talentoFotoFicha,
                    link talentoVideo,
                    visibilidad,
                    comentarios talentoComentarios,
                    idParticipante participanteId,
                    discapacidad  
                    FROM ParticipantesOrdenados  WHERE num_id  BETWEEN '$inicio' AND '$fin' AND anio = '$anio' ";

        $query = $queryBase.$queryComplemento;

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $beneficiarioAllFilter = $stmt->fetchAll();
        return $beneficiarioAllFilter; 
    } */


    public function dataParticipante($participanteId){

        $query = "SELECT 
                dis.dis_descripcion as disciplina, 
                par.id, 
                par.dni,
                ubiDpto.ubinombre as departamento,
                ede.ede_nombre as nombreComplejo,
                (per.perapepaterno+' '+per.perapematerno+' '+per.pernombres) as nombre,
                ins.id as idInscribete
                FROM ACADEMIA.participante par
                INNER JOIN ACADEMIA.inscribete ins ON ins.participante_id = par.id
                INNER JOIN ACADEMIA.horario hor on ins.horario_id = hor.id
                INNER JOIN CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                inner join catastro.disciplina dis on dis.dis_codigo = edi.dis_codigo
                inner join catastro.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                inner join grubigeo ubi on ede.ubicodigo = ubi.ubicodigo
                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                INNER JOIN grpersona per on per.percodigo = par.percodigo                     
                WHERE 
                ubi.ubidistrito <> '00' AND 
                ubi.ubiprovincia <> '00' AND 
                ubiDpto.ubidistrito = '00' AND 
                ubiDpto.ubiprovincia = '00' AND 
                ubiDpto.ubidpto <> '00' AND 
                par.id = $participanteId AND ins.estado = 2 ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $data;
    }

    public function dataUsuario($idUsuario){

        $query = "SELECT 
                ( app.paterno+' '+ app.materno+' '+ app.nombre) as nombre,
                app.numeroDoc as dni,
                app.correo,
                app.organizacion,
                app.telefono
                FROM ACADEMIA.usuario_app app WHERE id = $idUsuario";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dataUsuario = $stmt->fetchAll();

        return $dataUsuario;
    }


    public function registrarUsuario($nombre,$paterno,$materno,$numeroDoc,$telefono,$correo,$organizacion,$estado,$password,$token,$fechaNacimiento,$sexo,$tipoDoc){

        $message = 0;
       
        $query = " INSERT INTO ACADEMIA.usuario_app(nombre,paterno,materno,numeroDoc,telefono,correo,organizacion,estado,password,token,fechaNacimiento,sexo,tipoDoc) VALUES('$nombre','$paterno','$materno','$numeroDoc','$telefono','$correo','$organizacion','$estado','$password','$token', '$fechaNacimiento','$sexo','$tipoDoc'); ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $message = 1;

        return $message;
    }

    public function activarCuentaUsuario($token){

        $query = "UPDATE ACADEMIA.usuario_app SET estado=1 WHERE token='$token';";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();

    }

    public function loginUsuarioApp($correo/*,$password*/){

        $query = "SELECT * from ACADEMIA.usuario_app  WHERE correo='$correo' /*AND password='$password'*/ AND estado = 1 ;";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $usuarioApp = $stmt->fetchAll();
        return $usuarioApp;  
    }

    public function recuperarPassword($correo){

        $query = " SELECT nombre,password FROM ACADEMIA.usuario_app WHERE estado=1 AND correo='$correo'; ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $usuarioApp = $stmt->fetchAll();
        return $usuarioApp;  
    }

    public function recuperarToken($correo){

        $query = " SELECT token FROM ACADEMIA.usuario_app WHERE  correo='$correo'; ";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $tokenUser = $stmt->fetchAll();
        return $tokenUser;  
    }

}
