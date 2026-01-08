<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Índice</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #5e72e4;
            --primary-dark: #4c63d2;
            --secondary-color: #8392ab;
            --success-color: #2dce89;
            --danger-color: #f5365c;
            --warning-color: #fb6340;
            --info-color: #11cdef;
            --light-color: #f8f9fa;
            --dark-color: #1a202c;
            --sidebar-width: 280px;
            --header-height: 70px;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f7fafc;
            color: var(--dark-color);
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* Sidebar - Design Moderno e Limpo */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
        }
        
        .sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-logo img {
            height: 32px;
            width: auto;
            object-fit: contain;
        }
        
        .sidebar-menu {
            padding: 16px 0;
        }
        
        .menu-section {
            margin-bottom: 24px;
        }
        
        .menu-section-title {
            padding: 8px 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        
        .sidebar-menu-item {
            padding: 10px 20px;
            color: #4a5568;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border-radius: 8px;
            margin: 2px 12px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .sidebar-menu-item i {
            width: 20px;
            font-size: 18px;
            color: var(--text-muted);
            transition: color 0.2s;
        }
        
        .sidebar-menu-item:hover {
            background-color: #f7fafc;
            color: var(--dark-color);
        }
        
        .sidebar-menu-item:hover i {
            color: var(--primary-color);
        }
        
        .sidebar-menu-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .sidebar-menu-item.active i {
            color: white;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            background: #f7fafc;
        }
        
        /* Header - Design Moderno */
        .header {
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 24px;
            flex: 1;
        }
        
        .page-title-header {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .header-center {
            flex: 1;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .search-box {
            position: relative;
            width: 100%;
        }
        
        .search-box input {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px 45px 10px 16px;
            width: 100%;
            font-size: 14px;
            background: #f7fafc;
            transition: all 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
        }
        
        .search-box i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7fafc;
            color: var(--dark-color);
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            border: 1px solid transparent;
        }
        
        .header-icon:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .header-icon .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }
        
        /* Dropdown de Notificações */
        .notifications-dropdown {
            min-width: 380px;
            max-width: 380px;
            max-height: 500px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .notifications-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notifications-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 16px;
            color: #1a202c;
        }
        
        .notifications-header a {
            font-size: 13px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .notifications-header a:hover {
            text-decoration: underline;
        }
        
        .notifications-body {
            max-height: 400px;
            overflow-y: auto;
            background: white;
        }
        
        .notification-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 12px;
        }
        
        .notification-item:hover {
            background: #f8fafc;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .notification-icon.danger { background: #fee2e2; color: #f5365c; }
        .notification-icon.warning { background: #fef3c7; color: #fb6340; }
        .notification-icon.info { background: #dbeafe; color: #11cdef; }
        .notification-icon.success { background: #d1fae5; color: #2dce89; }
        
        .notification-content {
            flex: 1;
            min-width: 0;
        }
        
        .notification-title {
            font-weight: 600;
            font-size: 14px;
            color: #1a202c;
            margin-bottom: 4px;
            line-height: 1.4;
        }
        
        .notification-message {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        
        .notification-time {
            font-size: 11px;
            color: #94a3b8;
        }
        
        .notifications-empty {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
        }
        
        .notifications-empty i {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 12px;
            transition: background 0.2s;
        }
        
        .user-profile:hover {
            background: #f7fafc;
        }
        
        .user-profile-dropdown {
            min-width: 200px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .user-profile-dropdown .dropdown-item {
            padding: 12px 20px;
            font-size: 14px;
            color: #1a202c;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-profile-dropdown .dropdown-item:hover {
            background: #f7fafc;
            color: var(--primary-color);
        }
        
        .user-profile-dropdown .dropdown-item.logout {
            color: var(--danger-color);
        }
        
        .user-profile-dropdown .dropdown-item.logout:hover {
            background: #fee2e2;
            color: var(--danger-color);
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .user-role {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        /* Content Area */
        .content-wrapper {
            padding: 32px;
        }
        
        .page-header {
            margin-bottom: 32px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        /* Cards - Design Moderno */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            background: white;
            transition: all 0.2s;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
            border-radius: 16px 16px 0 0;
            font-weight: 600;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Buttons */
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        /* Ajusta tamanho dos ícones da paginação */
        .pagination .page-link i,
        .pagination .page-link svg {
            font-size: 14px !important;
            width: 14px !important;
            height: 14px !important;
        }
        
        .pagination .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 14px;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header-center {
                display: none;
            }
            
            .content-wrapper {
                padding: 20px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ (Auth::user()->is_super_admin ?? false) ? route('admin.dashboard') : route('company.dashboard') }}" class="sidebar-logo">
                <img src="{{ asset('imagens/logosemfundo.png') }}" alt="Índice">
                <span>Índice</span>
            </a>
        </div>
        <nav class="sidebar-menu">
            @if(Auth::user()->is_super_admin ?? false)
                <div class="menu-section">
                    <div class="menu-section-title">Overview</div>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Administração</div>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Usuários</span>
                    </a>
                    <a href="{{ route('admin.companies.index') }}" class="sidebar-menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span>Empresas</span>
                    </a>
                </div>
            @else
                <div class="menu-section">
                    <div class="menu-section-title">Overview</div>
                    <a href="{{ route('company.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Gestão</div>
                    <a href="{{ route('company.clients.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                    <a href="{{ route('company.projects.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.projects.*') ? 'active' : '' }}">
                        <i class="fas fa-project-diagram"></i>
                        <span>Projetos</span>
                    </a>
                    <a href="{{ route('company.contracts.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.contracts.*') ? 'active' : '' }}">
                        <i class="fas fa-file-contract"></i>
                        <span>Contratos</span>
                    </a>
                    <a href="{{ route('company.employees.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.employees.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Funcionários</span>
                    </a>
                    <a href="{{ route('company.expenses.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.expenses.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i>
                        <span>Despesas</span>
                    </a>
                    <a href="{{ route('company.suppliers.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck"></i>
                        <span>Fornecedores</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Financeiro</div>
                    <a href="{{ route('company.receivables.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.receivables.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-circle-down"></i>
                        <span>Contas a Receber</span>
                    </a>
                    <a href="{{ route('company.payables.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.payables.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-circle-up"></i>
                        <span>Contas a Pagar</span>
                    </a>
                </div>
            @endif
            <div class="menu-section" style="margin-top: auto; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <div class="menu-section-title">Configurações</div>
                <a href="{{ route('company.expense-categories.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.expense-categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Categorias de Despesas</span>
                </a>
                <a href="#" class="sidebar-menu-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Ajuda & Suporte</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <h1 class="page-title-header">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="header-center">
                <div class="search-box">
                    <input type="text" placeholder="Buscar cliente, projeto, contrato..." id="globalSearch">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="header-right">
                <div class="dropdown">
                    <div class="header-icon" id="notificationsIcon" data-bs-toggle="dropdown" aria-expanded="false" title="Notificações">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationsBadge" style="display: none;">0</span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsIcon">
                        <li>
                            <div class="notifications-header">
                                <h6>Notificações</h6>
                                <a href="#" id="markAllRead" style="display: none;">Marcar todas como lidas</a>
                            </div>
                        </li>
                        <li>
                            <div class="notifications-body" id="notificationsBody">
                                <div class="notifications-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p class="mb-0">Nenhuma notificação</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="header-icon" title="Mensagens">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="dropdown">
                    <div class="user-profile" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-role">{{ Auth::user()->is_super_admin ? 'Super Admin' : 'Usuário' }}</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end user-profile-dropdown" aria-labelledby="userProfileDropdown">
                        <li>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item logout" style="width: 100%; border: none; background: none; text-align: left;">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sair</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // SweetAlert2 para mensagens de sessão
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#5e72e4',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#f5365c',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: '{{ session('warning') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#fb6340',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informação!',
                text: '{{ session('info') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#11cdef',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // Função global para confirmação de exclusão
        window.confirmDelete = function(event) {
            event.preventDefault();
            const form = event.target.closest('form');
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter isso!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        };

        // Função global para confirmação de ação genérica
        window.confirmAction = function(event, title = 'Confirmar Ação?', text = 'Você tem certeza que deseja prosseguir?', confirmButtonText = 'Sim, prosseguir!') {
            event.preventDefault();
            const form = event.target.closest('form');
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5e72e4',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        };

        // Função global para exibir alertas simples
        window.showAlert = function(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#5e72e4',
            });
        };

        // Adiciona event listener para formulários com a classe 'delete-form'
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form.delete-form').forEach(form => {
                form.addEventListener('submit', window.confirmDelete);
            });
            
            // Carrega notificações
            loadNotifications();
            
            // Atualiza notificações a cada 30 segundos
            setInterval(loadNotifications, 30000);
        });
        
        // Função para carregar notificações
        function loadNotifications() {
            @if(!(Auth::user()->is_super_admin ?? false))
            fetch('{{ route("company.notifications") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notificationsBadge');
                const body = document.getElementById('notificationsBody');
                const markAllRead = document.getElementById('markAllRead');
                
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                    
                    let html = '';
                    data.notifications.forEach(function(notif) {
                        html += `
                            <a href="${notif.url || '#'}" class="notification-item text-decoration-none text-dark">
                                <div class="notification-icon ${notif.type}">
                                    <i class="fas fa-${notif.icon}"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">${notif.title}</div>
                                    <div class="notification-message">${notif.message}</div>
                                    <div class="notification-time">${notif.time}</div>
                                </div>
                            </a>
                        `;
                    });
                    
                    body.innerHTML = html;
                    markAllRead.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                    body.innerHTML = `
                        <div class="notifications-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p class="mb-0">Nenhuma notificação</p>
                        </div>
                    `;
                    markAllRead.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar notificações:', error);
            });
            @endif
        }
    </script>
    @stack('scripts')
</body>
</html>
