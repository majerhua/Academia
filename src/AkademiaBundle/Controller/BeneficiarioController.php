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
class BeneficiarioController extends Controller
{

 	public function beneficiariosAction(Request $request, $idHorario){
        
        $em = $this->getDoctrine()->getManager();
        $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorarioBeneficiario($idHorario);
        $Beneficiarios = $em->getRepository('AkademiaBundle:Horario')->getBeneficiarios($idHorario);
        $Asistencias = $em->getRepository('AkademiaBundle:Asistencia')->getMostrarAsistencia();
        $Categorias = $em->getRepository('AkademiaBundle:Categoria')->getMostrarCategoria();

        $movAsis = $em->getRepository('AkademiaBundle:Movimientos')->getCantAsistencias(2,$idHorario);
        $movRet = $em->getRepository('AkademiaBundle:Movimientos')->getCantRetirados(3,$idHorario);
        $movSel = $em->getRepository('AkademiaBundle:Movimientos')->getCantSeleccionados(2,$idHorario);
     
        return $this->render('AkademiaBundle:Default:beneficiarios.html.twig', array("horarios" => $Horarios, "beneficiarios" => $Beneficiarios, "asistencias" => $Asistencias, "categorias" => $Categorias, "asistentes" => $movAsis, "retirados" => $movRet, "seleccionados" => $movSel, "id" =>$idHorario));
    }
    
    public function estadoBeneficiarioAction(Request $request){
       
        if($request->isXmlHttpRequest()){

            $idFicha = $request->request->get('idFicha');
            $idAsistencia = $request->request->get('idAsistencia');
            $idCategoria = $request->request->get('idCategoria');
            $usuario = $this->getUser()->getId();
           
            $em = $this->getDoctrine()->getManager();
            $nuevoMovimiento = $em->getRepository('AkademiaBundle:Movimientos')->nuevoMovimiento($idCategoria, $idAsistencia, $idFicha,$usuario);

            if($idAsistencia == 3){

                $em = $this->getDoctrine()->getManager();
                $nuevoMovimiento = $em->getRepository('AkademiaBundle:Inscribete')->getBeneficiarioRetirado($idFicha);

                $horario = $em->getRepository('AkademiaBundle:Inscribete')->getHorarioFicha($idFicha);
                $idHorario = $horario[0]['idHorario'];
               
                if(!empty($idHorario)){
                    $em->getRepository('AkademiaBundle:Inscribete')->getActInscritosVigentes($idHorario);
                }
            }
            if(empty($nuevoMovimiento)){
                $mensaje = 1;
                return new JsonResponse($mensaje);
            }else{
                $mensaje = 2;
                return new JsonResponse($mensaje);
            }       
        }
    }

    public function cantInscritosAction(Request $request){
        if($request->isXmlHttpRequest()){

            $idHorario = $request->request->get('idHorario');
            $em = $this->getDoctrine()->getManager();
            $cant = $em->getRepository('AkademiaBundle:Horario')->cantidadInscritos($idHorario);
            $cantInscritos = $cant[0]['cantInscritos'];
            
            if(intval($cantInscritos == 0)){
                $msn = 1;
            }else{
                $msn = 2;
            }
            return new JsonResponse($msn);
        }
    }




}