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
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        .login-container {
            display: flex;
            min-height: 100vh;
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
        
        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 40px;
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
                flex: 0 0 auto;
                min-height: 200px;
                padding: 40px 30px;
            }
            
            .login-left-image {
                display: none;
            }
            
            .login-right {
                flex: 1;
                padding: 40px 30px;
            }
            
            .login-form-container {
                max-width: 100%;
            }
            
            .login-title {
                font-size: 28px;
                margin-bottom: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .login-left {
                padding: 30px 20px;
                min-height: 150px;
            }
            
            .login-logo-left {
                margin-bottom: 20px;
            }
            
            .login-logo-left img {
                height: 35px;
            }
            
            .login-right {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 24px;
                margin-bottom: 24px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-control-modern {
                padding: 12px 40px 12px 14px;
                font-size: 14px;
            }
            
            .btn-login {
                padding: 12px 20px;
                font-size: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .login-left {
                padding: 24px 16px;
                min-height: 120px;
            }
            
            .login-logo-left {
                margin-bottom: 16px;
            }
            
            .login-logo-left img {
                height: 30px;
            }
            
            .login-right {
                padding: 24px 16px;
            }
            
            .login-title {
                font-size: 20px;
                margin-bottom: 20px;
            }
            
            .form-label {
                font-size: 13px;
            }
            
            .form-control-modern {
                padding: 10px 36px 10px 12px;
                font-size: 14px;
            }
            
            .form-input-icon {
                right: 12px;
                font-size: 14px;
            }
            
            .form-forgot {
                margin-top: -12px;
                margin-bottom: 24px;
            }
            
            .form-forgot-link {
                font-size: 13px;
            }
            
            .btn-login {
                padding: 12px 16px;
                font-size: 14px;
            }
            
            .alert {
                padding: 12px;
                font-size: 13px;
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 400px) {
            .login-left {
                padding: 20px 12px;
            }
            
            .login-right {
                padding: 20px 12px;
            }
            
            .login-title {
                font-size: 18px;
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
            </div>
            <img src="{{ asset('imagens/loginleft.png') }}" alt="Ilustração" class="login-left-image">
        </div>
        
        <!-- Right Panel -->
        <div class="login-right">
            <div class="login-form-container">
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
