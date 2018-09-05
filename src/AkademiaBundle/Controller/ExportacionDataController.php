<?php

namespace AkademiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AkademiaBundle\Entity\Apoderado;
use AkademiaBundle\Entity\Departamento;
use AkademiaBundle\Entity\Provincia;
use AkademiaBundle\Entity\ComplejoDeportivo;
use AkademiaBundle\Entity\DisciplinaDeportiva;
use AkademiaBundle\Entity\Distrito;
use AkademiaBundle\Entity\Persona;
use AkademiaBundle\Entity\Participante;
use AkademiaBundle\Entity\Post;
use AkademiaBundle\Entity\Inscribete;
use AkademiaBundle\Entity\ComplejoDisciplina;
use AkademiaBundle\Entity\Horario;
use AkademiaBundle\Entity\Usuarios;
use Symfony\Component\HttpFoundation\StreamedResponse;
use AkademiaBundle\Component\Security\Authentication\authenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ExportacionDataController extends Controller
{

  // MODULO DE EXORTACION
  public function exportInscripcionesRegionesAction(Request $request){

    $anio = $request->query->get('ano');
    $mes = $request->query->get('mes');

    if(empty($mes)){
      $mes=-1;
    }

    $conn = $this->get('database_connection');
    
    $response = new StreamedResponse(function() use($conn,$anio,$mes) {
      $handle = fopen('php://output','w+');
              fputcsv($handle, ['Departamento','Provincia', 'Disciplina', 'Modalidad','InscritosTotales','CantidadInscritosVigentes','CantidadRetirados'],",");

      $results = $conn->query("exec ACADEMIA.inscripcionesRegiones $anio,$mes");

      while($row = $results->fetch()) {
        fputcsv($handle, array( $row['Departamento'], $row['Provincia'], $row['Disciplina'],$row['Modalidad'],$row['InscritosTotales'],$row['CantidadInscritosVigentes'],$row['CantidadRetirados']), ",");
      }
      fclose($handle);
    });
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="InscripcionesPorRegiones.csv"');
    return $response;
  }

  public function exportInscripcionesLimaCallaosAction(Request $request){

    $anio = $request->query->get('ano');
    $mes = $request->query->get('mes');

    if(empty($mes)){
      $mes=-1;
    }

    $conn = $this->get('database_connection');
    
    $response = new StreamedResponse(function() use($conn,$anio,$mes) {

    $handle = fopen('php://output','w+');
              fputcsv($handle, ['Departamento','Provincia','Complejo','Disciplina','Modalidad','InscritosTotales','CantidadInscritosVigentes','CantidadRetirados'],",");

      $results = $conn->query("exec ACADEMIA.inscripcionesLimaCallao $anio,$mes ");

      while($row = $results->fetch()) {
        fputcsv($handle, array( $row['Departamento'], $row['Provincia'], $row['Complejo'], $row['Disciplina'],$row['Modalidad'],$row['InscritosTotales'],$row['CantidadInscritosVigentes'],$row['CantidadRetirados']), ",");
      }
      fclose($handle);
    });
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="InscripcionesLimaCallaos.csv"');
    return $response;
  }

  public function exportCantidadHorariosCreadosRegionAction(Request $request){

    $idComplejo = $request->query->get('idComplejo');
    $conn = $this->get('database_connection');

    $response = new StreamedResponse(function() use($conn,$idComplejo) {

      $handle = fopen('php://output','w+');
              fputcsv($handle,['Departamento','Provincia','Complejo','Disciplina','Modalidad','CodigoHorario','Horario','Etapa','Convocatoria','Estado'],",");

      $results = $conn->query("exec ACADEMIA.cantidadHorariosCreadosRegion '$idComplejo' ");

      while($row = $results->fetch()){
        fputcsv($handle, array( $row['Departamento'], $row['Provincia'], $row['Complejo'],$row['Disciplina'],$row['Modalidad'],$row['CodigoHorario'],$row['Horario'],$row['Etapa'],$row['Convocatoria'],$row['Estado']), ",");
      }
      fclose($handle);
    });
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/csv; charset=UTF-16LE');
    $response->headers->set('Content-Disposition', 'attachment; filename="cantidadHorariosCreadosRegion.csv"');
    return $response;
  }


    public function exportCantidadUsuariosAction(Request $request){

      $conn = $this->get('database_connection');

      $response = new StreamedResponse(function() use($conn) {

        $handle = fopen('php://output','w+');
                fputcsv($handle,['Usuario','Contrasena','Complejo','Provincia','Departamento'],",");

        $results = $conn->query("EXEC ACADEMIA.cantidadUsuario ");

        while($row = $results->fetch()){
          fputcsv($handle, array( $row['Usuario'], $row['Contrasena'], $row['Complejo'],$row['Provincia'],$row['Departamento']), ",");
        }
        fclose($handle);
      });
      $response->setStatusCode(200);
      $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
      $response->headers->set('Content-Disposition', 'attachment; filename="cantidadUsuarios.csv"');
      return $response;
  }

  public function exportDataBeneficiariosAnalistaAction(Request $request)
  {

    $ano = $request->query->get('ano');
    $numMes = $request->query->get('mes');
    $departamento = $request->query->get('departamento');
    $conn = $this->get('database_connection');

    $response = new StreamedResponse(function() use($conn,$ano,$numMes,$departamento) {
      
      $query2='';
      
      if( empty($numMes) && empty($departamento) )
        $query2 = " YEAR(mov.fecha_modificacion)='$ano' ";  
      
      else if(!empty($numMes) && !empty($departamento) )
        $query2 = " YEAR(mov.fecha_modificacion)='$ano' AND ubiDpto.ubidpto='$departamento' AND MONTH(mov.fecha_modificacion)<='$numMes' ";    
      else if(!empty($numMes) && empty($departamento) )
        $query2 = "  YEAR(mov.fecha_modificacion)='$ano' AND MONTH(mov.fecha_modificacion)<='$numMes' "; 
        
      else if(empty($numMes) && !empty($departamento)  )
        $query2 = "  YEAR(mov.fecha_modificacion)='$ano' AND ubiDpto.ubidpto='$departamento' ";     
      
      $handle = fopen('php://output','w+');

      fputcsv($handle, ['Departamento','Provincia' ,'Complejo','Disciplina',"DNI",'ApellidoPaterno','ApellidoMaterno','Nombres','F.Nacimiento','Edad','Sexo','FechaMovimiento','Mes','Categoria','Matricula','Asistencia','Horario','Modalidad','Etapa','TipoSeguro','Telefono','Correo'],",");
  
      $queryConMes = "SELECT ubiDpto.ubinombre Departamento ,ubiProv.ubinombre Provincia  , ede.ede_nombre as Complejo,
                              dis.dis_descripcion as Disciplina, '\"'+RTRIM(grPar.perdni)+'\"' DNI, grPar.perapepaterno ApellidoPaterno, 
                              grPar.perapematerno ApellidoMaterno ,
                              grPar.pernombres Nombres,
                              CONVERT(varchar, grPar.perfecnacimiento, 103) FechaNacimiento,
                              (cast(datediff(dd,grPar.perfecnacimiento,GETDATE()) / 365.25 as int)) as Edad,
                              CASE grPar.persexo
                              WHEN 2 THEN 'Femenino'
                              WHEN 1 THEN 'Masculino'
                              ELSE 'Otro' END
                              AS Sexo,
                            mov.fecha_modificacion FechaMovimiento,
                                CASE MONTH(mov.fecha_modificacion) 
                                WHEN 1 THEN 'Enero'
                                WHEN 2 THEN 'Febrero'
                                WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril'
                                WHEN 5 THEN 'Mayo'
                                WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio'
                                WHEN 8 THEN 'Agosto'
                                WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre'
                                WHEN 11 THEN 'Noviembre'
                                WHEN 12 THEN 'Diciembre'
                                ELSE 'No existe Mes' END
                                AS Mes,                      
                            cat.descripcion Categoria,
              
                             CASE 
                             WHEN asis.descripcion = 'Retirado' THEN 'Retirado'
                             ELSE 'Inscrito'
                             END AS Matricula
                             ,
                             CASE
                             WHEN asis.descripcion = 'Asistente' AND MONTH(mov.fecha_modificacion) = '$numMes' THEN 'Asistente'
                             ELSE 'No Asistente' 
                             END AS Asistencia
                             ,

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
                                  ), 1, 1, ''))+',vac: '+CONVERT(VARCHAR(10),hor.vacantes) AS 'Horario',

                                CASE hor.discapacitados
                                WHEN 0 THEN 'Convencional'
                                WHEN 1 THEN 'PersonasDiscapacidad'
                                ELSE 'No se sabe' END
                                AS Modalidad,
                                CAST(
                                   CASE 
                                      WHEN hor.etapa = 0 
                                       THEN 'Formacion' 
                                      ELSE 'Masificacion' 
                                   END AS VARCHAR)
                                as 'Etapa',
                                par.tipoDeSeguro TipoSeguro,
                                grApod.pertelefono Telefono,
                                grApod.percorreo Correo
                                FROM ACADEMIA.inscribete AS ins 
                                INNER JOIN 
                                (
                                SELECT MAX(movi.fecha_modificacion) fechaModificacion ,inscri.id inscribeteId 
                                FROM ACADEMIA.participante parti
                                INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                GROUP BY inscri.id,parti.id
                                HAVING MAX(movi.fecha_modificacion)=
                                (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                WHERE
                                par2.id = parti.id
                                GROUP BY par2.id)
                                
                                ) insUltimo ON insUltimo.inscribeteId = ins.id
            
                                inner join ACADEMIA.participante par on par.id = ins.participante_id
                                inner join grpersona grPar on grPar.percodigo = par.percodigo
                                inner join ACADEMIA.horario hor on hor.id=ins.horario_id
                                inner join ACADEMIA.movimientos mov on mov.inscribete_id = ins.id
                                inner join ACADEMIA.categoria cat on cat.id=mov.categoria_id
                                inner join ACADEMIA.asistencia asis on asis.id = mov.asistencia_id
                                inner join CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                                inner join CATASTRO.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                                inner join CATASTRO.disciplina dis on dis.dis_codigo = edi.dis_codigo
                                inner join ACADEMIA.apoderado apod on apod.id = par.apoderado_id
                                inner join grpersona grApod on grApod.percodigo = apod.percodigo
                                inner join grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
                                inner join grubigeo ubiProv on ubiProv.ubiprovincia = ubi.ubiprovincia 
                                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                                WHERE
                                ubiProv.ubidpto = ubi.ubidpto AND
                                ubi.ubidistrito <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubiProv.ubidistrito = '00' AND 
                                ubiProv.ubiprovincia <> '00' AND 
                                ubiProv.ubidpto <> '00' AND
                                ubiDpto.ubidistrito = '00' AND 
                                ubiDpto.ubiprovincia = '00' AND 
                                mov.id in (
                                SELECT movi.id as id
                                FROM ACADEMIA.participante parti
                                INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                GROUP BY movi.id,parti.id
                                HAVING MAX(movi.fecha_modificacion)=
                                (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                WHERE
                                MONTH(mov2.fecha_modificacion) <= '$numMes' and
                                par2.id = parti.id 
                                GROUP BY par2.id) 
                                ) AND
                               ubiDpto.ubidpto <> '00' AND ".$query2;

                              $querySinMes = "SELECT ubiDpto.ubinombre Departamento ,ubiProv.ubinombre Provincia  , ede.ede_nombre as Complejo,
                              dis.dis_descripcion as Disciplina, grPar.perdni DNI,grPar.perapepaterno ApellidoPaterno, 
                              grPar.perapematerno ApellidoMaterno ,
                              grPar.pernombres Nombres,
                              CONVERT(varchar, grPar.perfecnacimiento, 103) FechaNacimiento,
                              (cast(datediff(dd,grPar.perfecnacimiento,GETDATE()) / 365.25 as int)) as Edad,
                              CASE grPar.persexo
                              WHEN 2 THEN 'Femenino'
                              WHEN 1 THEN 'Masculino'
                              ELSE 'Otro' END
                              AS Sexo,
                            mov.fecha_modificacion FechaMovimiento,
                                CASE MONTH(mov.fecha_modificacion) 
                                WHEN 1 THEN 'Enero'
                                WHEN 2 THEN 'Febrero'
                                WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril'
                                WHEN 5 THEN 'Mayo'
                                WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio'
                                WHEN 8 THEN 'Agosto'
                                WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre'
                                WHEN 11 THEN 'Noviembre'
                                WHEN 12 THEN 'Diciembre'
                                ELSE 'No existe Mes' END
                                AS Mes,                      
                            cat.descripcion Categoria,
                             CASE 
                             WHEN asis.descripcion = 'Retirado' THEN 'Retirado'
                             ELSE 'Inscrito'
                             END AS Matricula
                             ,
                             CASE
                             WHEN asis.descripcion = 'Asistente'
                              THEN 'Asistente'
                             ELSE 'No Asistente' 
                             END AS Asistencia
                             ,
                            
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
                                  ), 1, 1, '')) AS Horario,

                                CASE hor.discapacitados
                                WHEN 0 THEN 'Convencional'
                                WHEN 1 THEN 'PersonasDiscapacidad'
                                ELSE 'No se sabe' END
                                AS Modalidad,
                                CAST(
                                   CASE 
                                      WHEN hor.etapa = 0 
                                       THEN 'Formacion' 
                                      ELSE 'Masificacion' 
                                   END AS VARCHAR)
                                as 'Etapa',
                                par.tipoDeSeguro TipoSeguro,
                                grApod.pertelefono Telefono,
                                grApod.percorreo Correo
                                
                                FROM ACADEMIA.inscribete AS ins 
                                INNER JOIN 
                                (
                                SELECT MAX(movi.fecha_modificacion) fechaModificacion ,inscri.id inscribeteId 
                                FROM ACADEMIA.participante parti
                                INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                GROUP BY inscri.id,parti.id
                                HAVING MAX(movi.fecha_modificacion)=
                                (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                WHERE
                                par2.id = parti.id
                                GROUP BY par2.id)
                                
                                ) insUltimo ON insUltimo.inscribeteId = ins.id
            
                                inner join ACADEMIA.participante par on par.id = ins.participante_id
                                inner join grpersona grPar on grPar.percodigo = par.percodigo
                                inner join ACADEMIA.horario hor on hor.id=ins.horario_id
                                inner join ACADEMIA.movimientos mov on mov.inscribete_id = ins.id
                                inner join ACADEMIA.categoria cat on cat.id=mov.categoria_id
                                inner join ACADEMIA.asistencia asis on asis.id = mov.asistencia_id
                                inner join CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                                inner join CATASTRO.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                                inner join CATASTRO.disciplina dis on dis.dis_codigo = edi.dis_codigo
                                inner join ACADEMIA.apoderado apod on apod.id = par.apoderado_id
                                inner join grpersona grApod on grApod.percodigo = apod.percodigo
                                inner join grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
                                inner join grubigeo ubiProv on ubiProv.ubiprovincia = ubi.ubiprovincia 
                                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                                WHERE
                                ubiProv.ubidpto = ubi.ubidpto AND
                                ubi.ubidistrito <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubiProv.ubidistrito = '00' AND 
                                ubiProv.ubiprovincia <> '00' AND 
                                ubiProv.ubidpto <> '00' AND
                                ubiDpto.ubidistrito = '00' AND 
                                ubiDpto.ubiprovincia = '00' AND 
                                mov.id in (
                                SELECT movi.id as id
                                  FROM ACADEMIA.participante parti
                                  INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                  INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                  GROUP BY movi.id,parti.id
                                  HAVING MAX(movi.fecha_modificacion)=
                                  (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                  INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                  INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                  WHERE
                                  par2.id = parti.id 
                                  GROUP BY par2.id) 
                                ) AND
                                ubiDpto.ubidpto <> '00' AND ".$query2;

              if(empty($numMes)){
                $results = $conn->query($querySinMes);
              }else{
                $results = $conn->query($queryConMes);
              }
               
          while($row = $results->fetch()) {

            fputcsv($handle, array( $row['Departamento'],$row['Provincia'], $row['Complejo'], $row['Disciplina'],$row["DNI"],$row['ApellidoPaterno'],$row['ApellidoMaterno'],$row['Nombres'],$row['FechaNacimiento'],$row['Edad'],$row['Sexo'],$row['FechaMovimiento'],$row['Mes'],$row['Categoria'],$row['Matricula'],$row['Asistencia'],$row['Horario'],$row['Modalidad'],$row['Etapa'],$row['TipoSeguro'],$row['Telefono'],$row['Correo']  ), ",");
          }
          fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="beneficiarios.csv"');
        return $response;
  }
