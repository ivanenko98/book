<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\PasswordReset;
use App\ResetPasswordSMSRequest;
use App\Transformers\Json;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Nexmo\Laravel\Facade\Nexmo;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function generateToken()
    {
        $this->api_token = str_random(60);
        $this->save();
        return $this->api_token;
    }

    public function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );
        if ($request->wantsJson()) {
            if ($response == Password::PASSWORD_RESET) {
                return response()->json(Json::response(null, trans('passwords.reset')));
            } else {
                return response()->json(Json::response($request->input('email'), trans($response), 202));
            }
        }
        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($response)
            : $this->sendResetFailedResponse($request, $response);
    }

    public function userPasswordReset(ResetPasswordRequest $request)
    {
        $passwordReset = PasswordReset::where('token', $request->token)->first();
        $user = User::where('email', $passwordReset->email);
        $user->update([
            'password' => bcrypt($request->get('password'))
        ]);
        return response()->json(['status'=>true,'message'=>'Password was updated successfully','data'=>$user]);
    }

    public function resetPasswordSendSMS(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if ($user) {
            $code = rand(241866, 894388);

            $old_request = ResetPasswordSMSRequest::where('code', $code)->first();

            if ($old_request == null) {

                $new_request = new ResetPasswordSMSRequest();
                $new_request->code = $code;
                $new_request->user_id = $user->id;
                $new_request->save();

                Nexmo::message()->send([
                    'to'   => $request->phone,
                    'from' => 'Book Translate',
                    'text' => $code
                ]);

                $response = $this->arrayResponse('success',null);
                return response($response, 200);

            } else {
                $code = rand(241855, 894377);

                $new_request = new ResetPasswordSMSRequest();
                $new_request->code = $code;
                $new_request->user_id = $user->id;
                $new_request->save();

                Nexmo::message()->send([
                    'to'   => $request->phone,
                    'from' => 'Book Translate',
                    'text' => $code
                ]);

                $response = $this->arrayResponse('success',null);
                return response($response, 200);
            }

        } else {
            $response = $this->arrayResponse('error','user not found');
            return response($response, 200);
        }
    }

    public function resetPasswordFromSMS(Request $request)
    {
        $request_sms = ResetPasswordSMSRequest::where('code', $request->code)->first();

        if ($request_sms == null) {
            $response = $this->arrayResponse('error','code invalid');
            return response($response, 200);
        }

        $user = User::find($request_sms->user_id);

        $user->update([
            'password' => bcrypt($request->password)
        ]);

        $request_sms->delete();

        $response = $this->arrayResponse('success',null);
        return response($response, 200);
    }

    public function checkCode(Request $request)
    {
        $request_sms = ResetPasswordSMSRequest::where('code', $request->code)->first();

        if ($request_sms == null) {
            $response = $this->arrayResponse('error','code invalid');
            return response($response, 200);
        } else {
            $response = $this->arrayResponse('success',null);
            return response($response, 200);
        }
    }
}
