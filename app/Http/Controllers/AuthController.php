<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function register(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|unique:users', 'password' => 'required|min:8|confirmed']);
        $user = User::create($data);
        Auth::login($user);
        return redirect()->route('home')->with('success', 'Akun berhasil dibuat. Selamat datang!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        $request->session()->regenerate();
        Auth::user()->update(['is_online' => true, 'last_seen_at' => now()]);
        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        $request->user()->update(['is_online' => false, 'last_seen_at' => now()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function googleRedirect(Request $request)
    {
        if (! $this->googleConfigured()) {
            return redirect()->route($request->query('from') === 'register' ? 'register' : 'login')
                ->with('auth_error', 'Login Google belum aktif. Administrator perlu mengisi kredensial Google OAuth.');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);
        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->googleRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state,
        ]);
        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function googleCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->with('auth_error', 'Login Google dibatalkan atau tidak diizinkan.');
        }

        $expectedState = $request->session()->pull('google_oauth_state');
        if (! $this->googleConfigured() || ! $request->filled(['code', 'state']) || ! filled($expectedState) || ! hash_equals((string) $expectedState, (string) $request->state)) {
            return redirect()->route('login')->with('auth_error', 'Permintaan login Google tidak valid. Silakan coba kembali.');
        }

        try {
            $tokenResponse = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'code' => $request->code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->googleRedirectUri(),
                'grant_type' => 'authorization_code',
            ]);
            $token = $tokenResponse->json('access_token');
            if (! $token) {
                return redirect()->route('login')->with('auth_error', 'Google menolak proses login. Periksa callback URL dan kredensial OAuth.');
            }

            $profileResponse = Http::withToken($token)->timeout(15)->get('https://www.googleapis.com/oauth2/v3/userinfo');
            $profile = $profileResponse->json();
            if (! $profileResponse->successful() || empty($profile['email']) || empty($profile['sub'])) {
                return redirect()->route('login')->with('auth_error', 'Profil Google tidak dapat dibaca. Silakan coba kembali.');
            }

            $user = User::where('google_id', $profile['sub'])->orWhere('email', $profile['email'])->first() ?? new User(['password' => Hash::make(Str::random(32))]);
            $user->fill(['email' => $profile['email'], 'name' => $profile['name'] ?? $profile['email'], 'google_id' => $profile['sub'], 'avatar' => $profile['picture'] ?? $user->avatar, 'email_verified_at' => now()])->save();
            Auth::login($user, true);
            $request->session()->regenerate();
            $user->update(['is_online' => true, 'last_seen_at' => now()]);
            return redirect()->route('home')->with('success', 'Berhasil masuk menggunakan Google.');
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('login')->with('auth_error', 'Tidak dapat terhubung ke Google. Silakan coba kembali beberapa saat lagi.');
        }
    }

    private function googleConfigured(): bool
    {
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');
        $redirectUri = $this->googleRedirectUri();

        return filled($clientId)
            && filled($clientSecret)
            && str_ends_with($clientId, '.apps.googleusercontent.com')
            && ! str_contains(strtolower($clientSecret), 'client_secret')
            && filter_var($redirectUri, FILTER_VALIDATE_URL)
            && ! str_contains($redirectUri, 'alamat-website');
    }

    private function googleRedirectUri(): string
    {
        return config('services.google.redirect_uri') ?: route('google.callback');
    }
}
