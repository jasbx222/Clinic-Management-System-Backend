<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
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
    public function store(StoreEmployeeRequest $request)
    {
        $validated = $request->validated();

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
    public function update(UpdateEmployeeRequest $request, User $employee)
    {
        if ($employee->role === 'patient') {
            return response()->json(['message' => 'Not an employee'], 400);
        }

        $validated = $request->validated();

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
