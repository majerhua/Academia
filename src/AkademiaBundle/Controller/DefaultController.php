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
class DefaultController extends Controller
{
    // LANDING PRINCIPAL 

    public function paginaPreHorariosAction(Request $request,$estado){

        $em = $this->getDoctrine()->getManager();

        $mdlDepartamentosFlag = $em->getRepository('AkademiaBundle:Departamento')->departamentosFlagAll($estado);
        $mdlProvinciasFlag = $em->getRepository('AkademiaBundle:Provincia')->provinciasFlagAll($estado);
        $mdlDistritosFlag = $em->getRepository('AkademiaBundle:Distrito')->distritosFlagAll($estado);
        $mdlDepartamentos = $em->getRepository('AkademiaBundle:Departamento')->departamentosAll();
        $mdlProvincias = $em->getRepository('AkademiaBundle:Provincia')->provinciasAll();
        $mdlDistritos = $em->getRepository('AkademiaBundle:Distrito')->distritosAll();
        $mdlComplejosDeportivosFlag = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->complejosDeportivosFlagAllLanding($estado);
        $mdlDisciplinasDeportivasFlag = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->disciplinasDeportivasFlagAllLanding($estado);
        $mdlhorariosFlag = $em->getRepository('AkademiaBundle:Horario')->horariosFlagAllLanding($estado);

        return $this->render('AkademiaBundle:Default:horariosLanding.html.twig', array('departamentosFlag' => $mdlDepartamentosFlag , "provinciasFlag" => $mdlProvinciasFlag ,'distritosFlag' => $mdlDistritosFlag,'departamentos'=>$mdlDepartamentos,'provincias'=>$mdlProvincias,'distritos'=>$mdlDistritos, 'complejosDeportivos' => $mdlComplejosDeportivosFlag, 'disciplinasDeportivas' => $mdlDisciplinasDeportivasFlag ,'horarios' => $mdlhorariosFlag,'estado'=>$estado) );
    }

    // CONTADOR - INICIO DE LA TEMPORADA (CRONOMETRO)
    public function contadorAction(Request $request){
      
        return $this->render('AkademiaBundle:Default:contador.html.twig' );
    }

    // VISTA CONSULTAS O PREGUNTAS FRECUENTES
    public function consultaAction(Request $request){

        return $this->render('AkademiaBundle:Default:cuestions.html.twig');
    }

    //PANEL PRINCIPAL 
    public function panelAction(Request $request){
  
        $idComplejo = $this->getUser()->getIdComplejo();
        $em = $this->getDoctrine()->getManager();
        $Nombre = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->nombreComplejo($idComplejo);

        if(!empty($Nombre)){ 
           return $this->render('AkademiaBundle:Default:menuprincipal.html.twig', array("valor"=>"1", "nombreComplejo"=> $Nombre));

        }
    }

    //VISTA INSCRIPCION DIRECTA
    public function inscritosAction(Request $request){
        return $this->render('AkademiaBundle:Default:inscritos.html.twig');
    }


}