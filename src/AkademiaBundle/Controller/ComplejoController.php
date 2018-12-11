<?php

namespace AkademiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AkademiaBundle\Entity\Usuarios;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ComplejoController extends Controller
{

  	public function complejosAction(Request $request,$idTemporada){

		$em = $this->getDoctrine()->getManager();
        $complejosDeportivos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();

        $usuario = $this->getUser()->getId();
		$descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);


        $departamentos = $em->getRepository('AkademiaBundle:Distrito')->getDepartamentos( );
        $provincias = $em->getRepository('AkademiaBundle:Distrito')->getProvincias( );
        $distritos = $em->getRepository('AkademiaBundle:Distrito')->getDistritos( );

      	return $this->render('AkademiaBundle:Complejo:complejo.html.twig', 
					      		array(  'complejosDeportivos' => $complejosDeportivos,
					      				'descripcionTemporada' => $descripcionTemporada,
					      				'idTemporada' => $idTemporada,
					      				'departamentos'=> $departamentos,
					      				'provincias' => $provincias,
					      				'distritos' => $distritos 
					      			)
      						);
    }

    public function complejosEditarAction(Request $request){

    	if( $request->isXmlHttpRequest() ){

             
            $ubigeo = $request->request->get('ediUbigeo'); 
            $nombre = $request->request->get('ediNombre');
            $tipo =   $request->request->get('tipo');

            $idComplejo = $this->getUser()->getIdComplejo();
            $usuario = $this->getUser()->getId();

            $em = $this->getDoctrine()->getManager();
            $estado = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getCompararEstado($idComplejo, $idDisciplina,$idTemporada);
        
            if(!empty($estado)){

                $em = $this->getDoctrine()->getManager();
                $estadoActual = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getCambiarEstado($idComplejo, $idDisciplina);

                $mensaje = 1;  
            
            }else{

                $estado = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->crearComplejoDisciplina($idComplejo, $idDisciplina,$usuario,$idTemporada);
 
                $mensaje = $estado;            
            }

            return new JsonResponse($mensaje); 
        }
    }

    public function getComplejoByIdAction(Request $request){

    	if( $request->isXmlHttpRequest() ){

            $codigo = $request->get('codigoComplejo'); 
            $em = $this->getDoctrine()->getManager();
            $complejo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejoById($codigo);
        
            return new JsonResponse($complejo); 
        }
    }


}
