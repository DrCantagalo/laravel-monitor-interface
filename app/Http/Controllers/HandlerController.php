<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class HandlerController extends Controller
{
    public function handle(Request $request)
    {
        switch ($request->input('user-verb')) {
            case 'change-lang':
                return $this->changeLang($request);
            case 'cookie-permission':
                return $this->cookiePermission($request);
            case 'remember-me':
                return $this->rememberMe($request);
            /*case 'check-hash':
                return $this->checkHash($request);
            case 'create-admin':
                return $this->createAdmin($request);
            case 'sign-in':
                return $this->signin($request);*/
            default:
                return response()->json(['status' => 'invalid-verb'], 400);
        }
    }

    protected function changeLang(Request $request)
    {
        return 'You are inside!';
        /*$lang = $request->input('lang', 'en');
        $cookie_box = $request->input('cookie-box', false);
        if($cookie_box) { Session::put('templang', $lang); }
        else {
            Session::put('lang', $lang);
            if(session('monitor_id', false)){
                $monitor = Monitor::find(session('monitor_id'));
                $monitor->data['lang'] = $lang;
                $monitor->save();
            }
        }
        Session::put('avoid_monitor', 1);
        return response()->json([
            'status' => 'ok',
            'action' => 'change-lang',
            'lang' => $lang
        ]);*/
    }

    protected function cookiePermission(Request $request)
    {
        $request->validate([
            'id-token' => 'required_if:remember-decision,on|max:30',
            'visits' => 'required|integer|min:1'
        ]);
        $frontData = $request->except(['user-verb', '_token', 'remember-decision']);
        if(session('monitor_id', false)) {
            $monitor = Monitor::find(session('monitor_id'));
            foreach ($frontData as $key => $value) { $monitor->data[$key] = $value; }
        }
        $lang_changed = 0;
        if (!empty($frontData['ipapi_languages'])) {
            $lang = explode(',', $frontData['ipapi_languages'])[0];
            if (Str::contains($lang, 'it')) { $lang = 'it'; }
            elseif (Str::contains($lang, 'pt')) { $lang = 'pt'; }
            else { $lang = 'en'; }
            if(isset($monitor)) { $monitor->data['lang'] = $lang; }
            if (session('lang') !== $lang) { 
                Session::put('lang', $lang);
                $lang_changed = 1;
                Session::put('avoid_monitor', 1);
            }
        }
        else { if(isset($monitor)) { $monitor->data['lang'] = session('lang'); } }
        if(isset($monitor)) { $monitor->save(); }
        Session::put('permission', true);
        return response()->json([
            'status' => 'ok',
            'action' => 'cookie-permission',
            'lang_changed' => $lang_changed
        ]);
    }

    protected function rememberMe(Request $request)
    {
        $request->validate(['id-token' => 'required|max:30']);
        $status = 'ok';
        $lang_changed = 0;
        $token = $request->input('id-token');
        Session::put('remember_me', $token);
        if(class_exists('Monitor\Models\Monitor')) {
            Session::put('permission', true);
            $user = Monitor::where('data->id-token', $token)->first();
            if (!empty($user->data['lang'])) {
                $lang = $user->data['lang'];
                if (session('lang') !== $lang) { 
                    Session::put('lang', $lang);
                    $lang_changed = 1;
                    Session::put('avoid_monitor', 1);
                }
            }
        }
        else { $status = 'error'; }

        return response()->json([
            'status' => $status,
            'action' => 'remember-me',
            'lang_changed' => $lang_changed
        ]);
    }

    /*protected function checkHash(Request $request)
    {
        App::setLocale(session('lang'));

        if(User::count()){
            return back()->withErrors(['ERROR' => __('Admin already exist.')])->withInput();
        }

        if(session('email_temp', false) || session('verification_code', false)){
            return back()->withErrors(['ERROR' => __('Wrong data.')])->withInput();
        }

        $request->validate([
            __('secret-code') => 'required|string',
            'email' => 'required|email',
        ]);

        if (!Hash::check($request->input(__('secret-code')), config('app.secret_hash'))) {
            return back()->withErrors([__('secret-code') => __('Invalid secret code.')])->withInput();
        }
        
        $email = $request->input('email');
        $verification_code = Str::random(14);
        $body = __("Welcome, dear user! We are sending the security code below to verify that this email is yours") . ".<br><br>";
        $body .= $verification_code . "<br><br>";
        $body .= __("This code must be entered on the page where you requested registration, in the \"Verification Code\" field.");
        
        //depois adicionar email laravel
        $SesClient = new SesClient([
            'version' => 'latest',
            'region'  => 'us-east-2',
        ]);

        try {
            $SesClient->sendEmail([
                'Source' => __('no-reply') . '@cantagalo.it',
                'Destination' => [
                    'ToAddresses' => [$email]
                ],
                'Message' => [
                    'Subject' => [
                        'Data' => __('Verifying your email') . ' - cantagalo.it',
                        'Charset' => 'UTF-8',
                    ],
                    'Body' => [
                        'Html' => [
                            'Data' => $body,
                            'Charset' => 'UTF-8',
                        ],
                    ],
                ],
            ]);
        } catch (AwsException $e) {
            Log::error('SES send failed: ' . $e->getAwsErrorMessage());
            return back()->withErrors(['email' => __("We couldn't send you the email with the verification code. Please try again later.")]);
        }

        Session::put('email_temp', $email);
        Session::put('verification_code', $verification_code);
        return back();
    }*/

    /*public function createAdmin(Request $request)
    {
        App::setLocale(session('lang'));
        
        if(User::count()){
            return back()->withErrors(['ERROR' => __('Admin already exist.')])->withInput();
        }

        if(!session('email_temp', false) || !session('verification_code', false)){
            return back()->withErrors(['ERROR' => __('Wrong data.')])->withInput();
        }

        $validator = Validator::make($request->all(), [
            __('verification-code') => ['required', 'string', 'size:14'],
            __("created-password") => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            __('password-confirmation') => ['required', 'same:' . __("created-password")],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $sessionCode = session('verification_code', false);
        if ($sessionCode !== $request->input(__('verification-code'))) {
            return back()->withErrors([__('Verification code') => __("Invalid verification code.")]);
        }

        $email = session('email_temp', false);
        if (!$email) {
            return back()->withErrors(['email' => __('Session expired. Request a new code.')]);
        }

        try {
            User::create([
                'name' => 'Postmaster',
                'email' => $email,
                'password' => Hash::make($request->input(__("created-password"))),
                'email_verified_at' => now(),
            ]);

            session()->forget(['email_temp', 'verification_code']);
            return redirect()->route('supercontrols')
                ->with(__('success'), __("Credentials created successfully, you can now log in."));
        } catch (\Exception $e) {
            Log::error('Error in admin creation: ' . $e->getMessage());

            return back()->withErrors([
                __("internal error") => __("We had a problem registering your credentials. Please try again later.")
            ]);
        }
    }*/

    /*public function signin(Request $request){
        App::setLocale(session('lang'));

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        
        $email = $request->input('email');
        $password = $request->input('password');
        if (Auth::attempt(['email' => $email, 'password' => $password], request()->filled('remember'))) {
            return back();
        }
        else{ return back()->withErrors(['email' => __('Invalid Credentials.')])->withInput(); }
    }*/
}
