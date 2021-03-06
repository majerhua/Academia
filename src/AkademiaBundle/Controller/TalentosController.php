<?php

namespace AkademiaBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AkademiaBundle\Entity\Apoderado;
use AkademiaBundle\Entity\ComplejoDeportivo;
use AkademiaBundle\Entity\DisciplinaDeportiva;
use AkademiaBundle\Entity\Persona;
use AkademiaBundle\Entity\Participante;
use AkademiaBundle\Entity\ComplejoDisciplina;
use AkademiaBundle\Entity\Usuarios;
use AkademiaBundle\Component\Security\Authentication\authenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;




class TalentosController extends controller
{

  public function evaluadosAction(Request $request,$idTemporada){

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

      $Seleccionados = $em->getRepository('AkademiaBundle:Participante')->getMostrarSeleccionados($idTemporada);

      $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

      return $this->render('AkademiaBundle:Default:evaluados.html.twig', array("seleccionados" => $Seleccionados,'idTemporada' => $idTemporada, 'descripcionTemporada' => $descripcionTemporada ));
   
    }


    public function talentosExportAction(Request $request,$temporadaId)
    {
        $em = $this->getDoctrine()->getManager();
        $talentos = $em->getRepository('AkademiaBundle:Participante')->listarTalentos($temporadaId);
        return $this->render('AkademiaBundle:Excel:talentos.xlsx.twig',['data' => $talentos]); 
    }

    public function mostrarTalentosAction(Request $request,$idTemporada){

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

        $talentos = $em->getRepository('AkademiaBundle:Participante')->listarTalentos($idTemporada);

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        $departamentos = $em->getRepository('AkademiaBundle:Departamento')->getDepartamentosByTalentos($idTemporada);
        $complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejosByTalentos($idTemporada);
        $disciplinas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasByTalentos($idTemporada);

        return $this->render('AkademiaBundle:Default:mostrarTalentos.html.twig', 
                      array(
                            "talentos" => $talentos,
                            'idTemporada' => $idTemporada,
                            'descripcionTemporada' =>$descripcionTemporada,
                            'departamentos' => $departamentos,
                            'complejos' => $complejos,
                            'disciplinas' => $disciplinas
                          )
                        );
    }

    public function tableMostrarTalentosAction(Request $request){

      $temporadaId = $request->get('temporadaId');
      $departamentoId = $request->get('departamentoId');
      $disciplinaId = $request->get('disciplinaId');
      $complejoId = $request->get('complejoId');

      $em = $this->getDoctrine()->getManager();
      $talentos = $em->getRepository('AkademiaBundle:Participante')->listarTalentosByDepCompDis($temporadaId,$departamentoId,$complejoId,$disciplinaId);

      $em = $this->getDoctrine()->getManager();
      echo $this->renderView('AkademiaBundle:Default:tableMostrarTalentos.html.twig', 
                      array(
                            'talentos' => $talentos,
                            'idTemporada' => $temporadaId
                          )
                        );
      exit;
    }
    
    public function talentosAction( Request $request, $idParticipante , $idTemporada ){

      $em = $this->getDoctrine()->getManager();
      $talento = $em->getRepository('AkademiaBundle:Participante')->getMostrarTalento($idParticipante);
      $controles = $em->getRepository('AkademiaBundle:Participante')->getMostrarControles($idParticipante);
      $numControl = $em->getRepository('AkademiaBundle:Participante')->getNumeroControl($idParticipante);

      $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

      return $this->render('AkademiaBundle:Default:talento.html.twig',array('talento' => $talento, 'controles' => $controles, 'numeros' =>$numControl, 'idTemporada' => $idTemporada,'descripcionTemporada' => $descripcionTemporada ));
    }

