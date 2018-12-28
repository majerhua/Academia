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
    public function mostrarHorariosLandingAction(Request $request,$estado,$idTemporada){

        $em = $this->getDoctrine()->getManager();

        $mdDepartamentsByDisability = $em->getRepository('AkademiaBundle:Departamento')->getDepartmentsLandingByDisability($estado,$idTemporada);
        $mdProvinceByDisability = $em->getRepository('AkademiaBundle:Provincia')->getProvincesLandingByDisability($estado,$idTemporada);
        $mdDistrictsByDisability = $em->getRepository('AkademiaBundle:Distrito')->getDistrictsLandingByDisability($estado,$idTemporada);
        $mdComplexesByDisability = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplexesLandingByDisability($estado,$idTemporada);
        $mdDisciplinesByDisability = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinesLandingByDisability($estado,$idTemporada);

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);


        return $this->render('AkademiaBundle:Default:mostrarHorariosLanding.html.twig', array('departamentosFlag' => $mdDepartamentsByDisability , "provinciasFlag" => $mdProvinceByDisability ,'distritosFlag' => $mdDistrictsByDisability,'complejosDeportivos' => $mdComplexesByDisability, 'disciplinasDeportivas' => $mdDisciplinesByDisability,'estado'=>$estado,'descripcionTemporada'=>$descripcionTemporada ) );
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

        $confPerfilUsuario = $this->container->getParameter('perfilUsuario');
        $usuario = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);
        //OBTENEMOS LA TEMPORADA COMPARAR CON LA TEMPORADA QUE RECIBIMOS COMO PARAMETRO
        $arrayTemporada = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();

        if(!empty($arrayTemporada))
            $idTemporadaActiva = $arrayTemporada[0]['temporadaId'];
        else
            $idTemporadaActiva = null;

        if( $usuario->getIdPerfil() == $confPerfilUsuario['administrador'] ){
            $disciplinasComplejo=NULL;
            $ubigeos = $em->getRepository('AkademiaBundle:Distrito')->getDepartamentos();
            $complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();

        }else if( $usuario->getIdPerfil() == $confPerfilUsuario['macro'] ){
            $disciplinasComplejo = NULL;
            $ubigeos = $em->getRepository('AkademiaBundle:Distrito')->getDepartamentosUsuario($usuario->getId());
            $complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();

        }else if( $usuario->getIdPerfil() == $confPerfilUsuario['monitor'] ){
            $disciplinasComplejo = NULL;
            $ubigeos = $em->getRepository('AkademiaBundle:Distrito')->getProvinciasUsuario($usuario->getId());
            $complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejos();

        }else if( $usuario->getIdPerfil() == $confPerfilUsuario['promotor'] ){
            $disciplinasComplejo = NULL;
            $ubigeos = NULL;
            $complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejosUsuario($usuario->getId());

        }else if( $usuario->getIdPerfil() == $confPerfilUsuario['profesor'] ){
            $ubigeos = NULL;
            $complejos = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejosDisciplinaUsuario($usuario->getId());
            $disciplinasComplejo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getDisciplinaComplejoUsuario($usuario->getId());
            $idDisciplina = $disciplinasComplejo[0]['disciplinaId'];


            $idComplejo = $disciplinasComplejo[0]['complejoId'];
            $complejo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejoById($idComplejo);
            $nombreComplejo = $complejo[0]['complejoNombre'];

            $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo,$idTemporada);
            $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

            $ComplejoDisciplinas = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorariosByDisciplinaId($idComplejo,$idTemporada,$idDisciplina);

            $Disciplinas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasDiferentes($idComplejo,$idTemporada);
            $disciplinasByComplejoTemporada = $em->getRepository('AkademiaBundle:Usuarios')->disciplinasByComplejoTemporada($idComplejo,$idTemporada);
            return $this->render('AkademiaBundle:Default:horarios.html.twig', 
                                array(
                                    "complejosDisciplinas" => $ComplejoDisciplinas,
                                    'idTemporada' => $idTemporada,
                                    'idTemporadaActiva' => $idTemporadaActiva,
                                    'descripcionTemporada' => $descripcionTemporada ,
                                    'horarios' => $Horarios,
                                    'ubigeos' => $ubigeos,
                                    'turnos'=> $turnos,
                                    'complejos' => $complejos,
                                    "disciplinas" => $Disciplinas,
                                    'idComplejo' => $idComplejo,
                                    "nombreComplejo"=> $nombreComplejo,
                                    'disciplinasComplejo' => $disciplinasComplejo,
                                    'disciplinasByComplejoTemporada' => $disciplinasByComplejoTemporada
                                )
                    );
        }

        return $this->render('AkademiaBundle:Default:horarios.html.twig', 
                                array(
                                    'idTemporada' => $idTemporada,
                                    'idTemporadaActiva' => $idTemporadaActiva,
                                    'descripcionTemporada' => $descripcionTemporada ,
                                    'ubigeos' => $ubigeos,
                                    'complejos' => $complejos,
                                    'disciplinasComplejo' => $disciplinasComplejo
                                )
                    );
    }

    public function tableHorarioComplejoBeneficiarioAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $arrayTemporada = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();

        if(!empty($arrayTemporada))
            $idTemporadaActiva = $arrayTemporada[0]['temporadaId'];
        else
            $idTemporadaActiva = null;

        $idComplejo = $request->request->get('idComplejo');
        $idDisciplina = $request->request->get('idDisciplina');
        $idTemporada = $request->request->get('idTemporada');

        $usuario = $this->getUser();
        $confPerfilUsuario = $this->container->getParameter('perfilUsuario');

        if( $usuario->getIdPerfil() == $confPerfilUsuario['profesor'] )
            $ComplejoDisciplinas = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorariosByDisciplinaId($idComplejo,$idTemporada,$idDisciplina);
        else
            $ComplejoDisciplinas = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejosDisciplinasHorarios($idComplejo,$idTemporada);                      

        $complejo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejoById($idComplejo);
        $nombreComplejo = $complejo[0]['complejoNombre'];

        $Horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo,$idTemporada);
        $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

        $Disciplinas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasDiferentes($idComplejo,$idTemporada);

        $disciplinasByComplejoTemporada = $em->getRepository('AkademiaBundle:Usuarios')->disciplinasByComplejoTemporada($idComplejo,$idTemporada);

        $usuarios = $em->getRepository('AkademiaBundle:Usuarios')->getUsuariosProfesoresAll($idComplejo,$idTemporada);

        echo $this->renderView('AkademiaBundle:Disciplina_Horario_Beneficiario:table_disciplina_horario_beneficiario.html.twig',
                        array(
                                "complejosDisciplinas" => $ComplejoDisciplinas ,
                                "horarios" => $Horarios, 
                                "disciplinas" => $Disciplinas,
                                "nombreComplejo"=> $nombreComplejo, 
                                'turnos'=> $turnos,
                                'idTemporada' => $idTemporada,
                                'idTemporadaActiva' => $idTemporadaActiva,
                                'idComplejo' => $idComplejo,
                                'usuarios' => $usuarios,
                                'disciplinasByComplejoTemporada' => $disciplinasByComplejoTemporada
                        )
                    );
                exit;
    }

    // FUNCION PARA LA CREACION DE HORARIOS
    public function crearHorarioAction(Request $request){
             
        $em = $this->getDoctrine()->getManager();

        $idDisciplina = $request->request->get('idDisciplina');
        $idTemporada = $request->request->get('idTemporada'); 
        $idComplejo = $request->request->get('idComplejo');

        $ediCodigo = $em->getRepository('AkademiaBundle:Horario')->getCapturarEdiCodigo($idComplejo, $idDisciplina,$idTemporada); 

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

        if( $mensaje == "2" ){

            $horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo,$idTemporada);
            $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

            echo $this->renderView('AkademiaBundle:Disciplina_Horario_Beneficiario:table_disciplina_horario.html.twig',
                                        array(
                                            'idTemporada'=>$idTemporada,
                                            'horarios' => $horarios,
                                            'turnos' => $turnos,
                                            'idDisciplina'=> $idDisciplina
                                        )
                            );
            exit;

        }else{
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
        
        $idHorario = $request->request->get('idHorario');
        $idTemporada = $request->request->get('idTemporada');
        $idDisciplina = $request->request->get('idDisciplina');
        $idComplejo = $request->request->get('idComplejo');

        $usuario = $this->getUser();
        $usuarioId = $usuario->getId();

        $em = $this->getDoctrine()->getManager();
        $cantidadInscritosByHorario = $em->getRepository('AkademiaBundle:Horario')->cantidadInscritos($idHorario);


        if( !empty($cantidadInscritosByHorario) ){
            echo "-2"; //NO SE PUEDE ELIMINAR PORQUE EXISTEN INSCRITOS ACTIVOS EN EL HORARIO
            exit;
        }

        if(!empty($idHorario) && !empty($usuarioId) ){

            $em ->getRepository('AkademiaBundle:Horario')->getOcultarHorario($idHorario, $usuarioId);

            $horarios = $em->getRepository('AkademiaBundle:Horario')->getHorariosComplejos($idComplejo,$idTemporada);
            $turnos = $em->getRepository('AkademiaBundle:Horario')->getTurnosComplejos($idComplejo);

            echo $this->renderView('AkademiaBundle:Disciplina_Horario_Beneficiario:table_disciplina_horario.html.twig',
                                        array(
                                            'idTemporada'=>$idTemporada,
                                            'horarios' => $horarios,
                                            'turnos' => $turnos,
                                            'idDisciplina'=> $idDisciplina
                                        )
                            );
            exit;
        }else{
            echo "-1"; //NO SE PUDO OCULTAR HORARIO.
            exit;
        }
    }
}