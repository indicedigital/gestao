<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Índice</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #667eea;
            --primary-dark: #764ba2;
            --success-color: #2dce89;
            --danger-color: #f5365c;
            --text-muted: #64748b;
            --dark-color: #1a202c;
            --border-color: #e2e8f0;
            --bg-color: #f7fafc;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-color);
            color: var(--dark-color);
            font-size: 14px;
            line-height: 1.6;
            padding-bottom: 80px;
            overflow-x: hidden;
        }
        
        /* Sidebar Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.3s;
            backdrop-filter: blur(2px);
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 999;
            transform: translateX(-100%);
            transition: transform 0.3s;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-logo {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-logo img {
            height: 28px;
            width: auto;
        }
        
        .sidebar-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
        }
        
        .sidebar-menu {
            padding: 16px 0;
        }
        
        .menu-section {
            margin-bottom: 20px;
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
            padding: 12px 20px;
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
        
        .sidebar-menu-item:hover {
            background-color: var(--bg-color);
            color: var(--dark-color);
        }
        
        .sidebar-menu-item.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }
        
        .sidebar-menu-item i {
            width: 20px;
            font-size: 18px;
        }
        
        /* Header Mobile */
        .mobile-header {
            background: white;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .mobile-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .mobile-header-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .mobile-header-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
        }
        
        /* Content */
        .mobile-content {
            padding: 20px;
            max-width: 100%;
        }
        
        /* Cards */
        .mobile-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid var(--border-color);
            padding: 8px 0;
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 500;
            padding: 8px 4px;
            border-radius: 12px;
            transition: all 0.2s;
            flex: 1;
            min-width: 0;
        }
        
        .bottom-nav-item.active {
            color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }
        
        .bottom-nav-item i {
            font-size: 18px;
        }
        
        .bottom-nav-item.active i {
            color: var(--primary-color);
        }
        
        .bottom-nav-item span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            text-align: center;
        }
        
        /* Balance Card */
        .balance-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        
        .balance-label {
            font-size: 10px;
            opacity: 0.9;
            margin-bottom: 4px;
            font-weight: 500;
        }
        
        .balance-value {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        
        .balance-toggle {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            opacity: 0.9;
            cursor: pointer;
        }
        
        .balance-toggle i {
            font-size: 12px;
        }
        
        /* Income/Expense Cards */
        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .summary-icon.income {
            background: rgba(45, 206, 137, 0.1);
            color: var(--success-color);
        }
        
        .summary-icon.expense {
            background: rgba(245, 54, 92, 0.1);
            color: var(--danger-color);
        }
        
        .summary-content {
            flex: 1;
        }
        
        .summary-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        
        .summary-value {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .summary-value.positive {
            color: var(--success-color);
        }
        
        .summary-value.negative {
            color: var(--danger-color);
        }
        
        /* Chart Container */
        .chart-container {
            position: relative;
            height: 200px;
            margin: 20px 0;
        }
        
        .chart-legend {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
        }
        
        .chart-legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }
        
        .chart-legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .chart-legend-label {
            flex: 1;
            color: var(--dark-color);
        }
        
        .chart-legend-value {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        /* Bills Section */
        .bills-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .bill-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 12px;
            background: var(--bg-color);
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .bill-tab.active {
            background: var(--success-color);
            color: white;
        }
        
        .bill-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: var(--bg-color);
            border-radius: 12px;
            margin-bottom: 12px;
        }
        
        .bill-info {
            flex: 1;
        }
        
        .bill-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--dark-color);
            margin-bottom: 4px;
        }
        
        .bill-due {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .bill-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
            margin-right: 12px;
        }
        
        .bill-button {
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
        }
        
        /* Month Selector */
        .month-selector {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .month-nav {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
        }
        
        .month-current {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        /* Table to Cards Conversion for Mobile */
        .table-mobile-wrapper {
            display: none;
        }
        
        .mobile-card-item {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }
        
        .mobile-card-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .mobile-card-item-title {
            font-weight: 600;
            font-size: 15px;
            color: var(--dark-color);
            flex: 1;
        }
        
        .mobile-card-item-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .mobile-card-item-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .mobile-card-item-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .mobile-card-item-label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .mobile-card-item-value {
            font-size: 13px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .mobile-card-item-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
        }
        
        .mobile-card-item-actions .btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
            border-radius: 8px;
        }
        
        .mobile-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 16px;
        }
        
        .mobile-pagination-btn {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: 8px;
            color: var(--dark-color);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .mobile-pagination-btn:hover:not(:disabled) {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .mobile-pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .mobile-pagination-info {
            font-size: 12px;
            color: var(--text-muted);
            padding: 0 12px;
        }
        
        @media (max-width: 992px) {
            body {
                display: block !important;
            }
            
            /* Hide tables on mobile */
            .table-responsive,
            .table {
                display: none !important;
            }
            
            /* Show mobile cards */
            .table-mobile-wrapper {
                display: block;
            }
        }
        
        /* Hide on desktop */
        @media (min-width: 993px) {
            body {
                display: none;
            }
            
            .table-mobile-wrapper {
                display: none !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ (Auth::user()->is_super_admin ?? false) ? route('admin.dashboard') : route('company.dashboard') }}" class="sidebar-logo">
                <img src="{{ asset('imagens/logosemfundo.png') }}" alt="Índice">
                <span>Índice</span>
            </a>
            <div class="sidebar-close" onclick="closeSidebar()">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <nav class="sidebar-menu">
            @if(Auth::user()->is_super_admin ?? false)
                <div class="menu-section">
                    <div class="menu-section-title">Overview</div>
                    <a href="{{ route('admin.dashboard') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Administração</div>
                    <a href="{{ route('admin.users.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Usuários</span>
                    </a>
                    <a href="{{ route('admin.companies.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span>Empresas</span>
                    </a>
                </div>
            @else
                <div class="menu-section">
                    <div class="menu-section-title">Overview</div>
                    <a href="{{ route('company.dashboard') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Gestão</div>
                    <a href="{{ route('company.clients.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                    <a href="{{ route('company.projects.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.projects.*') ? 'active' : '' }}">
                        <i class="fas fa-project-diagram"></i>
                        <span>Projetos</span>
                    </a>
                    <a href="{{ route('company.contracts.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.contracts.*') ? 'active' : '' }}">
                        <i class="fas fa-file-contract"></i>
                        <span>Contratos</span>
                    </a>
                    <a href="{{ route('company.employees.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.employees.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Funcionários</span>
                    </a>
                    <a href="{{ route('company.expenses.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.expenses.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i>
                        <span>Despesas</span>
                    </a>
                    <a href="{{ route('company.suppliers.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck"></i>
                        <span>Fornecedores</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Financeiro</div>
                    <a href="{{ route('company.receivables.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.receivables.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-circle-down"></i>
                        <span>Contas a Receber</span>
                    </a>
                    <a href="{{ route('company.payables.index') }}?mobile=1" class="sidebar-menu-item {{ request()->routeIs('company.payables.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-circle-up"></i>
                        <span>Contas a Pagar</span>
                    </a>
                </div>
            @endif
        </nav>
    </aside>
    
    <!-- Header -->
    <header class="mobile-header">
        <div class="mobile-header-left">
            <div class="mobile-header-icon" id="menuToggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </div>
            <h1 class="mobile-header-title">@yield('title', 'Dashboard')</h1>
        </div>
        <div class="mobile-header-icon">
            <i class="fas fa-bell"></i>
        </div>
    </header>
    
    <!-- Content -->
    <div class="mobile-content">
        @yield('content')
    </div>
    
    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ route('company.dashboard') }}?mobile=1" class="bottom-nav-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Principal</span>
        </a>
        <a href="{{ route('company.clients.index') }}?mobile=1" class="bottom-nav-item {{ request()->routeIs('company.clients.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Clientes</span>
        </a>
        <a href="{{ route('company.contracts.index') }}?mobile=1" class="bottom-nav-item {{ request()->routeIs('company.contracts.*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i>
            <span>Contratos</span>
        </a>
        <a href="{{ route('company.receivables.index') }}?mobile=1" class="bottom-nav-item {{ request()->routeIs('company.receivables.*') ? 'active' : '' }}">
            <i class="fas fa-arrow-circle-down"></i>
            <span>Receber</span>
        </a>
        <a href="{{ route('company.payables.index') }}?mobile=1" class="bottom-nav-item {{ request()->routeIs('company.payables.*') ? 'active' : '' }}">
            <i class="fas fa-arrow-circle-up"></i>
            <span>Pagar</span>
        </a>
    </nav>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set mobile cookie
        if (window.innerWidth <= 992) {
            document.cookie = 'is_mobile=1; path=/; max-age=86400';
        }
        
        // Sidebar toggle functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Close sidebar when clicking overlay
        document.getElementById('sidebarOverlay')?.addEventListener('click', closeSidebar);
        
        // Close sidebar when clicking menu item
        document.querySelectorAll('.sidebar-menu-item').forEach(item => {
            item.addEventListener('click', function() {
                setTimeout(closeSidebar, 300);
            });
        });
        
        // Convert tables to cards on mobile
        function convertTablesToCards() {
            if (window.innerWidth > 992) return;
            
            document.querySelectorAll('table.table, table.table-hover, table.table-modern').forEach(table => {
                // Skip if already converted
                if (table.dataset.converted === 'true') return;
                if (table.nextElementSibling && table.nextElementSibling.classList.contains('table-mobile-wrapper')) return;
                
                const wrapper = document.createElement('div');
                wrapper.className = 'table-mobile-wrapper';
                const wrapperId = 'mobile-cards-' + Math.random().toString(36).substr(2, 9);
                wrapper.id = wrapperId;
                
                const thead = table.querySelector('thead');
                const tbody = table.querySelector('tbody');
                
                if (!thead || !tbody) return;
                
                const headers = Array.from(thead.querySelectorAll('th')).map(th => {
                    const text = th.textContent.trim();
                    // Remove icons from header text
                    return text.replace(/[^\w\s]/g, '').trim();
                });
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                if (rows.length === 0) {
                    wrapper.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i><p>Nenhum registro encontrado</p></div>';
                    table.parentElement.insertBefore(wrapper, table);
                    table.dataset.converted = 'true';
                    return;
                }
                
                // Pagination settings
                const itemsPerPage = 10;
                const totalPages = Math.ceil(rows.length / itemsPerPage);
                
                function renderPage(page) {
                    const start = (page - 1) * itemsPerPage;
                    const end = start + itemsPerPage;
                    const pageRows = rows.slice(start, end);
                    
                    let html = '<div class="mobile-cards-container">';
                    
                    pageRows.forEach((row) => {
                        const cells = Array.from(row.querySelectorAll('td'));
                        if (cells.length === 0) return;
                        
                        html += '<div class="mobile-card-item">';
                        html += '<div class="mobile-card-item-header">';
                        
                        // First column as title
                        if (cells[0]) {
                            const titleText = cells[0].textContent.trim() || cells[0].innerHTML;
                            html += `<div class="mobile-card-item-title">${titleText}</div>`;
                        }
                        
                        // Status/Badge from last column if exists
                        const lastCell = cells[cells.length - 1];
                        const badges = lastCell ? lastCell.querySelectorAll('.badge') : [];
                        if (badges.length > 0) {
                            badges.forEach(badge => {
                                html += `<span class="badge ${badge.className}">${badge.textContent}</span>`;
                            });
                        }
                        
                        html += '</div>';
                        html += '<div class="mobile-card-item-body">';
                        
                        // Render other columns (skip first and last)
                        for (let i = 1; i < cells.length - 1; i++) {
                            if (headers[i] && cells[i]) {
                                const label = headers[i];
                                let value = cells[i].innerHTML;
                                
                                // Skip if it's a button or action
                                if (value.includes('btn') || cells[i].querySelector('.btn')) continue;
                                
                                // Clean value
                                value = value.replace(/<[^>]*>/g, '').trim() || '-';
                                
                                html += `<div class="mobile-card-item-field">`;
                                html += `<div class="mobile-card-item-label">${label}</div>`;
                                html += `<div class="mobile-card-item-value">${value}</div>`;
                                html += `</div>`;
                            }
                        }
                        
                        html += '</div>';
                        
                        // Actions
                        if (lastCell) {
                            const actionButtons = lastCell.querySelectorAll('.btn');
                            if (actionButtons.length > 0) {
                                html += '<div class="mobile-card-item-actions">';
                                actionButtons.forEach(btn => {
                                    const btnText = btn.textContent.trim();
                                    const btnHref = btn.getAttribute('href') || '#';
                                    const btnClass = btn.className.includes('btn-danger') ? 'btn-danger' : 
                                                   btn.className.includes('btn-warning') ? 'btn-warning' :
                                                   btn.className.includes('btn-success') ? 'btn-success' : 'btn-primary';
                                    html += `<a href="${btnHref}" class="btn ${btnClass} btn-sm">${btnText}</a>`;
                                });
                                html += '</div>';
                            }
                        }
                        
                        html += '</div>';
                    });
                    
                    html += '</div>';
                    
                    // Pagination
                    if (totalPages > 1) {
                        html += '<div class="mobile-pagination">';
                        html += `<button class="mobile-pagination-btn" onclick="changeMobilePage('${wrapperId}', ${page - 1})" ${page === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                        html += `<span class="mobile-pagination-info">Página ${page} de ${totalPages}</span>`;
                        html += `<button class="mobile-pagination-btn" onclick="changeMobilePage('${wrapperId}', ${page + 1})" ${page === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                        html += '</div>';
                    }
                    
                    wrapper.innerHTML = html;
                }
                
                renderPage(1);
                
                // Store data for pagination
                wrapper.dataset.currentPage = '1';
                wrapper.dataset.totalPages = totalPages;
                wrapper.dataset.rows = rows.length;
                wrapper.dataset.tableId = table.id || wrapperId;
                
                table.parentElement.insertBefore(wrapper, table);
                table.dataset.converted = 'true';
            });
        }
        
        // Pagination function
        window.changeMobilePage = function(wrapperId, page) {
            const wrapper = document.getElementById(wrapperId);
            if (!wrapper) return;
            
            const totalPages = parseInt(wrapper.dataset.totalPages);
            
            if (page < 1 || page > totalPages) return;
            
            // Find the original table
            const table = Array.from(document.querySelectorAll('table')).find(t => 
                t.nextElementSibling && t.nextElementSibling.id === wrapperId
            );
            
            if (!table || !table.querySelector('tbody')) return;
            
            const thead = table.querySelector('thead');
            const tbody = table.querySelector('tbody');
            const headers = Array.from(thead.querySelectorAll('th')).map(th => {
                const text = th.textContent.trim();
                return text.replace(/[^\w\s]/g, '').trim();
            });
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const itemsPerPage = 10;
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageRows = rows.slice(start, end);
            
            let html = '<div class="mobile-cards-container">';
            
            pageRows.forEach((row) => {
                const cells = Array.from(row.querySelectorAll('td'));
                if (cells.length === 0) return;
                
                html += '<div class="mobile-card-item">';
                html += '<div class="mobile-card-item-header">';
                
                if (cells[0]) {
                    const titleText = cells[0].textContent.trim() || cells[0].innerHTML;
                    html += `<div class="mobile-card-item-title">${titleText}</div>`;
                }
                
                const lastCell = cells[cells.length - 1];
                const badges = lastCell ? lastCell.querySelectorAll('.badge') : [];
                if (badges.length > 0) {
                    badges.forEach(badge => {
                        html += `<span class="badge ${badge.className}">${badge.textContent}</span>`;
                    });
                }
                
                html += '</div>';
                html += '<div class="mobile-card-item-body">';
                
                for (let i = 1; i < cells.length - 1; i++) {
                    if (headers[i] && cells[i]) {
                        const label = headers[i];
                        let value = cells[i].innerHTML;
                        
                        if (value.includes('btn') || cells[i].querySelector('.btn')) continue;
                        
                        value = value.replace(/<[^>]*>/g, '').trim() || '-';
                        
                        html += `<div class="mobile-card-item-field">`;
                        html += `<div class="mobile-card-item-label">${label}</div>`;
                        html += `<div class="mobile-card-item-value">${value}</div>`;
                        html += `</div>`;
                    }
                }
                
                html += '</div>';
                
                if (lastCell) {
                    const actionButtons = lastCell.querySelectorAll('.btn');
                    if (actionButtons.length > 0) {
                        html += '<div class="mobile-card-item-actions">';
                        actionButtons.forEach(btn => {
                            const btnText = btn.textContent.trim();
                            const btnHref = btn.getAttribute('href') || '#';
                            const btnClass = btn.className.includes('btn-danger') ? 'btn-danger' : 
                                           btn.className.includes('btn-warning') ? 'btn-warning' :
                                           btn.className.includes('btn-success') ? 'btn-success' : 'btn-primary';
                            html += `<a href="${btnHref}" class="btn ${btnClass} btn-sm">${btnText}</a>`;
                        });
                        html += '</div>';
                    }
                }
                
                html += '</div>';
            });
            
            html += '</div>';
            
            if (totalPages > 1) {
                html += '<div class="mobile-pagination">';
                html += `<button class="mobile-pagination-btn" onclick="changeMobilePage('${wrapperId}', ${page - 1})" ${page === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                html += `<span class="mobile-pagination-info">Página ${page} de ${totalPages}</span>`;
                html += `<button class="mobile-pagination-btn" onclick="changeMobilePage('${wrapperId}', ${page + 1})" ${page === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                html += '</div>';
            }
            
            wrapper.innerHTML = html;
            wrapper.dataset.currentPage = page;
            
            // Scroll to top
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
        
        // Run on load and resize
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(convertTablesToCards, 100);
        });
        
        window.addEventListener('resize', function() {
            setTimeout(convertTablesToCards, 100);
        });
        
        // Re-convert after AJAX updates
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    setTimeout(convertTablesToCards, 100);
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
    @stack('scripts')
</body>
</html>
