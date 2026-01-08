<?php

namespace App\Http\Middleware;

use App\Services\RecurringContractService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class GenerateRecurringReceivables
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Executa apenas uma vez por dia (cache de 24 horas)
        $cacheKey = 'recurring_receivables_generated_' . date('Y-m-d');
        
        if (!Cache::has($cacheKey)) {
            try {
                $service = new RecurringContractService();
                $service->generateAllRecurringReceivables();
                
                // Marca como executado hoje
                Cache::put($cacheKey, true, now()->endOfDay());
            } catch (\Exception $e) {
                // Log do erro mas não interrompe a requisição
                \Log::error('Erro ao gerar contas recorrentes: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
}
