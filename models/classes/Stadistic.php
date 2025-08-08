<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class Stadistic extends Entity

{
    public function __construct() {}

    public function getCountsByIdDoctor(String $idDoctor)
    {
        $sql = "SELECT 
                u.id AS id_usuario,
                u.name AS nombre,
                u.lastname AS apellido,

                -- Total de ingresos reales en el mes actual
                (
                    SELECT SUM(p.amount)
                    FROM payments p
                    JOIN appointments ap ON p.id_appointment = ap.id
                    WHERE 
                        p.active = 1
                        AND (p.status = 'Pagado' OR p.status = '1')
                        AND ap.personal = CONCAT(u.name, ' ', u.lastname)
                        AND MONTH(p.created_at) = MONTH(CURDATE())
                        AND YEAR(p.created_at) = YEAR(CURDATE())
                ) AS total_ingresos_mes,

                -- Total de pacientes únicos atendidos en el mes actual
                (
                    SELECT COUNT(DISTINCT a2.client)
                    FROM appointments a2
                    WHERE 
                        a2.personal = CONCAT(u.name, ' ', u.lastname)
                        AND a2.active = 0
                        AND MONTH(a2.appointment) = MONTH(CURDATE())
                        AND YEAR(a2.appointment) = YEAR(CURDATE())
                ) AS total_pacientes_mes,

                -- Total de citas en el mes actual
                (
                    SELECT COUNT(*)
                    FROM appointments a3
                    WHERE 
                        a3.personal = CONCAT(u.name, ' ', u.lastname)
                        AND a3.active = 0
                        AND MONTH(a3.appointment) = MONTH(CURDATE())
                        AND YEAR(a3.appointment) = YEAR(CURDATE())
                ) AS total_citas_mes,

                -- Total de recetas emitidas en el mes actual
                (
                    SELECT COUNT(*)
                    FROM prescriptions p2
                    WHERE 
                        p2.id_doctor = u.id
                        AND p2.active = 1
                        AND MONTH(p2.created_at) = MONTH(CURDATE())
                        AND YEAR(p2.created_at) = YEAR(CURDATE())
                ) AS total_recetas_mes,

                -- Promedio de ingreso por cita
                ROUND(
                    (
                        SELECT SUM(p.amount)
                        FROM payments p
                        JOIN appointments ap ON p.id_appointment = ap.id
                        WHERE 
                            p.active = 1
                            AND (p.status = 'Pagado' OR p.status = '1')
                            AND ap.personal = CONCAT(u.name, ' ', u.lastname)
                            AND MONTH(p.created_at) = MONTH(CURDATE())
                            AND YEAR(p.created_at) = YEAR(CURDATE())
                    ) / NULLIF(
                        (
                            SELECT COUNT(*)
                            FROM appointments a4
                            WHERE 
                                a4.personal = CONCAT(u.name, ' ', u.lastname)
                                AND a4.active = 0
                                AND MONTH(a4.appointment) = MONTH(CURDATE())
                                AND YEAR(a4.appointment) = YEAR(CURDATE())
                        ), 0
                    ), 2
                ) AS ingreso_promedio_por_cita,

                -- Total de cortes de caja realizados en el mes actual (solo conteo)
                (
                    SELECT COUNT(*)
                    FROM cash_cuts cc
                    WHERE 
                        cc.id_user = u.id
                        AND cc.active = 1
                        AND MONTH(cc.start_date) = MONTH(CURDATE())
                        AND YEAR(cc.start_date) = YEAR(CURDATE())
                ) AS total_cortes_mes

            FROM users u
            WHERE u.id = '$idDoctor';
            ";
        try {

            return Helpers::myQuery($sql);
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error: " . $e->getMessage());

            return $error;
        }
    }

    public function getTop(String $idDoctor)
    {
        $sql = "SELECT 
                s.name AS servicio,
                COUNT(a.id) AS cantidad
            FROM appointments a
            JOIN services s ON a.service = s.id
            JOIN users u ON CONCAT(u.name, ' ', u.lastname) = a.personal
            WHERE 
                a.active = 0
                AND u.id = '$idDoctor'
            GROUP BY s.name
            ORDER BY cantidad DESC
            LIMIT 3;
            ";
        try {
            return Helpers::myQuery($sql);
        } catch (\Exception $e) {
            $error = error_log("Error: " . $e->getMessage());
            return $error;
        }
    }

    public function getCountsSubsidiaryDoctor(String $idDoctor)
    {
        $sql = "SELECT 
            s.name AS sucursal,
            SUM(p.amount) AS total_ingresos
            FROM appointments a
            JOIN payments p ON p.id_appointment = a.id
            JOIN subsidiaries s ON a.id_subsidiary = s.id
            JOIN users u ON CONCAT(u.name, ' ', u.lastname) = a.personal
            WHERE 
                p.active = 1
                AND (p.status = 'Pagado' OR p.status = '1')
                AND u.id = '$idDoctor'
            GROUP BY s.id
            ORDER BY total_ingresos DESC;
            ";
        try {
            return Helpers::myQuery($sql);
        } catch (\Exception $e) {
            $error = error_log("Error: " . $e->getMessage());
            return $error;
        }
    }

    public function getCountsPerWeekByDoctor(String $idDoctor)
    {
        $sql = "SELECT 
            WEEK(p.created_at, 1) AS semana,
            CONCAT('Semana ', WEEK(p.created_at, 1)) AS etiqueta_semana,
            SUM(p.amount) AS total_ingresos
        FROM payments p
        JOIN appointments a ON p.id_appointment = a.id
        JOIN users u ON CONCAT(u.name, ' ', u.lastname) = a.personal
        WHERE 
            p.active = 1
            AND p.status = 'Pagado'
            AND u.id = '$idDoctor'
            AND MONTH(p.created_at) = MONTH(CURDATE())
            AND YEAR(p.created_at) = YEAR(CURDATE())
        GROUP BY WEEK(p.created_at, 1)
        ORDER BY semana;
        ";
        try {
            return Helpers::myQuery($sql);
        } catch (\Exception $e) {
            $error = error_log("Error: " . $e->getMessage());
            return $error;
        }
    }
    public function getCountsPerMonthByDoctor(String $idDoctor, String $param)
    {
        // Parsear el parámetro formato "mes-año" (ej: "8-2025")
        $parts = explode('-', $param);
        if (count($parts) !== 2) {
            return ['error' => 'Formato de parámetro inválido. Use: mes-año (ej: 8-2025)'];
        }

        $month = (int)$parts[0];
        $year = (int)$parts[1];

        if ($month < 1 || $month > 12) {
            return ['error' => 'Mes inválido. Debe estar entre 1 y 12'];
        }

        if ($year < 2020 || $year > 2030) {
            return ['error' => 'Año inválido. Debe estar entre 2020 y 2030'];
        }

        $sql = "
        SELECT 
            DATE_FORMAT(cc.start_date, '%d-%m-%Y') AS fecha_corte,
            cc.total AS ingreso
        FROM cash_cuts cc
        WHERE 
            cc.id_user = '$idDoctor'
            AND cc.active = 1
            AND MONTH(cc.start_date) = $month
            AND YEAR(cc.start_date) = $year
        ORDER BY cc.start_date ASC
    ";

        try {
            $result = Helpers::myQuery($sql);

            // Formato gráfico
            $labels = [];
            $data = [];

            foreach ($result as $row) {
                $labels[] = $row['fecha_corte'];         // Ej: "01-08-2025"
                $data[] = (float)$row['ingreso'];        // Ej: 3000.00
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Ingresos por fecha de corte',
                        'data' => $data
                    ]
                ]
            ];
        } catch (\Exception $e) {
            error_log("Error in getCountsPerMonthByDoctor: " . $e->getMessage());
            return ['error' => 'Error al consultar los datos: ' . $e->getMessage()];
        }
    }

    /*  public function getCountsPerMonthByDoctor(String $idDoctor, String $param)
    {
        // Parsear el parámetro formato "mes-año" (ej: "8-2025")
        $parts = explode('-', $param);
        if (count($parts) !== 2) {
            return ['error' => 'Formato de parámetro inválido. Use: mes-año (ej: 8-2025)'];
        }

        $month = (int)$parts[0];
        $year = (int)$parts[1];

        // Validar mes y año
        if ($month < 1 || $month > 12) {
            return ['error' => 'Mes inválido. Debe estar entre 1 y 12'];
        }

        if ($year < 2020 || $year > 2030) {
            return ['error' => 'Año inválido. Debe estar entre 2020 y 2030'];
        }

        $sql = "
                SELECT 
                    CONCAT(MONTH(cc.start_date), '-', YEAR(cc.start_date)) AS etiqueta,
                    SUM(cc.total) AS total_ingresos
                FROM cash_cuts cc
                WHERE cc.id_user = '$idDoctor'
                    AND cc.active = 1
                    AND MONTH(cc.start_date) = $month
                    AND YEAR(cc.start_date) = $year
                GROUP BY etiqueta
                ORDER BY YEAR(cc.start_date), MONTH(cc.start_date)
                ";

        try {
            $result = Helpers::myQuery($sql);

            // Formatear para gráfica (ej: Chart.js)
            $labels = [];
            $data = [];

            foreach ($result as $row) {
                $labels[] = $row['etiqueta'];             // Ej: "8-2025"
                $data[] = (float)$row['total_ingresos'];  // Total
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Ingresos del mes',
                        'data' => $data
                    ]
                ]
            ];
        } catch (\Exception $e) {
            error_log("Error in getCountsPerMonthByDoctor: " . $e->getMessage());
            return ['error' => 'Error al consultar los datos: ' . $e->getMessage()];
        }
    } */
}