    public function nuevoControlAction(Request $request){
      if($request->isXmlHttpRequest()){

        $fc = $this->getDoctrine()->getManager();
        $usuario = $this->getUser()->getId();

        $peso = $request->get('peso');
        $talla = $request->get('talla');
        $ind30mt = $request->get('ind30mt');
        $saltoLargo = $request->get('saltoLargo');
        $lanzPelota = $request->get('lanzPelota');
        $saltoV = $request->get('saltoV');
        $abdominales = $request->get('abdominales');
        $agCubitoDorsal = $request->get('agCubitoDorsal');

        $idParticipante = $request->get('idParticipante');
        $idInscribete = $request->get('idInscribete');
        $fechaDato = $request->get('fechaDato');
  
        $nuevoControl = $fc->getRepository('AkademiaBundle:Participante')->nuevoControl($fechaDato, $idParticipante, $usuario);

        if($nuevoControl == 1){

          $cantidadControl = $fc->getRepository('AkademiaBundle:Participante')->cantidadControl($idParticipante);
          $num = $cantidadControl[0]['cantidad'];
       

          if($num == 1 ){

              /*CREAR UN MOVIMIENTO DE EVALUADO AUTOMATICAMENTE DEL PARTICIPANTE*/
              $fc->getRepository('AkademiaBundle:Participante')->registrarMovEva($idInscribete,$usuario);
          }

          $idControl = $fc->getRepository('AkademiaBundle:Participante')->retornoIdControl($idParticipante);
          $idNuevoControl = $idControl[0]['id'];
          $idNewControl = intval($idNuevoControl);

          if(!empty($idNuevoControl)){
            
            $fc->getRepository('AkademiaBundle:Participante')->nuevoControlIndicador($peso,$talla,$ind30mt,$saltoLargo,$lanzPelota,$saltoV,$abdominales,$agCubitoDorsal,$idNewControl,$usuario);

            $mensaje = 1;
            return new JsonResponse($mensaje);
  
          }else{
            $mensaje = 3;
            return new JsonResponse($mensaje);
          }
  
        }else{
          $mensaje = 2;
          return new JsonResponse($mensaje);
        }
  
      } 
    }

    //NUEVO TALENTO
    public function nuevoTalentoAction(Request $request){

      $fc = $this->getDoctrine()->getManager();
      $usuario = $this->getUser()->getId();

      $idInscribete = $request->request->get('idInscribete');
      $idParticipante = $request->request->get('idParticipante');

      $cantidadControl = $fc->getRepository('AkademiaBundle:Participante')->cantidadControl($idParticipante);
      $num = $cantidadControl[0]['cantidad'];

      if($num != 0 ){
        
        $fc-> getRepository('AkademiaBundle:Participante')->registrarMovTal($idInscribete,$usuario);             
        $mensaje = 1;
        return new JsonResponse($mensaje);
        
      }else{
      
        $mensaje = 2;
        return new JsonResponse($mensaje);
      }
    }


    public function mostrarControlesIndAction(Request $request){

      if($request->isXmlHttpRequest()){

        $fc = $this->getDoctrine()->getManager();
        $idParticipante = $request->get('idParticipante');
        $idControl = $request->get('idControl');

        $datos = $fc->getRepository('AkademiaBundle:Participante')->listarControlInd($idParticipante, $idControl);

      if(!empty($datos)){
            
                $encoders = array(new JsonEncoder());
                $normalizer = new ObjectNormalizer();
                $normalizer->setCircularReferenceLimit(1);
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $normalizers = array($normalizer);
                $serializer = new Serializer($normalizers, $encoders);
                $jsonContent = $serializer->serialize($datos,'json');
                return new JsonResponse($jsonContent);   
            
            }else{

                $mensaje = 1;
                return new JsonResponse($mensaje);
            }

      }

    }

  public function actualizarControlAction(Request $request){
    
    if($request->isXmlHttpRequest()){

        $fc = $this->getDoctrine()->getManager();
        $usuario = $this->getUser()->getId();
     
        $peso = $request->get('peso');
        $talla = $request->get('talla');
        $ind30mt = $request->get('ind30mt');
        $saltoLargo = $request->get('saltoLargo');
        $lanzPelota = $request->get('lanzPelota');
        $saltoV = $request->get('saltoV');
        $abdominales = $request->get('abdominales');
        $agCubitoDorsal = $request->get('agCubitoDorsal');
    
        $idControl = $request->get('idControl');
        $fechaDato = $request->get('fechaDato');

        $controlActualizado = $fc->getRepository('AkademiaBundle:Participante')->actualizarControlIndicador($fechaDato,$peso,$talla,$ind30mt,$saltoLargo,$lanzPelota,$saltoV,$abdominales,$agCubitoDorsal,$idControl);

        if($controlActualizado == 1){

           $mensaje = 1;
           return new JsonResponse($mensaje);
        
        }else{
           
           $mensaje = 2;
           return new JsonResponse($mensaje);
        }
    
    }
  }

