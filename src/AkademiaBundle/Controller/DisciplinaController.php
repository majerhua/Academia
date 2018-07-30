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
class DisciplinaController extends Controller
{
	// CREAR NUEVA DISCIPLINA 

    public function crearDisciplinaAction(Request $request){    
        if($request->isXmlHttpRequest()){

            $idDisciplina = $request->request->get('idDisciplina');           
            $idComplejo = $this->getUser()->getIdComplejo();
            $usuario = $this->getUser()->getId();
            
            $em = $this->getDoctrine()->getManager();
            $estado = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getCompararEstado($idComplejo, $idDisciplina);
        
            if(!empty($estado)){
                $em = $this->getDoctrine()->getManager();
                $estadoActual = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getCambiarEstado($idComplejo, $idDisciplina);
                $mensaje = 1;
                return new JsonResponse($mensaje);    
            
            }else{
                $disciplina = new ComplejoDisciplina();
                $em = $this->getDoctrine()->getRepository(DisciplinaDeportiva::class);
                $codigoDisciplina = $em->find($idDisciplina);
                $disciplina->setDisciplinaDeportiva($codigoDisciplina);
               
                $em = $this->getDoctrine()->getRepository(ComplejoDeportivo::class);
                $codigoComplejo = $em->find($idComplejo);
                $disciplina->setComplejoDeportivo($codigoComplejo);
                $disciplina->setEstado(1);
                $disciplina->setUsuario($usuario);
         
                $em = $this->getDoctrine()->getManager();
                $em->persist($disciplina);
                $em->flush();  
                $mensaje = 2;
                return new JsonResponse($mensaje);           
            }
          
        }
    }


    // ELIMINAR DISCIPLINA
    
    public function eliminarDisciplinaAction(Request $request){
        if($request->isXmlHttpRequest()){

            $idDisciplina = $request->request->get('codigoDisciplina');  
            $idComplejo = $this->getUser()->getIdComplejo();

            $em = $this->getDoctrine()->getManager();
            $ediCodigo = $em->getRepository('AkademiaBundle:Horario')->getCapturarEdiCodigo($idComplejo, $idDisciplina);   
            $codigoEdi = $ediCodigo[0]['edi_codigo'];

            $cantidad = $em->getRepository('AkademiaBundle:Horario')->cantHorarioDisciplina($codigoEdi);

            if(!empty($cantidad)){
                $mensaje = 1;
                return new JsonResponse($mensaje);
            
            }else{

                $em->getRepository('AkademiaBundle:Horario')->eliminarDisciplina($codigoEdi);
                $mensaje = 2;
                return new JsonResponse($mensaje);
            
            }
        }
    }

}