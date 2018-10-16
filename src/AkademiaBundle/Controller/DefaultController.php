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

        $idComplejo = $this->getUser()->getIdComplejo();
        $em = $this->getDoctrine()->getManager();

        $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadas();

        if($idTemporada == 0){
           $temporadaArray = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva(); 

            if(!empty($temporadaArray)){
                $idTemporada = $temporadaArray[0]['temporadaId'];
            }else{
                $idTemporada = $temporadasHabilitadas[0]['temporadaId'];
            }  
        }

        $Nombre = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->nombreComplejo($idComplejo);

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        if(!empty($Nombre)){ 

            $arrayFaseTemporada = $em->getRepository('AkademiaBundle:Temporada')->faseTemporadaActiva($idTemporada);
            $faseTemporada = $arrayFaseTemporada[0]['fase'];

          return $this->render('AkademiaBundle:Default:menuprincipal.html.twig', array("valor"=>"1", "nombreComplejo"=> $Nombre,'temporadasHabilitadas'=>$temporadasHabilitadas,'idTemporadaHabilidatada' => $idTemporada , 'faseTemporada'=> $faseTemporada,'descripcionTemporada' => $descripcionTemporada ));
          
        }else{
          return $this->render('AkademiaBundle:Default:menuprincipal.html.twig', array("valor"=>"2",'temporadasHabilitadas'=>$temporadasHabilitadas,'idTemporadaHabilidatada'=>$idTemporada,'descripcionTemporada' => $descripcionTemporada  ));
        }
    }

    //VISTA INSCRIPCION DIRECTA
    public function inscritosAction(Request $request,$idTemporada){

        $em = $this->getDoctrine()->getManager();
        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        return $this->render('AkademiaBundle:Default:inscritos.html.twig',array("idTemporada"=>$idTemporada, "descripcionTemporada" => $descripcionTemporada));
    }


}