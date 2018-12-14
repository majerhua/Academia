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

    public function complejosCrearAction(Request $request){


        $nombre = $request->get('nombre');
        $ubicodigo = $request->get('ubicodigo');
        $tipo = $request->get('tipo');
        $estado = $request->get('estado');
        $latitud = $request->get('latitud');
        $longitud = $request->get('longitud');
        $usuario = $this->getUser()->getId();

        if( 
            isset($nombre) && !empty($nombre) && 
            isset($ubicodigo) && !empty($ubicodigo) && 
            isset($tipo) && !empty($tipo) && 
            isset($estado) && !empty($estado) &&
            isset($latitud) && !empty($latitud) &&
            isset($longitud) && !empty($longitud) &&
            isset($usuario) && !empty($usuario) 
        ){

            $em = $this->getDoctrine()->getManager(); 
            
            $estadoCrear = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->crearComplejoDeportivo($nombre,$ubicodigo,$tipo,$estado,$latitud,$longitud,$usuario);
            $complejosDeportivos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();
            if( $estadoCrear != 0){

                echo $this->renderView('AkademiaBundle:Complejo:table_complejo.html.twig',
                        array(
                            'complejosDeportivos' => $complejosDeportivos
                        )
                    );
                exit;    
            }else{

                echo "0";//Ocurrio un Error a la hora de editar
                exit;
            }

        }else{
            echo "-1";//EXISTEN CAMPOS VACIOS O NULOS
            exit;
        } 
    }


    public function complejosEditarAction(Request $request){

        $codigo = $request->get('codigo');
        $nombre = $request->get('nombre');
        $ubicodigo = $request->get('ubicodigo');
        $tipo = $request->get('tipo');
        $estado = $request->get('estado');
        $latitud = $request->get('latitud');
        $longitud = $request->get('longitud');
        $usuario = $this->getUser()->getId();

        if( isset($codigo) && !empty($codigo) &&
            isset($nombre) && !empty($nombre) && 
            isset($ubicodigo) && !empty($ubicodigo) && 
            isset($tipo) && !empty($tipo) && 
            isset($estado) && !empty($estado) &&
            isset($latitud) && !empty($latitud) &&
            isset($longitud) && !empty($longitud) &&
            isset($usuario) && !empty($usuario) 
        ){

            $em = $this->getDoctrine()->getManager(); 
            
            $estadoUpdate = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->editarComplejoDeportivo($codigo,$nombre,$ubicodigo,$tipo,$estado,$latitud,$longitud,$usuario);
            $complejosDeportivos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();
            if( $estadoUpdate != 0){

                echo $this->renderView('AkademiaBundle:Complejo:table_complejo.html.twig',
                        array(
                            'complejosDeportivos' => $complejosDeportivos
                        )
                    );
                exit;    
            }else{

                echo "0";//Ocurrio un Error a la hora de editar
                exit;
            }

        }else{
            echo "-1";//EXISTEN CAMPOS VACIOS O NULOS
            exit;
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
