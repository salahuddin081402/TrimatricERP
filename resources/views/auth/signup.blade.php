@extends('auth.layout')

@section('content')
    <div class="d-flex justify-content-between">
        <h4 class="mb-3 text-center">Create Account</h4>
        <span>Back To 
            <a href="{{ route('backend.company.dashboard.public', request()->route()->parameters()) }}">
                Home
            </a>
            <i class="bi bi-arrow-right ms-1"></i>
        </span>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>

        <!-- Password Rule Hint Block -->
        <div class="alert alert-info py-2 small">
            <strong>Password Requirements:</strong><br>
            • At least 8 characters long<br>
            • At least one UPPERCASE letter<br>
            • At least one lowercase letter<br>
            • At least one number<br>
            • At least one special character (anything except letters & numbers, e.g., _, -, @, #, !, %, *)<br>
            • No spaces allowed
        </div>
    @endif

    <form method="POST" novalidate>
        @csrf

        <div class="mb-2">
            <label class="form-label fw-semibold small">Full Name</label>
            <input
                class="form-control @error('name') is-invalid @enderror"
                name="name"
                placeholder="Enter your full name"
                value="{{ old('name') }}"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-2">
            <label class="form-label fw-semibold small">Phone Number</label>
            <input
                class="form-control @error('phone') is-invalid @enderror"
                name="phone"
                placeholder="Enter your phone number"
                value="{{ old('phone') }}"
                required
            >
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-2">
            <label class="form-label fw-semibold small">Email (Optional)</label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email"
                placeholder="Enter your email"
                value="{{ old('email') }}"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Password</label>
            <div class="input-group">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter password"
                    required
                    onkeyup="checkPasswordRules(this.value)"
                >
                <span class="input-group-text">
                    <i class="bi bi-eye-slash cursor-pointer"
                       onclick="togglePassword('password', this)"></i>
                </span>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            <!-- 6 Standard Password Rules -->
            <div class="mt-1 small" id="password-rules">
                <span class="me-2 text-muted" id="rule-length">
                    <i class="bi bi-circle"></i> 8+ chars
                </span>
                <span class="me-2 text-muted" id="rule-upper">
                    <i class="bi bi-circle"></i> Uppercase
                </span>
                <span class="me-2 text-muted" id="rule-lower">
                    <i class="bi bi-circle"></i> Lowercase
                </span>
                <span class="me-2 text-muted" id="rule-number">
                    <i class="bi bi-circle"></i> Number
                </span>
                <span class="me-2 text-muted" id="rule-special">
                    <i class="bi bi-circle"></i> Special
                </span>
                <span class="text-muted" id="rule-nospace">
                    <i class="bi bi-circle"></i> No spaces
                </span>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Confirm Password</label>
            <div class="input-group">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    placeholder="Confirm password"
                    required
                >
                <span class="input-group-text">
                    <i class="bi bi-eye-slash cursor-pointer"
                       onclick="togglePassword('password_confirmation', this)"></i>
                </span>
            </div>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-success w-100">Sign Up</button>
    </form>

    <div class="text-center mt-3">
        Already have an account?
        <a href="{{ route('company.login', request()->route()->parameters()) }}">
            Login
        </a>
    </div>

    @push('scripts')
        <script>
            function checkPasswordRules(password) {
                updateRule('rule-length', password.length >= 8);
                updateRule('rule-upper', /[A-Z]/.test(password));
                updateRule('rule-lower', /[a-z]/.test(password));
                updateRule('rule-number', /[0-9]/.test(password));
                updateRule('rule-special', /[^A-Za-z0-9]/.test(password)); // any special character
                updateRule('rule-nospace', /^\S+$/.test(password));        // no spaces
            }

            function updateRule(id, valid) {
                const el = document.getElementById(id);
                const icon = el.querySelector('i');

                if (valid) {
                    el.classList.remove('text-muted');
                    el.classList.add('text-success');
                    icon.classList.remove('bi-circle');
                    icon.classList.add('bi-check-circle-fill');
                } else {
                    el.classList.add('text-muted');
                    el.classList.remove('text-success');
                    icon.classList.add('bi-circle');
                    icon.classList.remove('bi-check-circle-fill');
                }
            }
        </script>
    @endpush
@endsection
