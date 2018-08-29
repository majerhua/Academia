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
class FichaController extends Controller
{
    
    // MOSTRAR FICHA PARA ISNCRIPCION DIRECTA

    public function mostrarfichaAction(Request $request){
        if($request->isXmlHttpRequest()){
           
            $idFicha = $request->request->get('id');
            $em = $this->getDoctrine()->getManager();
            $ficha = $em->getRepository('AkademiaBundle:Inscribete')->getFicha($idFicha);
            $idHorario = $ficha[0]['horario_id'];
            $fichaTurnoHorario = $em->getRepository('AkademiaBundle:Horario')->getTurnosIndividual($idHorario);
            $ficha[0]['turnos'] = $fichaTurnoHorario;

            if( !empty($ficha) && !empty($fichaTurnoHorario) ){
                $encoders = array(new JsonEncoder());
                $normalizer = new ObjectNormalizer();
                $normalizer->setCircularReferenceLimit(1);
                   
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $normalizers = array($normalizer);
                $serializer = new Serializer($normalizers, $encoders);
                $jsonContent = $serializer->serialize($ficha,'json');
                return new JsonResponse($jsonContent);
            }else{
                $mensaje = 1;
                return new JsonResponse($mensaje);
            }
        }
    }
    
    // FUNCION PARA LA VALIDACION DEL ESTADO :DE PREISNCRITO A INSCRITO

    public function cambiarestadoAction(Request $request){
        
        if($request-> isXmlHttpRequest()){
            $idFicha = $request->request->get('id');
            $usuario = $this->getUser()->getId();
           
            $em = $this->getDoctrine()->getManager();

            $data = $em->getRepository('AkademiaBundle:Inscribete')->cargaDatos($idFicha);
            $estadoFicha = $data[0]['estadoFicha'];
            $idParticipante = $data[0]['idParticipante'];
            $dniParticipante = $data[0]['dniParticipante'];
            $idApoderado = $data[0]['idApoderado'];
            $dniApoderado = $data[0]['dniApoderado'];
            $idHorario = $data[0]['idHorario'];
            
            //Verificar si el horario está activo
            $horarios = $em->getRepository('AkademiaBundle:Horario')->horarioActivos($idHorario);

            if(empty($horarios)){
                $mensaje = 5;
                return new JsonResponse($mensaje);
            }else{
                
                //Verficar si el alumno tiene una inscripcion previa
                $data = $em->getRepository('AkademiaBundle:Inscribete')->getDobleInscripcion($idHorario,$idParticipante);
                if(!empty($data)){
                    $mensaje = 3;
                    return new JsonResponse($mensaje);
                }else {
                    
                    $em = $this->getDoctrine()->getManager();
                    $data = $em->getRepository('AkademiaBundle:Inscribete')->getCantInscripciones($idParticipante);
                    $cantRegistros = $data[0]['cantidadRegistros'];

                    // CANTIDAD DE PRE - INSCRIPCIONES
                    if(intval($cantRegistros) >= 1){
                        $mensaje = 4;
                        return new JsonResponse($mensaje);
                    }else{
                       
                        if( $estadoFicha == 1){

                            $em = $this->getDoctrine()->getManager();
                            $vacantesHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosVacantes($idHorario);
                            $cantVacantes = $vacantesHorario[0]['vacantes'];
                            
                            // VALIDAR CANTIDAD DE VACANTES
                            if($cantVacantes > 0 ){
                        
                                $em = $this->getDoctrine()->getRepository(Participante::class);
                                $participante = $em->find($idParticipante);
                                $em = $this->getDoctrine()->getManager();
                                $em->flush();
                                $em = $this->getDoctrine()->getRepository(Apoderado::class);
                                $apoderado = $em->find($idApoderado);
                                $em = $this->getDoctrine()->getManager();
                                $em->flush();
                               
                                $em2 = $this->getDoctrine()->getManager();
                                $ficha = $em2->getRepository('AkademiaBundle:Inscribete');
                                $estadoFicha = $ficha->find($idFicha);
                                $estadoFicha->setEstado(2);
                                $estadoFicha->setUsuarioValida($usuario);
                                $em2->persist($estadoFicha);
                                $em2->flush();
                                $em= $this->getDoctrine()->getManager();
                                $em->getRepository('AkademiaBundle:Horario')->getActualizarVacantesHorarios($idHorario);
                                $em->flush();
                                $em= $this->getDoctrine()->getManager();
                                $em->getRepository('AkademiaBundle:Horario')->getAcumularInscritos($idHorario);
                                $em->flush();
                                $em3= $this->getDoctrine()->getManager();
                                $em3->getRepository('AkademiaBundle:Movimientos')->RegistrarMovInicial($idFicha, $usuario);
                                $em3->flush();
                                $mensaje = 1;
                                return new JsonResponse($mensaje);
                           
                            }else{
                                $mensaje = 2;
                                return new JsonResponse($mensaje);
                                
                            }


                              
                        }  
                    }
                }  
            } 
        }
    }
}