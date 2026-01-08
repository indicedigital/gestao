<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Índice</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
            height: 100vh;
        }
        
        .login-container {
            display: flex;
            height: 100vh;
        }
        
        /* Left Panel - Illustration */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
        }
        
        .login-left-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
        }
        
        .login-logo-left {
            margin-bottom: 40px;
        }
        
        .login-logo-left img {
            height: 45px;
            width: auto;
        }
        
        .login-title-left {
            font-size: 42px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .login-subtitle-left {
            font-size: 18px;
            color: #64748b;
            line-height: 1.6;
        }
        
        .login-left-image {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: auto;
            object-fit: contain;
            object-position: bottom;
            z-index: 1;
        }
        
        /* Right Panel - Login Form */
        .login-right {
            flex: 1;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            overflow-y: auto;
        }
        
        .login-form-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-header-right {
            text-align: right;
            margin-bottom: 40px;
        }
        
        .login-signup-link {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .login-signup-link strong {
            color: #5e72e4;
            font-weight: 600;
        }
        
        .login-signup-link:hover strong {
            text-decoration: underline;
        }
        
        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 40px;
        }
        
        .login-social-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
        }
        
        .btn-social {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            color: #1a202c;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-social:hover {
            border-color: #5e72e4;
            background: #f7fafc;
            color: #5e72e4;
        }
        
        .btn-social-google {
            border-color: #4285f4;
            color: #4285f4;
        }
        
        .btn-social-google:hover {
            background: #4285f4;
            color: white;
        }
        
        .btn-social-small {
            flex: 0;
            min-width: 56px;
            padding: 12px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            text-align: center;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .divider span {
            padding: 0 16px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .form-control-modern {
            width: 100%;
            padding: 14px 48px 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            color: #1a202c;
            background: #ffffff;
            transition: all 0.2s;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: #5e72e4;
            box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.1);
        }
        
        .form-control-modern::placeholder {
            color: #94a3b8;
        }
        
        .form-input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .form-input-icon:hover {
            color: #5e72e4;
        }
        
        .form-forgot {
            text-align: right;
            margin-top: -16px;
            margin-bottom: 32px;
        }
        
        .form-forgot-link {
            color: #5e72e4;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-forgot-link:hover {
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            border: none;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-left {
                flex: 0 0 40vh;
                padding: 40px;
            }
            
            .login-right {
                flex: 1;
                padding: 40px;
            }
            
            .login-title-left {
                font-size: 32px;
            }
            
            .login-subtitle-left {
                font-size: 16px;
            }
        }
        
        @media (max-width: 576px) {
            .login-left,
            .login-right {
                padding: 30px 24px;
            }
            
            .login-title {
                font-size: 28px;
            }
            
            .login-social-buttons {
                flex-direction: column;
            }
            
            .btn-social {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel -->
        <div class="login-left">
            <div class="login-left-content">
                <div class="login-logo-left">
                    <img src="{{ asset('imagens/logosemfundo.png') }}" alt="Índice">
                </div>
                <h1 class="login-title-left">Bem-vindo de volta!</h1>
                <p class="login-subtitle-left">
                    Gerencie sua empresa de forma simples e eficiente com nossa plataforma completa. 
                    Acesse todas as funcionalidades e mantenha seu negócio organizado.
                </p>
            </div>
            <img src="{{ asset('imagens/loginleft.png') }}" alt="Ilustração" class="login-left-image">
        </div>
        
        <!-- Right Panel -->
        <div class="login-right">
            <div class="login-form-container">
                <div class="login-header-right">
                    <a href="{{ route('register') }}" class="login-signup-link">
                        Não tem uma conta? <strong>Cadastre-se agora</strong>
                    </a>
                </div>
                
                <h1 class="login-title">Entrar no Índice</h1>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('status') }}
                    </div>
                @endif
                
                <div class="login-social-buttons">
                    <a href="#" class="btn-social btn-social-google">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Entrar com Google
                    </a>
                    <a href="#" class="btn-social btn-social-small" title="Entrar com Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
                
                <div class="divider">
                    <span>Ou</span>
                </div>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="form-input-wrapper">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control-modern @error('email') is-invalid @enderror" 
                                placeholder="seu@email.com"
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                                autocomplete="email"
                            >
                            <i class="fas fa-envelope form-input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Senha</label>
                        <div class="form-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control-modern @error('password') is-invalid @enderror" 
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <i class="fas fa-eye form-input-icon" id="togglePassword" style="cursor: pointer;"></i>
                        </div>
                    </div>
                    
                    <div class="form-forgot">
                        <a href="{{ route('password.request') }}" class="form-forgot-link">
                            Esqueceu a senha?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
