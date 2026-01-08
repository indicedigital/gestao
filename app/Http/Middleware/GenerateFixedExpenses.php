<?php

namespace App\Http\Middleware;

use App\Services\FixedExpenseService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class GenerateFixedExpenses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Executa apenas uma vez por dia (cache de 24 horas)
        $cacheKey = 'fixed_expenses_generated_' . date('Y-m-d');
        
        if (!Cache::has($cacheKey)) {
            try {
                $service = new FixedExpenseService();
                $service->generatePayablesForFixedExpenses();
                
                // Marca como executado hoje
                Cache::put($cacheKey, true, now()->endOfDay());
            } catch (\Exception $e) {
                // Log do erro mas não interrompe a requisição
                \Log::error('Erro ao gerar despesas fixas: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
}
