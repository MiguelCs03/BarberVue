<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagoFacilService
{
    private string $baseUrl;
    private string $serviceToken;
    private string $secretToken;
    private string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.base_url');
        $this->serviceToken = config('services.pagofacil.service_token');
        $this->secretToken = config('services.pagofacil.secret_token');
        $this->callbackUrl = config('services.pagofacil.callback_url');
    }

    /**
     * Autenticarse en PagoFácil y obtener el access token
     * El token se cachea por 50 minutos (expira en 1 hora según docs)
     */
    private function authenticate(): string
    {
        // Intentar obtener el token del cache
        $cachedToken = Cache::get('pagofacil_access_token');
        
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            // Los tokens se envían como HEADERS, no en el body
            $response = Http::withHeaders([
                'tcTokenService' => $this->serviceToken,
                'tcTokenSecret' => $this->secretToken,
            ])->post("{$this->baseUrl}/login");

            if (!$response->successful()) {
                Log::error('Error al autenticar con PagoFácil', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('No se pudo autenticar con PagoFácil');
            }

            $data = $response->json();

            if (!isset($data['values']['accessToken'])) {
                Log::error('Token no encontrado en respuesta de PagoFácil', ['response' => $data]);
                throw new Exception('Token de acceso no encontrado en la respuesta');
            }

            $accessToken = $data['values']['accessToken'];

            // Cachear el token por 50 minutos (expira en 60)
            Cache::put('pagofacil_access_token', $accessToken, now()->addMinutes(50));

            Log::info('Autenticación exitosa con PagoFácil');

            return $accessToken;

        } catch (Exception $e) {
            Log::error('Excepción al autenticar con PagoFácil: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar un código QR para el pago
     * 
     * @param string $uuid UUID único de la transacción
     * @param float $amount Monto a cobrar
     * @param array $clientData Datos del cliente (name, documentId, phoneNumber, email)
     * @param string $orderDetailText Descripción del pedido
     * @return array Contiene transactionId y qrImage (base64)
     */
    public function generarQr(
        string $uuid,
        float $amount,
        array $clientData,
        string $orderDetailText = 'Reserva de Cita'
    ): array {
        try {
            $accessToken = $this->authenticate();

            // Estructura según el ejemplo de PagoFácil
            $payload = [
                'paymentMethod' => 4, // 4 = QR según documentación
                'clientName' => $clientData['name'] ?? 'Cliente',
                'documentType' => 1, // 1 = CI (Cédula de Identidad)
                'documentId' => $clientData['documentId'] ?? '0',
                'phoneNumber' => $clientData['phoneNumber'] ?? '75642403',
                'email' => $clientData['email'] ?? '',
                'paymentNumber' => $uuid, // Nuestro identificador único
                'amount' => $amount,
                'currency' => 2, // 2 = Bolivianos (BOB)
                'clientCode' => config('services.pagofacil.client_code', '11001'), // Código de cliente
                'callbackUrl' => $this->callbackUrl,
                'orderDetail' => [
                    [
                        'serial' => 1,
                        'product' => $orderDetailText,
                        'quantity' => 1,
                        'price' => $amount,
                        'discount' => 0,
                        'total' => $amount,
                    ]
                ],
            ];

            Log::info('Generando QR en PagoFácil', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/generate-qr", $payload);

            if (!$response->successful()) {
                Log::error('Error al generar QR en PagoFácil', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('No se pudo generar el código QR');
            }

            $data = $response->json();

            // Según documentación oficial, el campo se llama 'qrBase64'
            if (!isset($data['values']['transactionId'])) {
                Log::error('Datos incompletos en respuesta de QR', ['response' => $data]);
                throw new Exception('Respuesta incompleta al generar QR - falta transactionId');
            }

            // El QR puede venir en diferentes formatos según la configuración
            $qrImage = $data['values']['qrBase64'] 
                    ?? $data['values']['qrImage'] 
                    ?? null;

            if (!$qrImage) {
                Log::error('QR no encontrado en respuesta', ['response' => $data]);
                throw new Exception('Código QR no encontrado en la respuesta');
            }

            Log::info('QR generado exitosamente', [
                'transactionId' => $data['values']['transactionId'],
                'status' => $data['values']['status'] ?? null,
            ]);

            return [
                'transactionId' => $data['values']['transactionId'],
                'qrImage' => $qrImage, // Base64
                'checkoutUrl' => $data['values']['checkoutUrl'] ?? null,
                'deepLink' => $data['values']['deepLink'] ?? null,
                'expirationDate' => $data['values']['expirationDate'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('Excepción al generar QR: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Consultar el estado de una transacción
     * 
     * @param string|null $pagofacilTransactionId ID de transacción de PagoFácil
     * @param string|null $companyTransactionId ID de transacción de la empresa (nuestro UUID)
     * @return array Estado de la transacción
     */
    public function consultarTransaccion(?string $pagofacilTransactionId = null, ?string $companyTransactionId = null): array
    {
        try {
            $accessToken = $this->authenticate();

            // Según documentación: solo se requiere uno de los dos IDs
            $payload = [];
            
            if ($pagofacilTransactionId) {
                $payload['pagofacilTransactionId'] = $pagofacilTransactionId;
            }
            
            if ($companyTransactionId) {
                $payload['companyTransactionId'] = $companyTransactionId;
            }

            if (empty($payload)) {
                throw new Exception('Se requiere al menos un ID de transacción');
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/query-transaction", $payload);

            if (!$response->successful()) {
                Log::error('Error al consultar transacción en PagoFácil', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('No se pudo consultar la transacción');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Excepción al consultar transacción: ' . $e->getMessage());
            throw $e;
        }
    }
}
