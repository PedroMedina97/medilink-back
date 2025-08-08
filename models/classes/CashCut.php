<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;
use Utils\Key;
use Classes\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class CashCut extends Entity
{
    public function setCashcut($body)
    {
        $id_user = $body['id_user'];
        $id_subsidiary = $body['id_subsidiary'];
        $start_date = $body['start_date'];
        $end_date = $body['end_date'];
        $total = $body['total'];
        $conn = Helpers::connect(); // Obtener la conexión
        $key = new Key();

        $id = $key->generate_uuid();
        $query = "INSERT INTO cash_cuts VALUES ('$id', '$id_user', '$id_subsidiary', '$start_date', '$end_date', $total, 1, NOW(), NOW())";
        try {
            // Check if email already exists
            Helpers::connect()->query($query);
            return $id;
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error inserting cashcut: " . $e->getMessage());

            return $error;
        }
    }

    public function getGains($month)
    {
        // Validación del mes (esperado: 'YYYY-MM')
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return ['error' => 'Formato de mes inválido. Usa YYYY-MM'];
        }

        $start_date = $month . "-01";
        $end_date = date("Y-m-d", strtotime("$start_date +1 month"));

        $sql = "SELECT 
                DATE(p.created_at) AS fecha,
                SUM(p.amount) AS total_ingresos,
                COUNT(p.id) AS total_pagos
            FROM payments p
            WHERE 
                p.active = 1
                AND p.created_at >= '$start_date'
                AND p.created_at < '$end_date'
            GROUP BY DATE(p.created_at)
            ORDER BY fecha DESC;
        ";

        try {
            return Helpers::myQuery($sql);
        } catch (\Exception $e) {
            error_log("Error: " . $e->getMessage());
            return ['error' => 'Error al ejecutar la consulta'];
        }
    }

    public function getPaymentsByIdCashcut(String $id)
    {
        $sql = "SELECT 
                CONCAT(u.name, ' ', u.lastname) AS usuario_corte,
                s.name AS sucursal,
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'pago_id', p.id,
                        'metodo_pago', p.method,
                        'monto', p.amount,
                        'fecha_pago', p.created_at,
                        'cliente', a.client,
                        'servicio', sv.name
                    )
                ) AS movimientos
            FROM cash_cuts cc
            JOIN users u ON cc.id_user = u.id
            JOIN appointments a ON a.id_subsidiary = cc.id_subsidiary
            JOIN payments p ON p.id_appointment = a.id
            LEFT JOIN services sv ON a.service = sv.id
            LEFT JOIN subsidiaries s ON cc.id_subsidiary = s.id
            WHERE cc.id = '$id'
              AND p.created_at BETWEEN cc.start_date AND cc.end_date
              AND p.status = 'Pagado'
            GROUP BY cc.id, u.name, u.lastname, s.name";
      /*   echo $sql;
        die(); */
        try {
            $data = Helpers::myQuery($sql);
            $info = $data[0];
            $info['movimientos'] = json_decode($info['movimientos'], true);
            $info['id_corte'] = $id; // para el nombre del archivo
            $file = new File();
            return $file->generateCashCutPDF($info);
        } catch (\Exception $e) {
            error_log("Error: " . $e->getMessage());
            return ['error' => 'Error al ejecutar la consulta'];
        }
    }

    public function getPaymentsByIdCashcutExcel(String $id)
    {
        $sql = "SELECT 
                CONCAT(u.name, ' ', u.lastname) AS usuario_corte,
                s.name AS sucursal,
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'pago_id', p.id,
                        'metodo_pago', p.method,
                        'monto', p.amount,
                        'fecha_pago', p.created_at,
                        'cliente', a.client,
                        'servicio', sv.name
                    )
                ) AS movimientos
            FROM cash_cuts cc
            JOIN users u ON cc.id_user = u.id
            JOIN appointments a ON a.id_subsidiary = cc.id_subsidiary
            JOIN payments p ON p.id_appointment = a.id
            LEFT JOIN services sv ON a.service = sv.id
            LEFT JOIN subsidiaries s ON cc.id_subsidiary = s.id
            WHERE cc.id = '$id'
              AND p.created_at BETWEEN cc.start_date AND cc.end_date
              AND p.status = 'Pagado'
            GROUP BY cc.id, u.name, u.lastname, s.name";

        try {
            $data = Helpers::myQuery($sql);
            $info = $data[0];
            $info['movimientos'] = json_decode($info['movimientos'], true);
            $info['id_corte'] = $id; // para el nombre del archivo
            $file = new File();
            return $file->generateCashCutExcel($info);
        } catch (\Exception $e) {
            error_log("Error: " . $e->getMessage());
            return ['error' => 'Error al ejecutar la consulta'];
        }
    }

    public function getCashCutsByDoctor($doctorId)
    {
        $sql = "
        SELECT
                cc.id AS cash_cut_id,
                CONCAT(u.name, ' ', u.lastname) AS user_name,
                s.name AS subsidiary_name,
                cc.start_date,
                cc.end_date,
                cc.total
            FROM cash_cuts cc
            JOIN users u ON cc.id_user = u.id
            JOIN subsidiaries s ON cc.id_subsidiary = s.id
            WHERE cc.id_user = '$doctorId'
            ORDER BY cc.start_date DESC;
                    ";
        try {
            /* echo $sql;
            die(); */
            return Helpers::myQuery($sql);
           
        } catch (\Exception $e) {
            return  error_log("Error: " . $e->getMessage());
        }
        
    }

    public function getCashCutsGroupedWithPayments(string $dateRange)
    {
        $mainParts = explode('::', $dateRange); // separa fechas y sucursal

        if (count($mainParts) !== 2) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Formato inválido. Usa "YYYY-M-YYYY-M-SUCURSAL_ID"']);
            exit;
        }

        $datePart = $mainParts[0];
        $idSucursal = $mainParts[1];

        $parts = explode('-', $datePart);
        if (count($parts) !== 4) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Fechas inválidas. Usa "YYYY-M-YYYY-M"']);
            exit;
        }

        list($startYear, $startMonth, $endYear, $endMonth) = $parts;


        $startMonth = str_pad($startMonth, 2, '0', STR_PAD_LEFT);
        $endMonth = str_pad($endMonth, 2, '0', STR_PAD_LEFT);

        $startDate = "$startYear-$startMonth-01";
        $endDate = date("Y-m-d", strtotime("$endYear-$endMonth-01 +1 month"));

        $sql = "
            SELECT 
                cc.id AS id_corte,
                cc.start_date,
                cc.end_date,
                CONCAT(u.name, ' ', u.lastname) AS usuario,
                s.name AS sucursal,
                p.id AS pago_id,
                p.amount AS monto,
                p.method AS metodo_pago,
                p.created_at AS fecha_pago,
                a.client AS cliente,
                sv.name AS servicio
            FROM cash_cuts cc
            JOIN users u ON cc.id_user = u.id
            JOIN subsidiaries s ON cc.id_subsidiary = s.id
            JOIN appointments a ON a.id_subsidiary = cc.id_subsidiary
            JOIN payments p ON p.id_appointment = a.id
            LEFT JOIN services sv ON a.service = sv.id
            WHERE 
                cc.start_date >= '$startDate'
                AND cc.end_date < '$endDate'
                AND cc.id_subsidiary = '$idSucursal'
                AND p.status = 'Pagado'
                AND p.created_at BETWEEN cc.start_date AND cc.end_date
            ORDER BY cc.start_date ASC, p.created_at ASC
        ";

        try {
            $rows = Helpers::myQuery($sql);
            $result = [];

            foreach ($rows as $row) {
                $id = $row['id_corte'];

                if (!isset($result[$id])) {
                    $result[$id] = [
                        'id_corte' => $id,
                        'usuario' => $row['usuario'],
                        'sucursal' => $row['sucursal'],
                        'start_date' => $row['start_date'],
                        'end_date' => $row['end_date'],
                        'pagos' => []
                    ];
                }

                $result[$id]['pagos'][] = [
                    'pago_id' => $row['pago_id'],
                    'cliente' => $row['cliente'],
                    'servicio' => $row['servicio'],
                    'metodo_pago' => $row['metodo_pago'],
                    'monto' => $row['monto'],
                    'fecha_pago' => $row['fecha_pago']
                ];
            }

            // ✅ Si no hay resultados, enviar JSON
            if (empty($result)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No hay cortes de caja en ese rango para esta sucursal.']);
                exit;
            }

            // ✅ Generar el archivo Excel
            return File::exportCashCutsRangeToExcel(array_values($result));
        } catch (\Exception $e) {
            error_log("Error al agrupar pagos por corte: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al ejecutar la consulta.']);
            exit;
        }
    }



    public function getPercentSubsidiary()
    {
        $sql = "SELECT 
            s.name AS sucursal,
            COUNT(a.id) AS total_servicios,
            ROUND((COUNT(a.id) * 100.0) / total.total_global, 2) AS porcentaje
        FROM appointments a
        INNER JOIN subsidiaries s ON a.id_subsidiary = s.id
        JOIN (
            SELECT COUNT(*) AS total_global
            FROM appointments
            WHERE active = 1
        ) AS total ON 1 = 1
        WHERE a.active = 1
        GROUP BY s.id, s.name, total.total_global
        ORDER BY total_servicios DESC;";
        try {
            // Check if email already exists
            return Helpers::myQuery($sql);
            return $sql;
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }

    public function getAllCashCuts()
    {
        $sql = "SELECT
            cc.id AS cash_cut_id,
            CONCAT(u.name, ' ', u.lastname) AS user_name,
            s.name AS subsidiary_name,
            cc.start_date,
            cc.end_date,
            cc.total
            FROM cash_cuts cc
            JOIN users u ON cc.id_user = u.id
            JOIN subsidiaries s ON cc.id_subsidiary = s.id
            ORDER BY cc.start_date DESC;
        ";
        try {

            return Helpers::myQuery($sql);
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $headers = ['ID Corte', 'Usuario', 'Sucursal', 'Inicio', 'Fin', 'Total'];
            $sheet->fromArray($headers, NULL, 'A1');

            // Datos
            $rowIndex = 2;
            while ($row = $result->fetch_assoc()) {
                $sheet->setCellValue("A{$rowIndex}", $row['cash_cut_id']);
                $sheet->setCellValue("B{$rowIndex}", $row['user_name']);
                $sheet->setCellValue("C{$rowIndex}", $row['subsidiary_name']);
                $sheet->setCellValue("D{$rowIndex}", $row['start_date']);
                $sheet->setCellValue("E{$rowIndex}", $row['end_date']);
                $sheet->setCellValue("F{$rowIndex}", number_format($row['total'], 2));
                $rowIndex++;
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="cortes_caja.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }

    public function getTopServices()
    {
        $sql = "SELECT s.name AS servicio, COUNT(*) AS total FROM appointments a JOIN services s ON a.service = s.id WHERE a.active = 1 AND a.appointment >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND a.appointment < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01') GROUP BY s.name ORDER BY total DESC LIMIT 10; ";
        try {
            // Check if email already exists
            return Helpers::myQuery($sql);
            return $sql;
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }

    public function getGainsWeek()
    {
        $sql = "SELECT 
            DAYNAME(created_at) AS dia_semana,
            SUM(total) AS total_dia
            FROM cash_cuts
            WHERE 
            active = 1
            AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            GROUP BY dia_semana
            ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
            ";
        try {
            // Check if email already exists
            return Helpers::myQuery($sql);
            return $sql;
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }


    public function updateTotal($data)
    {
        $id = $data['id_cashcut'];

        $conn = Helpers::connect();

        // Obtener sucursal, fechas del corte
        $meta = $conn->query("SELECT id_subsidiary, start_date, end_date FROM cash_cuts WHERE id = '$id'")
            ->fetch_assoc();

        $query = "
        SELECT IFNULL(SUM(p.amount), 0) AS total
        FROM appointments a
        JOIN payments p ON p.id_appointment = a.id
        WHERE
            a.id_subsidiary = '{$meta['id_subsidiary']}'
            AND p.created_at BETWEEN '{$meta['start_date']}' AND '{$meta['end_date']}'
            AND p.status = 'Pagado'
        ";

        $result = $conn->query($query);
        $total = $result->fetch_assoc()['total'];

        // Actualiza con total validado
        $conn->query("UPDATE cash_cuts SET total = $total WHERE id = '$id'");
        return $id;
    }

    public function getDataHome()
    {
        $sql = "SELECT
            (SELECT COUNT(*) FROM users WHERE id_rol = 6 AND active = 1) AS total_clientes,
            (SELECT COUNT(*) FROM users WHERE id_rol = 5 AND active = 1) AS total_doctores,
            (SELECT COUNT(*) FROM appointments 
            WHERE appointment >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                AND appointment < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01') 
                AND active = 1) AS total_citas_mes,
            (SELECT SUM(total) FROM cash_cuts 
            WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                AND created_at < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01') 
                AND active = 1) AS ganancia_mes;
            ";
        try {
            // Check if email already exists
            return Helpers::myQuery($sql);
            return $sql;
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }
}
