@extends('layouts.mobile')

@section('title', 'Clientes')

@section('content')
<div class="mobile-content">
    <!-- Estatísticas -->
    <div class="mobile-card" style="background: linear-gradient(135deg, rgba(245, 54, 92, 0.1) 0%, rgba(245, 54, 92, 0.05) 100%); border-left: 3px solid #f5365c; margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">CLIENTES BLOQUEADOS</div>
                <div style="font-size: 32px; font-weight: 700; color: #1a202c;">{{ $stats['blocked'] }}</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(245, 54, 92, 0.1); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-ban" style="font-size: 24px; color: #f5365c;"></i>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <form method="GET" action="{{ route('company.clients.index') }}" id="filterForm">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Buscar</label>
                <div style="position: relative;">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nome, e-mail, telefone ou documento" 
                           style="width: 100%; padding: 10px 12px 10px 40px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <button type="button" 
                        class="btn btn-primary" 
                        style="width: 100%; padding: 10px; border-radius: 8px; font-size: 14px;"
                        onclick="document.getElementById('filterForm').submit();">
                    <i class="fas fa-filter me-2"></i>Filtros
                </button>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Ordenar</label>
                <select name="sort_by" 
                        id="sort_by" 
                        class="form-select" 
                        style="padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; width: 100%;"
                        onchange="document.getElementById('sort_order').value = (this.value === '{{ request('sort_by', 'created_at') }}' && document.getElementById('sort_order').value === 'asc') ? 'desc' : 'asc'; document.getElementById('filterForm').submit();">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Data Cadastro</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Nome</option>
                    <option value="id" {{ request('sort_by') === 'id' ? 'selected' : '' }}>ID</option>
                </select>
                <input type="hidden" id="sort_order" name="sort_order" value="{{ request('sort_order', 'desc') }}">
            </div>

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('company.clients.export.excel', request()->query()) }}" 
                   class="btn btn-success" 
                   style="flex: 1; padding: 10px; border-radius: 8px; font-size: 14px; text-align: center;">
                    <i class="fas fa-file-excel"></i>
                </a>
                <a href="{{ route('company.clients.export.pdf', request()->query()) }}" 
                   class="btn btn-danger" 
                   style="flex: 1; padding: 10px; border-radius: 8px; font-size: 14px; text-align: center;">
                    <i class="fas fa-file-pdf"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Botão Novo Cliente -->
    <div style="margin-bottom: 16px;">
        <a href="{{ route('company.clients.create') }}" 
           class="btn btn-primary" 
           style="width: 100%; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-plus"></i> Novo Cliente
        </a>
    </div>

    <!-- Lista de Clientes -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Clientes</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ $clients->total() }} registro(s)</span>
        </div>

        @forelse($clients as $client)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden;">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                    #{{ $client->id }} - {{ $client->name }}
                </div>
                <span class="badge bg-{{ $client->status === 'active' ? 'success' : ($client->status === 'inactive' ? 'secondary' : 'danger') }}" 
                      style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $client->status === 'active' ? 'Ativo' : ($client->status === 'inactive' ? 'Inativo' : 'Bloqueado') }}
                </span>
            </div>

            <div class="mobile-card-item-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Tipo</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        <span class="badge bg-{{ $client->type === 'pj' ? 'primary' : 'info' }}" style="padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                            {{ $client->type === 'pj' ? 'PJ' : 'PF' }}
                        </span>
                    </div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Documento</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">{{ $client->document ?? '-' }}</div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">E-mail</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        @if($client->email)
                            <a href="mailto:{{ $client->email }}" style="color: #5e72e4; text-decoration: none;">{{ $client->email }}</a>
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Telefone</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        @if($client->phone)
                            <a href="tel:{{ $client->phone }}" style="color: #5e72e4; text-decoration: none;">{{ $client->phone }}</a>
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </div>
                </div>

                @if($client->city || $client->state)
                <div class="mobile-card-item-field" style="grid-column: 1 / -1;">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Cidade/Estado</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        {{ $client->city ?? '' }}{{ $client->city && $client->state ? '/' : '' }}{{ $client->state ?? '' }}
                    </div>
                </div>
                @endif
            </div>

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.clients.show', $client) }}" 
                   class="btn btn-info btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('company.clients.edit', $client) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('company.clients.destroy', $client) }}" 
                      method="POST" 
                      class="d-inline delete-form" 
                      data-message="Tem certeza que deseja remover este cliente?"
                      style="flex: 1; margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="btn btn-danger btn-sm" 
                            style="width: 100%; padding: 8px 12px; border-radius: 8px; font-size: 12px;">
                        <i class="fas fa-trash"></i> Remover
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhum cliente encontrado</p>
            @if(request()->hasAny(['search', 'type', 'status', 'city']))
                <a href="{{ route('company.clients.index') }}" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px;">
                    Limpar Filtros
                </a>
            @else
                <a href="{{ route('company.clients.create') }}" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px;">
                    Cadastrar Primeiro Cliente
                </a>
            @endif
        </div>
        @endforelse

        @if($clients->hasPages())
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; padding: 16px;">
            {{ $clients->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
