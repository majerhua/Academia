<?php

namespace AkademiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MigracionAsistenciaController extends Controller
{

	public function disciplinaPrincipalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager(); 
        $disciplinasTotalesActivas = $em->getRepository('AkademiaBundle:DisciplinaDeportiva')->getDisciplinasActivas();
        return $this->render('AkademiaBundle:Migracion_Asistencia:disciplinas.html.twig', array('disciplinas' => $disciplinasTotalesActivas));
    }

}
