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

    public function getRankAgesDisciplineAction(Request $request){

        if($request->isXmlHttpRequest()){

            $idDisciplina = $request->request->get('idDisciplina');
            $idTemporada = $request->request->get('idTemporada');  

            if(!empty($idDisciplina)){

                $em = $this->getDoctrine()->getManager();
                $rankAgeDiscipline = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getRankAgeDisciplineById($idDisciplina,$idTemporada);

                if( !empty($rankAgeDiscipline[0]['edad_min_convencional']) )
                    return new JsonResponse($rankAgeDiscipline);
                else
                    return new JsonResponse(0);

            }else
                return new JsonResponse(0);
            
        }
    }

	// CREAR NUEVA DISCIPLINA 
    public function crearDisciplinaAction(Request $request){ 

            $mensaje = NULL;
            $idDisciplina = $request->request->get('idDisciplina'); 
            $idTemporada = $request->request->get('idTemporada');  
            $idComplejo = $request->request->get('idComplejo'); 
            $usuario = $this->getUser()->getId();

            if( !empty($idDisciplina) && isset($idDisciplina) &&
                !empty($idTemporada) && isset($idTemporada) &&
                !empty($idComplejo) && isset($idComplejo) &&
                !empty($usuario) && isset($usuario)  
                ){

                    $em = $this->getDoctrine()->getManager();
                    $ediEstado = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->vertificarEdificacionDisciplina($idComplejo, $idDisciplina,$idTemporada);
        
                    if( !empty($ediEstado) ){
                        $estadoActualizacion = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->habilitarEdificacionDisciplina($idComplejo, $idDisciplina); 
                        $mensaje = $estadoActualizacion;
                    
                    }else{
                        $estadoRegistro = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->crearComplejoDisciplina($idComplejo, $idDisciplina,$usuario,$idTemporada);
                        $mensaje = $estadoRegistro;            
                    }

                    if( $mensaje == "1" ){

                        $temporadaActiva = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();

                        if(!empty($temporadaActiva))
                            $idTemporadaActiva = $temporadaActiva[0]['temporadaId'];
                        else
                            $idTemporadaActiva = null;

                        $ComplejoDisciplinas = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorarios($idComplejo,$idTemporada);
                        $Disciplinas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasDiferentes($idComplejo,$idTemporada);

                        $complejo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejoById($idComplejo);
                        $nombreComplejo = $complejo[0]['complejoNombre'];

                        $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo,$idTemporada);
                        $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

                        echo $this->renderView('AkademiaBundle:Disciplina_Horario_Beneficiario:table_disciplina_horario_beneficiario.html.twig',
                                array(
                                        "complejosDisciplinas" => $ComplejoDisciplinas ,
                                        "disciplinas" => $Disciplinas,
                                        "horarios" => $Horarios, 
                                        "nombreComplejo"=> $nombreComplejo, 
                                        'turnos'=> $turnos,
                                        'idTemporada' => $idTemporada,
                                        'idTemporadaActiva' => $idTemporadaActiva,
                                        'idComplejo' => $idComplejo
                                )
                            );
                        exit; 

                    }else if($mensaje == "0") {
                        echo "0"; //ERROR
                        exit;
                    }else{
                        echo "-2";
                        exit;
                    }
            }else{
                echo "-1"; //CAMPOS VACIOS
                exit;
            }    
    }


    // ELIMINAR DISCIPLINA
    public function eliminarDisciplinaAction(Request $request){

 
            $idDisciplina = $request->request->get('codigoDisciplina'); 
            $idTemporada =  $request->request->get('idTemporada');
            $idComplejo = $request->request->get('idComplejo');

            $em = $this->getDoctrine()->getManager();
            $ediCodigo = $em->getRepository('AkademiaBundle:Horario')->getCapturarEdiCodigo($idComplejo, $idDisciplina,$idTemporada);   
            $codigoEdi = $ediCodigo[0]['edi_codigo'];

            $cantidad = $em->getRepository('AkademiaBundle:Horario')->cantHorarioDisciplina($codigoEdi);

            if(!empty($cantidad['cantHorarios'])){
                $mensaje = "-1";
                return new JsonResponse($mensaje);
            
            }else{

                $usuario = $this->getUser()->getId();
                $em->getRepository('AkademiaBundle:Horario')->eliminarDisciplina($codigoEdi,$usuario);

                $temporadaActiva = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();

                if(!empty($temporadaActiva))
                    $idTemporadaActiva = $temporadaActiva[0]['temporadaId'];
                else
                    $idTemporadaActiva = null;

                $ComplejoDisciplinas = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorarios($idComplejo,$idTemporada);
                $Disciplinas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasDiferentes($idComplejo,$idTemporada);
                $complejo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejoById($idComplejo);
                $nombreComplejo = $complejo[0]['complejoNombre'];

                $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo,$idTemporada);
                $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

                echo $this->renderView('AkademiaBundle:Disciplina_Horario_Beneficiario:table_disciplina_horario_beneficiario.html.twig',
                        array(
                                "complejosDisciplinas" => $ComplejoDisciplinas ,
                                "horarios" => $Horarios, 
                                "disciplinas" => $Disciplinas,
                                "nombreComplejo"=> $nombreComplejo, 
                                'turnos'=> $turnos,
                                'idTemporada' => $idTemporada,
                                'idTemporadaActiva' => $idTemporadaActiva,
                                'idComplejo' => $idComplejo
                        )
                    );
                exit; 
            }
        }
}