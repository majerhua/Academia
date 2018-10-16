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

    public function getTableHorarioMigracionAction(Request $request){

        $ediCodigo = $request->request->get('ediCodigo');
        $modalidad = $request->request->get('modalidad');
        $etapa = $request->request->get('etapa');
        $edad = $request->request->get('edad');
        $horarioActual = $request->request->get('horario-actual');

        $flagHorariosDisponiblesMigracion = 0;
        $turnsDiscipline = NULL;

        $em = $this->getDoctrine()->getManager();

        $horariosDisponiblesMigracion = $em->getRepository('AkademiaBundle:Migracion')->getHorariosDisponibleMigracion($ediCodigo,$modalidad,$etapa,$edad,$horarioActual);

        if( !empty($horariosDisponiblesMigracion) ){
            $flagHorariosDisponiblesMigracion = 1;
            $turnsDiscipline = $em->getRepository('AkademiaBundle:Horario')->getTurnsDiscipline($ediCodigo);
        }

        return new Response( $this->renderView('AkademiaBundle:Migracion_Asistencia:table_horario_migracion.html.twig',array('horarios' => $horariosDisponiblesMigracion , "turnos" => $turnsDiscipline,"flagHorariosDisponiblesMigracion" => $flagHorariosDisponiblesMigracion ) ) );
    }

    public function guardarAsistenciaBeneficiariosAction(Request $request){

        if($request->isXmlHttpRequest()){

            $estadoRespuesta = null;

            $asistenciaBeneMen = $request->request->get('asistencia-mensual');
            $idHorario = $request->request->get('idHorario');
            $usuario = $this->getUser()->getId();

            $em = $this->getDoctrine()->getManager();

            $cantidadRegistroAsistencia = $em->getRepository('AkademiaBundle:Asistencia')->getCantidadRegistrosMesActual($idHorario);

            if( count($cantidadRegistroAsistencia) <= 9 && count($cantidadRegistroAsistencia) > -1 ){
                 $em->getRepository('AkademiaBundle:Asistencia')->insertAsistenciaBeneficiarios($asistenciaBeneMen,$idHorario,$usuario);
                 $estadoRespuesta = 1;
            }else{
                 $estadoRespuesta = 2;
            }

            return new JsonResponse($estadoRespuesta);
        }
    }

    // FUNCION PARA EL RENDERIZADO INICIAL

 	public function beneficiariosAction(Request $request, $idHorario ,$idTemporada){
        
        $em = $this->getDoctrine()->getManager();

        $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorarioBeneficiario($idHorario);
        $fichaTurnoHorario = $em->getRepository('AkademiaBundle:Horario')->getTurnosIndividual($Horarios[0]['idHorario']);
        $Horarios[0]['turnos']=$fichaTurnoHorario;

        $Beneficiarios = $em->getRepository('AkademiaBundle:Horario')->getBeneficiarios($idHorario);
        $Asistencias = $em->getRepository('AkademiaBundle:Asistencia')->getMostrarAsistencia();
        $Categorias = $em->getRepository('AkademiaBundle:Categoria')->getMostrarCategoria();

        $horInscritos = $em->getRepository('AkademiaBundle:Movimientos')->getCantInscritos($idHorario);
        $movAsis = $em->getRepository('AkademiaBundle:Movimientos')->getCantAsistencias(2,$idHorario);
        $movRet = $em->getRepository('AkademiaBundle:Movimientos')->getCantRetirados(3,$idHorario);
        $movSel = $em->getRepository('AkademiaBundle:Movimientos')->getCantSeleccionados(2,$idHorario);

        $cantidadMesesTemporada = $em->getRepository('AkademiaBundle:Temporada')->getCantidadMesesTemporadaEnCurso($idTemporada);
        $mesesTemporada = $cantidadMesesTemporada[0];
        $mesInicio = intval( $mesesTemporada['mes_inicio_temporada'] );
        $mesFin = intval( $mesesTemporada['mes_fin_temporada'] );

        $meses = [];

        for ($i = $mesInicio; $i <= $mesFin; $i++) {
            array_push($meses,$i);
        }

        $asistenciaMensualInscribete = $em->getRepository('AkademiaBundle:Migracion')->getAsistenciaMensualInscribete($idHorario);

        $arrayTemporada = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();

        if( !empty($arrayTemporada) )
            $idTemporadaActiva = $arrayTemporada[0]['temporadaId'];
        else
            $idTemporadaActiva = null;

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        return $this->render('AkademiaBundle:Default:beneficiarios.html.twig', array("horarios" => $Horarios, "beneficiarios" => $Beneficiarios, "asistencias" => $Asistencias, "categorias" => $Categorias, "asistentes" => $movAsis, "retirados" => $movRet, "seleccionados" => $movSel , "inscritos"=>$horInscritos , "id" =>$idHorario, 'mesesTemporada' => $meses,'asistenciaMensual' => $asistenciaMensualInscribete,'ediCodigo'=>$Horarios[0]['ediCodigo'],'modalidad'=>$Horarios[0]['discapacitados'],'idTemporada' => $idTemporada, 'idTemporadaActiva'=>$idTemporadaActiva,'descripcionTemporada'=>$descripcionTemporada ));
    }
    
    // GENERAR NUEVO MOVIMIENTO
    public function estadoBeneficiarioAction(Request $request){
       
        if($request->isXmlHttpRequest()){

            $idFicha = $request->request->get('idFicha');
            $idAsistencia = $request->request->get('idAsistencia');
            $idCategoria = $request->request->get('idCategoria');
            $horarioActual = $request->request->get('horarioActual');
            $horarioAMigrar = $request->request->get('horarioAMigrar');

            $usuario = $this->getUser()->getId();
           
            $em = $this->getDoctrine()->getManager();

            $nuevoMovimiento = $em->getRepository('AkademiaBundle:Movimientos')->nuevoMovimiento($idCategoria, $idAsistencia, $idFicha,$usuario,$horarioActual,$horarioAMigrar);

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

    // CANTIDAD DE INSCRITOS 
    
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