@extends('layouts.app')

@section('content')
<div class="login-wrapper">
    <div class="login-container">
        <div class="login-content">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/pn-logo-image.jpg') }}" 
                         alt="Logo" class="mb-3" style="max-width:240px; height:auto;">
                </div>

                <h2 class="mb-3 text-center fw-bold" style ="color: white;">Welcome To <br><span style="color: #ff9933;">PN</span><span style="color: #22bbea;">-Food Management</span></h2>
                <p class="text-center text-white-50 mb-4">Please login to your account</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label text-white">Email Address</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-primary"></i>
                            </span>
                            <input type="text"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email') }}"
                                   required autocomplete="email" autofocus
                                   placeholder="Enter your email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-white">Password</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock text-primary"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" 
                                   required autocomplete="current-password" 
                                   placeholder="Enter your password">
                            <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember" name="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember" class="form-check-label text-white">
                                Remember Me
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">Sign In</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .login-wrapper {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        overflow: hidden;
    }
    .login-container {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100vh;
        background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(0,0,0,0.9)), 
                    url('{{ asset('images/pn-bg-image.jpg') }}') no-repeat;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .login-content {
        width: 100%;
        max-width: 396px;
        margin: 0 auto;
        animation: slideIn 0.6s ease-out;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .card {
        border: none;
        border-radius: 15px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .form-label {
        font-weight: 500;
        font-size: 0.95rem;
    }
    .btn-primary {
        background: #22bbea;
        border: none;
        padding: 12px 24px;
        font-weight: 600;
        letter-spacing: 0.7px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: #ff9933;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 153, 51, 0.3);
    }
    .form-control {
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 12px 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-radius: 10px;
    }
    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
    .form-control:focus {
        border-color: #22bbea;
        box-shadow: 0 0 0 0.25rem rgba(34,187,234,0.15);
    }
    .form-control::placeholder {
        color: #adb5bd;
        font-size: 0.95rem;
    }
    .input-group-text {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-right: none;
        color: white;
        border-radius: 10px 0 0 10px;
    }
    .input-group .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    .input-group .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        box-shadow: none;
    }
    #togglePassword {
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-left: none;
        border-radius: 0 10px 10px 0;
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    #togglePassword:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    .form-check-input {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }
    .form-check-input:checked {
        background-color: #ff9933;
        border-color: #ff9933;
    }
    .form-check-input:focus {
        border-color: #ff9933;
        box-shadow: 0 0 0 0.2rem rgba(185, 173, 7, 0.25);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const loginForm = document.querySelector('#loginForm');
    const loginBtn = document.querySelector('#loginBtn');

    // Password toggle functionality
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);

        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // Form submission debugging
    loginForm.addEventListener('submit', function(e) {
        console.log('Form submission started');
        console.log('Email:', document.querySelector('#email').value);
        console.log('Password:', document.querySelector('#password').value ? '***provided***' : 'empty');
        
        // Show loading state
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing In...';
        loginBtn.disabled = true;
        
        // Let the form submit normally
        console.log('Form submitting to:', this.action);
        return true;
    });

    // Debug any redirects or page changes
    window.addEventListener('beforeunload', function(e) {
        console.log('Page is about to unload - redirect happening');
    });

    // Debug any JavaScript errors
    window.addEventListener('error', function(e) {
        console.error('JavaScript error:', e.error);
    });

    // Debug form validation
    loginForm.addEventListener('invalid', function(e) {
        console.log('Form validation failed:', e.target.name);
    }, true);
});
</script>
@endsection



