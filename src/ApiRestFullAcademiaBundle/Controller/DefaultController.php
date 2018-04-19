<?php

namespace ApiRestFullAcademiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use ApiRestFullAcademiaBundle\Entity\PersonaApi;

class DefaultController extends FOSRestController
{

  /**
   * @Rest\Get("/beneficiario")
   */
  public function getBeneficiarioAllFindAction(Request $request)
  {
    $inicio = $request->get('inicio');
    $fin = $request->get('fin');
    $idDisciplina = $request->get('disciplina');

    $em = $this->getDoctrine()->getManager(); 
   
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->beneficiarioAllFind($inicio,$fin,$idDisciplina);
    if ( $restresult === null ) {
      return new View("No existen Beneficiarios", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Get("/disciplinaGeneral")
   */
  public function disciplinaAllGeneralAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->disciplinaAllGeneral();
    if ($restresult === null) {
      return new View("No existen Disciplinas", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Get("/departamento")
   */
  public function getDepartamentoAllAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->departamentoAll();
    if ($restresult === null) {
      return new View("No existen Departamentos", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Get("/provincia")
   */
  public function getProvinciaAllAction(Request $request)
  {
    $departamentoId = $request->get('departamentoId');

    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->provinciaAll($departamentoId);
    if ($restresult === null) {
      return new View("No existen Provincias", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }


  /**
   * @Rest\Get("/distrito")
   */
  public function getDistritoAllAction(Request $request)
  {
    $departamentoId = $request->get('departamentoId');
    $provinciaId = $request->get('provinciaId');

    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->distritoAll($departamentoId,$provinciaId);
    if ($restresult === null) {
      return new View("No existen Provincias", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Get("/complejo")
   */
  public function complejoDeportivoAllAction(Request $request)
  {
    $ubicodigo = $request->get('ubicodigo');

    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->complejoDeportivoAll($ubicodigo);
    if ($restresult === null) {
      return new View("No existen Provincias", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Get("/disciplina")
   */
  public function disciplinaAllAction(Request $request)
  {
    $complejoId = $request->get('complejoId');
      
    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->disciplinaAll($complejoId);
    if ($restresult === null) {
      return new View("No existen Provincias", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Get("/indicadores")
   */
  public function indicadoresTalentoAction(Request $request)
  {
      $idParticipante = $request->get('participanteId');

      $fc = $this->getDoctrine()->getManager();
      $indicadores = $fc->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->indicadoresTalento($idParticipante);

      if($indicadores === null){
        return new View("Error al mostrar los datos", Response::HTTP_NOT_FOUND);
      }

      return $indicadores;
  }

  /**
   * @Rest\Get("/evolucion")
   */
  public function evolucionTalentoAction(Request $request)
  {
    $participanteId = $request->get('participanteId');
    $indicadorId = $request->get('indicadorId');

    $fc = $this->getDoctrine()->getManager();
    $evolucion = $fc->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->controlesTalento($participanteId,$indicadorId);

    if( $evolucion === null){
      return new View("Se produjo un error en la petición", Response::HTTP_NOT_FOUND);
    }
    return $evolucion;

  }

  /**
   * @Rest\Get("/beneficiario-all-filter")
   */
  public function beneficiarioAllFilterAction(Request $request)
  {

    $inicio = $request->get('inicio');
    $fin = $request->get('fin');
    $anio = $request->get('anio');
    $departamentoId = $request->get('departamentoId');
    $provinciaId = $request->get('provinciaId');
    $distritoId = $request->get('distritoId');
    $complejoId = $request->get('complejoId');
    $disciplinaId = $request->get('disciplinaId');
     
    $em = $this->getDoctrine()->getManager(); 
            
    $restresult = $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->beneficiarioAllFilter($anio ,$departamentoId,$provinciaId,$distritoId,$complejoId,$disciplinaId,$inicio,$fin);

    if ($restresult === null) {
      return new View("No exite Beneficiario", Response::HTTP_NOT_FOUND);
    }
    return $restresult;
  }

  /**
   * @Rest\Post("/envio-mail")
  */
  public function postEmail(Request $request)
  {
    
    $idUser = $request->get('userId');
    $idParticipante = $request->get('participanteId');
    $comentario = $request->get('comentario');
   // $organizacion = $request->get('organizacion');
    

    if(empty($idUser) || empty($idParticipante) || empty($comentario)){
      
      return new view("LOS DATOS DE ENVIO NO ESTAN COMPLETOS", Response::HTTP_NOT_ACCEPTABLE);

    }else{

      $fc = $this->getDoctrine()->getManager();

      //DATOS PARTICIPANTE
      $datosParticipante = $fc->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->dataParticipante($idParticipante);

      $id = $datosParticipante[0]['id'];
      $dniParticipante = $datosParticipante[0]['dni'];
      $nombreParticipante = $datosParticipante[0]['nombre'];
      $disciplinaParticipante = $datosParticipante[0]['disciplina'];
      $complejo = $datosParticipante[0]['nombreComplejo'];
      $departamento = $datosParticipante[0]['departamento'];

      //DATOS DEL INTERESADOO
      $datosUsuario = $fc->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->dataUsuario($idUser);

      $nombreUsuario = $datosUsuario[0]['nombre'];
      $correoUsuario = $datosUsuario[0]['correo'];
      $dniUsuario = $datosUsuario[0]['dni'];
      $organizacionUsuario = $datosUsuario[0]['organizacion'];
      $telefonoUsuario = $datosUsuario[0]['telefono'];

      $correoEnvio = 'isabel1625.luna@gmail.com';
      
      $subject = 'NUEVO CONTACTO PARA RECLUTAMIENTO DE TALENTO DEPORTIVO por '. $nombreUsuario;
      
      $message = $nombreUsuario.' con Nº de documento de identidad: ('. $dniUsuario .') de la organización '. $organizacionUsuario. ', desea contactar al talento deportivo con ID: ('.$id.') '.$nombreParticipante.' con Nº de documento de identidad: '. $dniParticipante .' , que viene practicando la disciplina '. $disciplinaParticipante.' y que realiza sus actividades en el Complejo '.$complejo .' en el departamento de '. $departamento .'.'. "\r\n" ."\r\n".'DESCRIPCION DEL MENSAJE: '."\r\n"."\r\n". $comentario."\r\n".

      'Para responder al mensaje, contactarse con el solicitante al: '. "\r\n"  . 'Celular/Teléfono: '.$telefonoUsuario. "\r\n" ."\r\n".'Correo: '.$correoUsuario ."\r\n" ;
      $headers = 'From: soporte@ipd.gob.pe' . "\r\n" .
          'X-Mailer: PHP/' . phpversion();
      mail($correoEnvio, $subject, $message, $headers);

      return new view("El mensaje ha sido enviado satisfactoriamente", Response::HTTP_OK);

    }

  }

}

