<?php

namespace App\Http\Controllers;

use App\Models\AddBranchModel;
use App\Models\User;
use App\Traits\HandleAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Str;


class AuthController extends Controller
{
use HandleAccount;
    public function login(Request $request){
          $Account = User::with('branch')->latest()->paginate(10);
        $branches = AddBranchModel::select('id','branch_name')
        ->where('status', 1)
        ->orderBy('branch_name')->get();
        return Inertia::render('Auth/Login', compact('Account','branches'));


    }
      public function index(Request $request)
    {
        $data = $this->getAccountData($request);
        return inertia('Auth/Register', $data);
    }
    //  public function index(Request $request){
    //     $Account = User::with('branch')->latest()->paginate(10);
    //     $branches = AddBranchModel::select('id','branch_name')
    //     ->where('status', 1)
    //     ->orderBy('branch_name')->get();
    //     return Inertia::render('Auth/Register', compact('Account','branches'));

    // }

    public function store(Request $request){
        //validate
        $fields = $request->validate([
        'acct_name' => 'required|string|max:255',
        'password' => 'required|string',
        'branch_id' => 'required|exists:branch,id',
        'status' => 'required|in:1,2',
        'acct_roles' => 'required|in:1,2,3,4'
        ],[],[
            'acct_name' => 'account name',
            'acct_roles' => 'account role',
            'branch_id' => 'branch',
        ]);
        $User = User::create($fields);
        return back()->with('success', 'Account Created successfully.');

        
    }
    public function update(Request $request, $id){
        $User = User::findOrFail($id);
         $validated  = $request->validate([
        'acct_name' => 'required|string|max:255',
        'branch_id' => 'required|exists:branch,id',
        'status' => 'required|in:1,2',
        'acct_roles' => 'required|in:1,2,3,4'
        ],[],[
            'acct_name' => 'account name',
            'acct_roles' => 'account role',
            'branch_id' => 'branch',
        ]);

        if(Auth::user()->acct_roles == 1){
            $request->validate([
                'password' => 'required|string',
            ]);

            if($request-> filled('password')){
                $validated['password'] = bcrypt($request->password);
            }

        }



        $User->update($validated);
        return redirect()->to(url()->previous() . '?page=' . $request->query('page'))
    ->with('success', 'Account updated successfully.');

    }

    public function Authenticate(Request $request)
{
    $request->validate([
        'acct_name' => 'required|string',
        'branch_id' => 'required|exists:branch,id',
        'password' => 'required|string',
        ],[],[
            'acct_name' => 'account name',
            'branch_id' => 'branch',
    ]);

    //Check if acct is existing
    $user = User::where('acct_name', $request->acct_name)
        ->first();

        if(!$user){
            return back()->withErrors([
                'acct_name' => 'Account name not found',
            ])->onlyInput('acct_name','branch_id');
        }

        //check branch
        if($user->branch_id != $request->branch_id){
            return back()->withErrors([
                'branch_id' => 'Incorrect branch selected',
            ])->onlyInput('acct_name','branch_id');
        }
        
        //Check status
        if($user->status != 1){
            return back()->withErrors([
                'acct_name' => 'Your Account is inactive. Please Contact Administrator',
            ]);
        }

        //check password
        if(!Hash::check($request->password, $user->password)){
             return back()->withErrors([
                'password' => 'Incorrect password',
            ])->onlyInput('acct_name','branch_id');
        }

        
        Auth::login($user, $request->remember);
        $branch = AddBranchModel::find($request->branch_id);
        $branchName = Str::slug($branch->branch_name);


        // Redirect by role
        return match ((int) $user->acct_roles) {
            1 => redirect()->route('home'),
            2 => redirect()->route('admin'),
            3 => redirect()->route('supervisor', ['branch' => $branchName]),
            4 => redirect()->route('cashier',['branch' => $branchName]),
            default => redirect()->route('/'),
        };
    
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function destroy($id)
{
    $account = User::findOrFail($id);
    $account->delete();

    return back()->with('success', 'Account deleted successfully.');
}



}
