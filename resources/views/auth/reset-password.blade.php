<form method="POST" action="{{ route('password.reset') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">
    <label>New Password: <input type="password" name="password" required></label>
    <label>Confirm Password: <input type="password" name="password_confirmation" required></label>
    <button type="submit">Reset Password</button>
</form>
