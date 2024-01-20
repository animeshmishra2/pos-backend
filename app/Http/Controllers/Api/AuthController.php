<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User as ModelsUser;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\CountersLogin;
use App\Models\Otp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use App\Models\WalletBalance;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string'
            ]);
            $user = new ModelsUser([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->contact
            ]);
            $user->save();

            $data = array('name' => $user->name);;
            $template = "email_welcome";

            Mail::send($template, $data, function ($message) use ($request) {
                $message->to($request->name, $request->email)
                    ->subject("Welcome to GGB");
                $message->from('no-reply@ggb.in', 'GGB');
            });
            return response()->json([
                "statusCode" => 0,
                'message' => 'Successfully registered!'
            ], 201);
        } catch (Exception $e) {
            return ["statusCode" => 1, "message" => $e->getMessage()];
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'contact' => 'required|string'
            ]);

            $userid = Auth::guard('api')->user()->id;

            ModelsUser::where('id', $userid)->update(['contact_number' => $request->contact]);

            return response()->json([
                "statusCode" => 0,
                'message' => 'Successfully Updated!'
            ], 201);
        } catch (Exception $e) {
            return ["statusCode" => 1, "message" => $e->getMessage()];
        }
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $customErr = "";
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
                'remember_me' => 'boolean'
            ]);
            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials))
                throw new Exception("Invalid Credentials");

            $user = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;

            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);

            $token->save();
            $counterslogin = [];
            $idWarehouse = 0;
            $aLvlStore = 1;
            $SWname = '';
            $address = '';
            $city = '';
            $pin = '';
            $contact = '';
            if ($user->user_type != "A") {
                $counterslogin = CountersLogin::where('idstaff', '=', $user->id)
                    ->where('status', '=', 1)
                    ->get();

                $userAccess = DB::table('staff_access')
                    ->leftJoin('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                    ->select(
                        'staff_access.idstore_warehouse',
                        'staff_access.idstaff_access',
                        'store_warehouse.is_store',
                        'store_warehouse.name AS sw_name',
                        'store_warehouse.address AS sw_address',
                        'store_warehouse.pincode AS sw_pin',
                        'store_warehouse.city AS sw_city',
                        'store_warehouse.state AS sw_state',
                        'store_warehouse.contact AS sw_contact',
                        'staff_access.idstaff'
                    )
                    ->where('staff_access.idstaff', $user->id)
                    ->where('store_warehouse.status', 1)
                    ->where('staff_access.status', 1)
                    ->first();
                if (!isset($userAccess->idstore_warehouse)) {
                    throw new Exception("User Don't have any access.");
                }
                $idWarehouse = $userAccess->idstore_warehouse;
                $aLvlStore = $userAccess->is_store;
                $SWname = $userAccess->sw_name;
                $address = $userAccess->sw_address;
                $city = $userAccess->sw_city;
                  $state = $userAccess->sw_state;
                $pin = $userAccess->sw_pin;
                $contact = $userAccess->sw_contact;
            }
            $det = [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString(),
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->user_type,
                'contact' => $user->contact_number,
                'counter_detail' => $counterslogin,
                'is_store' => $aLvlStore,
                'idwarehouse' => $idWarehouse,
                'sw_name' => $SWname,
                'sw_address' => $address,
                'sw_city' => $city,
                'sw_state' => $state ?? '',
                'sw_pin' => $pin,
                'sw_contact' => $contact,

            ];
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $det], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'statusCode' => 0,
            'message' => 'Successfully logged out'
        ]);
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required',
            ]);
            if (!Hash::check($request->old_password, Auth::guard('api')->user()->password)) {
                return response()->json([
                    "statusCode" => 1,
                    'message' => "Old Password Doesn't match!"
                ], 200);
            }
            ModelsUser::whereId(Auth::guard('api')->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);
            return response()->json([
                "statusCode" => 0,
                'message' => 'Password changed successfully!'
            ], 200);
        } catch (Exception $e) {
            return ["statusCode" => 1, "message" => $e->getMessage()];
        }
    }

    public function loginCustomer(Request $request)
    {
        $customErr = "";
        $req = json_decode($request->getContent());
        try {
            $credentials = ['user_type' => 'C'];
            if (isset($req->contact) && strlen($req->contact) > 0 && isset($req->password) && strlen($req->password) > 0) {
                $credentials['contact'] = $req->contact;
                $credentials['password'] = $req->password;
            } elseif (isset($req->contact)) {
                throw new Exception("Invalid Credentials");
            }

            if (!Auth::attempt($credentials))
                throw new Exception("Invalid Credentials");

            $user = $request->user();
            if ($user->status == 0) {
                throw new Exception("User Blocked");
            }
            $tokenResult = $user->createToken('FKDIWIJdfdsfdsjhkgyW IEW J77872 78*&*&839039J DKSJH!#@^*&(');
            $token = $tokenResult->token;

            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);

            $token->save();

            $data =
                DB::table('users')
                ->leftJoin('customer_address', 'users.id', '=', 'customer_address.idcustomer')
                ->leftJoin('membership_plan', 'users.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                ->leftJoin('wallet_balance', 'users.idmembership_plan', '=', 'wallet_balance.idmembership_plan')
                ->select(
                    'users.id AS idcustomer',
                    'users.name',
                    // 'users.idstore_warehouse',
                    'users.contact',
                    'users.email',
                    'users.idmembership_plan',
                    'users.created_by',
                    'users.status',
                    'wallet_balance.current_amount AS wallet_balance',
                    'customer_address.address',
                    'customer_address.pincode',
                    'customer_address.landmark',
                    'membership_plan.name as membership_type',
                    'membership_plan.instant_discount',
                    'membership_plan.commission'
                )
                ->where('user_type', 'C')
                ->where('users.contact', $user->contact)
                ->first();

            $det = [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString(),
                'name' => $data->name,
                'email' => $data->email,
                'contact' => $data->contact,
                'user-details' => $data
            ];
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $det], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function customerRequestOTP(Request $request)
    {
        $customErr = "";
        $req = json_decode($request->getContent());
        try {
            $notRegistered = false;
            if (!is_numeric($req->contact) || strlen($req->contact) != 10) {
                throw new Exception("Bad reqest");
            }
            $det = ModelsUser::where('contact', $req->contact)
                ->where('user_type', 'C')->first();
            if (!isset($det->id))
                $notRegistered = true;

            if (isset($det->id) && $det->status == 0) {
                throw new Exception("User is blocked");
            }
            $otp = self::generateNumericOTP(6);
            self::sentOTP($req->contact, $otp);
            if ($notRegistered) {
                $user = new ModelsUser([
                    'name' => '',
                    'email' => '',
                    'password' => bcrypt('Random&&^%Pa66ss'),
                    'contact' => $request->contact,
                    'status' => 2, //Verification Pending
                    'user_type' => 'C',
                    'otp' => $otp,
                    'idmembership_plan' => 1,
                    'otp_gen_time' => Carbon::now()
                ]);
                $user->save();
            } else {
                ModelsUser::where('id', $det->id)
                    ->update([
                        'otp' => $otp,
                        'otp_gen_time' => Carbon::now()
                    ]);
            }

            return response()->json(["statusCode" => 0, "message" => "Success", "otp" => base64_encode($otp), "isRegistered" => !$notRegistered], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function customerVerifyOTP(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $refID = -1;
            $referrerType = "";
            $det = ModelsUser::where('contact', $req->contact)
                ->where('user_type', 'C')->first();
            if (!isset($det->id))
                throw new Exception("User Not Registered.");

            $user  = ModelsUser::where([['contact', '=', $req->contact], ['otp', '=', $req->otp], ['user_type', '=', 'C']])->first();
            if (!$user) {
                throw new Exception("Invalid OTP");
            }

            if ($user->status == 0) {
                throw new Exception("User is Blocked");
            }

            if ($user->status == 2) {//Now Verifying
                $mplans = DB::table('membership_plan')
                ->where('status', 1)
                ->get();
                foreach ($mplans as $mplan) {
                    WalletBalance::create([
                        'idcustomer' => $user->id,
                        'idmembership_plan' => $mplan->idmembership_plan,
                        'current_amount' => 0,
                        'total_incurred' => 0,
                        'redeemed' => 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    ]);
                }
                WalletBalance::create([
                    'idcustomer' => $user->id,
                    'idmembership_plan' => 0,
                    'current_amount' => 0,
                    'total_incurred' => 0,
                    'redeemed' => 0,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'status' => 1
                ]);
            }

            if ($user->referrer_id === null) {
                if (isset($req->referrer_id)) {
                    $refBon = DB::table('gen_config')
                                ->where('conf_type', 'ref_amount')
                                ->where('status', 1)
                                ->first();
                    $strId = (int)substr($req->referrer_id, 3);
                    $refFrom = substr($req->referrer_id, 0, 3);
                    switch ($refFrom) {
                        case 'GGB':
                            $referrerType = 'GGB';
                            Helper::addWalletAmount($strId, $user->id, $refBon->conf_val, "Referred a User");
                            break;
                        case 'AGB':
                            $referrerType = 'AGB';
                            break;
                        default:
                            break;
                    }
                    $refuser  = ModelsUser::where([['id', '=', $strId]])->first();
                    if ($refuser) {
                        $refID = $strId;
                    }
                }
            }

            Auth::login($user, true);
            ModelsUser::where('id', '=', $user->id)->update(['otp' => null, 'status' => 1, 'active_firebaseId' => $req->active_firebaseId, 'referrer_id' => $refID, 'referrer_type' => $referrerType]);

            $tokenResult = $user->createToken('FKDIWIJdfdsfdsjhkgyW IEW J77872 78*&*&839039J DKSJH!#@^*&(');
            $token = $tokenResult->token;

            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);

            $token->save();

            $data =
                DB::table('users')
                ->leftJoin('customer_address', 'users.id', '=', 'customer_address.idcustomer')
                ->leftJoin('membership_plan', 'users.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                ->leftJoin('wallet_balance', 'users.idmembership_plan', '=', 'wallet_balance.idmembership_plan')
                ->select(
                    'users.id AS idcustomer',
                    'users.name',
                    'users.contact',
                    'users.email',
                    'users.idmembership_plan',
                    'wallet_balance.current_amount AS wallet_balance',
                    'users.created_by',
                    'users.status',
                    'customer_address.address',
                    'customer_address.pincode',
                    'customer_address.landmark',
                    'membership_plan.name as membership_type',
                    'membership_plan.instant_discount',
                    'membership_plan.commission'
                )
                ->where('user_type', 'C')
                ->where('users.contact', $user->contact)
                ->first();

            $det = [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString(),
                'name' => $data->name,
                'email' => $data->email,
                'contact' => $data->contact,
                'user-details' => $data,
                'my-referral-id' => "GGB" . str_pad($data->idcustomer, 6, '0', STR_PAD_LEFT)
            ];
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $det], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    function generateNumericOTP($length)
    {
        $generator = "1357902468";
        $result = "";
        for ($i = 1; $i <= $length; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        return $result;
    }

    public function updateProfile(Request $request)
    {
        try {
            $req = json_decode($request->getContent());
            $user = auth()->guard('api')->user();
            if (!isset($user->id)) {
                throw new Exception("User not login");
            }
            if ($user->user_type != 'C') {
                throw new Exception("Only for Customers");
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email',
                'idmembership_plan' => 'required'
            ]);
            $toUpdate = [
                'name' => $req->name,
                'email' => $req->email,
                'idmembership_plan' => $req->idmembership_plan
            ];
            if (isset($req->password)) {
                if (strlen($req->password) > 7) {
                    $toUpdate['password'] = bcrypt($req->password);
                } else {
                    throw new Exception("Password length should be minimum 8 characters.");
                }
            }
            $det = ModelsUser::where('id', $user->id)
                ->where('user_type', 'C')
                ->update($toUpdate);

            return response()->json([
                "statusCode" => 0,
                'message' => 'success'
            ], 200);
        } catch (Exception $e) {
            return ["statusCode" => 1, "message" => $e->getMessage()];
        }
    }
    public function completeVerification(Request $request)
    {
        try {
            $req = json_decode($request->getContent());

            $det = ModelsUser::where('contact', $req->contact)
                ->where('user_type', 'C')->first();
            if (!isset($det->id))
                throw new Exception("User Not Registered.");
            if ($det->status == 1)
                throw new Exception("User Already Active.");

            $user  = ModelsUser::where([['contact', '=', $req->contact], ['otp', '=', $req->otp]])->first();
            if (!$user) {
                throw new Exception("Invalid Credentials");
            }
            ModelsUser::where('id', '=', $user->id)->update(['otp' => null, 'status' => 1]); //TODO

            return response()->json([
                "statusCode" => 0,
                'message' => 'Successfully registered!'
            ], 200);
        } catch (Exception $e) {
            return ["statusCode" => 1, "message" => $e->getMessage()];
        }
    }

 
    public static function sentOTP($contact, $otp)
    {
        // $msg = "Dear Member , %otp% Is your OTP for Registration process with DRVGGB.";
        // $msg = Helper::getTemplate(["otp" => $otp], $msg);
        // Helper::smssend($contact, $msg);
          $p=$contact;
            $msg=rawurlencode('Dear GGB User, Your OTP is : '.$otp);
            $response = Http::get('http://sms1.mydnshost.in/api/SmsApi/SendSingleApi?UserID=DRVGGB&Password=rjqb7080RJ&SenderID=DRVGGB&Phno='.$p.'&Msg='.$msg.'&EntityID=1201169693784090732&TemplateID=1207169761921422505');
              return json_decode($response);
    }
}
