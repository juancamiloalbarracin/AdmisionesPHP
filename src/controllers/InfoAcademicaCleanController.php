<?php
namespace UDC\SistemaAdmisiones\Controllers;

use UDC\SistemaAdmisiones\Models\InfoAcademicaClean;
use UDC\SistemaAdmisiones\Middleware\AuthMiddleware;

class InfoAcademicaCleanController
{
    private InfoAcademicaClean $model;

    public function __construct()
    {
        $this->model = new InfoAcademicaClean();
    }

    public function get(?array $user): array
    {
        if (!$user) return ['success'=>false,'message'=>'No autorizado','code'=>401];
        $userId = (int)($user['id'] ?? $user['user_id']);
        $data = $this->model->getByUserId($userId);
    return ['success'=>true,'infoAcademica'=>$data,'code'=>200];
    }

    public function save(array $input, ?array $user): array
    {
        if (!$user) return ['success'=>false,'message'=>'No autorizado','code'=>401];
        $userId = (int)($user['id'] ?? $user['user_id']);

        // Campos requeridos mínimos
        $required = ['nombreInstitucion','ciudadInstitucion','departamentoInstitucion','tipoBachillerato','jornada','caracterInstitucion','anoGraduacion'];
        $missing = [];
        foreach ($required as $r) if (empty($input[$r])) $missing[] = $r;
        if (!empty($missing)) return ['success'=>false,'message'=>'Faltan campos requeridos','errors'=>$missing,'code'=>400];

        // Mapear y guardar
        $mapped = [
            'nombre_institucion'=>$input['nombreInstitucion'],
            'ciudad_institucion'=>$input['ciudadInstitucion'],
            'departamento_institucion'=>$input['departamentoInstitucion'],
            'tipo_bachillerato'=>$input['tipoBachillerato'],
            'jornada'=>$input['jornada'],
            'caracter_institucion'=>$input['caracterInstitucion'],
            'ano_graduacion'=>$input['anoGraduacion'],
            'promedio_academico'=>$input['promedioAcademico'] ?? null,
            'puntaje_icfes'=>$input['puntajeIcfes'] ?? null,
            'posicion_curso'=>$input['posicionCurso'] ?? null,
            'total_estudiantes'=>$input['totalEstudiantes'] ?? null,
            'observaciones'=>$input['observaciones'] ?? null
        ];

    $res = $this->model->saveForUser($userId, $mapped);
    if ($res) return ['success'=>true,'message'=>'Guardado exitoso','infoAcademica'=>$this->model->getByUserId($userId),'code'=>200];
    return ['success'=>false,'message'=>'Error guardando','code'=>500];
    }
}
