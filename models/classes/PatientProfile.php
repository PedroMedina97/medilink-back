<?php

namespace Classes;

use Abstracts\Entity;
use Utils\Helpers;
use Utils\Key;

class PatientProfile extends Entity
{
    /**
     * Get patient profile by user ID
     */
    public function getProfileByUserId(string $userId)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            
            $query = "SELECT 
                        pp.id,
                        pp.id_user,
                        pp.blood_type,
                        pp.allergies,
                        pp.diseases,
                        pp.surgeries,
                        pp.current_treatments,
                        pp.notes,
                        pp.created_at,
                        pp.updated_at,
                        u.name as patient_name,
                        u.lastname as patient_lastname,
                        u.email as patient_email
                      FROM patient_profiles pp
                      INNER JOIN users u ON pp.id_user = u.id
                      WHERE pp.id_user = '$userId' AND pp.active = 1 AND u.active = 1";
            
            $result = Helpers::connect()->query($query);
            
            if ($result && $result->num_rows > 0) {
                $profile = $result->fetch_assoc();
                return [
                    'success' => true,
                    'data' => $this->formatProfile($profile)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Perfil de salud no encontrado'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error getting patient profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Create or update patient profile
     */
    public function createOrUpdateProfile(string $userId, array $data)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            
            // Check if profile already exists
            $existingProfile = $this->getProfileByUserId($userId);
            
            if ($existingProfile['success']) {
                // Update existing profile
                return $this->updateProfile($existingProfile['data']['id'], $data);
            } else {
                // Create new profile
                return $this->createProfile($userId, $data);
            }
        } catch (\Exception $e) {
            error_log("Error creating or updating patient profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Create new patient profile
     */
    private function createProfile(string $userId, array $data)
    {
        try {
            $key = new Key();
            $profileId = $key->generate_uuid();
            
            $bloodType = mysqli_real_escape_string(Helpers::connect(), $data['blood_type'] ?? '');
            $allergies = mysqli_real_escape_string(Helpers::connect(), $data['allergies'] ?? '');
            $diseases = mysqli_real_escape_string(Helpers::connect(), $data['diseases'] ?? '');
            $surgeries = mysqli_real_escape_string(Helpers::connect(), $data['surgeries'] ?? '');
            $currentTreatments = mysqli_real_escape_string(Helpers::connect(), $data['current_treatments'] ?? '');
            $notes = mysqli_real_escape_string(Helpers::connect(), $data['notes'] ?? '');
            
            $query = "INSERT INTO patient_profiles 
                     (id, id_user, blood_type, allergies, diseases, surgeries, current_treatments, notes, active, created_at, updated_at) 
                     VALUES 
                     ('$profileId', '$userId', '$bloodType', '$allergies', '$diseases', '$surgeries', '$currentTreatments', '$notes', 1, NOW(), NOW())";
            
            $result = Helpers::connect()->query($query);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Perfil de salud creado exitosamente',
                    'data' => [
                        'id' => $profileId,
                        'user_id' => $userId
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el perfil de salud'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error creating patient profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Update existing patient profile
     */
    private function updateProfile(string $profileId, array $data)
    {
        try {
            $profileId = mysqli_real_escape_string(Helpers::connect(), $profileId);
            
            $bloodType = mysqli_real_escape_string(Helpers::connect(), $data['blood_type'] ?? '');
            $allergies = mysqli_real_escape_string(Helpers::connect(), $data['allergies'] ?? '');
            $diseases = mysqli_real_escape_string(Helpers::connect(), $data['diseases'] ?? '');
            $surgeries = mysqli_real_escape_string(Helpers::connect(), $data['surgeries'] ?? '');
            $currentTreatments = mysqli_real_escape_string(Helpers::connect(), $data['current_treatments'] ?? '');
            $notes = mysqli_real_escape_string(Helpers::connect(), $data['notes'] ?? '');
            
            $query = "UPDATE patient_profiles SET 
                     blood_type = '$bloodType',
                     allergies = '$allergies',
                     diseases = '$diseases',
                     surgeries = '$surgeries',
                     current_treatments = '$currentTreatments',
                     notes = '$notes',
                     updated_at = NOW()
                     WHERE id = '$profileId' AND active = 1";
            
            $result = Helpers::connect()->query($query);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Perfil de salud actualizado exitosamente',
                    'data' => [
                        'id' => $profileId
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el perfil de salud'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error updating patient profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Delete patient profile (soft delete)
     */
    public function deleteProfile(string $userId)
    {
        try {
            $userId = mysqli_real_escape_string(Helpers::connect(), $userId);
            
            $query = "UPDATE patient_profiles SET 
                     active = 0,
                     updated_at = NOW()
                     WHERE id_user = '$userId'";
            
            $result = Helpers::connect()->query($query);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Perfil de salud eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el perfil de salud'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error deleting patient profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Get all patient profiles for a doctor
     */
    public function getProfilesByDoctor(string $doctorId)
    {
        try {
            $doctorId = mysqli_real_escape_string(Helpers::connect(), $doctorId);
            
            $query = "SELECT 
                        pp.id,
                        pp.id_user,
                        pp.blood_type,
                        pp.allergies,
                        pp.diseases,
                        pp.surgeries,
                        pp.current_treatments,
                        pp.notes,
                        pp.created_at,
                        pp.updated_at,
                        u.name as patient_name,
                        u.lastname as patient_lastname,
                        u.email as patient_email,
                        u.phone as patient_phone
                      FROM patient_profiles pp
                      INNER JOIN users u ON pp.id_user = u.id
                      WHERE u.parent_id = '$doctorId' AND pp.active = 1 AND u.active = 1
                      ORDER BY u.name, u.lastname";
            
            $result = Helpers::connect()->query($query);
            
            if ($result) {
                $profiles = [];
                while ($row = $result->fetch_assoc()) {
                    $profiles[] = $this->formatProfile($row);
                }
                
                return [
                    'success' => true,
                    'data' => $profiles,
                    'total' => count($profiles)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al obtener perfiles de pacientes'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error getting patient profiles by doctor: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Validate profile data
     */
    public function validateProfileData(array $data)
    {
        $errors = [];
        
        // Validate blood type
        if (isset($data['blood_type']) && !empty($data['blood_type'])) {
            $validBloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!in_array($data['blood_type'], $validBloodTypes)) {
                $errors[] = 'Tipo de sangre no válido. Debe ser uno de: ' . implode(', ', $validBloodTypes);
            }
        }
        
        // Validate field lengths
        $maxLengths = [
            'blood_type' => 5,
            'allergies' => 1000,
            'diseases' => 1000,
            'surgeries' => 1000,
            'current_treatments' => 1000,
            'notes' => 2000
        ];
        
        foreach ($maxLengths as $field => $maxLength) {
            if (isset($data[$field]) && strlen($data[$field]) > $maxLength) {
                $errors[] = "El campo $field excede el límite de $maxLength caracteres";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Format profile data for response
     */
    private function formatProfile(array $profile)
    {
        return [
            'id' => $profile['id'],
            'user_id' => $profile['id_user'],
            'patient_info' => [
                'name' => $profile['patient_name'],
                'lastname' => $profile['patient_lastname'],
                'full_name' => trim($profile['patient_name'] . ' ' . $profile['patient_lastname']),
                'email' => $profile['patient_email'],
                'phone' => $profile['patient_phone'] ?? 'No especificado'
            ],
            'health_info' => [
                'blood_type' => $profile['blood_type'] ?: 'No especificado',
                'allergies' => $profile['allergies'] ?: 'Ninguna conocida',
                'diseases' => $profile['diseases'] ?: 'Ninguna',
                'surgeries' => $profile['surgeries'] ?: 'Ninguna',
                'current_treatments' => $profile['current_treatments'] ?: 'Ninguno',
                'notes' => $profile['notes'] ?: 'Sin observaciones'
            ],
            'dates' => [
                'created_at' => $this->formatDate($profile['created_at']),
                'updated_at' => $this->formatDate($profile['updated_at'])
            ]
        ];
    }

    /**
     * Format date for display
     */
    private function formatDate($date)
    {
        if (!$date || $date === '0000-00-00 00:00:00') {
            return 'No especificado';
        }
        try {
            return date('d/m/Y H:i', strtotime($date));
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }
} 