>>>>>>> desarrollo

 public function exportDataBeneficiariosComplejoAction(Request $request)
  {

    $ano = $request->query->get('ano');
    $numMes = $request->query->get('mes');
    $idComplejo = $this->getUser()->getIdComplejo();

    $conn = $this->get('database_connection');

    $response = new StreamedResponse(function() use($conn,$ano,$numMes,$idComplejo) {
       
      $handle = fopen('php://output','w+');

      fputcsv($handle, ['Departamento','Provincia' ,'Complejo','Disciplina','DNI','ApellidoPaterno','ApellidoMaterno','Nombres','F.Nacimiento','Edad','Sexo','FechaMovimiento','Mes','Categoria','Matricula','Asistencia','Horario','Modalidad','Etapa','TipoSeguro','Telefono','Correo'],",");
  
      $queryConMes = "SELECT ubiDpto.ubinombre Departamento ,ubiProv.ubinombre Provincia  , ede.ede_nombre as Complejo,
                              dis.dis_descripcion as Disciplina, grPar.perdni DNI,grPar.perapepaterno ApellidoPaterno, 
                              grPar.perapematerno ApellidoMaterno ,
                              grPar.pernombres Nombres,
                              CONVERT(varchar, grPar.perfecnacimiento, 103) FechaNacimiento,
                              (cast(datediff(dd,grPar.perfecnacimiento,GETDATE()) / 365.25 as int)) as Edad,
                              CASE grPar.persexo
                              WHEN 2 THEN 'Femenino'
                              WHEN 1 THEN 'Masculino'
                              ELSE 'Otro' END
                              AS Sexo,
                            mov.fecha_modificacion FechaMovimiento,
                                CASE MONTH(mov.fecha_modificacion) 
                                WHEN 1 THEN 'Enero'
                                WHEN 2 THEN 'Febrero'
                                WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril'
                                WHEN 5 THEN 'Mayo'
                                WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio'
                                WHEN 8 THEN 'Agosto'
                                WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre'
                                WHEN 11 THEN 'Noviembre'
                                WHEN 12 THEN 'Diciembre'
                                ELSE 'No existe Mes' END
                                AS Mes,                      
                            cat.descripcion Categoria,
              
                             CASE 
                             WHEN asis.descripcion = 'Retirado' THEN 'Retirado'
                             ELSE 'Inscrito'
                             END AS Matricula
                             ,
                             CASE
                             WHEN asis.descripcion = 'Asistente' AND MONTH(mov.fecha_modificacion) = '$numMes' THEN 'Asistente'
                             ELSE 'No Asistente' 
                             END AS Asistencia
                             ,
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
                                  ), 1, 1, ''))+

                                  ' ,'+ CONVERT(varchar(40),hor.edadMinima)+' a '+ CONVERT(varchar(40),edadMaxima)+' anos'

                                   AS Horario,
                                CASE hor.discapacitados
                                WHEN 0 THEN 'Convencional'
                                WHEN 1 THEN 'PersonasDiscapacidad'
                                ELSE 'No se sabe' END
                                AS Modalidad,
                                CAST(
                                   CASE 
                                      WHEN hor.etapa = 0 
                                       THEN 'Formacion' 
                                      ELSE 'Masificacion' 
                                   END AS VARCHAR)
                                as 'Etapa',
                                par.tipoDeSeguro TipoSeguro,
                                grApod.pertelefono Telefono,
                                grApod.percorreo Correo
                                FROM ACADEMIA.inscribete AS ins 
                                INNER JOIN 
                                (
                                SELECT MAX(movi.fecha_modificacion) fechaModificacion ,inscri.id inscribeteId 
                                FROM ACADEMIA.participante parti
                                INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                GROUP BY inscri.id,parti.id
                                HAVING MAX(movi.fecha_modificacion)=
                                (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                WHERE
                                par2.id = parti.id
                                GROUP BY par2.id)
                                
                                ) insUltimo ON insUltimo.inscribeteId = ins.id
            
                                inner join ACADEMIA.participante par on par.id = ins.participante_id
                                inner join grpersona grPar on grPar.percodigo = par.percodigo
                                inner join ACADEMIA.horario hor on hor.id=ins.horario_id
                                inner join ACADEMIA.movimientos mov on mov.inscribete_id = ins.id
                                inner join ACADEMIA.categoria cat on cat.id=mov.categoria_id
                                inner join ACADEMIA.asistencia asis on asis.id = mov.asistencia_id
                                inner join CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                                inner join CATASTRO.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                                inner join CATASTRO.disciplina dis on dis.dis_codigo = edi.dis_codigo
                                inner join ACADEMIA.apoderado apod on apod.id = par.apoderado_id
                                inner join grpersona grApod on grApod.percodigo = apod.percodigo
                                inner join grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
                                inner join grubigeo ubiProv on ubiProv.ubiprovincia = ubi.ubiprovincia 
                                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                                WHERE
                                ubiProv.ubidpto = ubi.ubidpto AND
                                ubi.ubidistrito <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubiProv.ubidistrito = '00' AND 
                                ubiProv.ubiprovincia <> '00' AND 
                                ubiProv.ubidpto <> '00' AND
                                ubiDpto.ubidistrito = '00' AND 
                                ubiDpto.ubiprovincia = '00' AND 
                                mov.id in (
                                SELECT movi.id as id
                                FROM ACADEMIA.participante parti
                                INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                GROUP BY movi.id,parti.id
                                HAVING MAX(movi.fecha_modificacion)=
                                (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                WHERE
                                MONTH(mov2.fecha_modificacion) <= '$numMes' and
                                par2.id = parti.id 
                                GROUP BY par2.id) 
                                ) AND
                               ubiDpto.ubidpto <> '00' AND YEAR(mov.fecha_modificacion)='$ano' AND ede.ede_codigo='$idComplejo' ";

                          $querySinMes = "SELECT ubiDpto.ubinombre Departamento ,ubiProv.ubinombre Provincia  , ede.ede_nombre as Complejo,
                              dis.dis_descripcion as Disciplina, grPar.perdni DNI,grPar.perapepaterno ApellidoPaterno, 
                              grPar.perapematerno ApellidoMaterno ,
                              grPar.pernombres Nombres,
                              CONVERT(varchar, grPar.perfecnacimiento, 103) FechaNacimiento,
                              (cast(datediff(dd,grPar.perfecnacimiento,GETDATE()) / 365.25 as int)) as Edad,
                              CASE grPar.persexo
                              WHEN 2 THEN 'Femenino'
                              WHEN 1 THEN 'Masculino'
                              ELSE 'Otro' END
                              AS Sexo,
                            mov.fecha_modificacion FechaMovimiento,
                                CASE MONTH(mov.fecha_modificacion) 
                                WHEN 1 THEN 'Enero'
                                WHEN 2 THEN 'Febrero'
                                WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril'
                                WHEN 5 THEN 'Mayo'
                                WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio'
                                WHEN 8 THEN 'Agosto'
                                WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre'
                                WHEN 11 THEN 'Noviembre'
                                WHEN 12 THEN 'Diciembre'
                                ELSE 'No existe Mes' END
                                AS Mes,                      
                            cat.descripcion Categoria,
              
                             CASE 
                             WHEN asis.descripcion = 'Retirado' THEN 'Retirado'
                             ELSE 'Inscrito'
                             END AS Matricula
                             ,
                             CASE
                             WHEN asis.descripcion = 'Asistente'
                              THEN 'Asistente'
                             ELSE 'No Asistente' 
                             END AS Asistencia
                             ,
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
                                  ), 1, 1, '')) +' ,'+ CONVERT(varchar(40),hor.edadMinima)+' a '+ CONVERT(varchar(40),edadMaxima)+' anos' AS Horario,
                                CASE hor.discapacitados
                                WHEN 0 THEN 'Convencional'
                                WHEN 1 THEN 'PersonasDiscapacidad'
                                ELSE 'No se sabe' END
                                AS Modalidad,
                                CAST(
                                   CASE 
                                      WHEN hor.etapa = 0 
                                       THEN 'Formacion' 
                                      ELSE 'Masificacion' 
                                   END AS VARCHAR)
                                as 'Etapa',
                                par.tipoDeSeguro TipoSeguro,
                                grApod.pertelefono Telefono,
                                grApod.percorreo Correo
                                
                                FROM ACADEMIA.inscribete AS ins 
                                INNER JOIN 
                                (
                                SELECT MAX(movi.fecha_modificacion) fechaModificacion ,inscri.id inscribeteId 
                                FROM ACADEMIA.participante parti
                                INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                GROUP BY inscri.id,parti.id
                                HAVING MAX(movi.fecha_modificacion)=
                                (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                WHERE
                                par2.id = parti.id
                                GROUP BY par2.id)
                                
                                ) insUltimo ON insUltimo.inscribeteId = ins.id
            
                                inner join ACADEMIA.participante par on par.id = ins.participante_id
                                inner join grpersona grPar on grPar.percodigo = par.percodigo
                                inner join ACADEMIA.horario hor on hor.id=ins.horario_id
                                inner join ACADEMIA.movimientos mov on mov.inscribete_id = ins.id
                                inner join ACADEMIA.categoria cat on cat.id=mov.categoria_id
                                inner join ACADEMIA.asistencia asis on asis.id = mov.asistencia_id
                                inner join CATASTRO.edificacionDisciplina edi on edi.edi_codigo = hor.edi_codigo
                                inner join CATASTRO.edificacionesdeportivas ede on ede.ede_codigo = edi.ede_codigo
                                inner join CATASTRO.disciplina dis on dis.dis_codigo = edi.dis_codigo
                                inner join ACADEMIA.apoderado apod on apod.id = par.apoderado_id
                                inner join grpersona grApod on grApod.percodigo = apod.percodigo
                                inner join grubigeo ubi on ubi.ubicodigo = ede.ubicodigo
                                inner join grubigeo ubiProv on ubiProv.ubiprovincia = ubi.ubiprovincia 
                                inner join grubigeo ubiDpto on ubiDpto.ubidpto = ubi.ubidpto
                                WHERE
                                ubiProv.ubidpto = ubi.ubidpto AND
                                ubi.ubidistrito <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubi.ubiprovincia <> '00' AND 
                                ubiProv.ubidistrito = '00' AND 
                                ubiProv.ubiprovincia <> '00' AND 
                                ubiProv.ubidpto <> '00' AND
                                ubiDpto.ubidistrito = '00' AND 
                                ubiDpto.ubiprovincia = '00' AND 
                                mov.id in (
                                SELECT movi.id as id
                                  FROM ACADEMIA.participante parti
                                  INNER JOIN ACADEMIA.inscribete inscri ON inscri.participante_id = parti.id
                                  INNER JOIN ACADEMIA.movimientos movi ON movi.inscribete_id = inscri.id
                                  GROUP BY movi.id,parti.id
                                  HAVING MAX(movi.fecha_modificacion)=
                                  (SELECT MAX(mov2.fecha_modificacion) FROM ACADEMIA.participante par2
                                  INNER JOIN ACADEMIA.inscribete ins2 ON ins2.participante_id = par2.id
                                  INNER JOIN ACADEMIA.movimientos mov2 ON mov2.inscribete_id = ins2.id
                                  WHERE
                                  par2.id = parti.id 
                                  GROUP BY par2.id) 
                                ) AND
                                ubiDpto.ubidpto <> '00' AND YEAR(mov.fecha_modificacion)='$ano' AND ede.ede_codigo='$idComplejo' ";

              if(empty($numMes)){
                $results = $conn->query($querySinMes);
              }else{
                $results = $conn->query($queryConMes);
              }
               
          while($row = $results->fetch()) {

            fputcsv($handle, array( $row['Departamento'],$row['Provincia'], $row['Complejo'], $row['Disciplina'],$row['DNI'],$row['ApellidoPaterno'],$row['ApellidoMaterno'],$row['Nombres'],$row['FechaNacimiento'],$row['Edad'],$row['Sexo'],$row['FechaMovimiento'],$row['Mes'],$row['Categoria'],$row['Matricula'],$row['Asistencia'],$row['Horario'],$row['Modalidad'],$row['Etapa'],$row['TipoSeguro'],$row['Telefono'],$row['Correo']  ), ",");
          }
          fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="beneficiarios.csv"');
        return $response;
  }

  public function exportDataAction(Request $request){

    
    $perfil = $this->getUser()->getIdPerfil();
    $idComplejo = $this->getUser()->getIdComplejo();
    
    $em = $this->getDoctrine()->getManager();

    if($perfil == 2){
      
      $mdlDepartamentosExport = $em->getRepository('AkademiaBundle:Departamento')->departamentosExport();   
      $mdlComplejoDeportivoExport = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->complejoDeportivoExport();
  
    }else if($perfil == 1 or $perfil == 3) {

    $mdlDepartamentosExport = $em->getRepository('AkademiaBundle:Departamento')->departamentosExportFind2($idComplejo);        
    $mdlComplejoDeportivoExport = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->complejoDeportivoExportFind2($idComplejo);
    }
    
    $mdlDepartamentos = $em->getRepository('AkademiaBundle:Departamento')->departamentosAll();

    $mdlDisciplinasDeportivasExport = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->disciplinaDeportivaExport();
    
    return $this->render('AkademiaBundle:Export:export.html.twig',array('departamentosExport' => $mdlDepartamentosExport,'departamentosAll' => $mdlDepartamentos,'ComplejoDeportivoExport' => $mdlComplejoDeportivoExport,'DisciplinaDeportivaExport' => $mdlDisciplinasDeportivasExport)); 
  
  }

