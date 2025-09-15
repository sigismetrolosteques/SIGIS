<!-- resources/views/auth/first-login.blade.php -->
<form method="POST" action="{{ route('first.login') }}">
    @csrf
    
    <div class="mb-3">
        <label>Nueva Contraseña</label>
        <input type="password" name="password" required class="form-control">
    </div>
    
    <div class="mb-3">
        <label>Confirmar Contraseña</label>
        <input type="password" name="password_confirmation" required class="form-control">
    </div>
    
    @for($i = 1; $i <= 3; $i++)
        <div class="mb-3">
            <label>Pregunta de Seguridad {{ $i }}</label>
            <input type="text" name="security_question_{{ $i }}" required class="form-control">
        </div>
        
        <div class="mb-3">
            <label>Respuesta {{ $i }}</label>
            <input type="text" name="security_answer_{{ $i }}" required class="form-control">
        </div>
    @endfor
    
    <button type="submit" class="btn btn-primary">Guardar y Continuar</button>
</form>