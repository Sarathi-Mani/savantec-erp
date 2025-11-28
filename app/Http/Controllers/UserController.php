<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Employee;
use App\Models\ExperienceCertificate;
use App\Models\GenerateOfferLetter;
use App\Models\JoiningLetter;
use App\Models\LoginDetail;
use App\Models\NOC;
use App\Models\User;
use App\Models\UserCompany;
use Auth;
use File;
use App\Models\Utility;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserToDo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Session;
use Spatie\Permission\Models\Role;



class UserController extends Controller
{

    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type == 'super admin')
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->where('is_active',1)->get();
            }
            else
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->where('is_active',1)->get();
            }

            return view('user.index')->with('users', $users);
        }
        else
        {
            return redirect()->back();
        }

    }

   public function create()
{
    $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();
    $user  = \Auth::user();
    $roles = Role::where('created_by', '=', $user->creatorId())->where('name','!=','client')->get()->pluck('name', 'id');
    if(\Auth::user()->can('create user'))
    {
        return view('user.create', compact('roles', 'customFields'));
    }
    else
    {
        return redirect()->back();
    }
}

  public function store(Request $request)
{
    if(\Auth::user()->can('create user'))
    {
        $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', '=', \Auth::user()->creatorId())->first();

        if(\Auth::user()->type == 'super admin')
        {
            $validator = \Validator::make(
                $request->all(), [
                    'first_name' => 'required|max:120',
                    'last_name' => 'required|max:120',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:6',
                    'mobile' => 'nullable|string|max:20',
                ]
            );
            
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            
            // Handle profile picture upload
            $avatar = null;
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $settings = Utility::getStorageSetting();
                
                if($settings['storage_setting'] == 'local') {
                    $dir = 'app/public/uploads/avatar/';
                } else {
                    $dir = 'uploads/avatar';
                }
                
                $path = Utility::upload_file($request, 'profile_picture', $fileName, $dir, []);
                if($path['flag'] == 1) {
                    if($settings['storage_setting'] == 'local') {
                        $avatar = str_replace('app/public/', '', $path['url']);
                    } else {
                        $avatar = $path['url'];
                    }
                }
            }

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name = $request->first_name . ' ' . $request->last_name; // Combine names
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->avatar = $avatar;
            $user->password = Hash::make($request->password);
            $user->type = 'company';
            $user->default_pipeline = 1;
            $user->plan = 1;
            $user->lang = !empty($default_language) ? $default_language->value : 'en';
            $user->created_by = \Auth::user()->creatorId();
            $user->plan = Plan::first()->id;
            $user->email_verified_at = date('Y-m-d H:i:s');
            
            // Set company_id based on your logic
            // If you have multiple companies, you might get this from the request
            // $user->company_id = $request->company_id;

            $user->save();
            
            // Rest of your existing code...
            $role_r = Role::findByName('company');
            $user->assignRole($role_r);
            $user->userDefaultDataRegister($user->id);
            $user->userWarehouseRegister($user->id);

            // Rest of your default data creation...
        }
        else
        {
            $validator = \Validator::make(
                $request->all(), [
                    'first_name' => 'required|max:120',
                    'last_name' => 'required|max:120',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:6',
                    'role' => 'required',
                    'mobile' => 'nullable|string|max:20',
                ]
            );
            
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Handle profile picture upload for non-super admin
            $avatar = null;
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $settings = Utility::getStorageSetting();
                
                if($settings['storage_setting'] == 'local') {
                    $dir = 'app/public/uploads/avatar/';
                } else {
                    $dir = 'uploads/avatar';
                }
                
                $path = Utility::upload_file($request, 'profile_picture', $fileName, $dir, []);
                if($path['flag'] == 1) {
                    if($settings['storage_setting'] == 'local') {
                        $avatar = str_replace('app/public/', '', $path['url']);
                    } else {
                        $avatar = $path['url'];
                    }
                }
            }

            $objUser = \Auth::user()->creatorId();
            $objUser = User::find($objUser);
            $total_user = $objUser->countUsers();
            $plan = Plan::find($objUser->plan);
            
            if($total_user < $plan->max_users || $plan->max_users == -1)
            {
                $role_r = Role::findById($request->role);
                $psw = $request->password;
                
                $userData = [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'avatar' => $avatar,
                    'password' => Hash::make($request->password),
                    'type' => $role_r->name,
                    'lang' => !empty($default_language) ? $default_language->value : 'en',
                    'created_by' => \Auth::user()->creatorId(),
                    'email_verified_at' => date('Y-m-d H:i:s'),
                ];

                $user = User::create($userData);
                $user->assignRole($role_r);
                
                if($request['type'] != 'client') {
                    \App\Models\Utility::employeeDetails($user->id, \Auth::user()->creatorId());
                }
                
                // Save custom fields
                CustomField::saveData($user, $request->customField);
            }
            else
            {
                return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
            }
        }

        // Send Email
        $setings = Utility::settings();
        if($setings['new_user'] == 1)
        {
            $user->password = $psw;
            $user->type = $role_r->name;
            
            $userArr = [
                'email' => $user->email,
                'password' => $user->password,
            ];
            $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $userArr);

            return redirect()->route('user.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }
        
        return redirect()->route('user.index')->with('success', __('User successfully created.'));
    }
    else
    {
        return redirect()->back();
    }
}
    public function show()
    {
        return redirect()->route('user.index');
    }

    public function destroy($id)
{
    if(\Auth::user()->can('delete user'))
    {
        $user = User::find($id);
        
        if($user)
        {
            // Check if user is trying to delete themselves
            if($user->id == \Auth::user()->id)
            {
                return redirect()->route('users.index')->with('error', __('You cannot delete yourself.'));
            }

            // Update delete_status to 0 (soft delete)
            $user->delete_status = 0;
            $user->is_active = 0;
            $user->save();

            return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
        }
        else
        {
            return redirect()->route('users.index')->with('error', __('User not found.'));
        }
    }
    else
    {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

   public function edit($id)
{
    $user  = \Auth::user();
    $roles = Role::where('created_by', '=', $user->creatorId())->where('name','!=','client')->get()->pluck('name', 'id');
    if(\Auth::user()->can('edit user'))
    {
        $user              = User::findOrFail($id);
        $user->customField = CustomField::getData($user, 'user');
        $customFields      = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();

        return view('user.edit', compact('user', 'roles', 'customFields'));
    }
    else
    {
        return redirect()->back();
    }
}


 public function update(Request $request, $id)
{
    if(\Auth::user()->can('edit user'))
    {
        $user = User::findOrFail($id);
        
        if(\Auth::user()->type == 'super admin')
        {
            $validator = \Validator::make(
                $request->all(), [
                    'first_name' => 'required|max:120',
                    'last_name' => 'required|max:120',
                    'email' => 'required|email|unique:users,email,' . $id,
                    'mobile' => 'nullable|string|max:20',
                ]
            );
            
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Handle profile picture update
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $settings = Utility::getStorageSetting();
                
                if($settings['storage_setting'] == 'local') {
                    $dir = 'app/public/uploads/avatar/';
                } else {
                    $dir = 'uploads/avatar';
                }
                
                // Delete old avatar if exists
                if ($user->avatar) {
                    $oldAvatarPath = $user->avatar;
                    if (File::exists($oldAvatarPath)) {
                        File::delete($oldAvatarPath);
                    }
                }
                
                $path = Utility::upload_file($request, 'profile_picture', $fileName, $dir, []);
                if($path['flag'] == 1) {
                    if($settings['storage_setting'] == 'local') {
                        $user->avatar = str_replace('app/public/', '', $path['url']);
                    } else {
                        $user->avatar = $path['url'];
                    }
                }
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            
            $user->save();
            CustomField::saveData($user, $request->customField);

            return redirect()->route('user.index')->with('success', 'User successfully updated.');
        }
        else
        {
            // Similar updates for non-super admin...
            $this->validate(
                $request, [
                    'first_name' => 'required|max:120',
                    'last_name' => 'required|max:120',
                    'email' => 'required|email|unique:users,email,' . $id,
                    'role' => 'required',
                    'mobile' => 'nullable|string|max:20',
                ]
            );

            // Handle profile picture update
            if ($request->hasFile('profile_picture')) {
                // Similar file upload logic as above...
            }

            $role = Role::findById($request->role);
            
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->type = $role->name;
            
            $user->save();
            Utility::employeeDetailsUpdate($user->id, \Auth::user()->creatorId());
            CustomField::saveData($user, $request->customField);

            $roles[] = $request->role;
            $user->roles()->sync($roles);

            return redirect()->route('user.index')->with('success', 'User successfully updated.');
        }
    }
    else
    {
        return redirect()->back();
    }
}

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = User::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'name' => 'required|max:120',
                        'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                    ]
        );
        if($request->hasFile('profile'))
        {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $settings = Utility::getStorageSetting();
            if($settings['storage_setting']=='local')
            {
                $dir        = 'app/public/uploads/avatar/';
            }
            else{
                $dir        = 'uploads/avatar';
            }

            $image_path = $dir . $userDetail['avatar'];

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }


            $url = '';
            $path = Utility::upload_file($request,'profile',$fileNameToStore,$dir,[]);
            if($path['flag'] == 1)
            {
                $url = $path['url'];
                // For local storage, save the relative path from public storage
                if($settings['storage_setting']=='local')
                {
                    // Remove 'app/public/' prefix if present, as Storage::url() expects path relative to public disk
                    $avatarPath = str_replace('app/public/', '', $path['url']);
                    $user['avatar'] = $avatarPath;
                }
                else
                {
                    $user['avatar'] = $path['url'];
                }
            }else{
                return redirect()->route('profile', \Auth::user()->id)->with('error', __($path['msg']));
            }

//            $dir        = storage_path('uploads/avatar/');
//            $image_path = $dir . $userDetail['avatar'];
//
//            if(File::exists($image_path))
//            {
//                File::delete($image_path);
//            }
//
//            if(!file_exists($dir))
//            {
//                mkdir($dir, 0777, true);
//            }
//            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        if(!empty($request->profile) && empty($user['avatar']))
        {
            $user['avatar'] = 'uploads/avatar/' . $fileNameToStore;
        }
        $user['name']  = $request['name'];
        $user['email'] = $request['email'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->route('dashboard')->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function updatePassword(Request $request)
    {

        if(Auth::Check())
        {
            $request->validate(
                [
                    'old_password' => 'required',
                    'password' => 'required|min:6',
                    'password_confirmation' => 'required|same:password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if(Hash::check($request_data['old_password'], $current_password))
            {
                $user_id            = Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->password = Hash::make($request_data['password']);;
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            }
            else
            {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        }
        else
        {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }
    // User To do module
  public function todo_store(Request $request)
  {
      $request->validate(
          ['title' => 'required|max:120']
      );

      $post            = $request->all();
      $post['user_id'] = Auth::user()->id;
      $todo            = UserToDo::create($post);


      $todo->updateUrl = route(
          'todo.update', [
                           $todo->id,
                       ]
      );
      $todo->deleteUrl = route(
          'todo.destroy', [
                            $todo->id,
                        ]
      );

      return $todo->toJson();
  }

  public function todo_update($todo_id)
  {
      $user_todo = UserToDo::find($todo_id);
      if($user_todo->is_complete == 0)
      {
          $user_todo->is_complete = 1;
      }
      else
      {
          $user_todo->is_complete = 0;
      }
      $user_todo->save();
      return $user_todo->toJson();
  }

  public function todo_destroy($id)
  {
      $todo = UserToDo::find($id);
      $todo->delete();

      return true;
  }

  // change mode 'dark or light'
  public function changeMode()
  {
      $usr = \Auth::user();
      if($usr->mode == 'light')
      {
          $usr->mode      = 'dark';
          $usr->dark_mode = 1;
      }
      else
      {
          $usr->mode      = 'light';
          $usr->dark_mode = 0;
      }
      $usr->save();

      return redirect()->back();
  }

  public function upgradePlan($user_id)
    {
        $user = User::find($user_id);
        $plans = Plan::get();
        return view('user.plan', compact('user', 'plans'));
    }
    public function activePlan($user_id, $plan_id)
    {

        $user       = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id);
        $plan       = Plan::find($plan_id);
        if($assignPlan['is_success'] == true && !empty($plan))
        {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => isset(\Auth::user()->planPrice()['currency']) ? \Auth::user()->planPrice()['currency'] : '',
                    'txn_id' => '',
                    'payment_status' => 'success',
                    'receipt' => null,
                    'user_id' => $user->id,
                ]
            );

            return redirect()->back()->with('success', 'Plan successfully upgraded.');
        }
        else
        {
            return redirect()->back()->with('error', 'Plan fail to upgrade.');
        }

    }

    public function userPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);

        return view('user.reset', compact('user'));

    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'password' => 'required|confirmed|same:password_confirmation',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
                             'password' => Hash::make($request->password),
                         ])->save();

        return redirect()->route('user.index')->with(
            'success', 'User Password successfully updated.'
        );


    }


    //start for user login details
    public function userLog(Request $request)
    {
        $filteruser = User::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $filteruser->prepend('Select User', '');

        $query = DB::table('login_details')
            ->join('users', 'login_details.user_id', '=', 'users.id')
            ->select(DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
            ->where(['login_details.created_by' => \Auth::user()->id]);

        if(!empty($request->month))
        {
            $query->whereMonth('date', date('m',strtotime($request->month)));
            $query->whereYear('date', date('Y',strtotime($request->month)));
        }else{
            $query->whereMonth('date', date('m'));
            $query->whereYear('date', date('Y'));
        }

        if(!empty($request->users))
        {
            $query->where('user_id', '=', $request->users);
        }
        $userdetails = $query->get();
        $last_login_details = LoginDetail::where('created_by', \Auth::user()->creatorId())->get();

        return view('user.userlog', compact( 'userdetails','last_login_details','filteruser'));
    }

    public function userLogView($id)
    {
        $users = LoginDetail::find($id);

        return view('user.userlogview', compact('users'));
    }

    public function userLogDestroy($id)
    {
        $users = LoginDetail::where('user_id', $id)->delete();
        return redirect()->back()->with('success', 'User successfully deleted.');
    }

    //end for user login details


}
