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


class HorarioController extends Controller
{

    //PAGINA PARA MOSTRAR HORARIOS LANDING
    public function mostrarHorariosLandingAction(Request $request,$estado){

        $em = $this->getDoctrine()->getManager();

        $mdDepartamentsByDisability = $em->getRepository('AkademiaBundle:Departamento')->getDepartmentsLandingByDisability($estado);
        $mdProvinceByDisability = $em->getRepository('AkademiaBundle:Provincia')->getProvincesLandingByDisability($estado);
        $mdDistrictsByDisability = $em->getRepository('AkademiaBundle:Distrito')->getDistrictsLandingByDisability($estado);
        $mdComplexesByDisability = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplexesLandingByDisability($estado);
        $mdDisciplinesByDisability = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinesLandingByDisability($estado);
        return $this->render('AkademiaBundle:Default:mostrarHorariosLanding.html.twig', array('departamentosFlag' => $mdDepartamentsByDisability , "provinciasFlag" => $mdProvinceByDisability ,'distritosFlag' => $mdDistrictsByDisability,'complejosDeportivos' => $mdComplexesByDisability, 'disciplinasDeportivas' => $mdDisciplinesByDisability,'estado'=>$estado) );
    }

    //MUESTRA TABLA  HORARIOS Y TURNOS
    public function tableHorarioAction(Request $request){

        $idCompleDis = $request->request->get('idComplejoDisciplina');
        $ageBeneficiario = $request->request->get('edadBeneficiario');
        $flagDiscapacitado = $request->request->get('flagDiscapacidad');
        //Si los horarios es para publico en general o pre-inscripcion
        $flag = $request->request->get('flag');

        //Si la ficha de Preinscripcion es para Promotores o para Publico en General 
        $role = $this->getUser();

        $flagUser = '0';
        $em = $this->getDoctrine()->getManager();

        //Visualizar Horarios de Referencia para Preinscripcion, Landing Page
        if( $flag == 0 ){

            $mdlScheduleDiscipline = $em->getRepository('AkademiaBundle:Horario')->getScheduleDisciplineLanding($idCompleDis,$flagDiscapacitado);
        //Visualizar Horarios Para Ficha Pre Inscripcion(Promotores y Publico General)
        }else if( $flag == 1){

            if(!empty($role)){
                $mdlScheduleDiscipline = $em->getRepository('AkademiaBundle:Horario')->getScheduleDisciplinePromotor($idCompleDis,$ageBeneficiario,$flagDiscapacitado);
                $flagUser='1';
            }else{
                $mdlScheduleDiscipline = $em->getRepository('AkademiaBundle:Horario')->getScheduleDisciplinePublicGeneral($idCompleDis,$ageBeneficiario,$flagDiscapacitado);
            }
        }

        $mdlTurnsDiscipline = $em->getRepository('AkademiaBundle:Horario')->getTurnsDiscipline($idCompleDis);

        echo $this->renderView('AkademiaBundle:Default:tableHorario.html.twig',array('horarios' => $mdlScheduleDiscipline , "turnos" => $mdlTurnsDiscipline,'flag'=>$flag,'flagUser'=>$flagUser));
        exit;
    }

    // FUNCION PARA RENDERIZAR LA VISTA DE HORARIOS
  	public function horariosAction(Request $request,$idTemporada){

        $idComplejo = $this->getUser()->getIdComplejo();
        $em = $this->getDoctrine()->getManager();

        $ComplejoDisciplinas = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorarios($idComplejo,$idTemporada);

        $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo);
        $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

        $Disciplinas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasDiferentes($idComplejo);
        $Nombre = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->nombreComplejo($idComplejo);
            
        $arrayTemporada = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();

        if(!empty($arrayTemporada))
            $idTemporadaActiva = $arrayTemporada[0]['temporadaId'];
        else
            $idTemporadaActiva = null;

