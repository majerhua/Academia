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

class PreinscripcionController extends Controller
{

    public function indexAction(Request $request){

        $flag_mantenimiento = $this->container->getParameter('flagMantenimiento');

        if( $flag_mantenimiento == 0 ){

            $em = $this->getDoctrine()->getManager();

            $arrayTemporadaActiva = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();
            $flagFinTemporada = false;
            $fechaPreInscripcion = null;
            $anio = null;
            $ciclo = null;
            
            if( !empty( $arrayTemporadaActiva) ){

                $idTemporada =  $arrayTemporadaActiva[0]['temporadaId'];
                $arrayFaseTemporada = $em->getRepository('AkademiaBundle:Temporada')->faseTemporadaActiva($idTemporada);
                $faseTemporada = $arrayFaseTemporada[0]['fase'];

                if( $faseTemporada == 10 ){

                    $arrayFechaPreInscripcion = $em->getRepository('AkademiaBundle:Temporada')->getFechaPreInscripcion($idTemporada);
                    $fechaPreInscripcion = $arrayFechaPreInscripcion[0]['fecha_preinscripcion'];
                    $ciclo = $arrayFechaPreInscripcion[0]['ciclo'];
                    $anio = $arrayFechaPreInscripcion[0]['anio'];
                }

                else if( $faseTemporada == 40 )
                    $flagFinTemporada = true;
                
            }else
                $flagFinTemporada = true;


            if( $flagFinTemporada ){

                $faseTemporada = 40; //OBTENEMOS LA FECHA DE PRE-INSCRIPCION PROXIMA

                $arrayProximaTemporada = $em->getRepository('AkademiaBundle:Temporada')->temporadaProxima();

                if( !empty($arrayProximaTemporada[0]['pre_inscripciones']) ){
                    $fechaPreInscripcion = $arrayProximaTemporada[0]['fecha_preinscripcion'];
                    $ciclo = $arrayProximaTemporada[0]['ciclo'];
                    $anio = $arrayProximaTemporada[0]['anio'];
                }
                else
                    $faseTemporada = 50; //NO EXISTE TEMPORADA PROXIMA
            }

            $arrayTemporada = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();
            $idTemporada =0;
            
            if( !empty( $arrayTemporada) )
                $idTemporada =  $arrayTemporada[0]['temporadaId'];

            else{

                if( !empty( $this->getUser() ) ){

                        $usuario = $this->getUser();
                        $roles = $usuario->getRoles();
                        
                    if( $roles[0] == "ROLE_ANALISTA" ){
                        $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadasAnalista();
                    }else{
                        $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadas();
                    }

                    if(!empty($temporadasHabilitadas)){
                        $idTemporada = $temporadasHabilitadas[0]['temporadaId'];
                    }else{
                        $idTemporada = NULL;
                    }

                    
                }
            }

            return $this->render('AkademiaBundle:Default:index.html.twig',array('faseTemporada'=>$faseTemporada, 'fechaPreInscripcion'=>$fechaPreInscripcion, 'anio'=>$anio, 'ciclo'=>$ciclo,'idTemporada'=>$idTemporada )); 
        }else if($flag_mantenimiento == 1) {
            return $this->render('AkademiaBundle:Default:pagina_en_mantenimiento.html.twig'); 
        }
        
    }

