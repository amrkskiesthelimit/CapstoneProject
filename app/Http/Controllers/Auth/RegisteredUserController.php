<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Log;
use App\Models\Department;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{

    public function showLogs(Request $request)
{
    $date = $request->input('date');
    $firstName = $request->input('first_name');

    $query = Log::query();

    if ($date) {
        $query->whereDate('login_time', $date)
              ->orWhereDate('logout_time', $date);
    }

    if ($firstName) {
        $query->whereHas('user', function ($query) use ($firstName) {
            $query->where('first_name', 'like', '%' . $firstName . '%');
        });
    }

    $logs = $query->orderBy('created_at', 'desc')->get();

    return view('logs.index', compact('logs'));
}

    /**
     * Display the registration view.
     */
    public function create(): View
    {

        $departments = Department::all();
        $user = new User();
        return view('users.create', compact('departments', 'user'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:user,admin',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'department' => ['nullable', 'string', 'max:255'],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'civil_status' => ['nullable', 'string', 'in:single,married,separated,widowed'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'blood_type' => ['nullable', 'string', 'max:255'],
            'sss_id_no' => ['nullable', 'string', 'max:255'],
            'pag_ibig_id_no' => ['nullable', 'string', 'max:255'],
            'philhealth_no' => ['nullable', 'string', 'max:255'],
            'tin_no' => ['nullable', 'string', 'max:255'],
            'mdc_id' => ['nullable', 'string', 'max:255'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('profile_pictures', 'public')
            : null;

        $user = User::create([
            'username' => $request->username,
            'surname' => $request->surname,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'department_id' => $request->input('department')
            ? Department::where('name', $request->input('department'))->first()->id
            : null,
            'profile_picture' => $imagePath,
            'civil_status' => $request->civil_status,
            'height' => $request->height,
            'weight' => $request->weight,
            'blood_type' => $request->blood_type,
            'sss_id_no' => $request->sss_id_no,
            'pag_ibig_id_no' => $request->pag_ibig_id_no,
            'philhealth_no' => $request->philhealth_no,
            'tin_no' => $request->tin_no,
            'mdc_id' => $request->mdc_id,
            'place_of_birth' => $request->place_of_birth,
        ]);

        event(new Registered($user));



        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function index(Request $request)
{
    $departments = Department::all();
    $search = $request->input('search');

    $usersQuery = User::query();

    if ($search) {
        $usersQuery->where('surname', 'like', '%' . $search . '%'); // Search by surname
    }

    // Instead of using get(), use paginate() to enable pagination
    $users = $usersQuery->paginate(1); // You can adjust the number of users per page (e.g., 10)

    $header = 'Users'; // Set the header title

    // Check if no users were found
    if ($users->isEmpty()) {
        return redirect()->route('users.index')->withErrors('No users found for the given search criteria.');
    }

    return view('users.index', compact('users', 'header', 'departments'));
}




public function show(User $user)
    {

        return view('users.show', compact('user'));
    }

public function edit(User $user)
{

    $departments = Department::all();
    return view('users.edit', compact('user', 'departments'));
}

public function update(Request $request, User $user)
{
    // Validate form data, including profile_picture field.
    $request->validate([
        'surname' => ['required', 'string', 'max:255'],
        'first_name' => ['required', 'string', 'max:255'],
        'middle_name' => ['nullable', 'string', 'max:255'],
        'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'] . $user->id,
        'role' => 'required|string|in:user,admin',
        'gender' => 'nullable|in:male,female,other',
        'date_of_birth' => 'nullable|date',
        'department' => ['nullable', 'string', 'max:255'],
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed.
        'civil_status' => ['nullable', 'string', 'in:single,married,separated,widowed'],
        'height' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        'weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        'blood_type' => ['nullable', 'string', 'max:255'],
        'sss_id_no' => ['nullable', 'string', 'max:255'],
        'pag_ibig_id_no' => ['nullable', 'string', 'max:255'],
        'philhealth_no' => ['nullable', 'string', 'max:255'],
        'tin_no' => ['nullable', 'string', 'max:255'],
        'mdc_id' => ['nullable', 'string', 'max:255'],
        'place_of_birth' => ['nullable', 'string', 'max:255'],
        'residential_house_no' => 'nullable|string|max:255',
        'residential_street' => 'nullable|string|max:255',
        'residential_subdivision' => 'nullable|string|max:255',
        'residential_barangay' => 'nullable|string|max:255',
        'residential_city' => 'nullable|string|max:255',
        'residential_province' => 'nullable|string|max:255',
        'residential_zip_code' => 'nullable|string|max:10',
        'permanent_house_no' => 'nullable|string|max:255',
        'permanent_street' => 'nullable|string|max:255',
        'permanent_subdivision' => 'nullable|string|max:255',
        'permanent_barangay' => 'nullable|string|max:255',
        'permanent_city' => 'nullable|string|max:255',
        'permanent_province' => 'nullable|string|max:255',
        'permanent_zip_code' => 'nullable|string|max:10',
        'telephone_number' => 'nullable|string|max:20',
        'mobile_number' => 'nullable|string|max:20',
        'messenger_account' => 'nullable|string|max:255',
    ]);

    // Handle profile picture upload.
    if ($request->hasFile('profile_picture')) {
        // Delete the old profile picture if it exists.
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store the new profile picture.
        $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->profile_picture = $imagePath; // Update the user's profile_picture field.
    }

    if ($request->has('department')) {
        $department = Department::where('name', $request->input('department'))->first();
        $user->department()->associate($department);
    }

    // Update other user information.
    $user->update(array_merge(
        $request->except('profile_picture', 'department'),
        ['department_id' => Department::where('name', $request->input('department'))->first()->id]
    ));

    return redirect()->route('users.index')->with('success', 'User updated successfully.');
}



public function destroy($id)
{

    $user = User::findOrFail($id);

    // Delete associated log records (this will trigger cascading delete)
    $user->logs()->delete();
    $user->delete();
    return redirect()->route('users.index')->with('error', 'User deleted successfully.');
}


}