        if(!empty($Nombre)){ 

            return $this->render('AkademiaBundle:Default:horarios.html.twig', array("complejosDisciplinas" => $ComplejoDisciplinas ,"horarios" => $Horarios, "disciplinas" => $Disciplinas, "valor"=>"1", "nombreComplejo"=> $Nombre, 'turnos'=>$turnos,'idTemporada'=>$idTemporada,'idTemporadaActiva'=>$idTemporadaActiva)); 
        }else{

            return $this->render('AkademiaBundle:Default:horarios.html.twig', array("complejosDisciplinas" => $ComplejoDisciplinas ,"horarios" => $Horarios, "disciplinas" => $Disciplinas, "valor"=>"2", 'turnos'=>$turnos, 'idTemporada'=>$idTemporada )); 
        }
    }

    // FUNCION PARA LA CREACION DE HORARIOS
    public function crearHorarioAction(Request $request){
            
        if( $request->isXmlHttpRequest() ){
             
            $em = $this->getDoctrine()->getManager();

            $idComplejo = $this->getUser()->getIdComplejo();
            $idDisciplina = $request->request->get('idDisciplina');  
            $ediCodigo = $em->getRepository('AkademiaBundle:Horario')->getCapturarEdiCodigo($idComplejo, $idDisciplina); 

            $codigoEdi = $ediCodigo[0]['edi_codigo'];
            
            $modalidad = $request->request->get('modalidad-horario');
            $etapa = $request->request->get('etapa-horario');
            $edadMinima = $request->request->get('edadMinima');
            $edadMaxima = $request->request->get('edadMaxima');
            $vacantes = $request->request->get('vacantes-horario');
            $preinscripciones = $request->request->get('preinscripciones-horario');
            $turnos = $request->request->get('turnos-seleccionados');
            $turnosString = $request->request->get('turnos-seleccionados-string');
            $usuario = $this->getUser()->getId();
         
            $dataHorarioRepetido = $em->getRepository('AkademiaBundle:Horario')->getDiferenciarHorarios($modalidad,$etapa,$edadMinima,$edadMaxima,$codigoEdi,$turnosString);
            $dataHorRept = $dataHorarioRepetido[0]['cantHorario'];
            
            if(!empty($dataHorRept)){

                $mensaje = 1;
            }else{

                if($modalidad == 1){
            
                    $cambios = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getEditarDiscapacitado($idComplejo, $usuario);
                    $cambiosDisciplina = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getEditarDiscapacitado($idDisciplina, $usuario);
                }

                $horario = new Horario();

                $horario->setDiscapacitados($modalidad);
                $horario->setEtapa($etapa);
                $horario->setEdadMinima($edadMinima);
                $horario->setEdadMaxima($edadMaxima);

                $em = $this->getDoctrine()->getRepository(complejoDisciplina::class);
                $codigoDisciplina = $em->find($codigoEdi);
                $horario->setComplejoDisciplina($codigoDisciplina);

                $horario->setUsuarioCrea($usuario);
                $horario->setVacantes($vacantes);
                $horario->setPreinscripciones($preinscripciones);
                $horario->setConvocatoria(0);
                $horario->setEstado(1);
                $horario->setInscritos(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($horario);
                $em->flush();
                $idHorarioNuevo = $horario->getId();
                
                $mensaje = $em->getRepository('AkademiaBundle:Horario')->guardarTurnoHorario($turnos,$idHorarioNuevo);
            } 
                echo $mensaje;
                exit;
        }
    }

    // FUNCION PARA MOSTRAR HORARIOS INDIVIDUALES

    public function mostrarHorarioIndividualAction(Request $request){  

        if($request->isXmlHttpRequest()){
            $idHorario=$request->request->get('idHorario');
            $idDisciplina= $request->request->get('idDisciplina');
            $em = $this->getDoctrine()->getManager();
            $datosHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosIndividual($idHorario, $idDisciplina);
            $datosTurno = $em->getRepository('AkademiaBundle:Horario')->getTurnosIndividual($idHorario);


            $datosHorario[0]['turnos']=$datosTurno;
            
            if(!empty($datosHorario) && !empty($datosTurno) ){
                $encoders = array(new JsonEncoder());
                $normalizer = new ObjectNormalizer();
                $normalizer->setCircularReferenceLimit(1);
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $normalizers = array($normalizer);
                $serializer = new Serializer($normalizers, $encoders);
                $jsonContent = $serializer->serialize($datosHorario,'json');
                return new JsonResponse($jsonContent);   
            }else{
                $mensaje = 1;
                return new JsonResponse($mensaje);
            }
        }
    }

    // FUNCION PARA ACTUALIZAR LOS HORARIOS 

    public function actualizarHorarioAction(Request $request){
        if($request->isXmlHttpRequest()){
           
            $idHorario = $request->request->get('idHorario');
            $vacantes = $request->request->get('vacantes');
            $preinscripciones = $request->request->get('preinscripciones');
            $convocatoria = $request->request->get('convocatoria');
            $etapa = $request->request->get('etapa');
            $usuario = $this->getUser()->getId();
           
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('AkademiaBundle:Horario')->getActualizarHorarios($idHorario, $vacantes, $convocatoria, $usuario, $preinscripciones,$etapa);
          
            $dataActualizada = $em->getRepository('AkademiaBundle:Horario')->getMostrarCambios($idHorario);
          
            if(!empty($dataActualizada)){
                $encoders = array(new JsonEncoder());
                $normalizer = new ObjectNormalizer();
                $normalizer->setCircularReferenceLimit(1);
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $normalizers = array($normalizer);
                $serializer = new Serializer($normalizers, $encoders);
                $jsonContent = $serializer->serialize($dataActualizada,'json');
                return new JsonResponse($jsonContent);   
            }else{
                $mensaje = 1;
                return new JsonResponse($mensaje);
            }
        }
    }

    // FUNCION PARA OCULTAR O ELIMINAR HORARIOS

    public function ocultarHorarioAction(Request $request){
        if($request->isXmlHttpRequest()){

            $idHorario = $request->request->get('idHorario');
            $usuario = $this->getUser()->getId();
            
            if(!empty($idHorario) && !empty($usuario) ){

                $em = $this->getDoctrine()->getManager();
                $em ->getRepository('AkademiaBundle:Horario')->getOcultarHorario($idHorario, $usuario);
                $em->flush();
                $mensaje = 1;
                return new JsonResponse($mensaje);
            }
        }
    }
}