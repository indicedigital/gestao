<?php

namespace App\Support;

use App\Services\CompanyAuthorizationService;
use App\Support\PermissionModules;
use Illuminate\Support\Facades\Route;

class TutorialCatalog
{
    /** @return array{title: string, subtitle: string, icon: string, sections: array<int, array<string, mixed>>} */
    public static function build(string $persona, CompanyAuthorizationService $authz): array
    {
        $meta = self::personaMeta()[$persona] ?? self::personaMeta()['collaborator'];
        $sections = [];

        foreach (self::sections() as $id => $section) {
            if (! self::sectionVisibleForPersona($persona, $section, $authz)) {
                continue;
            }

            $section['id'] = $id;
            $section['steps'] = self::filterSteps($section['steps'] ?? [], $authz);

            if (isset($section['module'])) {
                $def = PermissionModules::definitions()[$section['module']] ?? null;
                $section['module_label'] ??= $def['label'] ?? null;
                $section['group'] ??= $def['group'] ?? 'Geral';
            } else {
                $section['group'] ??= 'Geral';
            }

            $sections[] = $section;
        }

        return array_merge($meta, ['sections' => $sections]);
    }

    /** @param array<string, mixed> $section */
    private static function sectionVisibleForPersona(string $persona, array $section, CompanyAuthorizationService $authz): bool
    {
        $personas = $section['personas'] ?? [];

        if ($persona === 'admin') {
            if (isset($section['module']) && ! $authz->canAccessModule($section['module'])) {
                return false;
            }

            if (isset($section['requires']) && ! self::authzCan($authz, $section['requires'])) {
                return false;
            }

            return ! in_array('client', $personas, true) || count($personas) > 1;
        }

        if (! in_array($persona, $personas, true)) {
            return false;
        }

        if (isset($section['module']) && ! $authz->canAccessModule($section['module'])) {
            return false;
        }

        if (isset($section['requires']) && ! self::authzCan($authz, $section['requires'])) {
            return false;
        }

        return true;
    }

    private static function authzCan(CompanyAuthorizationService $authz, string $method): bool
    {
        return method_exists($authz, $method) && $authz->{$method}();
    }

    /** @param array<int, array<string, mixed>> $steps */
    private static function filterSteps(array $steps, CompanyAuthorizationService $authz): array
    {
        return array_values(array_filter($steps, function (array $step) use ($authz) {
            if (! isset($step['route'])) {
                return true;
            }

            if (! Route::has($step['route'])) {
                return false;
            }

            if (isset($step['module']) && ! $authz->canAccessModule($step['module'])) {
                return false;
            }

            return true;
        }));
    }

