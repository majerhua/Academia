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

        $disciplinasTotalesActivas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasActivas();
        return $this->render('AkademiaBundle:Migracion_Asistencia:disciplinas.html.twig', array('disciplinas' => $disciplinasTotalesActivas));
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

        return new JsonResponse($estadoUpdDis);
    }

}
