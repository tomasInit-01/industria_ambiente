<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AFIP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con AFIP
    |
    */

    'cuit' => env('AFIP_CUIT', '00000000001'),
    
    'production' => env('AFIP_PRODUCTION', false),
    
    'cert_path' => env('AFIP_CERT_PATH', storage_path('certificates/certificado.crt')),
    
    'key_path' => env('AFIP_KEY_PATH', storage_path('certificates/clave.key')),
    
    'passphrase' => env('AFIP_PASSPHRASE', ''),
    
    'punto_venta' => env('AFIP_PUNTO_VENTA', 1),
    
    /*
    |--------------------------------------------------------------------------
    | AFIP URLs
    |--------------------------------------------------------------------------
    */
    'wsaa_wsdl' => env('AFIP_PRODUCTION', false) 
        ? 'https://wsaa.afip.gov.ar/ws/services/LoginCms?wsdl'
        : 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl',
        
    'wsfe_wsdl' => env('AFIP_PRODUCTION', false)
        ? 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?WSDL'
        : 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
]; 