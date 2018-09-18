<?php

namespace AkademiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TemporadaController extends Controller
{

   public function temporadaCrearAction(Request $request){
    
        if($request->isXmlHttpRequest()){

            $crearAnio = $request->get('crear-anio');
            $crearCiclo = $request->get('crear-ciclo');
            $crearApertura  = $request->get('crear-apertura');
            $crearPreInscripcion = $request->get('crear-pre-inscripcion');
            $crearInicioClases = $request->get('crear-inicio-clases');
            $crearCierreClases = $request->get('crear-cierre-clases');
            $crearFechaSubsanacion = $request->get('crear-fecha-subsanacion');

            $em = $this->getDoctrine()->getManager();
            $estadoUpdTemp = NULL;
            
            $codeCrearTemporada = $em->getRepository('AkademiaBundle:Temporada')->crearTemporada($crearAnio,$crearCiclo,$crearApertura,$crearPreInscripcion,$crearInicioClases,$crearCierreClases,$crearFechaSubsanacion);

            $temporadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadas();


            if( $codeCrearTemporada == 1 ){

                echo $this->renderView('AkademiaBundle:Migracion_Asistencia:table_temporada.html.twig',array('estadoUpdTemp'=>$codeCrearTemporada,'temporadas'=>$temporadas));
                exit;

            }else
                return new JsonResponse($codeCrearTemporada);
            
        }
    }

   public function temporadaModificarAction(Request $request){
    
        if($request->isXmlHttpRequest()){

        	$idTemporada = $request->get('id-temporada');
            $editarAnio = $request->get('editar-anio');
            $editarCiclo = $request->get('editar-ciclo');
            $editarApertura  = $request->get('editar-apertura');
            $editarPreInscripcion = $request->get('editar-pre-inscripcion');
            $editarInicioClases = $request->get('editar-inicio-clases');
            $editarCierreClases = $request->get('editar-cierre-clases');
            $editarFechaSubsanacion = $request->get('editar-fecha-subsanacion');

            $em = $this->getDoctrine()->getManager();
        	$estadoUpdTemp = NULL;
        	
            $codeUpdateTemporada = $em->getRepository('AkademiaBundle:Temporada')->updateTemporada($editarAnio,$editarCiclo,$editarApertura,$editarPreInscripcion,$editarInicioClases,$editarCierreClases,$editarFechaSubsanacion,$idTemporada);
            $temporadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadas();
            if( $codeUpdateTemporada != 0 ){

            	echo $this->renderView('AkademiaBundle:Migracion_Asistencia:table_temporada.html.twig',array('estadoUpdTemp'=>$estadoUpdTemp,'temporadas'=>$temporadas));
	        	exit;

            }else
            	return new JsonResponse($codeUpdateTemporada);
            
        }
    }

	public function temporadaPrincipalAction(Request $request){
    
        $em = $this->getDoctrine()->getManager(); 
        $estadoUpdTemp = NULL;

        $temporadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadas();
        return $this->render('AkademiaBundle:Migracion_Asistencia:temporada.html.twig', array( 'estadoUpdTemp'=>$estadoUpdTemp , 'temporadas' => $temporadas));
    }
}
