<?php

namespace Modules\Account\Http\Controllers;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Contracts\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use function auth;
use function response;

class AuthController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(UserRepository $repository,Request $request)
    {

        $username = $request['username'];
        $password = $request['password'];

        $credentials = ['user_account' => $username,'password' => $password];
        if (! $token = auth()->attempt($credentials))
        {
            return Respond::error('用户验证失败',Response::HTTP_UNAUTHORIZED);
        }

        /*$user = $repository->findWhere(['user_account'=>$username,'password'=>md5($password)])->first();
        $token = auth()->login($user);*/

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return Respond::success(array(),'退出成功');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $data = [
            'token'       => $token,
            'token_type'  => 'bearer',
            'expires_in'  => auth()->factory()->getTTL() * 60
        ];

        return Respond::success($data);
    }
}
