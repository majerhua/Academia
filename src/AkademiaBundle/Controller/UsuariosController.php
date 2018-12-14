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

class UsuariosController extends Controller
{
	public function usuariosAction(Request $request,$idTemporada){

		$em = $this->getDoctrine()->getManager();
       
        $usuario = $this->getUser()->getId();
		$descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);
		$perfilUsuarios = $em->getRepository('AkademiaBundle:Usuarios')->getPerfilUsuariosAll();
		$departamentos = $em->getRepository('AkademiaBundle:Distrito')->getDepartamentos();
		$provincias = $em->getRepository('AkademiaBundle:Distrito')->getProvincias();
		$complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();
		$usuarios = $em->getRepository('AkademiaBundle:Usuarios')->getUsuariosAll();

      	return $this->render('AkademiaBundle:Usuario:usuarios.html.twig', 
					      		array( 
					      				'descripcionTemporada' => $descripcionTemporada,
					      				'idTemporada' => $idTemporada,
					      				'perfilUsuarios' => $perfilUsuarios,
					      				'departamentos'=> $departamentos,
					      				'provincias' => $provincias,
					      				'complejos' => $complejos,
					      				'usuarios' => $usuarios
					      			)
      						);
	}

    public function usuariosCrearAction(Request $request){


    	$perfilUsuario = $request->get('perfilUsuario');

    	$confRol = $this->container->getParameter('rol');

    	$confPerfilUsuario = $this->container->getParameter('perfilUsuario');

        $tipoDocumento = $request->get('tipoDocumento');
        $numeroDocumento = $request->get('numeroDocumento');
        $nombre = $request->get('nombre');
        $apellidoPaterno = $request->get('apellidoPaterno');
        $apellidoMaterno = $request->get('apellidoMaterno');
        $telefono = $request->get('telefono');
        $correo = $request->get('correo');
        $username = $request->get('username');
        $password = $request->get('password');
        $coleccion = $request->get('coleccion');
        $usuario = $this->getUser()->getId();


        $em = $this->getDoctrine()->getManager(); 
        $usuarioDuplicado = $em->getRepository('AkademiaBundle:Usuarios')->verificarDuplicidadUsuario($username);

        $flag = TRUE;

        if(!empty($usuarioDuplicado)){
        	$flag = FALSE;
        	echo "-2"; //Existe Usuario Con el Username solicitado
        	exit;
        }

        if( 
            isset($tipoDocumento) && !empty($tipoDocumento) && 
            isset($numeroDocumento) && !empty($numeroDocumento) && 
            isset($nombre) && !empty($nombre) && 
            isset($apellidoPaterno) && !empty($apellidoPaterno) &&
            isset($apellidoMaterno) && !empty($apellidoMaterno) &&
            isset($telefono) && !empty($telefono) &&
            isset($correo) && !empty($correo) && 
            isset($username) && !empty($username) && 
            isset($password) && !empty($password) && 
            isset($coleccion) && !empty($coleccion) &&
            $flag == true
        ){

            $estadoCrear = $em->getRepository('AkademiaBundle:Usuarios')->crearUsuario($tipoDocumento,$numeroDocumento,$nombre,$apellidoPaterno,$apellidoMaterno,$telefono,$correo,$username,$password,$coleccion,$perfilUsuario,$confRol[$perfilUsuario],$usuario,$confPerfilUsuario);
            
            if( $estadoCrear != 0){

            	$usuarios = $em->getRepository('AkademiaBundle:Usuarios')->getUsuariosAll();
    	        echo $this->renderView('AkademiaBundle:Usuario:table_usuario.html.twig',
					    	        	array(
					    	        		'perfilUsuarioId'=>$perfilUsuario,
					    	        		'usuarios' => $usuarios
					    	        	)
	    					);
        		exit;
                // echo "1";
            }else{
                echo "0";//Ocurrio un Error a la hora de editar
                exit;
            }

        }else{
            echo "-1";//EXISTEN CAMPOS VACIOS O NULOS
            exit;
        } 
    }

}