    public function fichaPreInscripcionAction(Request $request,$idTemporada){

        $em = $this->getDoctrine()->getManager();

        if($request->isXmlHttpRequest()){
            
            $parentesco = $request->request->get('persona');
            
            if($parentesco == "apoderado" || $parentesco == "hijo"){
                $dni = $request->request->get('dni');
               
                $datos = $em->getRepository('AkademiaBundle:Apoderado')->busquedaDni($dni);
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

        $usuario = $this->getUser();

        if( !empty( $usuario ) )
            $flagUser = 1;
        else
            $flagUser = 0;
        

        //Si la temporada es cero entonces se pasa como parametro la temporada actual sino la ultima temporada
        if( $idTemporada == 0 ){

            $arrayTemporada = $em->getRepository('AkademiaBundle:Temporada')->getTemporadaActiva();
            
            if(!empty( $arrayTemporada))
                $idTemporada =  $arrayTemporada[0]['temporadaId'];
            else{

                if( !empty( $this->getUser() ) ){
                    $temporadasHabilitadas = $em->getRepository('AkademiaBundle:Temporada')->getTemporadasHabilitadas();
                    $idTemporada = $temporadasHabilitadas[0]['temporadaId'];
                }
            }
        }

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        $mdlDitritoCD = $em->getRepository('AkademiaBundle:Distrito')->getDitritosCD( );
        $mdlProvinciasCD = $em->getRepository('AkademiaBundle:Distrito')->getProvinciasCD( );
        $mdlDepartamentosCD = $em->getRepository('AkademiaBundle:Distrito')->getDepartamentosCD( );
        $mdlDepartamento = $em->getRepository('AkademiaBundle:Distrito')->getDepartamentos( );
        $mdlProvincia = $em->getRepository('AkademiaBundle:Distrito')->getProvincias( );
        $mdlDistrito = $em->getRepository('AkademiaBundle:Distrito')->getDistritos( );
        $mdlComplejoDeportivo = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplejosDeportivos();
        $mdlComplejoDisciplina = $em->getRepository('AkademiaBundle:ComplejoDisciplina')->getComplejoDisciplinas();

        $rankAgesPreRegistration = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getRankAgePreRegistration();
        
        return $this->render( 'AkademiaBundle:Default:fichaPreInscripcion.html.twig' , array( "complejosDeportivo" => $mdlComplejoDeportivo , "complejosDisciplinas" => $mdlComplejoDisciplina , "departamentos" => $mdlDepartamento,"provincias" => $mdlProvincia ,"distritos" => $mdlDistrito ,'ditritosCD' => $mdlDitritoCD , "departamentosCD" => $mdlDepartamentosCD ,'provinciasCD' => $mdlProvinciasCD, 'flagUser' => $flagUser, 'rankAgesPreRegistration' => $rankAgesPreRegistration[0] , 'idTemporada'=> $idTemporada, 'descripcionTemporada' => $descripcionTemporada ));     
    }

    //FUNCION PARA CARGAR LOS DATOS DE LAS DISCIPLINAS Y HORARIOS SELECCIONADOS

	public function registroFinalAction(Request $request,$disability){

        $ageBeneficiario = $request->request->get('edadBeneficiario');
        $idTemporada = $request->request->get('idTemporada');



        $em = $this->getDoctrine()->getManager();
        $Role = $this->getUser();
        
        $flagUser='';
        //VISUALIZAR REGISTRO FINAL USUARIOS COMPLEJOS
        if($Role != NULL){
            
            $flagUser ='1';

            $mdDepartmentsByDisability = $em->getRepository('AkademiaBundle:Departamento')->getDepartmentsPromotorByDisability($disability,$ageBeneficiario , $idTemporada );

            $mdProvincesByDisability = $em->getRepository('AkademiaBundle:Provincia')->getProvincesPromotorByDisability($disability,$ageBeneficiario , $idTemporada);

            $mdDistrictsByDisability = $em->getRepository('AkademiaBundle:Distrito')->getDistrictsPromotorByDisability($disability,$ageBeneficiario ,  $idTemporada);

            $mdComplexesByDisability = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplexesPromotorByDisability($disability,$ageBeneficiario, $idTemporada);

            $mdlDisciplinesByDisability = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinesPromotorByDisability($disability,$ageBeneficiario,$idTemporada);
        //VISUALIZAR REGISTRO FINAL PUBLICO GENERAL
        }else{
            
            $flagUser = '0';

            $mdDepartmentsByDisability = $em->getRepository('AkademiaBundle:Departamento')->getDepartmentsPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada);


            $mdProvincesByDisability = $em->getRepository('AkademiaBundle:Provincia')->getProvincesPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada);
  
            $mdDistrictsByDisability = $em->getRepository('AkademiaBundle:Distrito')->getDistrictsPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada);

            $mdComplexesByDisability = $em->getRepository('AkademiaBundle:ComplejoDeportivo')->getComplexesPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada);
            

