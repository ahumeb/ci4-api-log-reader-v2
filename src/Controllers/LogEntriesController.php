<?php

namespace Encender\LogsAPI\Controllers;

use Encender\LogsAPI\Libraries\LogReaderService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class LogEntriesController extends ResourceController
{
    protected LogReaderService $logReaderService;

    public function __construct() {
        $this->logReaderService = new LogReaderService();
    }

    /**
     * Devuelve un listado de las fechas para las que hay un archivo de log disponible para ser
     * consultado.
     * @return ResponseInterface
     */
    public function listAvailableLogs(): ResponseInterface {
        $dates = $this->logReaderService->listAvailableLogs();
        $logDates = array_map(function ($ev) {
            return substr($ev, 4, strlen($ev));
        }, $dates);
        return $this->respond([
            "available" => $logDates,
        ]);
    }

    /**
     * Devuelve las diferentes entradas del archivo de loggueo tomado directamente del contenido
     * del archivo en el sistema.
     * @return ResponseInterface
     */
    public function listLogEntries(): ResponseInterface {
        // Capturar la fecha para la que se pide el log
        $fechaLog = $this->request->getJsonVar("fecha");
        if (empty($fechaLog)) {
            return $this->fail(["No fue provista una fecha de log para consultar"]);
        }

        // Buscar entradas de esa fecha y retornarlas
        $entries = $this->logReaderService->readLogFrom("log-" . $fechaLog);
        return $this->respond([
            "entries" => $entries
        ]);
    }
}