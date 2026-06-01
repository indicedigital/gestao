<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @php
        $companyFaviconUrl = null;
        if (auth()->check()) {
            $companyForFavicon = $currentCompany ?? null;
            if (! $companyForFavicon) {
                $companyId = session('current_company_id');
                $companyForFavicon = $companyId
                    ? \App\Models\Company::find($companyId)
                    : auth()->user()?->currentCompany();
            }
            $companyFaviconUrl = $companyForFavicon?->logoPublicUrl();
        }
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            var t = localStorage.getItem('app-theme');
            if (!t && window.matchMedia('(prefers-color-scheme: dark)').matches) t = 'dark';
            if (t === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <title>@yield('title', 'Dashboard') - Índice</title>
    <link rel="icon" type="image/png" href="{{ $companyFaviconUrl ?: asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ $companyFaviconUrl ?: asset('favicon.ico') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    @if(request()->routeIs('company.projects.*', 'company.tasks.*', 'company.dailies.*', 'portal.kanban', 'portal.tasks.show'))
    <link rel="stylesheet" href="{{ asset('css/work-module.css') }}">
    @endif
    @if(request()->routeIs('portal.*'))
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @endif
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-width-collapsed: 80px;
            --header-height: 70px;
            --dark-color: var(--text-primary);
            --light-color: var(--bg-surface-2);
            --secondary-color: #8392ab;
            --success-color: var(--color-success);
            --danger-color: var(--color-danger);
            --warning-color: var(--color-warning);
            --info-color: var(--color-info);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Sidebar - Design Moderno e Limpo */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--sidebar-bg);
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
        }
        
        .sidebar-logo span {
            transition: opacity 0.3s, width 0.3s;
            white-space: nowrap;
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
            color: var(--sidebar-section);
            margin-bottom: 8px;
        }
        
        .sidebar-menu-item {
            padding: 10px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border-radius: 8px;
            margin: 2px 12px;
            font-weight: 500;
            font-size: 14px;
            position: relative;
        }
        
        .sidebar-menu-item span {
            transition: opacity 0.3s, width 0.3s;
            white-space: nowrap;
            color: inherit;
        }
        
        .sidebar-menu-item i {
            width: 20px;
            font-size: 18px;
            color: var(--sidebar-icon);
            transition: color 0.2s;
        }
        
        .sidebar-menu-item:hover {
            background-color: var(--sidebar-hover-bg);
            color: var(--text-primary);
        }
        
        .sidebar-menu-item:hover i {
            color: var(--primary-color);
        }
        
        .sidebar-menu-item.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            box-shadow: var(--sidebar-active-shadow);
        }
        
        .sidebar-menu-item.active i {
            color: var(--sidebar-active-icon);
        }
        
        /* Sidebar Collapsed State */
        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }
        
        .sidebar.collapsed .sidebar-logo span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar.collapsed .menu-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
        
        .sidebar.collapsed .sidebar-menu-item {
            justify-content: center;
            padding: 10px;
            margin: 2px 8px;
        }
        
        .sidebar.collapsed .sidebar-menu-item span {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .sidebar.collapsed .sidebar-header {
            padding: 24px 10px;
            justify-content: center;
        }
        
        .sidebar.collapsed .sidebar-logo {
            justify-content: center;
        }
        
        /* Botão de minimizar */
        .sidebar-toggle-btn {
            position: absolute;
            top: 50%;
            right: -15px;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--bg-surface);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
            color: var(--sidebar-icon);
        }
        
        .sidebar-toggle-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(94, 114, 228, 0.3);
        }
        
        .sidebar.collapsed .sidebar-toggle-btn {
            right: -15px;
        }
        
        .sidebar-toggle-btn i {
            transition: transform 0.3s;
        }
        
        .sidebar.collapsed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }
        
        /* Tooltip para itens do menu quando minimizado */
        .sidebar.collapsed .sidebar-menu-item {
            position: relative;
        }
        
        .sidebar.collapsed .sidebar-menu-item:hover::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 12px;
            padding: 8px 12px;
            background: var(--tooltip-bg);
            color: var(--text-primary);
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1002;
            box-shadow: var(--shadow-md);
            pointer-events: none;
        }
        
        .sidebar.collapsed .sidebar-menu-item:hover::before {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 6px;
            border: 6px solid transparent;
            border-right-color: var(--tooltip-bg);
            z-index: 1003;
            pointer-events: none;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            background: var(--bg-body);
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed);
        }
        
        /* Header - Design Moderno */
        .header {
            height: var(--header-height);
            background: var(--header-bg);
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
            color: var(--text-primary);
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
            background: var(--bg-input);
            transition: all 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--bg-surface);
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
            background: var(--bg-input);
            color: var(--text-primary);
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
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .notifications-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-surface);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notifications-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 16px;
            color: var(--text-primary);
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
            background: var(--bg-surface);
        }
        
        .notification-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-subtle);
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 12px;
        }
        
        .notification-item:hover {
            background: var(--bg-hover);
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
        
        .notification-icon.danger { background: var(--notif-danger-bg); color: var(--color-danger); }
        .notification-icon.warning { background: var(--notif-warning-bg); color: var(--color-warning); }
        .notification-icon.info { background: var(--notif-info-bg); color: var(--color-info); }
        .notification-icon.success { background: var(--notif-success-bg); color: var(--color-success); }
        
        .notification-content {
            flex: 1;
            min-width: 0;
        }
        
        .notification-title {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 4px;
            line-height: 1.4;
        }
        
        .notification-message {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
            line-height: 1.4;
        }
        
        .notification-time {
            font-size: 11px;
            color: var(--text-faint);
        }
        
        .notifications-empty {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-faint);
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
            background: var(--bg-input);
        }
        
        .user-profile-dropdown {
            min-width: 200px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .user-profile-dropdown .dropdown-item {
            padding: 12px 20px;
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-profile-dropdown .dropdown-item:hover {
            background: var(--bg-input);
            color: var(--primary-color);
        }
        
        .user-profile-dropdown .dropdown-item.logout {
            color: var(--danger-color);
        }
        
        .user-profile-dropdown .dropdown-item.logout:hover {
            background: var(--notif-danger-bg);
            color: var(--danger-color);
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--accent-gradient);
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
            color: var(--text-primary);
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
            color: var(--text-primary);
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
            background: var(--bg-surface);
            transition: all 0.2s;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background: var(--bg-surface);
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
            background: var(--accent-gradient);
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
        
        /* Overlay para mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
            backdrop-filter: blur(2px);
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Botão menu mobile */
        .menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-primary);
            font-size: 18px;
        }
        
        .menu-toggle:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Responsive */
        /* Bottom Navigation for Mobile */
        .bottom-nav-mobile {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--header-bg);
            border-top: 1px solid var(--border-color);
            padding: 8px 0;
            z-index: 1000;
            display: none;
            justify-content: space-around;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }
        
        .bottom-nav-mobile-item {
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
        
        .bottom-nav-mobile-item i {
            font-size: 18px;
            color: var(--sidebar-icon);
        }
        
        .bottom-nav-mobile-item.active {
            color: var(--primary-color);
            background: color-mix(in srgb, var(--primary-color) 12%, transparent);
        }
        
        .bottom-nav-mobile-item.active i {
            color: var(--primary-color);
        }
        
        .bottom-nav-mobile-item span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            text-align: center;
        }
        
        /* Table to Cards for Mobile */
        .table-mobile-wrapper {
            display: none;
        }
        
        .mobile-card-item {
            background: var(--bg-surface);
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
            color: var(--text-primary);
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
            color: var(--text-primary);
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
            background: var(--bg-surface);
            border-radius: 8px;
            color: var(--text-primary);
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
                padding-bottom: 80px;
            }
            
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                width: var(--sidebar-width);
            }
            
            .sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggle-btn {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header-center {
                display: none;
            }
            
            .menu-toggle {
                display: flex;
            }
            
            .header {
                padding: 0 16px;
            }
            
            .header-left {
                gap: 12px;
            }
            
            .page-title-header {
                font-size: 18px;
            }
            
            .user-profile {
                padding: 4px 8px;
            }
            
            .user-info {
                display: none;
            }
            
            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .content-wrapper {
                padding: 20px 16px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .card-body {
                padding: 16px;
            }
            
            .card-header {
                padding: 16px;
            }
            
            /* Show bottom nav on mobile */
            .bottom-nav-mobile {
                display: flex;
            }
            
            /* Tables remain visible on desktop layout */
        }

        /* Impressão / exportar PDF: esconder navegação fixa do rodapé (mobile) */
        @media print {
            .bottom-nav-mobile {
                display: none !important;
                visibility: hidden !important;
            }
            body {
                padding-bottom: 0 !important;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 0 12px;
                height: 60px;
            }
            
            .header-height {
                --header-height: 60px;
            }
            
            .page-title-header {
                font-size: 16px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 200px;
            }
            
            .content-wrapper {
                padding: 16px 12px;
            }
            
            .page-title {
                font-size: 20px;
            }
            
            .page-subtitle {
                font-size: 13px;
            }
            
            .sidebar {
                width: 260px;
            }
            
            .sidebar-header {
                padding: 16px;
            }
            
            .sidebar-logo {
                font-size: 18px;
            }
            
            .sidebar-logo img {
                height: 28px;
            }
            
            .sidebar-menu-item {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            .user-profile-dropdown {
                min-width: 180px;
            }
            
            .card {
                margin-bottom: 16px;
            }
            
            .card-body {
                padding: 16px 12px;
            }
            
            .card-header {
                padding: 12px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                padding: 0 10px;
            }
            
            .page-title-header {
                font-size: 14px;
                max-width: 150px;
            }
            
            .content-wrapper {
                padding: 12px 10px;
            }
            
            .page-title {
                font-size: 18px;
            }
            
            .sidebar {
                width: 100%;
                max-width: 280px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 13px;
            }
        }
        
        /* Tabelas responsivas */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
        }
        
        @media (max-width: 992px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 16px;
            }
            
            .page-header .btn {
                width: 100%;
            }
            
            .kpi-card {
                margin-bottom: 16px;
            }
        }
        
        @media (max-width: 768px) {
            .table {
                font-size: 13px;
            }
            
            .table th,
            .table td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            
            .table th:first-child,
            .table td:first-child {
                position: sticky;
                left: 0;
                background: var(--bg-surface);
                z-index: 1;
            }
            
            .table .btn {
                padding: 4px 8px;
                font-size: 12px;
            }
            
            .table .btn-group {
                flex-direction: column;
                gap: 4px;
            }
            
            .table .btn-group .btn {
                width: 100%;
            }
            
            /* Cards KPI responsivos */
            .kpi-card {
                padding: 16px;
            }
            
            .kpi-card h3 {
                font-size: 24px;
            }
            
            .kpi-card h6 {
                font-size: 11px;
            }
            
            .icon-circle {
                width: 50px !important;
                height: 50px !important;
                font-size: 20px !important;
            }
        }
        
        @media (max-width: 576px) {
            .table {
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 6px 4px;
            }
            
            .table th {
                font-size: 11px;
            }
            
            .table .btn {
                padding: 3px 6px;
                font-size: 11px;
            }
            
            .kpi-card {
                padding: 12px;
            }
            
            .kpi-card h3 {
                font-size: 20px;
            }
        }
        
        /* Formulários responsivos */
        @media (max-width: 992px) {
            .row {
                margin-left: -8px;
                margin-right: -8px;
            }
            
            .row > * {
                padding-left: 8px;
                padding-right: 8px;
            }
        }
        
        @media (max-width: 768px) {
            .form-label {
                font-size: 13px;
            }
            
            .form-control {
                font-size: 14px;
                padding: 10px 12px;
            }
            
            .form-select {
                font-size: 14px;
                padding: 10px 12px;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .form-row .col,
            .form-row [class*="col-"] {
                width: 100%;
                margin-bottom: 16px;
            }
        }
        
        /* Badges e labels responsivos */
        @media (max-width: 576px) {
            .badge {
                font-size: 10px;
                padding: 4px 8px;
            }
            
            .btn-sm {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
        
        /* Paginação responsiva */
        @media (max-width: 576px) {
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 12px;
            }
        }
        
        /* Modais responsivos */
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 10px;
            }
            
            .modal-content {
                border-radius: 12px;
            }
            
            .modal-header {
                padding: 16px;
            }
            
            .modal-body {
                padding: 16px;
            }
            
            .modal-footer {
                padding: 12px 16px;
                flex-direction: column;
                gap: 8px;
            }
            
            .modal-footer .btn {
                width: 100%;
                margin: 0;
            }
        }
    </style>

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/theme-overrides.css') }}">
</head>
<body class="@if(request()->routeIs('portal.*')) portal-page @endif @if(request()->routeIs('company.tutorial', 'portal.tutorial')) tutorial-layout-page @endif">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Minimizar menu">
            <i class="fas fa-chevron-left"></i>
        </button>
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
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-title="Dashboard">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="menu-section">
                    <div class="menu-section-title">Administração</div>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-title="Usuários">
                        <i class="fas fa-users"></i>
                        <span>Usuários</span>
                    </a>
                    <a href="{{ route('admin.companies.index') }}" class="sidebar-menu-item {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}" data-title="Empresas">
                        <i class="fas fa-building"></i>
                        <span>Empresas</span>
                    </a>
                </div>
            @else
                @php $isClientPortal = auth()->user()->isClientUser(); @endphp
                @if($isClientPortal)
                <div class="menu-section">
                    <div class="menu-section-title">Portal</div>
                    <a href="{{ route('portal.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}" data-title="Início">
                        <i class="fas fa-home"></i>
                        <span>Início</span>
                    </a>
                    <a href="{{ route('portal.tasks.create') }}" class="sidebar-menu-item {{ request()->routeIs('portal.tasks.create') ? 'active' : '' }}" data-title="Nova solicitação">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nova solicitação</span>
                    </a>
                    <a href="{{ route('portal.tutorial') }}" class="sidebar-menu-item {{ request()->routeIs('portal.tutorial') ? 'active' : '' }}" data-title="Tutorial">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Tutorial</span>
                    </a>
                </div>
                @else
                @php $access = $sidebarAccess ?? null; @endphp
                @if($access && $access['company_dashboard'])
                <div class="menu-section">
                    <div class="menu-section-title">Overview</div>
                    <a href="{{ route('company.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}" data-title="Dashboard">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                @elseif($access && $access['developer_dashboard'])
                <div class="menu-section">
                    <div class="menu-section-title">Overview</div>
                    <a href="{{ route('company.developer-dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('company.developer-dashboard') ? 'active' : '' }}" data-title="Meu Dashboard">
                        <i class="fas fa-code"></i>
                        <span>Meu Dashboard</span>
                    </a>
                </div>
                @endif
                <div class="menu-section">
                    <div class="menu-section-title">Gestão</div>
                    @if($access && ($access['modules']['clients'] ?? false))
                    <a href="{{ route('company.clients.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.clients.*') ? 'active' : '' }}" data-title="Clientes">
                        <i class="fas fa-users"></i>
                        <span>Clientes</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['projects'] ?? false))
                    <a href="{{ route('company.projects.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.projects.*') ? 'active' : '' }}" data-title="Projetos">
                        <i class="fas fa-project-diagram"></i>
                        <span>Projetos</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['tasks'] ?? false))
                    <a href="{{ route('company.tasks.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.tasks.*') ? 'active' : '' }}" data-title="Tasks">
                        <i class="fas fa-tasks"></i>
                        <span>Tasks</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['dailies'] ?? false))
                    <a href="{{ route('company.dailies.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.dailies.index') ? 'active' : '' }}" data-title="Daily">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Daily</span>
                    </a>
                    @endif
                    @if($access && ($access['productivity'] ?? false))
                    <a href="{{ route('company.dailies.productivity') }}" class="sidebar-menu-item {{ request()->routeIs('company.dailies.productivity') ? 'active' : '' }}" data-title="Produtividade">
                        <i class="fas fa-chart-line"></i>
                        <span>Produtividade</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['leads'] ?? false))
                    <a href="{{ route('company.leads.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.leads.*') ? 'active' : '' }}" data-title="Leads">
                        <i class="fas fa-bullseye"></i>
                        <span>Leads</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['contracts'] ?? false))
                    <a href="{{ route('company.contracts.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.contracts.*') ? 'active' : '' }}" data-title="Contratos">
                        <i class="fas fa-file-contract"></i>
                        <span>Contratos</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['employees'] ?? false))
                    <a href="{{ route('company.employees.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.employees.*') ? 'active' : '' }}" data-title="Funcionários">
                        <i class="fas fa-user-tie"></i>
                        <span>Funcionários</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['expenses'] ?? false))
                    <a href="{{ route('company.expenses.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.expenses.*') ? 'active' : '' }}" data-title="Despesas">
                        <i class="fas fa-receipt"></i>
                        <span>Despesas</span>
                    </a>
                    @endif
                    @if($access && ($access['modules']['suppliers'] ?? false))
                    <a href="{{ route('company.suppliers.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.suppliers.*') ? 'active' : '' }}" data-title="Fornecedores">
                        <i class="fas fa-truck"></i>
                        <span>Fornecedores</span>
                    </a>
                    @endif
                </div>
                @if($access && (($access['modules']['receivables'] ?? false) || ($access['modules']['payables'] ?? false)))
                <div class="menu-section">
                    <div class="menu-section-title">Financeiro</div>
                    @if($access['modules']['receivables'] ?? false)
                    <a href="{{ route('company.receivables.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.receivables.*') ? 'active' : '' }}" data-title="Contas a Receber">
                        <i class="fas fa-arrow-circle-down"></i>
                        <span>Contas a Receber</span>
                    </a>
                    @endif
                    @if($access['modules']['payables'] ?? false)
                    <a href="{{ route('company.payables.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.payables.*') ? 'active' : '' }}" data-title="Contas a Pagar">
                        <i class="fas fa-arrow-circle-up"></i>
                        <span>Contas a Pagar</span>
                    </a>
                    @endif
                </div>
                @endif
                @if($access && (($access['modules']['accounting_entry'] ?? false) || ($access['modules']['accounting_exit'] ?? false) || ($access['modules']['accounting_report'] ?? false)))
                <div class="menu-section">
                    <div class="menu-section-title">Contabilidade</div>
                    @if($access['modules']['accounting_entry'] ?? false)
                    <a href="{{ route('company.accounting.fiscal-entry-notes.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.accounting.fiscal-entry-notes.*') && !request()->routeIs('company.accounting.fiscal-entry-notes.report') ? 'active' : '' }}" data-title="NF entrada">
                        <i class="fas fa-file-invoice"></i>
                        <span>Notas fiscais de entrada</span>
                    </a>
                    @endif
                    @if($access['modules']['accounting_exit'] ?? false)
                    <a href="{{ route('company.accounting.fiscal-exit-notes.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.accounting.fiscal-exit-notes.*') ? 'active' : '' }}" data-title="NF saída">
                        <i class="fas fa-file-export"></i>
                        <span>Notas fiscais de saída</span>
                    </a>
                    @endif
                    @if($access['modules']['accounting_report'] ?? false)
                    <a href="{{ route('company.accounting.report') }}" class="sidebar-menu-item {{ request()->routeIs('company.accounting.report') || request()->routeIs('company.accounting.fiscal-entry-notes.report') ? 'active' : '' }}" data-title="Relatório fiscal">
                        <i class="fas fa-file-alt"></i>
                        <span>Relatório fiscal</span>
                    </a>
                    @endif
                </div>
                @endif
                @endif
            @endif
            @if(!auth()->user()->isClientUser())
            @php $access = $sidebarAccess ?? ($access ?? null); @endphp
            <div class="menu-section">
                <div class="menu-section-title">Ajuda</div>
                <a href="{{ route('company.tutorial') }}" class="sidebar-menu-item {{ request()->routeIs('company.tutorial') ? 'active' : '' }}" data-title="Tutorial">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Tutorial</span>
                </a>
            </div>
            <div class="menu-section" style="margin-top: auto; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <div class="menu-section-title">Configurações</div>
                @if($access && ($access['manage_profiles'] ?? false))
                <a href="{{ route('company.permission-profiles.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.permission-profiles.*') ? 'active' : '' }}" data-title="Permissões">
                    <i class="fas fa-shield-alt"></i>
                    <span>Permissões</span>
                </a>
                @endif
                @if($access && ($access['modules']['expense_categories'] ?? false))
                <a href="{{ route('company.expense-categories.index') }}" class="sidebar-menu-item {{ request()->routeIs('company.expense-categories.*') ? 'active' : '' }}" data-title="Categorias de Despesas">
                    <i class="fas fa-tags"></i>
                    <span>Categorias de Despesas</span>
                </a>
                @endif
            </div>
            @endif
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title-header">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="header-center">
                <div class="search-box">
                    <input type="text" placeholder="Buscar cliente, projeto, contrato..." id="globalSearch">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="header-right">
                <button type="button" class="theme-toggle-btn" id="themeToggle" aria-label="Alternar tema" title="Alternar tema claro/escuro">
                    <i class="fas fa-moon" id="themeToggleIcon" data-theme-icon></i>
                </button>
                <div class="dropdown">
                    <div class="user-profile" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">
                            @if(Auth::user()->is_super_admin)
                                Super Admin
                            @elseif(auth()->user()->isClientUser())
                                Cliente
                            @else
                                {{ ucfirst(auth()->user()->companyRole() ?? 'Usuário') }}
                            @endif
                        </div>
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
            @include('components.loading-overlay')
            @yield('content')
        </div>
    </div>
    
    <!-- Bottom Navigation Mobile -->
    <nav class="bottom-nav-mobile">
        <a href="{{ route('company.dashboard') }}?mobile=1" class="bottom-nav-mobile-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Principal</span>
        </a>
        <a href="{{ route('company.clients.index') }}?mobile=1" class="bottom-nav-mobile-item {{ request()->routeIs('company.clients.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Clientes</span>
        </a>
        <a href="{{ route('company.contracts.index') }}?mobile=1" class="bottom-nav-mobile-item {{ request()->routeIs('company.contracts.*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i>
            <span>Contratos</span>
        </a>
        <a href="{{ route('company.receivables.index') }}?mobile=1" class="bottom-nav-mobile-item {{ request()->routeIs('company.receivables.*') ? 'active' : '' }}">
            <i class="fas fa-arrow-circle-down"></i>
            <span>Receber</span>
        </a>
        <a href="{{ route('company.payables.index') }}?mobile=1" class="bottom-nav-mobile-item {{ request()->routeIs('company.payables.*') ? 'active' : '' }}">
            <i class="fas fa-arrow-circle-up"></i>
            <span>Pagar</span>
        </a>
    </nav>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app-loading.js') }}"></script>
    <script src="{{ asset('js/theme.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        AppTheme.initThemeToggle('#themeToggle');

        // Detecta mobile e redireciona se necessário
        (function() {
            if (window.innerWidth <= 992 && !window.location.search.includes('desktop=1')) {
                // Salva preferência em cookie
                document.cookie = 'is_mobile=1; path=/; max-age=86400';
                
                // Se estiver no dashboard, tenta carregar versão mobile
                if (window.location.pathname.includes('/company/dashboard')) {
                    const url = new URL(window.location);
                    url.searchParams.set('mobile', '1');
                    // Não redireciona automaticamente para evitar loop
                    // window.location.href = url.toString();
                }
            } else {
                document.cookie = 'is_mobile=0; path=/; max-age=86400';
            }
        })();
        
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        
        // Função para minimizar/expandir sidebar
        function toggleSidebarCollapse() {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
        
        // Carregar estado salvo
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true' && window.innerWidth > 992) {
            sidebar.classList.add('collapsed');
        }
        
        // Toggle collapse
        sidebarToggleBtn?.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebarCollapse();
        });
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }
        
        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        sidebarToggle?.addEventListener('click', toggleSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);
        
        // Fechar sidebar ao clicar em um link no mobile
        if (window.innerWidth <= 992) {
            document.querySelectorAll('.sidebar-menu-item').forEach(item => {
                item.addEventListener('click', function() {
                    setTimeout(closeSidebar, 300);
                });
            });
        }
        
        // Ajustar ao redimensionar a janela
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                closeSidebar();
            } else {
                // No mobile, remover estado collapsed
                sidebar.classList.remove('collapsed');
            }
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

        // Convert tables to cards on mobile - Define function first
        function convertTablesToCards() {
            if (window.innerWidth > 992) {
                // Remove mobile wrappers if resized to desktop
                document.querySelectorAll('.table-mobile-wrapper').forEach(wrapper => {
                    wrapper.remove();
                });
                document.querySelectorAll('table').forEach(table => {
                    table.dataset.converted = 'false';
                });
                return;
            }
            
            // Get all tables, including DataTables
            let tables = Array.from(document.querySelectorAll('table.table, table.table-hover, table.table-modern, table'));
            
            // Debug: log found tables
            if (tables.length > 0) {
                console.log('Found', tables.length, 'tables to convert');
            }
            
            // Filter out DataTables internal tables
            tables = tables.filter(table => {
                // Skip if it's a DataTables internal table (not the main one)
                const wrapper = table.closest('.dataTables_wrapper');
                if (wrapper) {
                    const mainTable = wrapper.querySelector('table.dataTable, table');
                    return table === mainTable;
                }
                return true;
            });
            
            tables.forEach((table, index) => {
                // Skip if already converted
                if (table.dataset.converted === 'true') {
                    console.log('Table', index, 'already converted, skipping');
                    return;
                }
                
                // Check if wrapper already exists (check parent and siblings)
                const tableParent = table.parentElement;
                if (tableParent) {
                    const existingWrapper = Array.from(tableParent.children).find(child => 
                        child.classList.contains('table-mobile-wrapper') && 
                        (child.previousElementSibling === table || child.nextElementSibling === table)
                    );
                    if (existingWrapper) {
                        console.log('Table', index, 'has existing wrapper, skipping');
                        return;
                    }
                }
                
                // Skip if table is completely hidden (but allow DataTables tables)
                if (table.offsetParent === null && !table.closest('.dataTables_wrapper') && table.style.display === 'none') {
                    console.log('Table', index, 'is hidden, skipping');
                    return;
                }
                
                console.log('Converting table', index);
                
                const wrapper = document.createElement('div');
                wrapper.className = 'table-mobile-wrapper';
                const wrapperId = 'mobile-cards-' + Math.random().toString(36).substr(2, 9);
                wrapper.id = wrapperId;
                
                const thead = table.querySelector('thead');
                const tbody = table.querySelector('tbody');
                
                if (!thead || !tbody) {
                    console.log('Table', index, 'missing thead or tbody, skipping');
                    return;
                }
                
                // Get headers - clean text
                const headers = Array.from(thead.querySelectorAll('th')).map(th => {
                    let text = th.textContent.trim();
                    // Remove icons and sort indicators
                    text = text.replace(/[^\w\s]/g, '').trim();
                    // Get text from links if exists
                    const link = th.querySelector('a');
                    if (link) {
                        text = link.textContent.trim().replace(/[^\w\s]/g, '').trim();
                    }
                    return text || 'Campo';
                });
                
                // Get rows - handle DataTables
                let rows = Array.from(tbody.querySelectorAll('tr'));
                
                console.log('Table', index, 'found', rows.length, 'rows');
                
                // Filter out empty rows and DataTables hidden rows
                rows = rows.filter(row => {
                    // Skip if row is hidden by DataTables
                    if (row.style.display === 'none' && table.classList.contains('dataTable')) {
                        return false;
                    }
                    const cells = row.querySelectorAll('td');
                    // Allow rows with at least one cell (more flexible)
                    return cells.length > 0;
                });
                
                console.log('Table', index, 'filtered to', rows.length, 'valid rows');
                
                if (rows.length === 0) {
                    wrapper.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i><p>Nenhum registro encontrado</p></div>';
                    // Insert after table or in table-responsive
                    const tableResponsive = table.closest('.table-responsive');
                    const insertParent = tableResponsive || tableParent;
                    if (insertParent) {
                        // Insert after the table or table-responsive container
                        if (tableResponsive) {
                            insertParent.insertBefore(wrapper, tableResponsive.nextSibling);
                        } else {
                            insertParent.insertBefore(wrapper, table.nextSibling);
                        }
                    }
                    table.dataset.converted = 'true';
                    return;
                }
                
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
                            let titleText = cells[0].textContent.trim();
                            if (!titleText) {
                                titleText = cells[0].innerHTML.replace(/<[^>]*>/g, '').trim();
                            }
                            if (!titleText) titleText = 'Item';
                            html += `<div class="mobile-card-item-title">${titleText}</div>`;
                        }
                        
                        // Status badges from any cell
                        let statusBadge = null;
                        cells.forEach(cell => {
                            const badge = cell.querySelector('.badge');
                            if (badge && !badge.closest('.btn-group')) {
                                statusBadge = badge;
                            }
                        });
                        if (statusBadge) {
                            html += `<span class="badge ${statusBadge.className}">${statusBadge.textContent.trim()}</span>`;
                        }
                        
                        html += '</div>';
                        html += '<div class="mobile-card-item-body">';
                        
                        // Render other columns (skip first and last if it's actions)
                        const lastCell = cells[cells.length - 1];
                        const hasActions = lastCell && lastCell.querySelector('.btn, .btn-group');
                        
                        for (let i = 1; i < (hasActions ? cells.length - 1 : cells.length); i++) {
                            if (headers[i] && cells[i]) {
                                const label = headers[i];
                                let cell = cells[i];
                                
                                // Skip if contains buttons
                                if (cell.querySelector('.btn, .btn-group')) continue;
                                
                                // Get text value
                                let value = cell.textContent.trim();
                                if (!value) {
                                    value = cell.innerHTML.replace(/<[^>]*>/g, '').trim();
                                }
                                
                                // Handle links
                                const link = cell.querySelector('a');
                                if (link && !link.querySelector('.btn')) {
                                    value = link.textContent.trim() || link.getAttribute('href') || '-';
                                }
                                
                                if (!value) value = '-';
                                
                                // Skip if label is empty or same as value
                                if (!label || label.length < 2) continue;
                                
                                html += `<div class="mobile-card-item-field">`;
                                html += `<div class="mobile-card-item-label">${label}</div>`;
                                html += `<div class="mobile-card-item-value">${value}</div>`;
                                html += `</div>`;
                            }
                        }
                        
                        html += '</div>';
                        
                        // Actions
                        if (hasActions) {
                            html += '<div class="mobile-card-item-actions">';
                            const actionButtons = lastCell.querySelectorAll('.btn, .btn-group .btn, a.btn, button.btn');
                            if (actionButtons.length > 0) {
                                actionButtons.forEach(btn => {
                                    let btnText = btn.textContent.trim();
                                    if (!btnText) {
                                        const icon = btn.querySelector('i');
                                        if (icon) {
                                            const title = btn.getAttribute('title') || btn.getAttribute('aria-label') || '';
                                            btnText = title || 'Ação';
                                        } else {
                                            btnText = btn.getAttribute('title') || btn.getAttribute('aria-label') || 'Ação';
                                        }
                                    }
                                    
                                    let btnHref = btn.getAttribute('href');
                                    let isFormButton = false;
                                    let formAction = '';
                                    
                                    if (btn.tagName === 'BUTTON' || btn.tagName === 'button') {
                                        const form = btn.closest('form');
                                        if (form) {
                                            isFormButton = true;
                                            formAction = form.getAttribute('action') || '#';
                                            const method = form.getAttribute('method') || 'GET';
                                            
                                            // Create form HTML
                                            html += `<form action="${formAction}" method="${method}" class="d-inline" style="flex: 1;">`;
                                            // Add CSRF token if exists
                                            const csrfInput = form.querySelector('input[name="_token"]');
                                            if (csrfInput) {
                                                html += `<input type="hidden" name="_token" value="${csrfInput.value}">`;
                                            }
                                            // Add method override if exists
                                            const methodInput = form.querySelector('input[name="_method"]');
                                            if (methodInput) {
                                                html += `<input type="hidden" name="_method" value="${methodInput.value}">`;
                                            }
                                            html += `<button type="submit" class="btn ${btn.className.includes('btn-danger') ? 'btn-danger' : btn.className.includes('btn-warning') ? 'btn-warning' : btn.className.includes('btn-success') ? 'btn-success' : btn.className.includes('btn-info') ? 'btn-info' : 'btn-primary'} btn-sm w-100">${btnText}</button>`;
                                            html += `</form>`;
                                            return; // Skip to next button
                                        }
                                    }
                                    
                                    if (!btnHref) btnHref = '#';
                                    
                                    let btnClass = 'btn-primary';
                                    if (btn.className.includes('btn-danger')) btnClass = 'btn-danger';
                                    else if (btn.className.includes('btn-warning')) btnClass = 'btn-warning';
                                    else if (btn.className.includes('btn-success')) btnClass = 'btn-success';
                                    else if (btn.className.includes('btn-info')) btnClass = 'btn-info';
                                    else if (btn.className.includes('btn-secondary')) btnClass = 'btn-secondary';
                                    
                                    html += `<a href="${btnHref}" class="btn ${btnClass} btn-sm">${btnText || 'Ver'}</a>`;
                                });
                            }
                            html += '</div>';
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
                
                wrapper.dataset.currentPage = '1';
                wrapper.dataset.totalPages = totalPages;
                wrapper.dataset.rows = rows.length;
                wrapper.dataset.tableId = table.id || wrapperId;
                
                // Insert wrapper - try to insert in the right place
                const tableResponsive = table.closest('.table-responsive');
                const cardBody = table.closest('.card-body');
                const insertParent = tableResponsive || cardBody || tableParent;
                
                if (insertParent) {
                    // If table is inside table-responsive, insert after it
                    if (tableResponsive) {
                        insertParent.insertBefore(wrapper, tableResponsive.nextSibling);
                    } else {
                        // Otherwise insert after table
                        insertParent.insertBefore(wrapper, table.nextSibling);
                    }
                } else {
                    // Fallback: insert after table
                    tableParent?.insertBefore(wrapper, table.nextSibling);
                }
                table.dataset.converted = 'true';
                console.log('Table', index, 'converted successfully, wrapper inserted');
            });
        }
        
        // Pagination function
        window.changeMobilePage = function(wrapperId, page) {
            const wrapper = document.getElementById(wrapperId);
            if (!wrapper) return;
            
            const totalPages = parseInt(wrapper.dataset.totalPages);
            
            if (page < 1 || page > totalPages) return;
            
            // Find the original table
            const table = Array.from(document.querySelectorAll('table')).find(t => {
                const nextSibling = t.nextElementSibling;
                return nextSibling && nextSibling.id === wrapperId;
            });
            
            if (!table || !table.querySelector('tbody')) return;
            
            const thead = table.querySelector('thead');
            const tbody = table.querySelector('tbody');
            const headers = Array.from(thead.querySelectorAll('th')).map(th => {
                let text = th.textContent.trim();
                text = text.replace(/[^\w\s]/g, '').trim();
                const link = th.querySelector('a');
                if (link) {
                    text = link.textContent.trim().replace(/[^\w\s]/g, '').trim();
                }
                return text || 'Campo';
            });
            
            let rows = Array.from(tbody.querySelectorAll('tr'));
            rows = rows.filter(row => row.querySelectorAll('td').length > 0);
            
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
                    let titleText = cells[0].textContent.trim();
                    if (!titleText) {
                        titleText = cells[0].innerHTML.replace(/<[^>]*>/g, '').trim();
                    }
                    html += `<div class="mobile-card-item-title">${titleText || 'Item'}</div>`;
                }
                
                let statusBadge = null;
                cells.forEach(cell => {
                    const badge = cell.querySelector('.badge');
                    if (badge && !badge.closest('.btn-group')) {
                        statusBadge = badge;
                    }
                });
                if (statusBadge) {
                    html += `<span class="badge ${statusBadge.className}">${statusBadge.textContent.trim()}</span>`;
                }
                
                html += '</div>';
                html += '<div class="mobile-card-item-body">';
                
                const lastCell = cells[cells.length - 1];
                const hasActions = lastCell && lastCell.querySelector('.btn, .btn-group');
                
                for (let i = 1; i < (hasActions ? cells.length - 1 : cells.length); i++) {
                    if (headers[i] && cells[i]) {
                        const label = headers[i];
                        let cell = cells[i];
                        
                        if (cell.querySelector('.btn, .btn-group')) continue;
                        
                        let value = cell.textContent.trim();
                        if (!value) {
                            value = cell.innerHTML.replace(/<[^>]*>/g, '').trim();
                        }
                        
                        const link = cell.querySelector('a');
                        if (link && !link.querySelector('.btn')) {
                            value = link.textContent.trim() || link.getAttribute('href') || '-';
                        }
                        
                        if (!value) value = '-';
                        if (!label || label.length < 2) continue;
                        
                        html += `<div class="mobile-card-item-field">`;
                        html += `<div class="mobile-card-item-label">${label}</div>`;
                        html += `<div class="mobile-card-item-value">${value}</div>`;
                        html += `</div>`;
                    }
                }
                
                html += '</div>';
                
                if (hasActions) {
                    html += '<div class="mobile-card-item-actions">';
                    const actionButtons = lastCell.querySelectorAll('.btn, .btn-group .btn, a.btn, button.btn');
                    if (actionButtons.length > 0) {
                        actionButtons.forEach(btn => {
                            let btnText = btn.textContent.trim();
                            if (!btnText) {
                                const icon = btn.querySelector('i');
                                if (icon) {
                                    const title = btn.getAttribute('title') || btn.getAttribute('aria-label') || '';
                                    btnText = title || 'Ação';
                                } else {
                                    btnText = btn.getAttribute('title') || btn.getAttribute('aria-label') || 'Ação';
                                }
                            }
                            
                            let btnHref = btn.getAttribute('href');
                            let isFormButton = false;
                            
                            if (btn.tagName === 'BUTTON' || btn.tagName === 'button') {
                                const form = btn.closest('form');
                                if (form) {
                                    isFormButton = true;
                                    const formAction = form.getAttribute('action') || '#';
                                    const method = form.getAttribute('method') || 'GET';
                                    
                                    // Create form HTML
                                    html += `<form action="${formAction}" method="${method}" class="d-inline" style="flex: 1;">`;
                                    // Add CSRF token if exists
                                    const csrfInput = form.querySelector('input[name="_token"]');
                                    if (csrfInput) {
                                        html += `<input type="hidden" name="_token" value="${csrfInput.value}">`;
                                    }
                                    // Add method override if exists
                                    const methodInput = form.querySelector('input[name="_method"]');
                                    if (methodInput) {
                                        html += `<input type="hidden" name="_method" value="${methodInput.value}">`;
                                    }
                                    html += `<button type="submit" class="btn ${btn.className.includes('btn-danger') ? 'btn-danger' : btn.className.includes('btn-warning') ? 'btn-warning' : btn.className.includes('btn-success') ? 'btn-success' : btn.className.includes('btn-info') ? 'btn-info' : 'btn-primary'} btn-sm w-100">${btnText}</button>`;
                                    html += `</form>`;
                                    return; // Skip to next button
                                }
                            }
                            
                            if (!btnHref) btnHref = '#';
                            
                            let btnClass = 'btn-primary';
                            if (btn.className.includes('btn-danger')) btnClass = 'btn-danger';
                            else if (btn.className.includes('btn-warning')) btnClass = 'btn-warning';
                            else if (btn.className.includes('btn-success')) btnClass = 'btn-success';
                            else if (btn.className.includes('btn-info')) btnClass = 'btn-info';
                            
                            html += `<a href="${btnHref}" class="btn ${btnClass} btn-sm">${btnText || 'Ver'}</a>`;
                        });
                    }
                    html += '</div>';
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
            
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
        
        // Re-convert on resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (window.innerWidth <= 992) {
                    // Reset conversion flag
                    document.querySelectorAll('table').forEach(table => {
                        table.dataset.converted = 'false';
                    });
                    convertTablesToCards();
                } else {
                    // Remove mobile wrappers on desktop
                    document.querySelectorAll('.table-mobile-wrapper').forEach(wrapper => {
                        wrapper.remove();
                    });
                }
            }, 300);
        });
        
        // Make function globally available
        window.convertTablesToCards = convertTablesToCards;
        
        // Adiciona event listener para formulários com a classe 'delete-form'
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form.delete-form').forEach(form => {
                form.addEventListener('submit', window.confirmDelete);
            });
            
            // Convert tables to cards on mobile - wait for everything to load
            setTimeout(function() {
                if (window.innerWidth <= 992) {
                    convertTablesToCards();
                }
            }, 500);
        });
        
        // Also run on window load (after all resources loaded)
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (window.innerWidth <= 992) {
                    // Reset conversion flags to allow re-conversion
                    document.querySelectorAll('table').forEach(table => {
                        // Only reset if no wrapper exists
                        const tableParent = table.parentElement;
                        if (tableParent) {
                            const hasWrapper = Array.from(tableParent.children).some(child => 
                                child.classList.contains('table-mobile-wrapper')
                            );
                            if (!hasWrapper) {
                                table.dataset.converted = 'false';
                            }
                        }
                    });
                    convertTablesToCards();
                }
            }, 1000);
        });
        
        // Re-convert after DataTables initialization (if exists)
        if (typeof jQuery !== 'undefined' && jQuery.fn.dataTable) {
            // Listen for DataTables initialization
            jQuery(document).on('init.dt', function() {
                setTimeout(function() {
                    if (window.innerWidth <= 992) {
                        // Reset conversion flags for DataTables tables
                        jQuery('table.dataTable').each(function() {
                            this.dataset.converted = 'false';
                        });
                        convertTablesToCards();
                    }
                }, 800);
            });
            
            // Also try after a delay in case event doesn't fire
            setTimeout(function() {
                if (window.innerWidth <= 992 && jQuery('table.dataTable').length > 0) {
                    jQuery('table.dataTable').each(function() {
                        this.dataset.converted = 'false';
                    });
                    convertTablesToCards();
                }
            }, 2000);
        }
    </script>
    @include('components.ai-assistant-widget')
    @stack('scripts')
</body>
</html>
