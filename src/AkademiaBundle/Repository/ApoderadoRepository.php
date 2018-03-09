<?php

namespace AkademiaBundle\Repository;

/**
 * ApoderadoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApoderadoRepository extends \Doctrine\ORM\EntityRepository
{

    public function busquedaApoderado($dni){

        $query = "SELECT dni,apellidoPaterno,apellidoMaterno,nombre,sexo,fechaNacimiento,(cast(datediff(dd,fechaNacimiento,GETDATE()) / 365.25 as int)) as edad from ACADEMIA.apoderado where dni='$dni'";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dniApoderado = $stmt->fetchAll();

        return $dniApoderado;

    }

    public function getbuscarApoderado($dni){

        $query ="SELECT id from ACADEMIA.apoderado where dni='$dni'";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dni = $stmt->fetchAll();

        return $dni;
    }

    public function getApoderadoBusqueda($dni){

        $query ="SELECT top 1 id from ACADEMIA.apoderado where dni='$dni' order by id DESC";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dni = $stmt->fetchAll();

        return $dni;
    }

    public function busquedaDni($dni){

        $query = "SELECT perdni as dni,perapepaterno as apellidoPaterno, perapematerno as apellidoMaterno, pernombres as nombre,persexo as sexo,perfecnacimiento as fechaNacimiento,(cast(datediff(dd,perfecnacimiento,GETDATE()) / 365.25 as int)) as edad from grpersona where perdni = '$dni'";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $datos = $stmt->fetchAll();

        return $datos;

    }

    public function getbuscarApoderadoPersona($dni){

        $query ="SELECT percodigo as id from grpersona where perdni='$dni'";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $dni = $stmt->fetchAll();

    	return $dni;
	}

    public function maxDniPersona($dni){

        $query = "SELECT MAX(percodigo) as percodigo from grpersona where perdni = '$dni' group by(perdni)";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $codigo = $stmt->fetchAll();

        return $codigo;
    }

    public function maxDniAcademiaApod($dni){

        $query = "SELECT MAX(id) as id from academia.apoderado where dni = '$dni' group by(dni)";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        $codigo = $stmt->fetchAll();

        return $codigo;
    }


    public function guardarPersona($dni,$apellidoPaterno,$apellidoMaterno, $nombre,$fechaNacimiento,$sexo,$telefono, $correo, $direccion,$distrito){

        $query = "INSERT into grpersona 
            (perdni,perapepaterno,perapematerno,pernombres,perfecnacimiento,persexo,pertelefono,percorreo,perdomdireccion,perubigeo,perfechacrea)
            values('$dni','$apellidoPaterno','$apellidoMaterno','$nombre','$fechaNacimiento',$sexo,$telefono,'$correo','$direccion',$distrito, getdate())";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();

    }

    public function actualizarPersona($apellidoPaterno,$apellidoMaterno,$nombre,$fechaNacimiento, $percodigoApod, $telefono, $correo, $direccion, $distrito, $sexo){

        $query = "UPDATE grpersona set perapepaterno='$apellidoPaterno', perapematerno='$apellidoMaterno', pernombres='$nombre', perfecnacimiento='$fechaNacimiento', pertelefono='$telefono', percorreo = '$correo', perdomdireccion = '$direccion', perubigeo = $distrito, persexo = '$sexo', perfechamodi = getdate() where percodigo = $percodigoApod";

        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();

    }

    public function apoderadoAcademia($idApod){

        $query = "SELECT percodigo from academia.apoderado where percodigo = $idApod";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        
    }

}
