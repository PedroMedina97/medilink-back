<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;
use Classes\Auth;
use mysqli;
use Classes\Permission;
use Utils\Env;
use Utils\Key;

class User extends Entity
{
    private $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function insertUser(String $parentId, String $name, String $lastname, String $email, String $pass, String $birthday, String $phone, String $address, $idRol, String $related = '')
    {
        global $db;
        $parentId = mysqli_real_escape_string(Helpers::connect(), $parentId);
        $name = mysqli_real_escape_string(Helpers::connect(), $name);
        $lastname = mysqli_real_escape_string(Helpers::connect(), $lastname);
        $email = mysqli_real_escape_string(Helpers::connect(), $email);
        $birthday = mysqli_real_escape_string(Helpers::connect(), $birthday);
        $pass = password_hash(($pass), PASSWORD_BCRYPT, ['cost' => 4]);
        $phone = mysqli_real_escape_string(Helpers::connect(), $phone);
        $related = mysqli_real_escape_string(Helpers::connect(), $related);
        $address = mysqli_real_escape_string(Helpers::connect(), $address);
        
        try {
            // Check if email already exists
            $exists_email = $db->query("SELECT * FROM users WHERE email = '$email'");
            /*  var_dump($exists_email);
            die(); */
            if ($exists_email->num_rows > 0) {
                return false; // Email already exists
            } else {
                // Insert user data into database
                $key = new Key();
                $id = $key->generate_uuid();
                $query = "INSERT INTO users (id, parent_id, name, lastname, email, password, birthday, phone, related, address, id_rol, active, created_at, updated_at) VALUES ('$id', '$parentId','$name', '$lastname', '$email', '$pass', '$birthday', '$phone', '$related', '$address', $idRol, 1, NOW(), NOW())";
                $sql = $db->query($query);
                if (!$sql) {
                    throw new \Exception(mysqli_error($db));
                }
                return $sql; // Return the query result
            }
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error inserting user: " . $e->getMessage());

            return $error;
        }
    }

    public function getUsersByRol($id_rol){
        $query = "SELECT * FROM users WHERE id_rol= $id_rol; ";
        $users = Helpers::connect()->query($query);
        return isset($users) ? $users->fetch_all(MYSQLI_ASSOC) : null;
    }

    public function login(string $email, string $password)
    {
        $email = mysqli_real_escape_string(Helpers::connect(), $email);
        $user = Helpers::connect()->query("SELECT * FROM users WHERE email = '$email' AND active = 1");

        if ($user && $user->num_rows === 1) {
            $instance = $user->fetch_assoc();

            $verify = password_verify($password, $instance['password']);
            if ($verify) {
                $id = $instance['id'];
                $sql = "SELECT 
                        u.id AS user_id, 
                        u.name AS user_name, 
                        u.lastname AS user_lastname, 
                        r.id AS role_id, 
                        r.name AS role_name,
                        GROUP_CONCAT(p.name SEPARATOR ', ') AS permissions 
                    FROM users u
                    INNER JOIN rols r ON u.id_rol = r.id
                    INNER JOIN rols_permissions rp ON r.id = rp.id_rol
                    INNER JOIN permissions p ON rp.id_permission = p.id
                    WHERE u.id = '$id'
                    AND u.active = 1 
                    AND r.active = 1 
                    AND rp.active = 1 
                    AND p.active = 1
                    GROUP BY u.id, r.id
                    ORDER BY r.name;
                    ";
                $permissions = Helpers::connect()->query($sql);
                $data = $permissions->fetch_assoc();
                
                $tokenData = [
                    "id" => $instance['id'],
                    "name" => $instance['name'],
                    "lastname" => $instance['lastname'],
                    "email" => $instance['email'],
                    "parent_id" => $instance['parent_id'],
                    "permissions" => $data
                ];
                
                $token = $this->auth->getToken($tokenData);

                return [
                    "userId" => $instance['id'], // Devolviendo el id del usuario.
                    "token" => $token,
                ];
            }
        }

        // En caso de fallo, devolver false.
        return false;
    }

    public function getUsersbyParentId(int $id)
    {
        $sql = "SELECT * FROM users where parentId = $id and active=1";
        $users = Helpers::connect()->query($sql);
        return isset($users) ? $users->fetch_all(MYSQLI_ASSOC) : null;
    }

