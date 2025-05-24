<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\EncodingService;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;

class SanitizeFilamentNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo procesar respuestas Livewire/Filament
        if ($request->is('livewire/*') || str_contains($request->getPathInfo(), 'filament')) {
            $this->sanitizeNotifications();
        }

        return $response;
    }

    /**
     * Sanitiza las notificaciones de Filament almacenadas en sesión
     */
    private function sanitizeNotifications(): void
    {
        $notifications = session()->get('filament.notifications', []);
        
        if (!empty($notifications)) {
            $cleanNotifications = array_map(function ($notification) {
                if ($notification instanceof Notification) {
                    // Sanitizar los datos de la notificación
                    $reflection = new \ReflectionClass($notification);
                    
                    // Sanitizar título
                    if ($property = $this->getProperty($reflection, 'title')) {
                        $property->setValue($notification, EncodingService::sanitizeForJson($property->getValue($notification)));
                    }
                    
                    // Sanitizar cuerpo
                    if ($property = $this->getProperty($reflection, 'body')) {
                        $property->setValue($notification, EncodingService::sanitizeForJson($property->getValue($notification)));
                    }
                }
                
                return $notification;
            }, $notifications);
            
            session()->put('filament.notifications', $cleanNotifications);
        }
    }

    /**
     * Obtiene una propiedad privada/protegida si existe
     */
    private function getProperty(\ReflectionClass $reflection, string $propertyName): ?\ReflectionProperty
    {
        try {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            return $property;
        } catch (\ReflectionException) {
            return null;
        }
    }
} 