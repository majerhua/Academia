<?php

namespace AkademiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Horario
 *
 * @ORM\Table(name="horario")
 * @ORM\Entity(repositoryClass="AkademiaBundle\Repository\HorarioRepository")
 */
class Horario
{
    
    /**
     * @ORM\OneToMany(targetEntity="Inscribete", mappedBy="horario")
     */
    private $inscripciones;

    /**
     * @ORM\ManyToOne(targetEntity="ComplejoDisciplina", inversedBy="horarios")
     * @ORM\JoinColumn(name="complejoDisciplina_id", referencedColumnName="id")
     */
    private $complejoDisciplina;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="vacantes", type="string", length=255)
     */
    private $vacantes;

    /**
     * @var string
     *
     * @ORM\Column(name="horaInicio", type="string", length=255)
     */
    private $horaInicio;

    /**
     * @var string
     *
     * @ORM\Column(name="horaFin", type="string", length=255)
     */
    private $horaFin;

    /**
     * @var string
     *
     * @ORM\Column(name="edadMinima", type="string", length=255)
     */
    private $edadMinima;

    /**
     * @var string
     *
     * @ORM\Column(name="edadMaxima", type="string", length=255)
     */
    private $edadMaxima;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vacantes
     *
     * @param string $vacantes
     *
     * @return Horario
     */
    public function setVacantes($vacantes)
    {
        $this->vacantes = $vacantes;

        return $this;
    }

    /**
     * Get vacantes
     *
     * @return string
     */
    public function getVacantes()
    {
        return $this->vacantes;
    }

    /**
     * Set horaInicio
     *
     * @param string $horaInicio
     *
     * @return Horario
     */
    public function setHoraInicio($horaInicio)
    {
        $this->horaInicio = $horaInicio;

        return $this;
    }

    /**
     * Get horaInicio
     *
     * @return string
     */
    public function getHoraInicio()
    {
        return $this->horaInicio;
    }

    /**
     * Set horaFin
     *
     * @param string $horaFin
     *
     * @return Horario
     */
    public function setHoraFin($horaFin)
    {
        $this->horaFin = $horaFin;

        return $this;
    }

    /**
     * Get horaFin
     *
     * @return string
     */
    public function getHoraFin()
    {
        return $this->horaFin;
    }

    /**
     * Set edadMinima
     *
     * @param string $edadMinima
     *
     * @return Horario
     */
    public function setEdadMinima($edadMinima)
    {
        $this->edadMinima = $edadMinima;

        return $this;
    }

    /**
     * Get edadMinima
     *
     * @return string
     */
    public function getEdadMinima()
    {
        return $this->edadMinima;
    }

    /**
     * Set edadMaxima
     *
     * @param string $edadMaxima
     *
     * @return Horario
     */
    public function setEdadMaxima($edadMaxima)
    {
        $this->edadMaxima = $edadMaxima;

        return $this;
    }

    /**
     * Get edadMaxima
     *
     * @return string
     */
    public function getEdadMaxima()
    {
        return $this->edadMaxima;
    }

    /**
     * Set complejoDisciplina
     *
     * @param \AkademiaBundle\Entity\ComplejoDisciplina $complejoDisciplina
     *
     * @return Horario
     */
    public function setComplejoDisciplina(\AkademiaBundle\Entity\ComplejoDisciplina $complejoDisciplina = null)
    {
        $this->complejoDisciplina = $complejoDisciplina;

        return $this;
    }

    /**
     * Get complejoDisciplina
     *
     * @return \AkademiaBundle\Entity\ComplejoDisciplina
     */
    public function getComplejoDisciplina()
    {
        return $this->complejoDisciplina;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inscripciones = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add inscripcione
     *
     * @param \AkademiaBundle\Entity\Inscribete $inscripcione
     *
     * @return Horario
     */
    public function addInscripcione(\AkademiaBundle\Entity\Inscribete $inscripcione)
    {
        $this->inscripciones[] = $inscripcione;

        return $this;
    }

    /**
     * Remove inscripcione
     *
     * @param \AkademiaBundle\Entity\Inscribete $inscripcione
     */
    public function removeInscripcione(\AkademiaBundle\Entity\Inscribete $inscripcione)
    {
        $this->inscripciones->removeElement($inscripcione);
    }

    /**
     * Get inscripciones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInscripciones()
    {
        return $this->inscripciones;
    }
}
