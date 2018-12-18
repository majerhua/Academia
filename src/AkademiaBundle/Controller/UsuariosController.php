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

    public function usuariosEditarAction(Request $request){

        $usuarioId = $request->get('usuarioId');
        $perfilUsuario = $request->get('perfilUsuario');
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

        $em = $this->getDoctrine()->getManager(); 
        $usuarioDuplicado = $em->getRepository('AkademiaBundle:Usuarios')->verificarDuplicidadUsuario($username);

        var_dump($usuarioDuplicado);
        exit;
        
        $flag = TRUE;

        if(!empty($usuarioDuplicado)){
            $flag = FALSE;
            echo "-2"; //Existe Usuario Con el Username solicitado
            exit;
        }

        $usuario = $this->getUser();
        $usuarioIdSis = $usuario->getId();

        if( 
            isset($usuarioId) && !empty($usuarioId) &&
            isset($perfilUsuario) && !empty($perfilUsuario) &&
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
            $flag == TRUE
        ){
            $estadoEditar = $em->getRepository('AkademiaBundle:Usuarios')->editarUsuario($usuarioId,$perfilUsuario,$tipoDocumento,$numeroDocumento,$nombre,$apellidoPaterno,$apellidoMaterno,$telefono,$username,$password);

            if($estadoEditar == "1"){
               $coleccionInicio = $em->getRepository('AkademiaBundle:Usuarios')->getUsuarioUbigeoByUsuarioId($usuarioId);

                if( !empty($coleccionInicio) ) //SI EXISTEN MODALIDADES
                  $arrayColeccionInicio = explode(',',$coleccionInicio);
                else
                  $arrayColeccionInicio = Array();

                if(count($coleccion) >= count($arrayColeccionInicio)){

                  for ($i=0; $i < count($coleccion) ; $i++) { 

                      $em = $this->getDoctrine()->getManager();
                      if($i < count($arrayColeccionInicio)){
                          $em->getRepository('SudJuvenilesBundle:Inscripcion')->updateUsuarioUbigeo($usuarioId, $arrayColeccionInicio[$i], $coleccion[$i],$usuarioIdSis);
                      }else{
                          $em->getRepository('SudJuvenilesBundle:Inscripcion')->insertUsuarioUbigeo($usuarioId,$coleccion[$i],$usuarioIdSis);
                      }    
                  }
                }

                else if( count($coleccion) < count($arrayColeccionInicio)  && count($coleccion)> -1 ){

                    $em = $this->getDoctrine()->getManager();
                    $em->getRepository('SudJuvenilesBundle:Inscripcion')->removeUsuarioUbigeo($usuarioId);

                    for ($i=0; $i < count($coleccion) ; $i++) { 

                        $em->getRepository('SudJuvenilesBundle:Inscripcion')->insertUsuarioUbigeo($usuarioId,$coleccion[$i]);
                    }
                }

                echo "1";//SE MODIFICO CORRECTAMENTE
                exit;

            }else{
                echo "0"; //OCURRIO UN ERROR EN EL SISTEMA
                exit;
            }            
        }else{
            echo "-1";//EXISTEN CAMPOS VACIOS
            exit;
        }
    }

    public function getDatosUsuariosByEditarAction(Request $request){

        $confPerfilUsuario = $this->container->getParameter('perfilUsuario');
        $usuarioId = $request->get('usuarioId');
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('AkademiaBundle:Usuarios')->getUsuarioById($usuarioId);

        if( $usuario[0]["id_perfil"] == $confPerfilUsuario['macro'] ){
            $ubigeos = $em->getRepository('AkademiaBundle:Usuarios')->getUbigeoByUsuarioId($usuario[0]["id"]);

        }else if( $usuario[0]["id_perfil"] == $confPerfilUsuario['monitor'] ){
            $ubigeos = $em->getRepository('AkademiaBundle:Usuarios')->getUbigeoByUsuarioId($usuario[0]["id"]);
        }

        $usuario[0]["coleccion"] = $ubigeos;

        return new JsonResponse($usuario);
    }

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
