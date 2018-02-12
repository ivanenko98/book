<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\PhoneAuth;
use App\User;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use PhoneAuth;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }



    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $userSocial = Socialite::driver('facebook')->user();

        $findUser = $this->findOrCreateUser($userSocial);

        Auth::login($findUser, true);

        return ['status' => 200];
    }

    public function findOrCreateUser($facebookUser){

        $findUser = User::where('email', $facebookUser->email)->first();

        if ($findUser){
            return $findUser;
        } else {
            return User::create([
                'name' => $facebookUser->name,
                'surname' => null,
                'email' => $facebookUser->email,
                'phone' => null,
                'password' => null
            ]);
        }
    }

    public function login(Request $request)
    {
        $this->validateByPhone($request);

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();
            $user->generateToken(true);

            return [
                'status'=>200,
                'user'=>$user
            ];
        } else {
            return [
                'status'=>false,
                'msg' => $this->sendFailedLoginByPhoneResponse($request)
            ];
        }

    }

    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        return ['status' => 200];
    }
}
