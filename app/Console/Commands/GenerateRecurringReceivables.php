<?php

namespace App\Console\Commands;

use App\Services\RecurringContractService;
use Illuminate\Console\Command;

class GenerateRecurringReceivables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:generate-recurring-receivables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera contas a receber para contratos recorrentes do mês atual e próximo mês';

    /**
     * Execute the console command.
     */
    public function handle(RecurringContractService $service)
    {
        $this->info('Gerando contas a receber para contratos recorrentes...');
        
        $result = $service->generateAllRecurringReceivables();
        
        $this->info("✓ Geradas {$result['current_month']} contas para o mês atual");
        $this->info("✓ Geradas {$result['next_month']} contas para o próximo mês");
        
        if (!empty($result['errors'])) {
            $this->warn('Erros encontrados:');
            foreach ($result['errors'] as $error) {
                $this->error("  - Contrato #{$error['contract_id']} ({$error['contract_name']}): {$error['error']}");
            }
        }
        
        $this->info('Processo concluído!');
        
        return Command::SUCCESS;
    }
}
