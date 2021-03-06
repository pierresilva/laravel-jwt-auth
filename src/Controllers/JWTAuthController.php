<?php

namespace pierresilva\JWTAuth\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\User;

class JWTAuthController extends Controller
{
    //

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|between:2,100',
            'email' => 'required|email|unique:users|max:50',
            'password' => 'required|confirmed|string|min:6',
        ]);

        if ($validator->fails()) {
            return \response()->json([
                'message' => __('Validation error'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => __('Successfully registered'),
            'user' => $user
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json([
                'message' => __('Email or password not valid'),
                'errors' => [
                    'authentication' => __('Unauthorized')
                ]
            ], 401);
        }

        $data = $this->createNewToken($token);

        return \response()->json([
            'message' => __('Logged in successfully'),
            'data' => $data->original,
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        if (! \auth()->user()) {
            return response()->json([
                'message' => __('Not authorized'),
            ], 401);
        }

        return response()->json([
            'message' => __('Profile obtained successfully'),
            'data' => [
                'user' => auth()->user(),
                'acl' => $this->getAccessControlData()
            ]
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => __('Successfully logged out')
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $data = $this->createNewToken(auth()->refresh());

        return \response()->json([
            'message' => __('Token refreshed successfully'),
            'data' => $data->original
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get access control data (roles and permissions)
     *
     * @return array
     */
    private function getAccessControlData()
    {
        $acl = [];

        if (class_exists('\pierresilva\AccessControl\AccessControl')) {
            $acl['roles'] = \auth()->user()->getRoles();
            $acl['permissions'] = \auth()->user()->getPermissions();
        }

        return $acl;
    }
}
