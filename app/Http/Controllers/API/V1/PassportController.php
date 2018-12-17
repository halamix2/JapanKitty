<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;

/**
* Class responsible for managing user logging and registration
*
* All functions in this class require header "Accept": "application/json"
* and functions that require parameters also need "Content-Type": "application/json" header
*/
class PassportController extends Controller
{
    /**
     * Allows user to log in
     *
     * @param \Illuminate\Http\Request $request JSON request with following fields:
     *     email,
     *     password
     *
     *
     * @return string on success, {'success' => 'oAUTH2 token'} is retured, otherwise {'error' => 'unauthorised'} is returned
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => request('email'), 'password' => $request['password']])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('api')->accessToken;
            if(!$request["mobile"]) {
                return response()->json(['success' => $success], 200);
            } else {
                return response()->json(['success' => "success", 'token' => $success["token"]], 200);
            }
        }
        else {
          if(!$request["mobile"]) {
              return response()->json(['error'=>'Unauthorised'], 401);
          } else {
              return response()->json(['success'=>'Unauthorised'], 401);
          }
        }
    }

    /**
     * Allows user to log out
     *
     * @return string on success, {'success' => 'user data'} is retured, otherwise {'error' => 'unauthorised'} is returned
     */
    public function logout(Request $request)
    {
      if (Auth::check()) {
          Auth::user()->OauthAccessToken()->delete();
          return response()->json(['success' => "success"], 200);
      }
      else
      {
        if(!$request["mobile"]) {
            return response()->json(['error'=>'Unauthorised'], 401);
        } else {
            return response()->json(['success'=>'Unauthorised'], 401);
        }
      }
    }
    /**
     * Allows user registration
     *
     * @param \Illuminate\Http\Request $request JSON request with following fields:
     *     name,
     *     email,
     *     password
     *
     * @return string on success, {'success' => 'oAUTH2 token'} is retured, otherwise {'error' => 'unauthorised'} is returned
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'sex' => 'nullable',
            'surname' => 'nullable',
            'birthday' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('api')->accessToken;
        $success['name'] =  $user->name;

        if($request["mobile"]) {
            return response()->json(['success' => "success", 'token' => $success["token"], 'name' => $success['name']], 200);
        } else {
          return response()->json(['success' => $success], 200);
        }
    }

    /**
     * Shows user account details
     *
     * @return string on success, {'success' => 'user data'} is retured, otherwise {'error' => 'unauthorised'} is returned
     */
    public function getDetails(Request $request)
    {
        $user = Auth::user();
        if($request["mobile"]) {
            return response()->json([
                'success' => "success",
                "id" => $user["id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "sex" => $user["sex"],
                "surname" => $user["surname"],
                "birthday" => $user["birthday"],
                "role" => $user["role"],
                "status" => $user["status"],
                ], 200);
        } else {
            return response()->json(['success' => $user], 200);
        }
    }

    /**
     * Edit user account data
     *
     * @param \Illuminate\Http\Request $request JSON request with following optional fields:
     *     new_name,
     *     new_email,
     *     new_password
     *
     * @return string on success, {'success' => 'success'} is retured, otherwise {'error' => 'unauthorised'} is returned
     */

    public function editAccount(Request $request)
    {
        $input = $request->all();
        $user_id = Auth::User()->id;
        $obj_user = User::find($user_id);
        if(isset($input["new_name"]))
        {
          $obj_user->name = $input['new_name'];
          $obj_user->save();
        }
        if(isset($input["new_email"]))
        {
          $obj_user->email = $input['new_email'];
          $obj_user->save();
        }
        if(isset($input["new_password"]))
        {
          $obj_user->password = bcrypt($input['new_password']);
          $obj_user->save();
        }
        return response()->json(['success'=>"success"], 200);
    }
}
