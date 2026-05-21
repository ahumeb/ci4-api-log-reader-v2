<?php

namespace Encender\LogsAPI\Libraries;

/**
 * 20.11.2025
 * Este servicio permite exponer via API la información de los logs de la aplicación para que
 * puedan ser consultados y visualizados de manera clara por un cliente.
 */
class LogReaderService
{

    /**
     * Devuelve un array con las fechas para las cuales existe un archivo de log
     * que puede ser visualizado.
     * @return array
     */
    public function listAvailableLogs(): array {
        // Capturar todos los elementos que existan dentro de la carpeta de configuración de LOGS
        $logsDir = WRITEPATH . 'logs/';
        $contents = scandir($logsDir);
        if (!$contents) { return []; }

        // Recorrer los elementos encontrados en el directorio y devolver solo la fecha
        $logReturnArray = [];
        foreach ($contents as $logContent) {
            // Verifica que sea un archivo y con extensión LOG que identifica a los logs
            if (is_file($logsDir . $logContent) && str_ends_with($logContent, 'log')
            ) {
                $logReturnArray[] = substr($logContent, 0, -4);
            }
        }

        return $logReturnArray;
    }

    /**
     * Recorre el contenido del archivo de logs para un día y devuelve un array conteniendo elementos
     * en formato array associativo con los detalles de cada entrada independientemente de las líneas
     * que contenga.
     * @param string $date
     * @return array<int, array{
     *     level: string, datetime: string, body_preview: string,
     *     body_full: string, lines: integer
     * }>
     */
    public function readLogFrom(string $date): array {
        // Validar que exista el archivo. Si existe, abrirlo para leer los diferentes elementos
        if (!is_file(WRITEPATH . 'logs/' . $date . '.log')) { return []; }
        $log = file_get_contents(WRITEPATH . 'logs/' . $date . '.log');

        // Regex pattern para capturar las diferentes entradas del archivo de log
        $pattern = '~^(?P<level>DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\s*-\s*'
            . '(?P<datetime>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s*-->\s*'
            . '(?P<body>.*?)(?='
            . '^(?:DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\s*-\s*\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\s*-->|$\Z'
            . ')~ms';

        // Ejecutar la captura de las entradas utilizando la RegEx que permite separar las diferentes entradas aún
        // cuando estén compuestas por más de una línea.
        $entries = [];
        if (!preg_match_all($pattern, $log, $matches, PREG_SET_ORDER)) {
            return [];
        }

        // Luego recorrer el contenido para formatearlo de forma tal que sea más claro para que el
        // caller lo pueda trabajar
        foreach ($matches as $m) {
            $body = rtrim($m['body']); // remove trailing newlines
            $entries[] = [
                'level' => $m['level'],
                'datetime' => $m['datetime'],
                'body_preview' => (strlen($body) > 120) ? substr($body, 0, 120) . '…' : $body,
                'body_full' => $body,
                'lines' => substr_count($body, "\n") + 1,
            ];
        }
        return $entries;
    }
}