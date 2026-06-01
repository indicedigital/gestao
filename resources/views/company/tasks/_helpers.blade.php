@php
    $priorityColors = ['P0' => 'danger', 'P1' => 'warning', 'P2' => 'primary', 'P3' => 'secondary'];
    $statusColors = [
        'backlog' => 'secondary', 'todo' => 'dark', 'in_progress' => 'primary',
        'review' => 'info', 'waiting_client' => 'warning', 'homologation' => 'warning',
        'completed' => 'success',
    ];
    $historyLabels = [
        'created' => 'Task criada',
        'updated' => 'Campo atualizado',
        'status_changed' => 'Status alterado',
        'comment_added' => 'Comentário adicionado',
        'attachment_added' => 'Anexo adicionado',
        'subtask_created' => 'Subtask criada',
        'subtask_updated' => 'Subtask atualizada',
        'subtask_deleted' => 'Subtask removida',
    ];
    $channelLabels = ['system' => 'Sistema', 'client' => 'Cliente', 'api' => 'API', 'automation' => 'Automação'];
@endphp