    /** @return array<string, array{title: string, subtitle: string, icon: string}> */
    private static function personaMeta(): array
    {
        return [
            'admin' => [
                'title' => 'Guia do Administrador',
                'subtitle' => 'Visão completa do sistema conforme suas permissões — gestão, financeiro, equipe e configurações.',
                'icon' => 'fa-user-shield',
            ],
            'manager' => [
                'title' => 'Guia do Gestor',
                'subtitle' => 'Como acompanhar projetos, equipe, produtividade e operações do dia a dia.',
                'icon' => 'fa-user-tie',
            ],
            'developer' => [
                'title' => 'Guia do Programador',
                'subtitle' => 'Daily, tasks, kanban e fluxo de trabalho para entregar com clareza.',
                'icon' => 'fa-code',
            ],
            'collaborator' => [
                'title' => 'Guia do Colaborador',
                'subtitle' => 'O essencial para usar o sistema no seu dia a dia.',
                'icon' => 'fa-user',
            ],
            'client' => [
                'title' => 'Guia do Cliente',
                'subtitle' => 'Como acompanhar projetos, abrir solicitações e homologar entregas.',
                'icon' => 'fa-handshake',
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function sections(): array
    {
        return [
            'client-overview' => [
                'personas' => ['client'],
                'group' => 'Portal',
                'title' => 'Visão geral do portal',
                'icon' => 'fa-home',
                'summary' => 'O painel inicial concentra tudo o que você precisa acompanhar: solicitações, projetos, homologações e alertas de ação.',
                'flow' => ['Acessar Início', 'Ler indicadores', 'Verificar alertas', 'Agir nas pendências'],
                'steps' => [
                    [
                        'title' => 'Acesse o Início',
                        'body' => 'No menu lateral, clique em <strong>Início</strong>. Essa é sua página principal no portal — sempre comece por aqui para saber o que precisa de atenção.',
                        'route' => 'portal.dashboard',
                        'route_label' => 'Ir para o Início',
                        'bullets' => [
                            'Veja a saudação com seu nome e o cliente vinculado à sua conta.',
                            'Use o botão <strong>Nova solicitação</strong> no topo para abrir demandas rapidamente.',
                            'Role a página para ver projetos, solicitações recentes e histórico.',
                        ],
                    ],
                    [
                        'title' => 'Entenda os indicadores (KPIs)',
                        'body' => 'Os cartões no topo resumem sua operação. Cada um mede um aspecto diferente do acompanhamento.',
                        'bullets' => [
                            '<strong>Solicitações abertas</strong> — quantas demandas ainda não foram concluídas.',
                            '<strong>Taxa de conclusão</strong> — percentual de solicitações finalizadas no período.',
                            '<strong>Aguardando homologação</strong> — entregas que dependem da sua aprovação.',
                            '<strong>Projetos ativos</strong> — quantos projetos estão em andamento para você.',
                        ],
                    ],
                    [
                        'title' => 'Responda aos alertas de ação',
                        'body' => 'Quando houver tarefas aguardando homologação ou resposta sua, elas aparecem em destaque no topo da página, antes dos demais blocos.',
                        'tip' => 'Homologações pendentes bloqueiam o avanço do projeto. Priorize revisar e aprovar (ou solicitar ajustes) o quanto antes.',
                        'bullets' => [
                            'Clique diretamente no alerta para abrir a tarefa.',
                            'Itens em vermelho ou com ícone de relógio indicam urgência ou atraso.',
                        ],
                    ],
                ],
            ],
            'client-requests' => [
                'personas' => ['client'],
                'group' => 'Portal',
                'title' => 'Abrir solicitações',
                'icon' => 'fa-plus-circle',
                'summary' => 'Registre novas demandas para a equipe com contexto suficiente para agilizar a triagem e execução.',
                'flow' => ['Nova solicitação', 'Preencher formulário', 'Enviar', 'Acompanhar status'],
                'steps' => [
                    [
                        'title' => 'Inicie uma nova solicitação',
                        'body' => 'Clique em <strong>Nova solicitação</strong> no menu lateral ou no botão azul do painel inicial.',
                        'route' => 'portal.tasks.create',
                        'route_label' => 'Criar solicitação',
                        'bullets' => [
                            'O formulário abre em tela dedicada — preencha antes de sair para não perder dados.',
                            'Se você tiver vários projetos, selecione o projeto correto para direcionar a demanda.',
                        ],
                    ],
                    [
                        'title' => 'Preencha título, descrição e prioridade',
                        'body' => 'Quanto mais detalhada a solicitação, menos idas e vindas com a equipe.',
                        'bullets' => [
                            '<strong>Título</strong> — resumo claro do pedido (ex.: "Corrigir layout da página de contato").',
                            '<strong>Descrição</strong> — passos para reproduzir, comportamento esperado vs. atual, links úteis.',
                            '<strong>Prioridade</strong> — use "Alta" apenas para bloqueios ou impacto em produção.',
                            '<strong>Projeto</strong> — vincule ao projeto certo para aparecer no kanban correto.',
                        ],
                        'tip' => 'Inclua prints, vídeos ou documentos como anexo logo após criar a solicitação, na tela de detalhes.',
                    ],
                    [
                        'title' => 'Acompanhe o andamento',
                        'body' => 'Após enviar, a solicitação entra no fluxo interno da equipe. Você acompanha pelo Início ou abrindo a tarefa.',
                        'bullets' => [
                            'Status comuns: Backlog → Em progresso → Revisão → Homologação → Concluído.',
                            'Quando a equipe precisar de informação sua, o status pode mudar para "Aguardando cliente".',
                            'Comentários na tarefa geram notificações — responda pelo portal para manter o histórico.',
                        ],
                    ],
                ],
            ],
            'client-kanban' => [
                'personas' => ['client'],
                'group' => 'Portal',
                'title' => 'Acompanhar projetos (Kanban)',
                'icon' => 'fa-columns',
                'summary' => 'Visualize todas as entregas do projeto organizadas por etapa — modo somente leitura.',
                'flow' => ['Escolher projeto', 'Abrir kanban', 'Localizar tarefa', 'Ver detalhes'],
                'steps' => [
                    [
                        'title' => 'Acesse o kanban do projeto',
                        'body' => 'No Início, na seção de projetos, clique no projeto desejado. Você será direcionado ao quadro kanban daquele projeto.',
                        'bullets' => [
                            'Cada projeto tem seu próprio fluxo de colunas.',
                            'Somente tarefas vinculadas ao seu cliente aparecem para você.',
                        ],
                    ],
                    [
                        'title' => 'Leia as colunas de status',
                        'body' => 'Cada coluna representa uma etapa do fluxo de trabalho. No portal, você visualiza — não arrasta cards.',
                        'bullets' => [
                            '<strong>Backlog</strong> — demandas ainda não iniciadas pela equipe.',
                            '<strong>Em progresso</strong> — trabalho sendo executado agora.',
                            '<strong>Revisão / Homologação</strong> — aguardando validação interna ou sua aprovação.',
                            '<strong>Concluído</strong> — entrega finalizada.',
                        ],
                    ],
                    [
                        'title' => 'Abra o detalhe da tarefa',
                        'body' => 'Clique em qualquer card para ver descrição completa, comentários, anexos e histórico de movimentações.',
                        'tip' => 'Use os comentários para tirar dúvidas sem precisar abrir uma nova solicitação.',
                    ],
                ],
            ],
            'client-homologation' => [
                'personas' => ['client'],
                'group' => 'Portal',
                'title' => 'Homologar entregas',
                'icon' => 'fa-clipboard-check',
                'summary' => 'Valide entregas da equipe — aprove para liberar o próximo passo ou solicite ajustes via comentários.',
                'flow' => ['Identificar pendência', 'Revisar entrega', 'Comentar ajustes ou aprovar', 'Equipe continua'],
                'steps' => [
                    [
                        'title' => 'Localize tarefas aguardando homologação',
                        'body' => 'No Início, o card "Aguardando homologação" e os alertas no topo indicam o que precisa da sua revisão.',
                        'bullets' => [
                            'Tarefas nesse status ficaram prontas para sua validação.',
                            'Enquanto não homologar, a equipe pode estar bloqueada na próxima etapa.',
                        ],
                    ],
                    [
                        'title' => 'Revise com atenção antes de aprovar',
                        'body' => 'Abra a tarefa, leia a descrição da entrega, teste o que foi implementado e verifique anexos.',
                        'bullets' => [
                            'Use comentários para listar ajustes ponto a ponto.',
                            'Anexe referências (prints marcados, documentos) se precisar de correções.',
                            'Se estiver tudo certo, use o botão de aprovação/homologação na tarefa.',
                        ],
                        'tip' => 'Se precisar de correções, comente antes de aprovar — a equipe será notificada automaticamente.',
                    ],
                    [
                        'title' => 'Após homologar',
                        'body' => 'Com a aprovação, a tarefa avança no fluxo e a equipe pode seguir para próximas entregas ou encerrar o item.',
                        'bullets' => [
                            'Você pode acompanhar a mudança de status no kanban.',
                            'Homologações ficam registradas no histórico da tarefa.',
                        ],
                    ],
                ],
            ],
            'dev-dashboard' => [
                'personas' => ['developer', 'collaborator'],
                'module' => 'developer_dashboard',
                'title' => 'Meu Dashboard',
                'icon' => 'fa-code',
                'summary' => 'Central pessoal com suas tarefas, prazos, métricas e prioridades do dia — comece aqui toda manhã.',
                'flow' => ['Abrir dashboard', 'Revisar pendências', 'Priorizar tarefas', 'Executar'],
                'steps' => [
                    [
                        'title' => 'Acesse o Meu Dashboard',
                        'body' => 'No menu <strong>Overview → Meu Dashboard</strong>, você vê apenas o que está atribuído a você.',
                        'route' => 'company.developer-dashboard',
                        'route_label' => 'Abrir Meu Dashboard',
                        'module' => 'developer_dashboard',
                        'bullets' => [
                            'Tarefas do dia e atrasadas aparecem em destaque.',
                            'Métricas pessoais ajudam a acompanhar sua produtividade.',
                            'Atalhos levam direto para tasks e projetos.',
                        ],
                    ],
                    [
                        'title' => 'Organize seu dia',
                        'body' => 'Use a lista de tarefas para definir ordem de execução. Resolva bloqueios e atrasos primeiro.',
                        'bullets' => [
                            'Itens vermelhos ou com prazo vencido têm prioridade máxima.',
                            'Antes de iniciar, confirme se a task tem descrição e critérios claros.',
                            'Se faltar contexto, comente na task antes de começar.',
                        ],
                        'tip' => 'Combine o dashboard com a Daily: registre o plano do dia após revisar as pendências.',
                    ],
                ],
            ],
            'dev-daily' => [
                'personas' => ['developer', 'collaborator', 'manager', 'admin'],
                'module' => 'dailies',
                'title' => 'Registrar Daily',
                'icon' => 'fa-book',
                'summary' => 'Registro diário do que foi feito, do plano e dos impedimentos — base para produtividade e alinhamento do time.',
                'flow' => ['Abrir Daily', 'Registrar ontem/hoje', 'Informar impedimentos', 'Salvar'],
                'steps' => [
                    [
                        'title' => 'Abra o módulo Daily',
                        'body' => 'Menu <strong>Gestão → Daily</strong>. Registre preferencialmente no início do expediente.',
                        'route' => 'company.dailies.index',
                        'route_label' => 'Ir para Daily',
                        'module' => 'dailies',
                        'bullets' => [
                            'Cada dia útil deve ter um registro seu.',
                            'Gestores usam esses dados no dashboard de Produtividade.',
                            'Você pode editar o registro do dia atual se necessário.',
                        ],
                    ],
                    [
                        'title' => 'Preencha os campos principais',
                        'body' => 'Seja objetivo, mas inclua informação suficiente para quem lê entender seu progresso.',
                        'bullets' => [
                            '<strong>Ontem / Concluído</strong> — o que você finalizou desde o último registro.',
                            '<strong>Hoje / Plano</strong> — o que pretende entregar neste dia.',
                            '<strong>Impedimentos</strong> — bloqueios que precisam de ajuda (acesso, decisão, dependência).',
                        ],
                        'tip' => 'Impedimentos visíveis na daily permitem ao gestor desbloquear você no mesmo dia.',
                    ],
                    [
                        'title' => 'Vincule às tasks quando possível',
                        'body' => 'Associar o registro às tarefas do projeto alimenta métricas de produtividade e dá rastreabilidade ao trabalho.',
                        'bullets' => [
                            'Tasks vinculadas aparecem no histórico do projeto.',
                            'Facilita comprovar horas e entregas no fechamento do sprint/mês.',
                        ],
                    ],
                ],
            ],
            'dev-tasks' => [
                'personas' => ['developer', 'collaborator', 'manager', 'admin'],
                'module' => 'tasks',
                'title' => 'Trabalhar com Tasks',
                'icon' => 'fa-tasks',
                'summary' => 'Crie, atualize e conclua tarefas — unidade central de trabalho no sistema.',
                'flow' => ['Listar tasks', 'Abrir detalhe', 'Atualizar status', 'Comentar / Concluir'],
                'steps' => [
                    [
                        'title' => 'Navegue pela lista de tasks',
                        'body' => 'Em <strong>Gestão → Tasks</strong> você filtra por status, responsável, projeto e prazo.',
                        'route' => 'company.tasks.index',
                        'route_label' => 'Ver Tasks',
                        'module' => 'tasks',
                        'bullets' => [
                            'Programadores veem principalmente tasks atribuídas a si.',
                            'Use filtros para focar em "Em progresso" ou "Atrasadas".',
                            'Exportação Excel disponível para gestores com permissão.',
                        ],
                    ],
                    [
                        'title' => 'Atualize status e informações',
                        'body' => 'Ao abrir a task, altere status conforme avança: Backlog → Em progresso → Revisão → Concluído.',
                        'bullets' => [
                            'Registre comentários a cada mudança relevante.',
                            'Atualize prazo se identificar risco de atraso (conforme permissão).',
                            'Anexe evidências (prints, logs) antes de mover para revisão.',
                        ],
                    ],
                    [
                        'title' => 'Use subtarefas e checklist',
                        'body' => 'Divida entregas grandes em partes menores para mostrar progresso real.',
                        'bullets' => [
                            'Subtarefas podem ter responsável próprio.',
                            'Marque checklist item a item — evita fechar task incompleta.',
                            'Homologação do cliente pode ser etapa final em tasks de portal.',
                        ],
                        'tip' => 'Não mova para "Concluído" sem cumprir todos os critérios de aceite da task.',
                    ],
                ],
            ],
            'dev-kanban' => [
                'personas' => ['developer', 'collaborator', 'manager', 'admin'],
                'module' => 'projects',
                'title' => 'Kanban do projeto',
                'icon' => 'fa-columns',
                'summary' => 'Quadro visual do fluxo — arraste cards, crie tasks e acompanhe o time em tempo real.',
                'flow' => ['Entrar no projeto', 'Abrir Kanban', 'Mover cards', 'Criar/atualizar tasks'],
                'steps' => [
                    [
                        'title' => 'Acesse o kanban',
                        'body' => 'Vá em <strong>Projetos</strong>, abra o projeto e clique na aba <strong>Kanban</strong>.',
                        'route' => 'company.projects.index',
                        'route_label' => 'Ver Projetos',
                        'module' => 'projects',
                        'bullets' => [
                            'Cada coluna = um status do fluxo.',
                            'Cards mostram responsável, prazo e prioridade.',
                            'Filtros podem limitar por membro da equipe.',
                        ],
                    ],
                    [
                        'title' => 'Mova cards com responsabilidade',
                        'body' => 'Arrastar um card altera o status da task. Respeite o fluxo acordado com o gestor.',
                        'bullets' => [
                            'Não pule etapas (ex.: Backlog → Concluído direto).',
                            'Em "Revisão", aguarde feedback antes de concluir.',
                            'Programadores geralmente não veem abas Financeiro e Visão geral — normal por permissão.',
                        ],
                        'tip' => 'Se precisar de coluna nova ou fluxo diferente, fale com o gestor do projeto.',
                    ],
                    [
                        'title' => 'Crie tasks direto no quadro',
                        'body' => 'Use o botão "+" na coluna desejada para criar task já no status correto.',
                        'bullets' => [
                            'Informe título, responsável e prazo na criação.',
                            'Tasks criadas no kanban aparecem também na lista geral de Tasks.',
                        ],
                    ],
                ],
            ],
            'manager-productivity' => [
                'personas' => ['manager', 'admin'],
                'module' => 'productivity',
                'title' => 'Dashboard de Produtividade',
                'icon' => 'fa-chart-line',
                'summary' => 'Análise operacional da equipe — KPIs, rankings, alertas, histórico e metas por colaborador.',
                'flow' => ['Abrir Produtividade', 'Filtrar período', 'Analisar abas', 'Agir em alertas'],
                'steps' => [
                    [
                        'title' => 'Acesse o centro de Produtividade',
                        'body' => 'Menu <strong>Gestão → Produtividade</strong>. O painel carrega por abas para performance — cada aba traz um recorte diferente.',
                        'route' => 'company.dailies.productivity',
                        'route_label' => 'Abrir Produtividade',
                        'module' => 'productivity',
                        'bullets' => [
                            '<strong>Visão Geral</strong> — KPIs consolidados do time.',
                            '<strong>Colaboradores</strong> — detalhe individual por pessoa.',
                            '<strong>Rankings</strong> — comparativo de desempenho.',
                            '<strong>Alertas</strong> — dailies ausentes, queda de produtividade etc.',
                            '<strong>Metas</strong> — configure e acompanhe objetivos.',
                        ],
                    ],
                    [
                        'title' => 'Use filtros de período e equipe',
                        'body' => 'Ajuste datas e colaboradores no topo para comparar semanas, fechar o mês ou investigar um caso específico.',
                        'bullets' => [
                            'Períodos curtos (1 semana) para ação imediata.',
                            'Períodos longos (1 mês+) para tendências.',
                            'Exporte dados quando precisar compartilhar com diretoria.',
                        ],
                    ],
                    [
                        'title' => 'Defina e acompanhe metas',
                        'body' => 'Na aba Metas, configure objetivos de produtividade (% ou volume) e acompanhe atingimento ao longo do tempo.',
                        'tip' => 'Combine alertas + metas: quando alguém ficar abaixo da meta por 2 semanas, agende 1:1.',
                    ],
                ],
            ],
            'manager-projects' => [
                'personas' => ['manager', 'admin'],
                'module' => 'projects',
                'title' => 'Gerenciar projetos',
                'icon' => 'fa-project-diagram',
                'summary' => 'Ciclo completo do projeto — cadastro, equipe, kanban, dashboard, financeiro e visão geral.',
                'flow' => ['Criar projeto', 'Definir equipe', 'Planejar tasks', 'Acompanhar entregas'],
                'steps' => [
                    [
                        'title' => 'Cadastre um novo projeto',
                        'body' => 'Em Projetos, clique em criar e preencha cliente, nome, prazos, valor (se aplicável) e responsável.',
                        'route' => 'company.projects.index',
                        'route_label' => 'Gerenciar Projetos',
                        'module' => 'projects',
                        'bullets' => [
                            'Vincule sempre ao cliente correto para liberar portal.',
                            'Defina data de início e previsão de entrega.',
                            'Status do projeto reflete saúde geral (ativo, pausado, concluído).',
                        ],
                    ],
                    [
                        'title' => 'Monte a equipe do projeto',
                        'body' => 'Na aba <strong>Equipe</strong>, adicione programadores, designers ou freelancers que atuarão no projeto.',
                        'bullets' => [
                            'Membros da equipe passam a ver o projeto no kanban e receber tasks.',
                            'Remova membros que saíram do projeto para evitar acesso indevido.',
                        ],
                    ],
                    [
                        'title' => 'Use as abas de acompanhamento',
                        'body' => 'Gestores com permissão acessam visão completa do projeto.',
                        'bullets' => [
                            '<strong>Kanban</strong> — fluxo operacional diário.',
                            '<strong>Dashboard</strong> — métricas de andamento.',
                            '<strong>Visão geral</strong> — resumo executivo do projeto.',
                            '<strong>Financeiro</strong> — receitas/custos vinculados (se habilitado).',
                        ],
                        'tip' => 'Submódulos de projeto são controlados nos perfis de permissão — programadores podem ver só Kanban.',
                    ],
                ],
            ],
            'manager-clients' => [
                'personas' => ['manager', 'admin'],
                'module' => 'clients',
                'title' => 'Clientes e acesso ao portal',
                'icon' => 'fa-users',
                'summary' => 'Cadastro de clientes, contratos vinculados e liberação de login no portal do cliente.',
                'flow' => ['Cadastrar cliente', 'Vincular projetos', 'Criar acesso portal', 'Cliente acompanha'],
                'steps' => [
                    [
                        'title' => 'Cadastre o cliente',
                        'body' => 'Em Clientes, registre razão social, contatos, documento e observações comerciais.',
                        'route' => 'company.clients.index',
                        'route_label' => 'Ver Clientes',
                        'module' => 'clients',
                        'bullets' => [
                            'Um cliente pode ter múltiplos projetos e contratos.',
                            'Exporte lista em Excel/PDF para relatórios.',
                        ],
                    ],
                    [
                        'title' => 'Libere acesso ao portal',
                        'body' => 'Na ficha do cliente, seção de acesso, crie usuário com e-mail e senha para o portal.',
                        'bullets' => [
                            'O cliente loga e vê apenas seus projetos e solicitações.',
                            'Pode abrir novas demandas e homologar entregas.',
                            'Desative acessos de ex-funcionários do cliente quando necessário.',
                        ],
                        'tip' => 'Oriente o cliente a consultar o Tutorial do portal na primeira login.',
                    ],
                ],
            ],
            'admin-dashboard' => [
                'personas' => ['admin', 'manager'],
                'module' => 'dashboard',
                'title' => 'Dashboard da empresa',
                'icon' => 'fa-th-large',
                'summary' => 'Visão executiva — caixa, receitas, despesas, gráficos e assistente IA para decisões rápidas.',
                'flow' => ['Abrir dashboard', 'Analisar KPIs', 'Usar assistente IA', 'Tomar decisão'],
                'steps' => [
                    [
                        'title' => 'Painel executivo',
                        'body' => 'Menu <strong>Overview → Dashboard</strong>. Consolida indicadores financeiros e operacionais da empresa.',
                        'route' => 'company.dashboard',
                        'route_label' => 'Abrir Dashboard',
                        'module' => 'dashboard',
                        'bullets' => [
                            'Saldo de caixa e projeções.',
                            'Receitas vs. despesas no período.',
                            'Gráficos de tendência para reuniões de diretoria.',
                        ],
                    ],
                    [
                        'title' => 'Assistente IA integrado',
                        'body' => 'Use o chat do assistente para perguntas em linguagem natural sobre os números do painel.',
                        'bullets' => [
                            'Ex.: "Qual foi a receita do mês passado?"',
                            'Ex.: "Quais despesas mais cresceram?"',
                        ],
                        'tip' => 'Valide sempre os números críticos nos módulos financeiros antes de decisões importantes.',
                    ],
                ],
            ],
            'admin-finance' => [
                'personas' => ['admin', 'manager'],
                'title' => 'Financeiro',
                'icon' => 'fa-wallet',
                'summary' => 'Gestão completa do fluxo de caixa — receber, pagar, despesas e fornecedores.',
                'flow' => ['Lançar receita', 'Registrar despesa', 'Controlar vencimentos', 'Conciliar pagamentos'],
                'steps' => [
                    [
                        'title' => 'Contas a Receber',
                        'body' => 'Registre faturamentos, parcelas recorrentes e acompanhe o que está em aberto ou atrasado.',
                        'route' => 'company.receivables.index',
                        'route_label' => 'Contas a Receber',
                        'module' => 'receivables',
                        'bullets' => [
                            'Vincule recebíveis a clientes e contratos.',
                            'Marque como recebido ao confirmar pagamento.',
                            'Recebíveis recorrentes são gerados automaticamente pelo agendador diário.',
                        ],
                    ],
                    [
                        'title' => 'Contas a Pagar',
                        'body' => 'Controle obrigações com fornecedores, folha e impostos — por vencimento.',
                        'route' => 'company.payables.index',
                        'route_label' => 'Contas a Pagar',
                        'module' => 'payables',
                        'bullets' => [
                            'Filtre por status: pendente, pago, atrasado.',
                            'Despesas fixas geram pagáveis automaticamente.',
                            'Registre data de pagamento para atualizar o caixa.',
                        ],
                    ],
                    [
                        'title' => 'Despesas e fornecedores',
                        'body' => 'Lance despesas avulsas ou recorrentes e mantenha cadastro de fornecedores organizado.',
                        'route' => 'company.expenses.index',
                        'route_label' => 'Despesas',
                        'module' => 'expenses',
                        'bullets' => [
                            'Use categorias para relatórios (Configurações → Categorias).',
                            'Fornecedores centralizam dados para NF de entrada.',
                            'Despesas fixas reduzem trabalho manual mensal.',
                        ],
                    ],
                ],
            ],
            'admin-contracts' => [
                'personas' => ['admin', 'manager'],
                'module' => 'contracts',
                'title' => 'Contratos',
                'icon' => 'fa-file-contract',
                'summary' => 'Formalize acordos comerciais — valores, vigência, renovação e vínculo com clientes/projetos.',
                'flow' => ['Criar contrato', 'Vincular cliente', 'Associar projeto', 'Gerar recebíveis'],
                'steps' => [
                    [
                        'title' => 'Cadastre contratos',
                        'body' => 'Registre valor total, parcelas, datas de vigência e condições comerciais.',
                        'route' => 'company.contracts.index',
                        'route_label' => 'Ver Contratos',
                        'module' => 'contracts',
                        'bullets' => [
                            'Um contrato pertence a um cliente.',
                            'Pode alimentar contas a receber recorrentes.',
                            'Acompanhe contratos próximos do vencimento.',
                        ],
                    ],
                ],
            ],
            'admin-employees' => [
                'personas' => ['admin', 'manager'],
                'module' => 'employees',
                'title' => 'Funcionários',
                'icon' => 'fa-id-badge',
                'summary' => 'Cadastro da equipe — colaboradores CLT, PJ e freelancers vinculados à operação.',
                'flow' => ['Cadastrar colaborador', 'Vincular usuário', 'Atribuir em tasks', 'Medir produtividade'],
                'steps' => [
                    [
                        'title' => 'Cadastre colaboradores',
                        'body' => 'Funcionários são a base para atribuição de tasks, dailies e métricas de produtividade.',
                        'route' => 'company.employees.index',
                        'route_label' => 'Ver Funcionários',
                        'module' => 'employees',
                        'bullets' => [
                            'Vincule o registro ao usuário do sistema quando existir login.',
                            'Freelancers também podem ser cadastrados para projetos pontuais.',
                            'Dados aparecem em rankings e relatórios de Produtividade.',
                        ],
                    ],
                ],
            ],
            'admin-leads' => [
                'personas' => ['admin', 'manager'],
                'module' => 'leads',
                'title' => 'Leads',
                'icon' => 'fa-funnel-dollar',
                'summary' => 'Pipeline comercial — da prospecção ao fechamento e conversão em cliente.',
                'flow' => ['Captar lead', 'Qualificar', 'Negociar', 'Converter em cliente'],
                'steps' => [
                    [
                        'title' => 'Gerencie oportunidades',
                        'body' => 'Registre leads com origem, status e valor estimado. Mova pelo funil conforme avança a negociação.',
                        'route' => 'company.leads.index',
                        'route_label' => 'Ver Leads',
                        'module' => 'leads',
                        'bullets' => [
                            'Status típicos: Novo → Contato → Proposta → Ganho/Perdido.',
                            'Ao ganhar, converta em cliente para abrir projetos e contratos.',
                            'Use observações para histórico de conversas comerciais.',
                        ],
                    ],
                ],
            ],
            'admin-accounting' => [
                'personas' => ['admin', 'manager'],
                'title' => 'Contabilidade fiscal',
                'icon' => 'fa-file-invoice',
                'summary' => 'Controle de NF de entrada/saída e relatório fiscal consolidado.',
                'flow' => ['Lançar NF entrada', 'Registrar NF saída', 'Conferir relatório', 'Fechar período'],
                'steps' => [
                    [
                        'title' => 'NF de entrada',
                        'body' => 'Registre notas recebidas de fornecedores com valores, datas e vínculo.',
                        'route' => 'company.accounting.fiscal-entry-notes.index',
                        'route_label' => 'NF Entrada',
                        'module' => 'accounting_entry',
                        'bullets' => [
                            'Marque como lançada após contabilizar.',
                            'Relaciona com fornecedores cadastrados.',
                        ],
                    ],
                    [
                        'title' => 'NF de saída',
                        'body' => 'Registre emissões para clientes — faturamento prestado.',
                        'route' => 'company.accounting.fiscal-exit-notes.index',
                        'route_label' => 'NF Saída',
                        'module' => 'accounting_exit',
                        'bullets' => [
                            'Controle status de emissão.',
                            'Vincule ao cliente correto.',
                        ],
                    ],
                    [
                        'title' => 'Relatório fiscal',
                        'body' => 'Visão consolidada para conferência mensal ou entrega ao contador.',
                        'route' => 'company.accounting.report',
                        'route_label' => 'Relatório Fiscal',
                        'module' => 'accounting_report',
                        'bullets' => [
                            'Compare entradas vs. saídas no período.',
                            'Exporte ou filtre por intervalo de datas.',
                        ],
                    ],
                ],
            ],
            'admin-permissions' => [
                'personas' => ['admin'],
                'requires' => 'canManageProfiles',
                'group' => 'Configurações',
                'title' => 'Perfis de permissão',
                'icon' => 'fa-shield-alt',
                'summary' => 'Defina o que cada perfil pode acessar — módulos, escopo de dados e submódulos de projeto.',
                'flow' => ['Criar perfil', 'Marcar módulos', 'Vincular usuários', 'Validar acesso'],
                'steps' => [
                    [
                        'title' => 'Configure perfis de acesso',
                        'body' => 'Em <strong>Configurações → Permissões</strong>, crie perfis como Programador, Gestor, Financeiro etc.',
                        'route' => 'company.permission-profiles.index',
                        'route_label' => 'Gerenciar Permissões',
                        'bullets' => [
                            'Cada módulo pode ser liberado ou bloqueado.',
                            'Escopo "assigned" limita a tasks/projetos do usuário.',
                            'Submódulos de projeto: visão geral, financeiro, dashboard — separados.',
                        ],
                    ],
                    [
                        'title' => 'Associe perfis aos usuários',
                        'body' => 'Ao convidar ou editar membros da empresa, selecione o perfil correto.',
                        'bullets' => [
                            'Programador: Daily, Tasks, Kanban — sem financeiro.',
                            'Gestor: projetos + produtividade + clientes.',
                            'Admin/Owner: acesso amplo conforme perfil.',
                        ],
                        'tip' => 'Teste com um usuário de cada perfil para validar o que aparece no menu.',
                    ],
                ],
            ],
            'admin-settings' => [
                'personas' => ['admin', 'manager'],
                'module' => 'expense_categories',
                'title' => 'Categorias e configurações',
                'icon' => 'fa-cog',
                'summary' => 'Padronize categorias de despesas para relatórios financeiros consistentes.',
                'flow' => ['Criar categorias', 'Usar em despesas', 'Analisar relatórios'],
                'steps' => [
                    [
                        'title' => 'Organize categorias de despesas',
                        'body' => 'Crie categorias alinhadas ao plano de contas ou centros de custo da empresa.',
                        'route' => 'company.expense-categories.index',
                        'route_label' => 'Categorias',
                        'module' => 'expense_categories',
                        'bullets' => [
                            'Ex.: Infraestrutura, Marketing, Pessoal, Impostos.',
                            'Toda despesa deve usar categoria para DRE confiável.',
                        ],
                    ],
                ],
            ],
            'collab-basics' => [
                'personas' => ['collaborator'],
                'group' => 'Introdução',
                'title' => 'Primeiros passos',
                'icon' => 'fa-play-circle',
                'summary' => 'Orientações iniciais para navegar no sistema conforme suas permissões.',
                'flow' => ['Explorar menu', 'Verificar notificações', 'Consultar tutorial', 'Executar tasks'],
                'steps' => [
                    [
                        'title' => 'Entenda seu menu lateral',
                        'body' => 'Os itens visíveis dependem do perfil atribuído pelo administrador. Se faltar algum módulo, solicite acesso.',
                        'bullets' => [
                            'Overview: dashboards pessoais ou da empresa.',
                            'Gestão: projetos, tasks, daily e demais módulos liberados.',
                            'Ajuda: este tutorial sempre disponível.',
                        ],
                    ],
                    [
                        'title' => 'Acompanhe notificações',
                        'body' => 'O ícone de sino no topo concentra alertas de tasks, comentários, prazos e homologações.',
                        'bullets' => [
                            'Clique na notificação para ir direto ao item.',
                            'Notificações não lidas ficam destacadas.',
                        ],
                    ],
                    [
                        'title' => 'Use este guia como referência',
                        'body' => 'O conteúdo é filtrado pelo seu perfil e permissões. Volte sempre que precisar relembrar um fluxo.',
                        'tip' => 'Se seu dia a dia envolve tasks e daily, priorize esses módulos nos guias abaixo.',
                    ],
                ],
            ],
        ];
    }
}
