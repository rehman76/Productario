<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\CreateUserResource;
use App\User;
use Validator;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

          if($user)
          {
              $findUser = User::find($user->id);
              $findUser->assignRole('Curator');
              return new CreateUserResource($user);

          }
    }

}
