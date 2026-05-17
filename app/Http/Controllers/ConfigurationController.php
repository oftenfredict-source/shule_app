<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ConfigurationController extends Controller
{
    public function index()
    {
        if (Session::get('user_type') !== 'SuperAdmin') {
            return redirect()->route('login')->with('error', 'Unauthorized access. Please login as Super Admin.');
        }
        return view('SuperAdmin.school_registration');
    }

   public function storeSchool(Request $request)
{
    if (Session::get('user_type') !== 'SuperAdmin') {
        return redirect()->route('login')->with('error', 'Unauthorized access. Please login as Super Admin.');
    }
    //check if username exist??
    $check_username = User::where('name',$request->registration_number)->first();
    //check if Email exist??
    $check_email = User::where('email',$request->email)->first();

    if($check_username){
        return redirect()->back()->with('error','Registration number already exist');
    }
     if($check_email){
        return redirect()->back()->with('error','Email  already exist');
    }
    $validated = $request->validate([
        'school_name' => 'required|string|max:150',
        'registration_number' => 'required|string|max:50|unique:schools,registration_number',
        'exam_center_number' => 'nullable|string|max:50|unique:schools,exam_center_number',
        'school_type' => 'required|in:Primary,Secondary',
        'ownership' => 'required|in:Public,Private',
        'region' => 'required|string|max:100',
        'district' => 'required|string|max:100',
        'ward' => 'nullable|string|max:100',
        'village' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:100|unique:schools,email',
        'phone' => 'nullable|string|max:20|unique:schools,phone',
        'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
        'school_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'status' => 'required|in:Active,Inactive',
        'environment' => 'nullable|in:Demo,Live',
        'two_factor_enabled' => 'nullable|boolean',
        'senderID' => 'nullable|string|max:20',
        'login_status' => 'nullable|boolean',
        'suspension_reason' => 'nullable|string|max:1000',
    ]);

    if (!array_key_exists('environment', $validated) || !$validated['environment']) {
        $validated['environment'] = 'Demo';
    }

    $validated['two_factor_enabled'] = (bool)($request->input('two_factor_enabled', false));
    $password = 'Admin@3345';
    if ($request->hasFile('school_logo')) {
        // Determine upload path - Robust strategy for Main Domain and Subdomains
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/logos';
        
        // If scriptDir/logos doesn't exist or isn't writable, fallback to other common paths
        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/logos' : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path('logos');
            }
        }

        if (!file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        $logo = $request->file('school_logo');
        $filename = time() . '_' . $logo->getClientOriginalName();
        $logo->move($uploadPath, $filename);
        $validated['school_logo'] = 'logos/' . $filename;
    }

    School::create($validated);
    User::create([
        'name' => $request->registration_number,
        'email' => $request->email,
        'password' => Hash::make($password),
        'user_type' => 'Admin'
    ]);

    return redirect()->route('superadmin.schools.index')->with('success', 'School saved successfully.');
}


}