    public function createUser(string $parent_id, string $name, string $lastname, string $email, string $pass, $birthday)
    {
        global $db;
        $name = mysqli_real_escape_string(Helpers::connect(), $name);
        $lastname = mysqli_real_escape_string(Helpers::connect(), $lastname);
        $birthday = mysqli_real_escape_string(Helpers::connect(), $birthday);
        $email = mysqli_real_escape_string(Helpers::connect(), $email);
        $pass = password_hash(($pass), PASSWORD_BCRYPT, ['cost' => 4]);
        $birthday = mysqli_real_escape_string(Helpers::connect(), $birthday);

        $key = new Key();
        $id = $key->generate_uuid();

        $query = "INSERT INTO users(id, parent_id, name, lastname, email, password, birthday, active, created_at, updated_at) 
        values('$id', '$parent_id', '$name', '$lastname', '$email', '$pass', '$birthday', 1, NOW(), NOW());";
        /* echo($query);
        die(); */
        try {
            // Check if email already exists
            $exists_email = Helpers::connect()->query("SELECT * FROM users WHERE email = '$email'");
            if ($exists_email->num_rows > 0) {
                return false; // Email already exists
            } else {
                // Insert user data into database
                $sql = $db->query($query);

                if (!$sql) {
                    throw new \Exception(mysqli_error($db));
                }
                return $sql; // Return the query result
            }
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error inserting user: " . $e->getMessage());

            return $error;
        }
    }

    private function getUserByEmail(string $email)
    {
        $email = mysqli_real_escape_string(Helpers::connect(), $email);
        $user = Helpers::connect()->query("SELECT * FROM users WHERE email = '$email'");
        if ($user && $user->num_rows == 1) {
            return $user->fetch_assoc();
        } else {
            return null;
        }
    }

    public function setRandomId()
    {
        $id = new Key();
        $data = $id->generate_uuid();
        return $data;
    }

    /**
     * Upload and save user logo
     */
    public function uploadUserLogo(string $userId, array $file)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            
            // Ensure directories exist
            if (!Helpers::ensureDirectoriesExist()) {
                return ['success' => false, 'message' => 'Error al crear directorios necesarios.'];
            }
            
