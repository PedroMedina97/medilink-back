<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class MedicalHistory extends Entity
{
    /**
     * Obtiene el historial médico completo de un usuario por su ID
     */
    public function getMedicalHistoryByUserId(string $userId)
    {
        $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
        
        // Obtener información del paciente
        $patientInfo = $this->getPatientInfo($userId);
        
        // Obtener citas médicas
        $appointments = $this->getAppointmentsByUserId($userId);
        
        // Obtener órdenes médicas
        $medicalOrders = $this->getMedicalOrdersByUserId($userId);
        
        // Obtener prescripciones
        $prescriptions = $this->getPrescriptionsByUserId($userId);
        
        return [
            'patient_info' => $patientInfo,
            'appointments' => $appointments,
            'medical_orders' => $medicalOrders,
            'prescriptions' => $prescriptions
        ];
    }
    
    /**
     * Obtiene la información básica del paciente
     */
    private function getPatientInfo(string $userId)
    {
        $query = "SELECT 
                    u.id,
                    u.name,
                    u.lastname,
                    u.email,
                    u.birthday,
                    u.phone,
                    u.address,
                    u.created_at as registration_date,
                    r.name as role_name
                  FROM users u
                  LEFT JOIN rols r ON u.id_rol = r.id
                  WHERE u.id = '$userId' AND u.active = 1";
        
        $result = Helpers::connect()->query($query);
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Obtiene todas las citas médicas de un usuario
     */
    private function getAppointmentsByUserId(string $userId)
    {
        $query = "SELECT 
                    a.id,
                    a.code,
                    a.appointment as appointment_date,
                    a.end_appointment,
                    a.color,
                    a.created_at,
                    srv.name as service_name,
                    srv.price as service_price,
                    s.name as subsidiary_name,
                    u_personal.name as doctor_name,
                    u_personal.lastname as doctor_lastname
                  FROM appointments a
                  LEFT JOIN services srv ON a.service = srv.id
                  LEFT JOIN subsidiaries s ON a.id_subsidiary = s.id
                  LEFT JOIN users u_personal ON a.personal = u_personal.id
                  WHERE a.client = '$userId' AND a.active = 1
                  ORDER BY a.appointment DESC";
        
        $result = Helpers::connect()->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtiene todas las órdenes médicas relacionadas con las citas del usuario
     */
    private function getMedicalOrdersByUserId(string $userId)
    {
        $query = "SELECT 
                    o.*,
                    a.code as appointment_code,
                    a.appointment as appointment_date
                  FROM orders o
                  INNER JOIN appointments a ON o.id = a.id_order
                  WHERE a.client = '$userId' AND o.active = 1 AND a.active = 1
                  ORDER BY o.created_at DESC";
        
        $result = Helpers::connect()->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtiene todas las prescripciones de un usuario
     */
    private function getPrescriptionsByUserId(string $userId)
    {
        $query = "SELECT 
                    p.id,
                    p.next_date,
                    p.created_at,
                    u_doctor.name as doctor_name,
                    u_doctor.lastname as doctor_lastname
                  FROM prescriptions p
                  LEFT JOIN users u_doctor ON p.id_doctor = u_doctor.id
                  WHERE p.id_user = '$userId' AND p.active = 1
                  ORDER BY p.created_at DESC";
        
        $result = Helpers::connect()->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtiene el resumen del historial médico (estadísticas)
     */
    public function getMedicalHistorySummary(string $userId)
    {
        $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
        
        // Contar citas
        $appointmentCount = $this->getAppointmentCount($userId);
        
        // Contar órdenes médicas
        $orderCount = $this->getMedicalOrderCount($userId);
        
        // Contar prescripciones
        $prescriptionCount = $this->getPrescriptionCount($userId);
        
        // Última cita
        $lastAppointment = $this->getLastAppointment($userId);
        
        // Próxima cita
        $nextAppointment = $this->getNextAppointment($userId);
        
        return [
            'total_appointments' => $appointmentCount,
            'total_medical_orders' => $orderCount,
            'total_prescriptions' => $prescriptionCount,
            'last_appointment' => $lastAppointment,
            'next_appointment' => $nextAppointment
        ];
    }
    
    private function getAppointmentCount(string $userId)
    {
        $query = "SELECT COUNT(*) as total FROM appointments WHERE client = '$userId' AND active = 1";
        $result = Helpers::connect()->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    private function getMedicalOrderCount(string $userId)
    {
        $query = "SELECT COUNT(*) as total FROM orders o
                  INNER JOIN appointments a ON o.id = a.id_order
                  WHERE a.client = '$userId' AND o.active = 1 AND a.active = 1";
        $result = Helpers::connect()->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    private function getPrescriptionCount(string $userId)
    {
        $query = "SELECT COUNT(*) as total FROM prescriptions WHERE id_user = '$userId' AND active = 1";
        $result = Helpers::connect()->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    private function getLastAppointment(string $userId)
    {
        $query = "SELECT 
                    a.appointment as appointment_date,
                    srv.name as service_name,
                    u_personal.name as doctor_name,
                    u_personal.lastname as doctor_lastname
                  FROM appointments a
                  LEFT JOIN services srv ON a.service = srv.id
                  LEFT JOIN users u_personal ON a.personal = u_personal.id
                  WHERE a.client = '$userId' AND a.active = 1 AND a.appointment < NOW()
                  ORDER BY a.appointment DESC
                  LIMIT 1";
        
        $result = Helpers::connect()->query($query);
        return $result ? $result->fetch_assoc() : null;
    }
    
    private function getNextAppointment(string $userId)
    {
        $query = "SELECT 
                    a.appointment as appointment_date,
                    srv.name as service_name,
                    u_personal.name as doctor_name,
                    u_personal.lastname as doctor_lastname
                  FROM appointments a
                  LEFT JOIN services srv ON a.service = srv.id
                  LEFT JOIN users u_personal ON a.personal = u_personal.id
                  WHERE a.client = '$userId' AND a.active = 1 AND a.appointment > NOW()
                  ORDER BY a.appointment ASC
                  LIMIT 1";
        
        $result = Helpers::connect()->query($query);
        return $result ? $result->fetch_assoc() : null;
    }
} 