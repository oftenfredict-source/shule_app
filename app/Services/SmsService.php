<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $username = 'emcatechn';
    private $password = 'Emca@#12';
    private $senderID = 'ShuleXpert';
    private $baseUrl = 'https://messaging-service.co.tz/link/sms/v1/text/single';

    /**
     * Format SMS message with school name prefix.
     * If school has senderID, no school name prefix is added.
     * If school has no senderID, school name is added as prefix.
     *
     * @param string $message The message content (without school prefix)
     * @param string|null $schoolName School name for fallback (optional, will auto-detect if null)
     * @return string Formatted message
     */
    public function formatMessage(string $message, ?string $schoolName = null): string
    {
        $schoolID = session('schoolID');
        $hasSenderID = false;
        $detectedSchoolName = $schoolName;

        if ($schoolID) {
            $school = \App\Models\School::find($schoolID);
            $hasSenderID = $school && !empty($school->senderID);
            if ($school && !$detectedSchoolName) {
                $detectedSchoolName = $school->school_name;
            }
        }

        // If school has senderID, don't add school name prefix
        if ($hasSenderID) {
            return $message;
        }

        // Add school name prefix if no senderID
        $prefix = $detectedSchoolName ? "{$detectedSchoolName}. " : '';
        return $prefix . $message;
    }

    /**
     * Send SMS to a phone number
     *
     * @param string $phoneNumber Phone number (e.g., 255614863345)
     * @param string $message Message to send
     * @param string|null $senderID Optional sender ID override
     * @return array Response with success status and message
     */
    public function sendSms($phoneNumber, $message, $senderID = null)
    {
        try {
            // Determine Sender ID
            $finalSenderID = $senderID;

            if (!$finalSenderID) {
                $schoolID = session('schoolID');
                if ($schoolID) {
                    $school = \App\Models\School::find($schoolID);
                    if ($school && $school->senderID) {
                        $finalSenderID = $school->senderID;
                    }
                }
            }

            // Fallback to default
            if (!$finalSenderID) {
                $finalSenderID = $this->senderID;
            }
            // Remove any spaces or special characters from phone number
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Robust TZ phone number cleaning:
            // 1. Remove 255 if it exists at the start
            if (str_starts_with($phoneNumber, '255')) {
                $phoneNumber = substr($phoneNumber, 3);
            }
            // 2. Remove leading 0 if it exists
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = substr($phoneNumber, 1);
            }
            // 3. Prepend 255 (gateway expects 2557XXXXXXXX or 2556XXXXXXXX)
            $phoneNumber = '255' . $phoneNumber;

            // Log the attempt for debugging (mask part of the phone for privacy)
            $maskedPhone = substr($phoneNumber, 0, 5) . '...' . substr($phoneNumber, -3);
            Log::info("SmsService: Attempting to send SMS to {$maskedPhone}");

            $text = urlencode($message);
            $url = $this->baseUrl . '?username=' . urlencode($this->username) .
                   '&password=' . urlencode($this->password) .
                   '&from=' . urlencode($finalSenderID) .
                   '&to=' . $phoneNumber .
                   '&text=' . $text;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ));

            $response = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($error) {
                Log::error("SmsService CURL Error: {$error}");
                return [
                    'success' => false,
                    'message' => 'CURL Connection error: ' . $error,
                ];
            }

            $responseData = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
            $isSuccess = ($httpCode == 200 || $httpCode == 201);
            $errorMessage = 'SMS sent successfully';

            if ($responseData) {
                // Handle different gateway response structures
                if (isset($responseData['messages'][0]['status'])) {
                    $status = $responseData['messages'][0]['status'];
                    if (isset($status['groupName']) && ($status['groupName'] === 'REJECTED' || $status['groupName'] === 'FAILED')) {
                        $isSuccess = false;
                        $errorMessage = $status['description'] ?? 'Message was rejected by gateway';
                    }
                } elseif (isset($responseData['error']) || isset($responseData['description'])) {
                        $isSuccess = false;
                        $errorMessage = $responseData['description'] ?? $responseData['error'] ?? 'Gateway returned an error';
                }
            } elseif ($httpCode >= 400) {
                $isSuccess = false;
                $errorMessage = "Gateway returned HTTP code {$httpCode}";
            }

            if (!$isSuccess) {
                Log::warning("SmsService: Failed to send SMS. Response: " . $response);
            } else {
                Log::info("SmsService: SMS sent successfully. Raw Response: " . $response);
            }

            return [
                'success'    => $isSuccess,
                'message'    => $errorMessage,
                'message_id' => $responseData['messages'][0]['messageId'] ?? null,
                'response'   => $response,
                'http_code'  => $httpCode
            ];

        } catch (\Exception $e) {
            Log::error("SmsService Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send welcome SMS to parent with credentials
     *
     * @param string $phoneNumber Parent's phone number
     * @param string $schoolName School name
     * @param string $username Username (phone number)
     * @param string $password Password (last name)
     * @return array
     */
    public function sendParentCredentials($phoneNumber, $schoolName, $username, $password)
    {
        $school = null;
        $schoolID = session('schoolID');
        if ($schoolID) {
            $school = \App\Models\School::find($schoolID);
        }

        $prefix = ($school && $school->senderID) ? "" : "{$schoolName}. ";
        $message = "{$prefix}Usajili umekamilika. Username: {$username}. Password: {$password}. Login: https://shulexpert.com/login";

        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Send student credentials SMS to parent
     *
     * @param string $phoneNumber Parent's phone number
     * @param string $schoolName School name
     * @param string $studentName Student's full name
     * @param string $username Username (admission number)
     * @param string $password Password (last name)
     * @return array
     */
    public function sendStudentCredentials($phoneNumber, $schoolName, $studentName, $username, $password)
    {
        $school = null;
        $schoolID = session('schoolID');
        if ($schoolID) {
            $school = \App\Models\School::find($schoolID);
        }

        $prefix = ($school && $school->senderID) ? "" : "{$schoolName}. ";
        $message = "{$prefix}Mwanafunzi {$studentName} amesajiliwa kikamilifu. Username: {$username}. Password: {$password}. Login: https://shulexpert.com/login";

        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Get SMS account balance
     *
     * @return array Response with balance information
     */
    public function getBalance()
    {
        try {
            // Try different possible balance endpoints
            $balanceUrls = [
                'https://messaging-service.co.tz/link/sms/v1/balance',
                'https://messaging-service.co.tz/api/balance',
                'https://messaging-service.co.tz/link/sms/v1/account/balance'
            ];

            foreach ($balanceUrls as $balanceUrl) {
                $url = $balanceUrl . '?username=' . urlencode($this->username) .
                       '&password=' . urlencode($this->password);

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));

                $response = curl_exec($curl);
                $error = curl_error($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if (!$error && $httpCode == 200) {
                    // Try to parse response
                    $data = json_decode($response, true);

                    // If response is JSON
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return [
                            'success' => true,
                            'balance' => $data['balance'] ?? $data['credits'] ?? $data['amount'] ?? 0,
                            'currency' => $data['currency'] ?? 'TZS',
                            'response' => $data
                        ];
                    }

                    // If response is plain text/number
                    $balance = trim($response);
                    if (is_numeric($balance)) {
                        return [
                            'success' => true,
                            'balance' => (float)$balance,
                            'currency' => 'TZS',
                            'response' => $response
                        ];
                    }
                }
            }

            // If all endpoints failed, return error
            return [
                'success' => false,
                'message' => 'Unable to fetch balance. Please check API endpoint.',
                'balance' => 0
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching balance: ' . $e->getMessage(),
                'balance' => 0
            ];
        }
    }
}