            // Validate file using helper function
            $validation = Helpers::validateImageFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }

            // Generate unique filename
            $extension = $validation['extension'];
            $fileName = 'logo_' . $userId . '_' . time() . '.' . $extension;
            $logoDir = 'assets/images/logos/';
            $filePath = $logoDir . $fileName;
            $relativePath = $logoDir . $fileName;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Delete old logo if exists
                $this->deleteOldUserLogo($userId);

                // Update database with new logo path
                $query = "UPDATE users SET logo_path = '$relativePath', updated_at = NOW() WHERE id = '$userId'";
                $result = Helpers::connect()->query($query);

                if ($result) {
                    return [
                        'success' => true, 
                        'message' => 'Logo subido exitosamente.',
                        'logo_path' => $relativePath,
                        'file_info' => [
                            'original_name' => $file['name'],
                            'size' => $file['size'],
                            'type' => $validation['mime_type']
                        ]
                    ];
                } else {
                    // Delete uploaded file if database update fails
                    unlink($filePath);
                    return ['success' => false, 'message' => 'Error al actualizar la base de datos.'];
                }
            } else {
                return ['success' => false, 'message' => 'Error al subir el archivo.'];
            }
        } catch (\Exception $e) {
            error_log("Error uploading user logo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor.'];
        }
    }

    /**
     * Delete old user logo file
     */
    private function deleteOldUserLogo(string $userId)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            $query = "SELECT logo_path FROM users WHERE id = '$userId'";
            $result = Helpers::connect()->query($query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (!empty($user['logo_path']) && file_exists($user['logo_path'])) {
                    unlink($user['logo_path']);
                }
            }
        } catch (\Exception $e) {
            error_log("Error deleting old logo: " . $e->getMessage());
        }
    }

    /**
     * Get user logo path
     */
    public function getUserLogo(string $userId)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            $query = "SELECT logo_path FROM users WHERE id = '$userId' AND active = 1";
            $result = Helpers::connect()->query($query);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                return [
                    'success' => true,
                    'logo_path' => $user['logo_path']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado.'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error getting user logo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor.'];
        }
    }

    /**
     * Delete user logo
     */
    public function deleteUserLogo(string $userId)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            
            // Delete physical file
            $this->deleteOldUserLogo($userId);

            // Update database
            $query = "UPDATE users SET logo_path = NULL, updated_at = NOW() WHERE id = '$userId'";
            $result = Helpers::connect()->query($query);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Logo eliminado exitosamente.'
                ];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar la base de datos.'];
            }
        } catch (\Exception $e) {
            error_log("Error deleting user logo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor.'];
        }
    }

    /**
     * Get doctor statistics based on cash cuts periods
     * Income data is filtered to current month only
     */
    public function getDoctorStatisticsByCashCuts(string $doctorId)
    {
        try {
            $doctorId = mysqli_real_escape_string(Helpers::connect(), $doctorId);
            
         
            $doctorCheck = "SELECT id, name, lastname FROM users WHERE id = '$doctorId' AND id_rol = 5 AND active = 1";
         
            $doctorResult = Helpers::connect()->query($doctorCheck);
            
            if (!$doctorResult || $doctorResult->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Doctor no encontrado o no es un usuario válido.'
                ];
            }
            
            $doctorInfo = $doctorResult->fetch_assoc();
            
       
            $sql = "
                SELECT 
                    '{$doctorInfo['name']}' as doctor_name,
                    '{$doctorInfo['lastname']}' as doctor_lastname,
                    '$doctorId' as doctor_id,
                    (SELECT COUNT(DISTINCT a.client) 
                     FROM appointments a 
                     INNER JOIN cash_cuts cc ON a.id_subsidiary = cc.id_subsidiary
                     WHERE a.personal = '$doctorId' 
                       AND a.active = 1 
                       AND a.appointment BETWEEN cc.start_date AND cc.end_date
                       AND cc.active = 1) as total_patients,

                    (SELECT COUNT(a.id) 
                     FROM appointments a 
                     INNER JOIN cash_cuts cc ON a.id_subsidiary = cc.id_subsidiary
                     WHERE a.personal = '$doctorId' 
                       AND a.active = 1 
                       AND a.appointment BETWEEN cc.start_date AND cc.end_date
                       AND cc.active = 1) as total_appointments,

                    (SELECT COUNT(pr.id) 
                     FROM prescriptions pr 
                     INNER JOIN cash_cuts cc ON 1=1
                     WHERE pr.id_doctor = '$doctorId' 
                       AND pr.active = 1 
                       AND pr.created_at BETWEEN cc.start_date AND cc.end_date
                       AND cc.active = 1) as total_prescriptions,
                    
                    (SELECT COALESCE(SUM(p.amount), 0) 
                     FROM payments p 
                     INNER JOIN appointments a ON p.id_appointment = a.id
                     WHERE a.personal = '$doctorId' 
                       AND (p.status = 'Pagado' OR p.status = '1')
                       AND p.active = 1 
                       AND a.active = 1
                       AND YEAR(p.created_at) = YEAR(CURDATE())
                       AND MONTH(p.created_at) = MONTH(CURDATE())) as total_income,
                    
                    (SELECT COUNT(DISTINCT cc.id) 
                     FROM cash_cuts cc 
                     INNER JOIN appointments a ON a.id_subsidiary = cc.id_subsidiary
                     WHERE a.personal = '$doctorId' 
                       AND a.appointment BETWEEN cc.start_date AND cc.end_date
                       AND cc.active = 1 
                       AND a.active = 1) as related_cash_cuts,
                    
                    CASE 
                        WHEN (SELECT COUNT(p.id) 
                              FROM payments p 
                              INNER JOIN appointments a ON p.id_appointment = a.id
                              WHERE a.personal = '$doctorId' 
                                AND (p.status = 'Pagado' OR p.status = '1')
                                AND p.active = 1 
                                AND a.active = 1 
                                AND YEAR(p.created_at) = YEAR(CURDATE())
                                AND MONTH(p.created_at) = MONTH(CURDATE())) > 0 
                        THEN (SELECT COALESCE(SUM(p.amount), 0) 
                              FROM payments p 
                              INNER JOIN appointments a ON p.id_appointment = a.id
                              WHERE a.personal = '$doctorId' 
                                AND (p.status = 'Pagado' OR p.status = '1')
                                AND p.active = 1 
                                AND a.active = 1
                                AND YEAR(p.created_at) = YEAR(CURDATE())
                                AND MONTH(p.created_at) = MONTH(CURDATE())) / 
                             (SELECT COUNT(p.id) 
                              FROM payments p 
                              INNER JOIN appointments a ON p.id_appointment = a.id
                              WHERE a.personal = '$doctorId' 
                                AND (p.status = 'Pagado' OR p.status = '1')
                                AND p.active = 1 
                                AND a.active = 1 
                                AND YEAR(p.created_at) = YEAR(CURDATE())
                                AND MONTH(p.created_at) = MONTH(CURDATE()))
                        ELSE 0 
                    END as average_income_per_appointment
            ";
    
            $result = Helpers::connect()->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $statistics = $result->fetch_assoc();

                return [
                    'success' => true,
                    'doctor_info' => [
                        'id' => $statistics['doctor_id'],
                        'name' => $statistics['doctor_name'],
                        'lastname' => $statistics['doctor_lastname'],
                        'full_name' => trim($statistics['doctor_name'] . ' ' . $statistics['doctor_lastname'])
                    ],
                    'statistics' => [
                        'total_patients' => (int)$statistics['total_patients'],
                        'total_appointments' => (int)$statistics['total_appointments'],
                        'total_prescriptions' => (int)$statistics['total_prescriptions'],
                        'total_income' => (float)$statistics['total_income'],
                        'related_cash_cuts' => (int)$statistics['related_cash_cuts'],
                        'average_income_per_appointment' => round((float)$statistics['average_income_per_appointment'], 2)
                    ],
                    'formatted_data' => [
                        'total_income_formatted' => '$' . number_format($statistics['total_income'], 2),
                        'average_income_formatted' => '$' . number_format($statistics['average_income_per_appointment'], 2)
                    ],
                    'period_info' => [
                        'income_period' => 'Mes corriente (' . date('Y-m') . ')',
                        'other_stats_period' => 'Basado en cortes de caja'
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'doctor_info' => [
                        'id' => $doctorId,
                        'name' => $doctorInfo['name'],
                        'lastname' => $doctorInfo['lastname'],
                        'full_name' => trim($doctorInfo['name'] . ' ' . $doctorInfo['lastname'])
                    ],
                    'statistics' => [
                        'total_patients' => 0,
                        'total_appointments' => 0,
                        'total_prescriptions' => 0,
                        'total_income' => 0.0,
                        'related_cash_cuts' => 0,
                        'average_income_per_appointment' => 0.0
                    ],
                    'formatted_data' => [
                        'total_income_formatted' => '$0.00',
                        'average_income_formatted' => '$0.00'
                    ],
                    'period_info' => [
                        'income_period' => 'Mes corriente (' . date('Y-m') . ')',
                        'other_stats_period' => 'Basado en cortes de caja'
                    ]
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error getting doctor statistics: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor al obtener estadísticas.'
            ];
        }
    }

    /**
     * Get most requested service of the month by doctor
     */
    public function getMostRequestedServiceByDoctor(string $doctorId, string $month = null)
    {
        try {
            $doctorId = mysqli_real_escape_string(Helpers::connect(), $doctorId);
            
            // Si no se proporciona mes, usar el actual
            if ($month === null) {
                $month = date('Y-m');
            }
            
            // Validar formato del mes
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                return [
                    'success' => false,
                    'message' => 'Formato de mes inválido. Usa YYYY-MM'
                ];
            }
            
            $startDate = $month . '-01';
            $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
            
            $sql = "
                SELECT 
                    s.id as service_id,
                    s.name as service_name,
                    s.price as service_price,
                    COUNT(a.id) as total_requests,
                    COUNT(a.id) * s.price as potential_income,
                    sub.name as subsidiary_name
                FROM appointments a
                INNER JOIN services s ON a.service = s.id
                INNER JOIN subsidiaries sub ON a.id_subsidiary = sub.id
                WHERE a.personal = '$doctorId'
                  AND a.active = 1
                  AND a.appointment >= '$startDate'
                  AND a.appointment < '$endDate'
                GROUP BY s.id, s.name, s.price, sub.name
                ORDER BY total_requests DESC, potential_income DESC
                LIMIT 10
            ";
            
            $result = Helpers::connect()->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $services = $result->fetch_all(MYSQLI_ASSOC);
                
                return [
                    'success' => true,
                    'month' => $month,
                    'data' => $services,
                    'most_requested' => $services[0] ?? null
                ];
            } else {
                return [
                    'success' => true,
                    'month' => $month,
                    'data' => [],
                    'most_requested' => null
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error getting most requested service: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor.'
            ];
        }
    }

    /**
     * Get earnings by subsidiary for a doctor
     */
    public function getEarningsBySubsidiaryByDoctor(string $doctorId)
    {
        try {
            $doctorId = mysqli_real_escape_string(Helpers::connect(), $doctorId);
            
            $sql = "
                SELECT 
                    sub.id as subsidiary_id,
                    sub.name as subsidiary_name,
                    sub.address as subsidiary_address,
                    COUNT(DISTINCT a.id) as total_appointments,
                    COUNT(DISTINCT a.client) as total_patients,
                    COALESCE(SUM(CASE WHEN p.status = 'Pagado' THEN p.amount ELSE 0 END), 0) as total_earnings,
                    COALESCE(AVG(CASE WHEN p.status = 'Pagado' THEN p.amount ELSE NULL END), 0) as average_earning_per_appointment,
                    COUNT(CASE WHEN p.status = 'Pagado' THEN 1 END) as paid_appointments,
                    COUNT(CASE WHEN p.status != 'Pagado' OR p.id IS NULL THEN 1 END) as unpaid_appointments
                FROM subsidiaries sub
                INNER JOIN appointments a ON sub.id = a.id_subsidiary
                LEFT JOIN payments p ON a.id = p.id_appointment AND p.active = 1
                WHERE a.personal = '$doctorId'
                  AND a.active = 1
                  AND sub.active = 1
                GROUP BY sub.id, sub.name, sub.address
                ORDER BY total_earnings DESC, total_appointments DESC
            ";
            
            $result = Helpers::connect()->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $subsidiaries = $result->fetch_all(MYSQLI_ASSOC);
                
                // Calcular totales generales
                $totalEarnings = array_sum(array_column($subsidiaries, 'total_earnings'));
                $totalAppointments = array_sum(array_column($subsidiaries, 'total_appointments'));
                
                // Agregar porcentajes
                foreach ($subsidiaries as &$subsidiary) {
                    $subsidiary['earnings_percentage'] = $totalEarnings > 0 
                        ? round(($subsidiary['total_earnings'] / $totalEarnings) * 100, 2) 
                        : 0;
                    $subsidiary['appointments_percentage'] = $totalAppointments > 0 
                        ? round(($subsidiary['total_appointments'] / $totalAppointments) * 100, 2) 
                        : 0;
                }
                
                return [
                    'success' => true,
                    'data' => $subsidiaries,
                    'summary' => [
                        'total_subsidiaries' => count($subsidiaries),
                        'total_earnings' => $totalEarnings,
                        'total_appointments' => $totalAppointments,
                        'average_per_subsidiary' => count($subsidiaries) > 0 ? round($totalEarnings / count($subsidiaries), 2) : 0
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [],
                    'summary' => [
                        'total_subsidiaries' => 0,
                        'total_earnings' => 0,
                        'total_appointments' => 0,
                        'average_per_subsidiary' => 0
                    ]
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error getting earnings by subsidiary: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor.'
            ];
        }
    }

    /**
     * Get monthly income chart data for a doctor
     */
    public function getMonthlyIncomeByDoctor(string $doctorId, string $month)
    {
        try {
            $doctorId = mysqli_real_escape_string(Helpers::connect(), $doctorId);
            
            // Validar formato del mes
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                return [
                    'success' => false,
                    'message' => 'Formato de mes inválido. Usa YYYY-MM'
                ];
            }
            
            $startDate = $month . '-01';
            $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
            
            $sql = "
                SELECT 
                    DATE(p.created_at) as payment_date,
                    COALESCE(SUM(p.amount), 0) as daily_income,
                    COUNT(p.id) as daily_payments,
                    COUNT(DISTINCT a.client) as daily_patients
                FROM payments p
                INNER JOIN appointments a ON p.id_appointment = a.id
                INNER JOIN cash_cuts cc ON a.id_subsidiary = cc.id_subsidiary
                WHERE a.personal = '$doctorId'
                  AND p.status = 'Pagado'
                  AND p.active = 1
                  AND a.active = 1
                  AND p.created_at >= '$startDate'
                  AND p.created_at < '$endDate'
                  AND p.created_at BETWEEN cc.start_date AND cc.end_date
                  AND cc.active = 1
                GROUP BY DATE(p.created_at)
                ORDER BY payment_date ASC
            ";
            
            $result = Helpers::connect()->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $dailyData = $result->fetch_all(MYSQLI_ASSOC);
                
                // Calcular estadísticas del mes
                $totalIncome = array_sum(array_column($dailyData, 'daily_income'));
                $totalPayments = array_sum(array_column($dailyData, 'daily_payments'));
                $averageDaily = count($dailyData) > 0 ? round($totalIncome / count($dailyData), 2) : 0;
                
                return [
                    'success' => true,
                    'month' => $month,
                    'daily_data' => $dailyData,
                    'summary' => [
                        'total_income' => $totalIncome,
                        'total_payments' => $totalPayments,
                        'average_daily_income' => $averageDaily,
                        'working_days' => count($dailyData),
                        'best_day' => $dailyData ? max($dailyData) : null
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'month' => $month,
                    'daily_data' => [],
                    'summary' => [
                        'total_income' => 0,
                        'total_payments' => 0,
                        'average_daily_income' => 0,
                        'working_days' => 0,
                        'best_day' => null
                    ]
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error getting monthly income: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor.'
            ];
        }
    }

    /**
     * Get weekly income chart data for a doctor based on cash cuts
     */
    public function getWeeklyIncomeByDoctor(string $doctorId)
    {
        try {
            $doctorId = mysqli_real_escape_string(Helpers::connect(), $doctorId);
            
            $sql = "
                SELECT 
                    DAYNAME(p.created_at) as day_name,
                    DAYOFWEEK(p.created_at) as day_number,
                    DATE(p.created_at) as payment_date,
                    COALESCE(SUM(p.amount), 0) as daily_income,
                    COUNT(p.id) as daily_payments,
                    COUNT(DISTINCT a.client) as daily_patients
                FROM payments p
                INNER JOIN appointments a ON p.id_appointment = a.id
                INNER JOIN cash_cuts cc ON a.id_subsidiary = cc.id_subsidiary
                WHERE a.personal = '$doctorId'
                  AND p.status = 'Pagado'
                  AND p.active = 1
                  AND a.active = 1
                  AND YEARWEEK(p.created_at, 1) = YEARWEEK(CURDATE(), 1)
                  AND p.created_at BETWEEN cc.start_date AND cc.end_date
                  AND cc.active = 1
                GROUP BY DATE(p.created_at), DAYNAME(p.created_at), DAYOFWEEK(p.created_at)
                ORDER BY day_number ASC
            ";
            
            $result = Helpers::connect()->query($sql);
            
            // Inicializar datos de la semana
            $weekDays = [
                1 => ['day_name' => 'Sunday', 'day_name_es' => 'Domingo', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0],
                2 => ['day_name' => 'Monday', 'day_name_es' => 'Lunes', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0],
                3 => ['day_name' => 'Tuesday', 'day_name_es' => 'Martes', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0],
                4 => ['day_name' => 'Wednesday', 'day_name_es' => 'Miércoles', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0],
                5 => ['day_name' => 'Thursday', 'day_name_es' => 'Jueves', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0],
                6 => ['day_name' => 'Friday', 'day_name_es' => 'Viernes', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0],
                7 => ['day_name' => 'Saturday', 'day_name_es' => 'Sábado', 'daily_income' => 0, 'daily_payments' => 0, 'daily_patients' => 0]
            ];
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
                
                // Llenar datos reales
                foreach ($data as $row) {
                    $dayNum = $row['day_number'];
                    $weekDays[$dayNum]['daily_income'] = (float)$row['daily_income'];
                    $weekDays[$dayNum]['daily_payments'] = (int)$row['daily_payments'];
                    $weekDays[$dayNum]['daily_patients'] = (int)$row['daily_patients'];
                    $weekDays[$dayNum]['payment_date'] = $row['payment_date'];
                }
            }
            
            // Convertir a array indexado y calcular totales
            $weeklyData = array_values($weekDays);
            $totalWeeklyIncome = array_sum(array_column($weeklyData, 'daily_income'));
            $totalWeeklyPayments = array_sum(array_column($weeklyData, 'daily_payments'));
            
            return [
                'success' => true,
                'week_data' => $weeklyData,
                'summary' => [
                    'total_weekly_income' => $totalWeeklyIncome,
                    'total_weekly_payments' => $totalWeeklyPayments,
                    'average_daily_income' => round($totalWeeklyIncome / 7, 2),
                    'best_day' => $weeklyData ? max($weeklyData) : null,
                    'current_week' => date('Y-W')
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting weekly income: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor.'
            ];
        }
    }
}
