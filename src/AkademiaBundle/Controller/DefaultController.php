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
use AkademiaBundle\Component\Security\Authentication\authenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
  

    // CONTADOR - INICIO DE LA TEMPORADA (CRONOMETRO)
    public function contadorAction(Request $request){
      
        return $this->render('AkademiaBundle:Default:contador.html.twig' );
    }

    // VISTA CONSULTAS O PREGUNTAS FRECUENTES
    public function consultaAction(Request $request){

        return $this->render('AkademiaBundle:Default:cuestions.html.twig');
    }

    //PANEL PRINCIPAL 
    public function panelAction(Request $request,$idTemporada){

        $confPerfilUsuario = $this->container->getParameter('perfilUsuario');

        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getUser();

        $rol = $usuario->getRoles()[0];
        $idPerfil = $usuario->getIdPerfil();

        $nombrePerfilUsuario = $this->container->getParameter('nombrePerfilUsuario');
        $nombrePerfil = $nombrePerfilUsuario[$idPerfil];


        if( $idPerfil == $confPerfilUsuario['administrador'] )
            $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadasAdministrador();
        else
            $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadas();

        if ( $idTemporada == 0 ){
            if ($idPerfil == $confPerfilUsuario['administrador']){
                $idTemporada = $temporadasHabilitadas[0]['temporadaId'];
            }else{
                if( !empty($temporadasHabilitadas) ){
                    $temporadaActiva = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();
                    if(!empty($temporadaActiva)){
                        $idTemporada = $temporadaActiva[0]['temporadaId'];
                    }else{
                        $idTemporada = $temporadasHabilitadas[0]['temporadaId'];
                    }
                }
            }
        }

        // if( $roles[0] == "ROLE_ANALISTA" ){
        //     $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadasAnalista();
        // }else{
        //     $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadas();
        // }

        
        // if($idTemporada == 0){
        //    $temporadaArray = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva(); 

        //     if(!empty($temporadaArray)){
        //         $idTemporada = $temporadaArray[0]['temporadaId'];
        //     }else{

        //         if(!empty($temporadasHabilitadas))
        //             $idTemporada = $temporadasHabilitadas[0]['temporadaId'];
        //         else
        //             $idTemporada = NULL;
                
        //     }  
        // }

        
        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        if(empty($descripcionTemporada)){
            $descripcionTemporada = NULL;
        }

        if( $idPerfil != $confPerfilUsuario['administrador'] ){ 

            if(!empty($idTemporada)){
                $arrayFaseTemporada = $em->getRepository('AkademiaBundle:Temporada')->faseTemporadaActiva($idTemporada);
                $faseTemporada = $arrayFaseTemporada[0]['fase'];
            }else{
                $faseTemporada=NULL;
            }

          return $this->render('AkademiaBundle:Default:menuprincipal.html.twig', array("valor"=>"1", "nombreTipoUsuario"=> $nombrePerfil,'temporadasHabilitadas'=>$temporadasHabilitadas,'idTemporada' => $idTemporada , 'faseTemporada'=> $faseTemporada,'descripcionTemporada' => $descripcionTemporada ));
          
        }else{
          return $this->render('AkademiaBundle:Default:menuprincipal.html.twig', array("valor"=>"2",'temporadasHabilitadas'=>$temporadasHabilitadas,'idTemporada'=>$idTemporada,'descripcionTemporada' => $descripcionTemporada, "nombreTipoUsuario"=> $nombrePerfil ));
        }
    }

    //VISTA INSCRIPCION DIRECTA
    public function inscritosAction(Request $request,$idTemporada){

        $em = $this->getDoctrine()->getManager();
        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        return $this->render('AkademiaBundle:Default:inscritos.html.twig',array("idTemporada"=>$idTemporada, "descripcionTemporada" => $descripcionTemporada));
    }
}