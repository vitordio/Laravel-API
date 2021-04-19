<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

use Flugg\Responder\Responder;
use App\Transformers\UserTransformer;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Responder $responder) {
        // Removi o middleware da rota privada para conseguir exibir outras mensagens caso não esteja logado
        // $this->middleware('auth:api', ['except' => ['login', 'register', 'logout']]);

        $this->responder = $responder;
    }

    public function login(UserRequest $request)
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return $this->responder->error('dados_invalidos', 'Usuário ou senha inválidos')->respond();
        }

        return $this->responder->success(
            [
                "token" => $token,
                "user" => (new UserTransformer)->transform(User::where('email', $credentials['email'])->first())
            ]
        )->respond();
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if(!auth()->user()) {
            return $this->responder->error('usuario_nao_autenticado', 'Usuário não autenticado.')->respond();
        }

        return $this->responder->success(
            [
                "user" => (new UserTransformer)->transform(auth()->user())
            ]
        )->respond();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Caso não esteja logado, exibe a mensagem de erro
        if(!auth()->user()) {
            return $this->responder->error('usuario_nao_logado', 'Usuário não autenticado.')->respond();
        }

        auth()->logout();
        return $this->responder->success(['Logout realizado com sucesso'])->respond();
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        if(!auth()->user()) {
            return $this->responder->error('token_invalido', 'Token inválido.')->respond();
        }

        return $this->responder->success([
            'token' => auth()->refresh()
        ])->respond();
    }

}
