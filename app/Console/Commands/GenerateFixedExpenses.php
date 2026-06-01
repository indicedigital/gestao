<?php

namespace App\Console\Commands;

use App\Services\FixedExpenseService;
use Illuminate\Console\Command;

class GenerateFixedExpenses extends Command
{
    protected $signature = 'expenses:generate-fixed-payables';

    protected $description = 'Gera contas a pagar para despesas fixas ativas';

    public function handle(FixedExpenseService $service): int
    {
        $this->info('Gerando contas a pagar para despesas fixas...');

        try {
            $service->generatePayablesForFixedExpenses();
            $this->info('Processo concluído.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
