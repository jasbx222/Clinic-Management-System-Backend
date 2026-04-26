<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = User::with('permissions')->where('role', '!=', 'patient')->get();

        return UserResource::collection($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,receptionist,doctor,nurse,accountant',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $employee = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permissionName) {
                Permission::findOrCreate($permissionName, 'web');
            }
            $employee->syncPermissions($validated['permissions']);
        }

        return new UserResource($employee->load('permissions'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $employee)
    {
        if ($employee->role === 'patient') {
            return response()->json(['message' => 'Not an employee'], 400);
        }

        return new UserResource($employee->load('permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        if ($employee->role === 'patient') {
            return response()->json(['message' => 'Not an employee'], 400);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$employee->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$employee->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:admin,receptionist,doctor,nurse,accountant',
            'is_active' => 'sometimes|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $employee->update($validated);

        if (isset($validated['permissions'])) {
            foreach ($validated['permissions'] as $permissionName) {
                Permission::findOrCreate($permissionName, 'web');
            }
            $employee->syncPermissions($validated['permissions']);
        }

        return new UserResource($employee->load('permissions'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        if ($employee->role === 'patient') {
            return response()->json(['message' => 'Not an employee'], 400);
        }

        $employee->delete();

        return response()->json(null, 204);
    }
}
