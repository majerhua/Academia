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
    
    public function mostrarfichaAction(Request $request){
        if($request->isXmlHttpRequest()){
           
            $idFicha = $request->request->get('id');
            $em2 = $this->getDoctrine()->getManager();
            $ficha = $em2->getRepository('AkademiaBundle:Inscribete')->getFicha($idFicha);
            
            if(!empty($ficha)){
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
            
            $em = $this->getDoctrine()->getManager();

            $horarios = $em->getRepository('AkademiaBundle:Horario')->horarioActivos($idHorario);

            if(empty($horarios)){

                $mensaje = 5;
                return new JsonResponse($mensaje);
                
            }else{

                $data = $em->getRepository('AkademiaBundle:Inscribete')->getDobleInscripcion($idHorario,$idParticipante);
                   
                    if(!empty($data)){
                        
                        $mensaje = 3;
                        return new JsonResponse($mensaje);
                    
                    }else {
                       
                        $em = $this->getDoctrine()->getManager();
                        $data = $em->getRepository('AkademiaBundle:Inscribete')->getCantInscripciones($idParticipante);

                        $cantRegistros = $data[0]['cantidadRegistros'];
                        

                        if(intval($cantRegistros) >= 1){
                        
                            $mensaje = 4;
                            return new JsonResponse($mensaje);

                        }else{
                      
                            if( $estadoFicha == 0){
                      
                                $em = $this->getDoctrine()->getManager();
                                $vacantesHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosVacantes($idHorario);
                                $cantVacantes = $vacantesHorario[0]['vacantes'];
                      
                                if($cantVacantes > 0 ){

                                    $em = $this->getDoctrine()->getManager();
                                    $IDParticipante = $em->getRepository('AkademiaBundle:Participante')->getbuscarParticipante($dniParticipante);
                      
                                    if(!empty($IDParticipante)){   
                      
                                        $idParticipante = $IDParticipante[0]['id'];
                                        $em = $this->getDoctrine()->getRepository(Participante::class);
                                        $participante = $em->find($idParticipante);
                                        $participante->setUsuarioValida($usuario);
                                        $em = $this->getDoctrine()->getManager();
                                        $em->flush();
                                       
                                        $em = $this->getDoctrine()->getManager();
                                        $idApoderadoB = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderado($dniApoderado);          
                      
                                        if(!empty($idApoderadoB)){
                                            $idApoderado = $idApoderadoB[0]['id'];
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarApoderado($idApoderado,$idParticipante);
                                            $em->flush();
                                           
                                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                                            $apoderado = $em->find($idApoderado);
                                            $apoderado->setUsuarioValida($usuario);
                                            $em = $this->getDoctrine()->getManager();
                                            $em->flush();
                                           
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarParticipanteFicha($idParticipante,$idFicha);
                                            $em->flush(); 
                                        
                                        }else{
                                            
                                            $em = $this->getDoctrine()->getManager();
                                            $IDApoderado = $em->getRepository('AkademiaBundle:Apoderado')->getApoderadoBusqueda($dniApoderado);
                                            $idApoderado = $IDApoderado[0]['id'];
                                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                                            $apoderado = $em->find($idApoderado);
                                            $apoderado->setEstado(1);
                                            $apoderado->setUsuarioValida($usuario);
                                            $em = $this->getDoctrine()->getManager();
                                            $em->flush();
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarApoderado($idApoderado,$idParticipante);
                                            $em->flush(); 
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarParticipanteFicha($idParticipante,$idFicha);
                                            $em->flush(); 
                                        }
                                    }else{
                      
                                        $em = $this->getDoctrine()->getManager();
                                        $IDParticipanteExistente = $em->getRepository('AkademiaBundle:Participante')->getbuscarParticipanteApoderado($dniParticipante);
                                        
                                        $idParticipante = $IDParticipanteExistente[0]['id'];
                                        $em = $this->getDoctrine()->getRepository(Participante::class);
                                        $participante = $em->find($idParticipante);
                                        $participante->setEstado(1);
                                        $participante->setUsuarioValida($usuario);
                                        $em = $this->getDoctrine()->getManager();
                                        $em->flush();
                                        $em = $this->getDoctrine()->getManager();
                                        $em->getRepository('AkademiaBundle:Participante')->getActualizarParticipanteFicha($idParticipante,$idFicha);
                                        $em->flush(); 
                                        $em = $this->getDoctrine()->getManager();
                                        $idApoderadoB = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderado($dniApoderado);
                                        
                                        if(!empty($idApoderadoB)){

                                            $idApoderado = $idApoderadoB[0]['id'];
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarApoderado($idApoderado,$idParticipante);
                                            $em->flush(); 
                                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                                            $apoderado = $em->find($idApoderado);
                                            $apoderado->setUsuarioValida($usuario);
                                            $em = $this->getDoctrine()->getManager();
                                            $em->flush();
                                           
                                        }else{
                      
                                            $em = $this->getDoctrine()->getManager();
                                            $IDApoderado = $em->getRepository('AkademiaBundle:Apoderado')->getApoderadoBusqueda($dniApoderado);
                                            $idApoderado = $IDApoderado[0]['id'];
                                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                                            $apoderado = $em->find($idApoderado);
                                            $apoderado->setEstado(1);
                                            $apoderado->setUsuarioValida($usuario);
                                            $em = $this->getDoctrine()->getManager();
                                            $em->flush();
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarApoderado($idApoderado,$idParticipante);
                                            $em->flush(); 
                                            $em = $this->getDoctrine()->getManager();
                                            $em->getRepository('AkademiaBundle:Participante')->getActualizarParticipanteFicha($idParticipante,$idFicha);
                                            $em->flush(); 
                                        }                  
                                    }

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
                                    $em2 = $this->getDoctrine()->getManager();
                                    $em2->getRepository('AkademiaBundle:Horario')->getAcumularInscritos($idHorario);
                                    $em2->flush();
                                    $em3 = $this->getDoctrine()->getManager();
                                    $em3->getRepository('AkademiaBundle:Movimientos')->RegistrarMovInicial($idFicha, $usuario);
                                    $em3->flush();
                                    $mensaje = 1;
                                    return new JsonResponse($mensaje);
                                    
                                }else{
                                    
                                    $mensaje = 2;
                                    return new JsonResponse($mensaje);
                                    
                                }

                            }else if( $estadoFicha == 1){
                                
                                $em = $this->getDoctrine()->getManager();
                                $vacantesHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosVacantes($idHorario);
                                $cantVacantes = $vacantesHorario[0]['vacantes'];
                                if($cantVacantes > 0 ){
                               
                               //     if ( $estadoParticipante == 1 && $estadoApoderado == 1){
                                        $em = $this->getDoctrine()->getRepository(Participante::class);
                                        $participante = $em->find($idParticipante);
                                       // $participante->setUsuarioValida($usuario);
                                        $em = $this->getDoctrine()->getManager();
                                        $em->flush();
                                        $em = $this->getDoctrine()->getRepository(Apoderado::class);
                                        $apoderado = $em->find($idApoderado);
                                        //$apoderado->setUsuarioValida($usuario);
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
                                 //   }
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