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
class HorarioController extends Controller
{

    // FUNCION PARA RENDERIZAR LA VISTA DE HORARIOS
  	public function horariosAction(Request $request){

        $idComplejo = $this->getUser()->getIdComplejo();
        $em2 = $this->getDoctrine()->getManager();
      
        $ComplejoDisciplinas = $em2->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorarios($idComplejo);
        $Horarios = $em2->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo);
        $Disciplinas = $em2->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasDiferentes($idComplejo);
        $Nombre = $em2->getRepository('AkademiaBundle:ComplejoDeportivo')->nombreComplejo($idComplejo);
        
        if(!empty($Nombre)){ 

            return $this->render('AkademiaBundle:Default:horarios.html.twig', array("complejosDisciplinas" => $ComplejoDisciplinas ,"horarios" => $Horarios, "disciplinas" => $Disciplinas, "valor"=>"1", "nombreComplejo"=> $Nombre)); 
        }else{
            return $this->render('AkademiaBundle:Default:horarios.html.twig', array("complejosDisciplinas" => $ComplejoDisciplinas ,"horarios" => $Horarios, "disciplinas" => $Disciplinas, "valor"=>"2")); 
         
        }

    }

    // FUNCION PARA LA CREACION DE HORARIOS
    public function crearHorarioAction(Request $request){
        if($request->isXmlHttpRequest()){

            $horaInicio =$request->request->get('horarioInicio');
            $horaFin = $request->request->get('horarioFin');
            $edadMinima = $request->request->get('edadMinima');
            $edadMaxima = $request->request->get('edadMaxima');
            $vacantes = $request->request->get('vacantes');
            $preinscripciones = $request->request->get('preinscripciones');
            $discapacitados = $request->request->get('discapacidad');
            $turno = $request->request->get('turno');
            $usuario = $this->getUser()->getId();
            $idDisciplina = $request->request->get('idDisciplina');           
            $idComplejo = $this->getUser()->getIdComplejo();
            

            $em = $this->getDoctrine()->getManager();
 
            $ediCodigo = $em->getRepository('AkademiaBundle:Horario')->getCapturarEdiCodigo($idComplejo, $idDisciplina);         
            $codigoEdi = $ediCodigo[0]['edi_codigo'];
           
            $data = $em->getRepository('AkademiaBundle:Horario')->getDiferenciarHorarios($turno,$edadMinima,$edadMaxima,$horaInicio,$horaFin,$discapacitados,$codigoEdi);

            if(!empty($data)){
                $mensaje = 1;
                return new JsonResponse($mensaje);
            }else{
                
                if($discapacitados == 1){
                    $cambios = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getEditarDiscapacitado($idComplejo, $usuario);
                    $cambiosDisciplina = $em2->getRepository('AkademiaBundle:DisciplinaDeportiva')->getEditarDiscapacitado($idDisciplina, $usuario);
                }

                $horario = new Horario();
                $horario->setVacantes($vacantes);
                $horario->setPreinscripciones($preinscripciones);
                $horario->setHoraInicio($horaInicio);
                $horario->setHoraFin($horaFin);
                $horario->setEdadMinima($edadMinima);
                $horario->setEdadMaxima($edadMaxima);
                $horario->setDiscapacitados($discapacitados);
                $horario->setTurno($turno);
                $horario->setConvocatoria(0);
                $horario->setUsuarioCrea($usuario);
                $horario->setEstado(1);
                $horario->setInscritos(0);
                $em = $this->getDoctrine()->getRepository(complejoDisciplina::class);
                $codigoDisciplina = $em->find($codigoEdi);
                $horario->setComplejoDisciplina($codigoDisciplina);
         
                $em = $this->getDoctrine()->getManager();
                $em->persist($horario);
                $em->flush();
                $idHorarioNuevo = $horario->getId(); 
                $dataActualizada = $em->getRepository('AkademiaBundle:Horario')->getMostrarCambios($idHorarioNuevo);         
                
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
                    $mensaje = 2;
                    return new JsonResponse($mensaje);
                }
            } 
        }
    }

    // FUNCION PARA MOSTRAR HORARIOS INDIVIDUALES

    public function mostrarHorarioIndividualAction(Request $request){  
        if($request->isXmlHttpRequest()){
            $idHorario=$request->request->get('idHorario');
            $idDisciplina= $request->request->get('idDisciplina');
            $em = $this->getDoctrine()->getManager();
            $datosHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosIndividual($idHorario, $idDisciplina);
            
            if(!empty($datosHorario)){
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
            $usuario = $this->getUser()->getId();
           
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('AkademiaBundle:Horario')->getActualizarHorarios($idHorario, $vacantes, $convocatoria, $usuario, $preinscripciones);
            $em->flush();
          
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