/*
  public function exportCantidadInscritosAction(Request $request){
    
    $idComplejo = $request->query->get('idComplejo');
    $conn = $this->get('database_connection');
    $response = new StreamedResponse(function() use($conn,$idComplejo) {

      $handle = fopen('php://output','w+');
              fputcsv($handle, ['Departamento', 'Provincia', 'Complejo','Disciplina','Discapacidad','CodigoHorario','Horario','NroInscritos'],",");

      $results = $conn->query("exec ACADEMIA.cantidadInscritos '$idComplejo'");

      while($row = $results->fetch()) {
        fputcsv($handle, array( $row['Departamento'], $row['Provincia'], $row['Complejo'],$row['Disciplina'],$row['Discapacidad'],$row['CodigoHorario'],$row['Horario'],$row['NroInscritos']), ",");
      }
      fclose($handle);
    });
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="cantidadInscritos.csv"');
    return $response;
  }
*/


/*
  public function exportCantidadPreInscritosAction(Request $request){

    $idComplejo = $request->query->get('idComplejo');
    $conn = $this->get('database_connection');
    
    $response = new StreamedResponse(function() use($conn,$idComplejo) {
      $handle = fopen('php://output','w+');
              fputcsv($handle, ['Departamento', 'Provincia', 'Complejo','Disciplina','Discapacidad','CodigoHorario','Horario','NroPre-Inscritos'],",");

      $results = $conn->query("exec ACADEMIA.cantidadPreInscritos '$idComplejo' ");

      while($row = $results->fetch()) {
        fputcsv($handle, array( $row['Departamento'], $row['Provincia'], $row['Complejo'],$row['Disciplina'],$row['Discapacidad'],$row['CodigoHorario'],$row['Horario'],$row['NroPre-Inscritos']), ",");
      }
      fclose($handle);
    });
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="cantidadPreInscritos.csv"');
    return $response;
  }
*/

}

