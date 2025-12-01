<?php

namespace App\Services;

use App\Models\User;
use App\Models\Resident;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class ResidentBatchService
{
    protected $pinService;
    protected $idService;
    
    public function __construct(PinGenerationService $pinService, ResidentIdService $idService)
    {
        $this->pinService = $pinService;
        $this->idService = $idService;
    }
    
    /**
     * Process CSV file and create resident accounts
     */
    public function processCsvFile(array $csvData, int $organizationId): array
    {
        \Log::info('ResidentBatchService: Starting batch processing', [
            'csv_data_count' => count($csvData),
            'organization_id' => $organizationId
        ]);
        
        $results = [
            'successful' => [],
            'failed' => [],
            'errors' => []
        ];
        
        // Validate CSV data
        $validation = $this->validateCsvData($csvData);
        if (!$validation['valid']) {
            \Log::error('ResidentBatchService: CSV validation failed', $validation['errors']);
            $results['errors'] = $validation['errors'];
            return $results;
        }
        
        // Check batch size limit
        if (count($csvData) > 50) {
            \Log::error('ResidentBatchService: Batch size exceeded', ['count' => count($csvData)]);
            $results['errors'][] = 'Batch size cannot exceed 50 residents';
            return $results;
        }
        
        // Generate IDs and PINs
        $residentIds = $this->idService->generateBatchIds(count($csvData));
        $pins = $this->pinService->generateBatchPins(count($csvData));
        
        \Log::info('ResidentBatchService: Generated IDs and PINs', [
            'resident_ids' => $residentIds,
            'pins_count' => count($pins)
        ]);
        
        // Process each row
        foreach ($csvData as $index => $row) {
            try {
                \Log::info("ResidentBatchService: Processing row {$index}", $row);
                
                $residentId = $residentIds[$index];
                $pin = $pins[$index];
                
                $resident = $this->createResidentAccount($row, $residentId, $pin, $organizationId);
                
                \Log::info("ResidentBatchService: Successfully created resident", [
                    'resident_id' => $residentId,
                    'user_id' => $resident->user_id,
                    'name' => $resident->user->name
                ]);
                
                $results['successful'][] = [
                    'resident_id' => $residentId,
                    'name' => $resident->user->name,
                    'pin' => $pin
                ];
                
            } catch (Exception $e) {
                \Log::error("ResidentBatchService: Failed to create resident for row {$index}", [
                    'row' => $row,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $results['failed'][] = $row;
                $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        \Log::info('ResidentBatchService: Batch processing completed', [
            'successful_count' => count($results['successful']),
            'failed_count' => count($results['failed']),
            'errors_count' => count($results['errors'])
        ]);
        
        return $results;
    }
    
    /**
     * Validate CSV data
     */
    private function validateCsvData(array $csvData): array
    {
        $errors = [];
        
        foreach ($csvData as $index => $row) {
            $validator = Validator::make($row, [
                'first_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:50',
                'last_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|max:50',
                'date_of_birth' => 'required|date|date_format:Y-m-d|before:today',
                'room_number' => 'nullable|string|regex:/^[a-zA-Z0-9\s-]+$/|max:10',
                'floor_number' => 'nullable|string|regex:/^[a-zA-Z0-9\s-]+$/|max:10'
            ]);
            
            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Create a resident account
     */
    private function createResidentAccount(array $data, string $residentId, string $pin, int $organizationId): Resident
    {
        \Log::info('ResidentBatchService: Creating resident account', [
            'data' => $data,
            'resident_id' => $residentId,
            'organization_id' => $organizationId
        ]);
        
        return DB::transaction(function () use ($data, $residentId, $pin, $organizationId) {
            // Get resident user type
            $residentType = UserType::where('name', 'resident')->first();
            if (!$residentType) {
                \Log::error('ResidentBatchService: Resident user type not found');
                throw new Exception('Resident user type not found');
            }
            
            \Log::info('ResidentBatchService: Found resident user type', ['type_id' => $residentType->id]);
            
            // Sanitize inputs to prevent XSS
            $sanitizedFirstName = strip_tags(trim($data['first_name']));
            $sanitizedLastName = strip_tags(trim($data['last_name']));
            $sanitizedRoomNumber = isset($data['room_number']) && $data['room_number'] ? strip_tags(trim($data['room_number'])) : null;
            $sanitizedFloorNumber = isset($data['floor_number']) && $data['floor_number'] ? strip_tags(trim($data['floor_number'])) : null;
            
            // Create user account
            $userData = [
                'name' => trim($sanitizedFirstName . ' ' . $sanitizedLastName),
                'email' => null, // Residents don't use email
                'username' => $residentId,
                'password' => $this->pinService->hashPin($pin),
                'user_type_id' => $residentType->id,
                'email_verified_at' => null // No email verification needed
            ];
            
            \Log::info('ResidentBatchService: Creating user with data', $userData);
            
            $user = User::create($userData);
            
            \Log::info('ResidentBatchService: User created successfully', ['user_id' => $user->id]);
            
            // Create resident record
            $residentData = [
                'user_id' => $user->id,
                'organization_id' => $organizationId,
                'room_number' => $sanitizedRoomNumber,
                'floor_number' => $sanitizedFloorNumber,
                'date_of_birth' => $data['date_of_birth'],
                'pin_code' => $pin, // Store plain PIN code for admin viewing
                'status' => 'approved', // Auto-approve batch created residents
                'application_date' => now() // Set application date to current time
            ];
            
            \Log::info('ResidentBatchService: Creating resident with data', $residentData);
            
            $resident = Resident::create($residentData);
            
            \Log::info('ResidentBatchService: Resident created successfully', ['resident_id' => $resident->id]);
            
            return $resident;
        });
    }
    
    /**
     * Get CSV template data
     */
    public function getCsvTemplate(): array
    {
        return [
            ['first_name', 'last_name', 'date_of_birth', 'room_number', 'floor_number'],
            ['John', 'Smith', '1950-05-15', '101', '1'],
            ['Mary', 'Johnson', '1948-12-03', '102', '1']
        ];
    }
}
