<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Capilar Elite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-1: #DED5CE;
            --color-2: #CDAF95;
            --color-3: #1C6C73;
            --color-4: #4298A7;
            --color-5: #C8BAAF;
        }
        .bg-primary { background-color: var(--color-3); }
        .bg-secondary { background-color: var(--color-4); }
        .text-primary { color: var(--color-3); }
        .text-secondary { color: var(--color-4); }
        .border-custom { border-color: var(--color-2); }
        .hover-bg-secondary:hover { background-color: var(--color-4); }
    </style>
</head>
<body class="bg-[#DED5CE] flex items-center justify-center min-h-screen p-4">

  <div class="bg-white shadow-xl rounded-3xl p-8 sm:p-10 w-full max-w-md flex flex-col items-center">

    <!-- Logo -->
<img src="{{ asset('images/logos.png') }}" alt="Clínica Capilar Elite"
     class="w-40 sm:w-48 md:w-56 h-auto mb-6 object-contain rounded-full shadow-lg">
    <!-- Título -->
    <h1 class="text-3xl sm:text-4xl font-bold text-center text-primary mb-8">Iniciar Sesión</h1>

    <!-- Session Status -->
    @if (session('status'))
        <div class="bg-[#CDAF95] text-white p-3 rounded mb-4 text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6 w-full">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="block text-gray-700 font-medium mb-1">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-3 rounded-xl border border-custom focus:ring-2 focus:ring-secondary focus:outline-none transition">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-gray-700 font-medium mb-1">Contraseña</label>
            <input id="password" type="password" name="password" required
                class="w-full px-4 py-3 rounded-xl border border-custom focus:ring-2 focus:ring-secondary focus:outline-none transition">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me y Forgot -->
        <div class="flex items-center justify-between">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-secondary shadow-sm focus:ring-secondary">
                <span class="ml-2 text-sm text-gray-600">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-secondary hover:text-primary text-sm">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover-bg-secondary transition">
            Iniciar Sesión
        </button>
    </form>
</div>


</body>
</html>