            $mdlDisciplinesByDisability = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinesPublicGeneralByDisability($disability,$ageBeneficiario,$idTemporada);
               
        }

        if($disability == 1){
            $mensaje = 'Sólo para participantes con discapacidad.';
        }else{
            $mensaje = '';
        }

        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaById($idTemporada);

        return $this->render('AkademiaBundle:Default:registroFinal.html.twig' , array('departamentosFlag' => $mdDepartmentsByDisability , "provinciasFlag" => $mdProvincesByDisability ,'distritosFlag' => $mdDistrictsByDisability,'complejosDeportivos' => $mdComplexesByDisability, 'disciplinasDeportivas' => $mdlDisciplinesByDisability ,'mensaje' => $mensaje,'idTemporada'=>$idTemporada , 'descripcionTemporada' => $descripcionTemporada ));     
    }

    //FUNCION PARA REGISTRAR A LOS PREINSCRITOS 
    public function registrarAction(Request $request){
        
        if($request->isXmlHttpRequest()){

            $distrito = $request->request->get('distrito');
            $idTemporada = $request->request->get('idTemporada');

            if(intval($distrito) <> 0 ){
                //INICIO  VALIDACION DISTRITO

                $em = $this->getDoctrine()->getManager();

                $dniParticipante = $request->request->get('dniParticipante');

                $idInscribeteOrDataParticipante = $em->getRepository('AkademiaBundle:Participante')->getPreInscripcionUnica($dniParticipante,$idTemporada);
                
                if( !empty($idInscribeteOrDataParticipante[0]['id']) ){

                    $encoders = array(new JsonEncoder());
                    $normalizer = new ObjectNormalizer();
                    $normalizer->setCircularReferenceLimit(1);
                    $normalizer->setCircularReferenceHandler(function ($object) {
                        return $object->getId();
                    });
                    $normalizers = array($normalizer);
                    $serializer = new Serializer($normalizers, $encoders);
                    $jsonContent = $serializer->serialize($idInscribeteOrDataParticipante,'json');
                    return new JsonResponse($jsonContent);
                }

                $idHorario = $request->request->get('idHorario');
                
                $vacantesHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosVacantes($idHorario);
                $cantVacantes = $vacantesHorario[0]['vacantes'];

                if($cantVacantes > 0){
                    //DATOS APODERADO
                    $dni = $request->request->get('dni');
                    $tipoDocumentoApoderado = $request->request->get('tipoDocumentoApoderado');
                    $apellidoPaterno = $request->request->get('apellidoPaterno');
                    $apellidoMaterno = $request->request->get('apellidoMaterno');
                    $nombre = $request->request->get('nombre'); 
                    $fechaNacimiento = $request->request->get('fechaNacimiento');
                    $sexo = $request->request->get('sexo');
                    $direccion = $request->request->get('direccion');
                    $telefono = $request->request->get('telefono');
                    $correo = $request->request->get('correo');
                    $estado = $request->request->get('estado');
                    

                    //DATOS PARTICIPANTE
                    $tipoDocumentoParticipante = $request->request->get('tipoDocumentoParticipante');
                    $apellidoPaternoParticipante = $request->request->get('apellidoPaternoParticipante');
                    $apellidoMaternoParticipante = $request->request->get('apellidoMaternoParticipante');
                    $nombreParticipante = $request->request->get('nombreParticipante'); 
                    $fechaNacimientoParticipante = $request->request->get('fechaNacimientoParticipante');
                    $sexoParticipante = $request->request->get('sexoParticipante');
                    $parentesco = $request->request->get('parentesco');
                    $tipoSeguro = $request->request->get('tipoSeguro');
                    $estado = 1;
                    $discapacidad = $request->request->get('discapacidad');

                    //REGISTRAR APODERADO
                    $em = $this->getDoctrine()->getManager();
                    //BUSACMOS AL APODERADO EN LA TABLA GRPERSONA CATASTRO.
                    $percodigoApoderado = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderadoPersona($dni);
                    if(!empty($percodigoApoderado)){
                        
                        // CAPTURAMOS EL ULTIMO RGISTRO DEL APODERADO EN PERSONA
                        $codigo = $em->getRepository('AkademiaBundle:Apoderado')->maxDniPersona($dni);
                        $percodigoApod = $codigo[0]['percodigo'];
                       
                        //ACTUALIZAR LOS DATOS DEL APODERADO
                        $em->getRepository('AkademiaBundle:Apoderado')->actualizarPersona($apellidoPaterno, $apellidoMaterno, $nombre, $fechaNacimiento, $percodigoApod, $telefono, $correo, $direccion, intval($distrito),$sexo,$tipoDocumentoApoderado);

                        //Búsqueda en Academia.apoderado
                        $IDApoderado = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderado($dni);
                    
                        if(!empty($IDApoderado)){
                            // SI EXISTE APODERADO EN LA ENTIDAD ACADEMIA.APODERADO SE ACTUALIZA SUS DATOS
                            $em = $this->getDoctrine()->getManager();
                            $codigo = $em->getRepository('AkademiaBundle:Apoderado')->maxDniAcademiaApod($dni);
                            $idApoderado = $codigo[0]['id'];
                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                            $apoderado = $em->find($idApoderado);
                            $apoderado->setPercodigo($percodigoApod);
                            $em = $this->getDoctrine()->getManager();
                            $em->flush();
                            $idApod = $apoderado->getId();
                        }else{
                            
                            // SI NO EXISTE EL APODERADO EN LA ENTIDAD APODERADO , SE REGISTRA UN NUEVO REGISTRO.
                            $apoderado = new Apoderado();
                            $apoderado->setDni($dni);
                            $apoderado->setPercodigo($percodigoApod);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($apoderado);
                            $em->flush();
                            $idApod = $apoderado->getId();                 
                        }

                    }else{
                        
                        //si no existe apoderado en grpersona, registramos al usuario
                        $datosApoderado = $em->getRepository('AkademiaBundle:Apoderado')->guardarPersona($dni,$apellidoPaterno,$apellidoMaterno, $nombre,$fechaNacimiento,$sexo,$telefono, $correo, $direccion,intval($distrito),$tipoDocumentoApoderado);
                        //retornar el percodigo del nuevo registro
                        $percodigoApoderado = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderadoPersona($dni);
                        $percodigoApod = $percodigoApoderado[0]['id'];
                        //Búsqueda en Academia.apoderadO
                        $IDApoderado = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderado($dni);
                    
                        if(!empty($IDApoderado)){
                            
                            $codigo = $em->getRepository('AkademiaBundle:Apoderado')->maxDniAcademiaApod($dni);
                            $idApoderado = $codigo[0]['id'];
                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                            $apoderado = $em->find($idApoderado);
                            $apoderado->setPercodigo($percodigoApod);
                            $em = $this->getDoctrine()->getManager();
                            $em->flush();
                            $idApod = $apoderado->getId();
                        }else{
                            $apoderado = new Apoderado();
                            $apoderado->setDni($dni);
                            $apoderado->setPercodigo($percodigoApod);
                                      
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($apoderado);
                            $em->flush();
                            $idApod = $apoderado->getId();                 
                        }
                    } 
                
                    //REGISTRAR PARTICIPANTE
                    $em = $this->getDoctrine()->getManager();
                    $percodigoParticipante = $em->getRepository('AkademiaBundle:Participante')->getbuscarParticipantePersona($dniParticipante);
                
                    if(!empty($percodigoParticipante)){
                
                        $codigo = $em->getRepository('AkademiaBundle:Apoderado')->maxDniPersona($dniParticipante);
                        $percodigoPart = $codigo[0]['percodigo'];
                        $em->getRepository('AkademiaBundle:Apoderado')->actualizarPersona($apellidoPaternoParticipante,$apellidoMaternoParticipante,$nombreParticipante,$fechaNacimientoParticipante, $percodigoPart, $telefono, $correo, $direccion, intval($distrito), $sexoParticipante,$tipoDocumentoParticipante);
                        
                        // Búsqueda en academia.participantes 
                        $IDParticipante = $em->getRepository('AkademiaBundle:Participante')->getbuscarParticipante($dniParticipante);
            
                        if(!empty($IDParticipante)){      
                       
                            $codigo = $em->getRepository('AkademiaBundle:Participante')->maxDniAcademiaPart($dniParticipante);
                            $idParticipante = $codigo[0]['id'];
                            $em = $this->getDoctrine()->getRepository(Participante::class);
                            $participante = $em->find($idParticipante);
                            $participante->setPercodigo($percodigoPart);
                           
                            $em = $this->getDoctrine()->getManager();
                            $em->flush();
                            $idParticipanteN = $participante->getId();

                            $em = $this->getDoctrine()->getManager();
                            $em->getRepository('AkademiaBundle:Participante')->getActualizarApoderado($idApod, $idParticipanteN);  
                           
                        }else{
                            
                            $participante = new Participante();
                            $participante->setDni($dniParticipante);
                            $participante->setParentesco($parentesco);
                            $participante->setTipoDeSeguro($tipoSeguro);
                            $participante->setDiscapacitado($discapacidad);
                            $participante->setPercodigo($percodigoPart);
                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                            $buscarApoderadoInscripcion = $em->find($idApod);
                            $participante->setApoderado($buscarApoderadoInscripcion);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($participante);
                            $em->flush();
                            $idParticipanteN= $participante->getId();  

                        } 

                    }else{
                        
                        //Si no existe participante en grpersona, registramos al usuario
                        $em = $this->getDoctrine()->getManager();
                        $datosParticipante = $em->getRepository('AkademiaBundle:Apoderado')->guardarPersona($dniParticipante,$apellidoPaternoParticipante,$apellidoMaternoParticipante, $nombreParticipante,$fechaNacimientoParticipante,$sexoParticipante,$telefono, $correo, $direccion,intval($distrito),$tipoDocumentoParticipante);
                       
                        //Retornar el percodigo del nuevo registro
                        $percodigoParticipante = $em->getRepository('AkademiaBundle:Apoderado')->getbuscarApoderadoPersona($dniParticipante);
                        $percodigoPart = $percodigoParticipante[0]['id'];
                        
                        //Búsqueda en academia.participantes 
                        $IDParticipante = $em->getRepository('AkademiaBundle:Participante')->getbuscarParticipante($dniParticipante);
                
                       
                        if(!empty($IDParticipante)){      
                            
                            $codigo = $em->getRepository('AkademiaBundle:Participante')->maxDniAcademiaPart($dniParticipante);
                            $idParticipante = $codigo[0]['id'];
                            $em = $this->getDoctrine()->getRepository(Participante::class);
                            $participante = $em->find($idParticipante);
                            $participante->setPercodigo($percodigoPart);           
                            $em = $this->getDoctrine()->getManager();
                            $em->flush();
                            $idParticipanteN = $participante->getId();

                            $em = $this->getDoctrine()->getManager();
                            $em->getRepository('AkademiaBundle:Participante')->getActualizarApoderado($idApod, $idParticipanteN);
                           
                        }else{
                            

                            //Participantes nuevos academia.participante
                            $participante = new Participante();
                            $participante->setDni($dniParticipante);
                            $participante->setParentesco($parentesco);
                            $participante->setTipoDeSeguro($tipoSeguro);
                            $participante->setDiscapacitado($discapacidad);
                            $participante->setPercodigo($percodigoPart);
                            $em = $this->getDoctrine()->getRepository(Apoderado::class);
                            $buscarApoderadoInscripcion = $em->find($idApod);
                            $participante->setApoderado($buscarApoderadoInscripcion);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($participante);
                            $em->flush();
                            $idParticipanteN= $participante->getId();       
                        } 
                    }
                        $idHorario = $request->request->get('idHorario');
                        $fechaInscripcion = $hoy = date("Y-m-d");
                        $inscripcion = new Inscribete();
                        $estado=1;
                        $inscripcion->setFechaInscripcion(new \DateTime($fechaInscripcion));
                        $inscripcion->setEstado($estado);
                        $em = $this->getDoctrine()->getRepository(Participante::class);
                        $buscarParticipante = $em->find($idParticipanteN);
                        $inscripcion->setParticipante($buscarParticipante);
                        $em = $this->getDoctrine()->getRepository(Horario::class);
                        $buscarHorario = $em->find($idHorario);
                        $inscripcion->setHorario($buscarHorario);            
                        
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($inscripcion);
                        $em->flush();
                        
                        //Actualizar cantidad de preinscripciones disponibles
                        $preinscripciones = $em->getRepository('AkademiaBundle:Horario')->getActualizarPreinscripcionesHorarios($idHorario);   
                        $preinsHorario = $em->getRepository('AkademiaBundle:Horario')->getHorariosPreinscripciones($idHorario);
                        $cantPre = $preinsHorario[0]['preinscripciones'];
                        if($cantPre == 0){
                            $actConvocatoria = $em->getRepository('AkademiaBundle:Horario')->getActualizaConv($idHorario);
                        }

                        $em2 = $this->getDoctrine()->getManager();

                        $mdlFicha = $em2->getRepository('AkademiaBundle:Inscribete')->getFicha($inscripcion->getId(),$idTemporada);
                        $fichaTurnoHorario = $em->getRepository('AkademiaBundle:Horario')->getTurnosIndividual($idHorario);
                        $mdlFicha[0]['turnos']=$fichaTurnoHorario;
                                       
                        $encoders = array(new JsonEncoder());
                        $normalizer = new ObjectNormalizer();
                        $normalizer->setCircularReferenceLimit(1);
                        $normalizer->setCircularReferenceHandler(function ($object) {
                            return $object->getId();
                        });
                        $normalizers = array($normalizer);
                        $serializer = new Serializer($normalizers, $encoders);
                        $jsonContent = $serializer->serialize($mdlFicha,'json');
                        return new JsonResponse($jsonContent);
                
                }else{
                    $mensaje = 1;
                    return new JsonResponse($mensaje);
                }

                //FIN VALIDACION DISTRITO
            }

            else{
                return new JsonResponse('5');
            }                
        }
    }
    
    // FUNCION PARA GENERAR LA FICHA DE INSCRIPCION 

	public function generarPdfInscripcionAction(Request $request,$id,$idTemporada){   
 	     
        $em = $this->getDoctrine()->getManager();
        $mdlFicha = $em->getRepository('AkademiaBundle:Inscribete')->getFicha($id,$idTemporada);

        
        $fichaTurnoHorario = $em->getRepository('AkademiaBundle:Horario')->getTurnosIndividual($mdlFicha[0]['horario_id']);
        $descripcionTemporada = $em->getRepository('AkademiaBundle:Temporada')->getDescripcionTemporadaByFicha($id);

        $mdlFicha[0]['turnos'] = $fichaTurnoHorario;
        $mdlFicha[0]['temporada'] = $descripcionTemporada; 

        $html = $this->renderView('AkademiaBundle:Pdf:inscripcionPdf.html.twig', ["inscripcion" => $mdlFicha]);

        $pdf = $this->container->get("white_october.tcpdf")->create(
               'PORTRAID', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false
        );
        $pdf->SetAuthor('DNRPD');
        $pdf->SetTitle('La Academia IPD');
        $pdf->SetSubject('La Academia IPD');
        $pdf->SetKeywords('TCPDF, PDF, La Academia IPD, IPD, Sistemas IPD, Deportistas');
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->AddPage();
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->writeHTMLCell(
               $w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true
        );
        $pdf->Output("LaAcademia.pdf", 'I');
        exit;
    }
    
    // FUNCION PARA GENERAR LA DECLARACION JURADA

    public function generarPdfDeclaracionJuradaAction(Request $request , $id,$idTemporada){
        
        $em2 = $this->getDoctrine()->getManager();
        $mdlFicha = $em2->getRepository('AkademiaBundle:Inscribete')->getFicha($id,$idTemporada);
        
        $html = $this->renderView('AkademiaBundle:Pdf:declaracionJuradaPdf.html.twig', ["inscripcion" => $mdlFicha]);
     
        $pdf = $this->container->get("white_october.tcpdf")->create(
               'PORTRAID', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false
        );
        $pdf->SetAuthor('DNRPD');
        $pdf->SetTitle('La Academia IPD');
        $pdf->SetSubject('La Academia IPD');
        $pdf->SetKeywords('TCPDF, PDF, La Academia IPD, IPD, Sistemas IPD, Deportistas');
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->AddPage();
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->writeHTMLCell(
               $w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true
        );
        $pdf->Output("LaAcademia.pdf", 'I');
        exit;

    }


    // ENVIO DE CORREO: RECEPCION DE LAS CONSULTAS Y/O QUEJAS DE LA WEB 

    public function sendemailAction(Request $request){
        if($request->isXmlHttpRequest()){

            $nombre = $request->request->get('nombre');
            $email= $request->request->get('email');
            $mensaje=$request->request->get('message');
            $correo = 'consultasacademiaipd@gmail.com';
            $subject = 'La Academia - Comentarios de '.$nombre;
            $message = 'Hemos recibido un nuevo comentario y/o sugerencia de la web LA ACADEMIA'. "\r\n" ."\r\n".'NOMBRE: '.$nombre. "\r\n" ."\r\n".'CORREO ELECTRÓNICO: '.$email ."\r\n"."\r\n".'COMENTARIO: '."\r\n"."\r\n". $mensaje ;
            $headers = 'From: soporte@ipd.gob.pe' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            mail($correo, $subject, $message, $headers);
            return new JsonResponse("1");
        }
    }
    

    // FUNCION PARA ENVIAR CORREOS AL APODERADO DESPUES DE LA PRE-INSCRIPCION CON LA FICHA Y DECLARACION JURADA.

    public function sendemailapoderadoAction(Request $request){
        if($request->isXmlHttpRequest()){
            $correoApoderado = $request->request->get('correo');
            $nombre = $request->request->get('nombre');
            $id = $request->request->get('id');
            $subject =  'PRE INSCRIPCION CONFIRMADA PARA '.$nombre.'';
            $message =  '<html>'.
                        '<head><title>IPD</title></head>'.
                        '<body><h2>Hola! '.$nombre.' </h2>'.
                        '<hr>'.
                        'Aquí puedes descargar tu ficha de inscripción y la declaración jurada, haz click en estos enlaces:'.
                        '<br>'.
                        '<a href="http://appweb.ipd.gob.pe/academia/web/ajax/pdf/inscripcion/'.$id.'"> Ficha de Inscripción </a>'.
                        '<br>'.
                        '<a href="http://appweb.ipd.gob.pe/academia/web/ajax/pdf/declaracion-jurada/'.$id.'"> Declaración Jurada </a>'.
                        '<br>'.
                        '<p>Acércate al complejo que eligiste para finalizar tu inscripción.</p>'.
                        '<br>'.
                        '<p>NO SE RESERVAN VACANTES</p>'.
                        '<br>'.
                        '<h2><OBLIGATORIO</h2>'.
                        '<ol><li>Presentar ficha de inscripción y declaración jurada firmada y con la huella dactilar del apoderado</li><li>DNI del menor de edad y del apoderado (original y copia).</li><li>Presentar ficha de seguro activo (SIS, EsSalud, o privado).</li><li>Foto tamaño carnet del menor de edad (actual).</li></ol>'.
                        '</body>'.
                        '</html>'
                    ;
            $headers = 'From: soporte@ipd.gob.pe' . "\r\n" .'MIME-Version: 1.0'. "\r\n" .'Content-Type: text/html; charset=ISO-8859-1'. "\r\n";
            mail($correoApoderado,$subject,$message,$headers);
            return new JsonResponse("Enviado");
        }
    }


}