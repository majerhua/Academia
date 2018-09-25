<?php

namespace AkademiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class MigracionAsistenciaController extends Controller
{
	public function disciplinaPrincipalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager(); 
        $estadoUpdDis = NULL;

        $disciplinasTotalesActivas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasTotales();
        return $this->render('AkademiaBundle:Migracion_Asistencia:disciplinas.html.twig', array('estadoUpdDis'=>$estadoUpdDis,'disciplinas' => $disciplinasTotalesActivas));
    }

	public function disciplinaConfiguracionByIdAction(Request $request)
    {

    	$idDisciplina = $request->get('idDisciplina');
        $em = $this->getDoctrine()->getManager(); 

       	$disciplina = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinaConfiguracionById($idDisciplina);

        $encoders = array(new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(1);
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($disciplina,'json');
        return new JsonResponse($jsonContent);
    }

	public function updateDisciplinaAction(Request $request)
    {
    	
    	$idDisciplina = $request->get('idDisciplina');
    	$convencionalEdadMinima = $request->get('convencional-edad-minima');
    	$convencionalEdadMaxima = $request->get('convencional-edad-maxima');
    	$discapacitadoEdadMinima = $request->get('discapacitado-edad-minima');
    	$discapacitadoEdadMaxima = $request->get('discapacitado-edad-maxima');
        
        $em = $this->getDoctrine()->getManager(); 
        $estadoUpdDis = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->updateDisciplina($idDisciplina,$convencionalEdadMinima,$convencionalEdadMaxima,$discapacitadoEdadMinima,$discapacitadoEdadMaxima);

        $disciplinasTotalesActivas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasTotales();

        echo $this->renderView('AkademiaBundle:Migracion_Asistencia:table_disciplina.html.twig',array('estadoUpdDis'=>$estadoUpdDis,'disciplinas' => $disciplinasTotalesActivas));
        exit;
    }

public function insertAsistenciaBeneficiarios($arrayAsistenciaMensual,$idHorario,$usuario){

        $query = "  SELECT DISTINCT ins.id inscribeteId,mov.categoria_id,mov.asistencia_id
                    FROM ACADEMIA.horario hor 
                    INNER JOIN ACADEMIA.inscribete ins ON ins.horario_id = hor.id
                    INNER JOIN (SELECT MAX(movMax.id) idMov, movMax.inscribete_id insMov 
                                FROM ACADEMIA.movimientos movMax
                                GROUP BY movMax.inscribete_id
                                ) maxMov  ON  maxMov.insMov = ins.id
                    INNER JOIN ACADEMIA.movimientos mov ON mov.id = maxMov.idMov
                    WHERE ins.estado = 2 AND hor.id = $idHorario";
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $inscribeteHorario = $stmt->fetchAll();

        foreach ( $inscribeteHorario as $insHor ) {

            $codAsis = 4;

            for ( $i=0 ; $i < count($arrayAsistenciaMensual) ; $i++) { 

                if ( $insHor['inscribeteId'] == $arrayAsistenciaMensual[$i])
                    $codAsis = 2;
                
            }

            $categoriaId = $insHor['categoria_id'];
            $inscribeteId = $insHor['inscribeteId'];

            $queryInsertMovAsis = "INSERT INTO ACADEMIA.movimientos(categoria_id,asistencia_id,
                inscribete_id,usuario_valida)
                VALUES( $categoriaId , $codAsis , $inscribeteId , $usuario )";
        
            $stmt = $this->getEntityManager()->getConnection()->prepare($queryInsertMovAsis);
            $stmt->execute();
        }
    }
    
}