  public function guardarTalentoAction(Request $request){

      $fc = $this->getDoctrine()->getManager();
      
      $idParticipante = $request->get('cod-participante');
      $link = $request->get('link');
      $comentarios = $request->get('comentarios');  


      //SUBIDA DE FICHA

      $file = $request->files;
      $fileFicha = $file->get('imagen-ficha');
      $extensionFicha = $fileFicha->getClientOriginalExtension();
      //getClientOriginalExtension()

      if( $extensionFicha == 'png' || $extensionFicha == 'jpg' || $extensionFicha == 'jpeg' ){

        $nombreFicha = date('YmdHis');
        $fileFichaName = $nombreFicha.'.'.$fileFicha->guessExtension();
        $rutaFicha = "assets/images/imagesFicha/";
        $rutaFichaAll = $rutaFicha.$fileFichaName;
        $fileFicha->move($rutaFicha, $fileFichaName);

      }else{

        $mensaje = 3;
        return new JsonResponse($mensaje);
       
      }

      
      
      //SUBIDA DE FOTO

      $fileFoto = $file->get('imagen-foto');
      $extensionFoto = $fileFoto->getClientOriginalExtension();

       if( $extensionFicha == 'png' || $extensionFicha == 'jpg' || $extensionFicha == 'jpeg' ){
        $nombreFoto = date('YmdHis');
        $fileFotoName = $nombreFoto.'.'.$fileFoto->guessExtension();
        $rutaFoto = "assets/images/imagesFoto/";
        $rutaFotoAll = $rutaFoto.$fileFotoName;
        $fileFoto->move($rutaFoto, $fileFotoName);

       }else{

        $mensaje = 3;
        return new JsonResponse($mensaje);
       
       }

      

      if(!empty($fileFicha) || !empty($fileFoto) ){
       
        //GUARDAR FICHA y FOTO
        $fc->getRepository('AkademiaBundle:Participante')->guardarTalento($idParticipante, $link, $rutaFichaAll,$rutaFotoAll, $comentarios);

        $mensaje = 1;
        return new JsonResponse($mensaje);

      }else{

        $mensaje = 2;
        return new JsonResponse($mensaje);

      }

  }

  public function mostrarDetalleTalentoAction (Request $request, $idParticipante,$idTemporada){

      $em = $this->getDoctrine()->getManager();
      $talento = $em->getRepository('AkademiaBundle:Participante')->getMostrarTalento($idParticipante);
      $controles = $em->getRepository('AkademiaBundle:Participante')->getMostrarControles($idParticipante);
      $numControl = $em->getRepository('AkademiaBundle:Participante')->getNumeroControl($idParticipante);

      $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

      return $this->render('AkademiaBundle:Default:mostrarDetalleTalento.html.twig',array('talento' => $talento, 'controles' => $controles, 'numeros' =>$numControl, 'idTemporada'=>$idTemporada,'descripcionTemporada'=>$descripcionTemporada));

  }


  public function visibilidadAppAction(Request $request){

      $fc = $this->getDoctrine()->getManager();
      $idParticipante = $request->get('idParticipante');
      $visibilidad = $request->get('visibilidad');

      $fc->getRepository('AkademiaBundle:Participante')->actualizarVisibilidad($idParticipante, $visibilidad);
      $mensaje = 1;
      return new JsonResponse($mensaje);

  }

  public function activarCuentaUsuarioAction (Request $request, $token){

      $em = $this->getDoctrine()->getManager();
      $em->getRepository('ApiRestFullAcademiaBundle:PersonaApi')->activarCuentaUsuario($token);

      return $this->render('AkademiaBundle:Default:viewCuentaActivada.html.twig');
  }




}