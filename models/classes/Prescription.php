<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;

class Prescription extends Entity{
    
    public function getPrescriptionsByDoctor(String $id){
        $query = "SELECT pr.id, pr.next_date, pr.created_at,  u.name, u.lastname FROM prescriptions as pr INNER JOIN users as u on pr.id_user=u.id WHERE pr.active=1 and id_doctor= '$id';";
        /* echo $query;
        die(); */
        try {
            // Check if email already exists
            return Helpers::myQuery($query);
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error inserting cashcut: " . $e->getMessage());

            return $error;
        }
    }

    public function getPrescriptionWithDetailsById(String $id){
        $query = "SELECT 
                    pr.id,
                    pr.age,
                    pr.sex,
                    pr.weight,
                    pr.height,
                    pr.diagnosis,
                    pr.medications_dosage,
                    pr.special_indications,
                    pr.next_date,
                    pr.created_at,
                    pr.updated_at,
                    
                    -- Información del paciente
                    patient.id as patient_id,
                    patient.name as patient_name,
                    patient.lastname as patient_lastname,
                    patient.email as patient_email,
                    patient.birthday as patient_birthday,
                    patient.phone as patient_phone,
                    patient.address as patient_address,
                    
                    -- Información del doctor
                    doctor.id as doctor_id,
                    doctor.name as doctor_name,
                    doctor.lastname as doctor_lastname,
                    doctor.email as doctor_email,
                    doctor.phone as doctor_phone,
                    doctor.address as doctor_address,
                    doctor.professional_id as doctor_professional_id,
                    
                    -- Información de salud del paciente
                    pp.blood_type,
                    pp.allergies,
                    pp.diseases,
                    pp.surgeries,
                    pp.current_treatments,
                    pp.notes as health_notes
                    
                  FROM prescriptions as pr 
                  INNER JOIN users as patient ON pr.id_user = patient.id
                  INNER JOIN users as doctor ON pr.id_doctor = doctor.id
                  LEFT JOIN patient_profiles as pp ON patient.id = pp.id_user AND pp.active = 1
                  WHERE pr.id = '$id' AND pr.active = 1";
        
        try {
            return Helpers::myQuery($query);
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, display an error message)
            $error = error_log("Error getting prescription with details: " . $e->getMessage());
            return $error;
        }
    }

    public function getPrescriptionDetailsFormatted(String $id) {
        try {
            $data = $this->getPrescriptionWithDetailsById($id);
            
            if (!$data || count($data) === 0) {
                return [
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ];
            }

            $prescription = $data[0];
            
            // Formatear la información de manera estructurada
            $formattedData = [
                'success' => true,
                'prescription_info' => [
                    'id' => $prescription['id'],
                    'created_at' => $this->formatDate($prescription['created_at']),
                    'updated_at' => $this->formatDate($prescription['updated_at']),
                    'next_appointment' => $this->formatDate($prescription['next_date']),
                    'patient_data' => [
                        'age' => $prescription['age'],
                        'sex' => $this->formatGender($prescription['sex']),
                        'weight' => $prescription['weight'] ? $prescription['weight'] . ' kg' : 'No especificado',
                        'height' => $prescription['height'] ? $prescription['height'] . ' cm' : 'No especificado'
                    ]
                ],
                'patient_info' => [
                    'id' => $prescription['patient_id'],
                    'full_name' => trim($prescription['patient_name'] . ' ' . $prescription['patient_lastname']),
                    'name' => $prescription['patient_name'],
                    'lastname' => $prescription['patient_lastname'],
                    'email' => $prescription['patient_email'],
                    'phone' => $prescription['patient_phone'] ?: 'No especificado',
                    'address' => $prescription['patient_address'] ?: 'No especificado',
                    'birthday' => $this->formatDate($prescription['patient_birthday']),
                    'age_at_prescription' => $prescription['age'] . ' años'
                ],
                'patient_health_info' => [
                    'blood_type' => $prescription['blood_type'] ?: 'No especificado',
                    'allergies' => $prescription['allergies'] ?: 'Ninguna conocida',
                    'diseases' => $prescription['diseases'] ?: 'Ninguna',
                    'surgeries' => $prescription['surgeries'] ?: 'Ninguna',
                    'current_treatments' => $prescription['current_treatments'] ?: 'Ninguno',
                    'health_notes' => $prescription['health_notes'] ?: 'Sin observaciones',
                    'has_health_profile' => !empty($prescription['blood_type']) || !empty($prescription['allergies']) || !empty($prescription['diseases'])
                ],
                'doctor_info' => [
                    'id' => $prescription['doctor_id'],
                    'full_name' => trim($prescription['doctor_name'] . ' ' . $prescription['doctor_lastname']),
                    'name' => $prescription['doctor_name'],
                    'lastname' => $prescription['doctor_lastname'],
                    'email' => $prescription['doctor_email'],
                    'phone' => $prescription['doctor_phone'] ?: 'No especificado',
                    'address' => $prescription['doctor_address'] ?: 'No especificado',
                    'professional_id' => $prescription['doctor_professional_id'] ?: 'No especificado'
                ],
                'medical_info' => [
                    'diagnosis' => $prescription['diagnosis'] ?: 'No especificado',
                    'medications_and_dosage' => $prescription['medications_dosage'] ?: 'No especificado',
                    'special_indications' => $prescription['special_indications'] ?: 'No especificado'
                ],
                'appointment_info' => [
                    'next_date' => $this->formatDate($prescription['next_date']),
                    'next_date_formatted' => $this->formatDateWithDay($prescription['next_date'])
                ]
            ];

            return $formattedData;

        } catch (\Exception $e) {
            error_log("Error getting formatted prescription details: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    private function formatDate($date) {
        if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'No especificado';
        }
        try {
            return date('d/m/Y', strtotime($date));
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }

    private function formatDateWithDay($date) {
        if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'No especificado';
        }
        try {
            $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            $timestamp = strtotime($date);
            $dayName = $days[date('w', $timestamp)];
            $day = date('d', $timestamp);
            $monthName = $months[date('n', $timestamp) - 1];
            $year = date('Y', $timestamp);
            
            return "$dayName, $day de $monthName de $year";
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }

    private function formatGender($sex) {
        switch ($sex) {
            case 1:
            case '1':
                return 'Masculino';
            case 2:
            case '2':
                return 'Femenino';
            default:
                return 'No especificado';
        }
    }
}