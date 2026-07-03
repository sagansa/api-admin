<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicantDetail;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    public function getDetails(Request $request)
    {
        $user = $request->user();
        $details = ApplicantDetail::where('user_id', $user->id)->first();
        $experiences = WorkExperience::where('user_id', $user->id)->orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'details' => $details,
            'experiences' => $experiences,
        ]);
    }

    public function updateDetails(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'nickname' => 'nullable|string|max:255',
            'is_experienced' => 'nullable|boolean',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'nik' => 'nullable|string|max:20',
            'religion' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'children_count' => 'nullable|integer',
            'education_level' => 'nullable|string|max:255',
            'education_major' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'home_location' => 'nullable|string',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:255',
            'driver_license' => 'nullable|string|max:255',
            'ktp_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'selfie_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
        ]);

        $details = ApplicantDetail::where('user_id', $user->id)->first();
        if ($details && in_array($details->status, ['submitted', 'accepted', 'reviewed', 'rejected'])) {
            if ($details->join_date) {
                $allowedKeys = ['bank_account_name', 'bank_account_number', 'bank_name'];
                foreach ($request->keys() as $key) {
                    if (!in_array($key, $allowedKeys) && $request->has($key) && $request->input($key) != $details->$key) {
                        return response()->json(['success' => false, 'message' => 'Profile is locked. Only bank account details can be updated.'], 403);
                    }
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Profile is already submitted and cannot be changed.'], 403);
            }
        }

        $data = $request->except(['ktp_image', 'selfie_image']);

        if ($request->hasFile('ktp_image')) {
            if ($details && $details->ktp_image) {
                Storage::disk('public')->delete($details->ktp_image);
            }
            $data['ktp_image'] = $request->file('ktp_image')->store('recruitment/ktp', 'public');
        }

        if ($request->hasFile('selfie_image')) {
            if ($details && $details->selfie_image) {
                Storage::disk('public')->delete($details->selfie_image);
            }
            $data['selfie_image'] = $request->file('selfie_image')->store('recruitment/selfie', 'public');
        }

        // Handle empty or null children_count
        if (isset($data['children_count']) && ($data['children_count'] === '' || $data['children_count'] === null)) {
            $data['children_count'] = 0;
        }

        $details = ApplicantDetail::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile details updated successfully',
            'details' => $details,
        ]);
    }

    public function submitProfile(Request $request)
    {
        $user = $request->user();
        $details = ApplicantDetail::where('user_id', $user->id)->first();

        if (!$details) {
            return response()->json(['success' => false, 'message' => 'Profile not found. Please save as draft first.'], 404);
        }

        if ($details->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Profile is already submitted.'], 403);
        }

        $details->update(['status' => 'submitted']);

        return response()->json([
            'success' => true,
            'message' => 'Profile submitted successfully',
            'details' => $details,
        ]);
    }

    public function addExperience(Request $request)
    {
        $user = $request->user();
        
        $details = ApplicantDetail::where('user_id', $user->id)->first();
        if ($details && $details->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Profile is already submitted and cannot be changed.'], 403);
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'salary' => 'nullable|numeric',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'is_contactable' => 'nullable|boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $experience = WorkExperience::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'position' => $request->position,
            'salary' => $request->salary,
            'supervisor_name' => $request->supervisor_name,
            'supervisor_phone' => $request->supervisor_phone,
            'is_contactable' => $request->is_contactable ?? false,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work experience added successfully',
            'experience' => $experience,
        ]);
    }

    public function updateExperience(Request $request, $id)
    {
        $user = $request->user();

        $details = ApplicantDetail::where('user_id', $user->id)->first();
        if ($details && $details->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Profile is already submitted and cannot be changed.'], 403);
        }

        $experience = WorkExperience::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'salary' => 'nullable|numeric',
            'supervisor_name' => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:20',
            'is_contactable' => 'nullable|boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $experience->update([
            'company_name' => $request->company_name,
            'position' => $request->position,
            'salary' => $request->salary,
            'supervisor_name' => $request->supervisor_name,
            'supervisor_phone' => $request->supervisor_phone,
            'is_contactable' => $request->is_contactable ?? false,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work experience updated successfully',
            'experience' => $experience,
        ]);
    }

    public function deleteExperience(Request $request, $id)
    {
        $user = $request->user();

        $details = ApplicantDetail::where('user_id', $user->id)->first();
        if ($details && $details->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Profile is already submitted and cannot be changed.'], 403);
        }

        $experience = WorkExperience::where('user_id', $user->id)->findOrFail($id);
        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work experience deleted successfully'
        ]);
    }

    public function deleteImage(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type');

        $details = ApplicantDetail::where('user_id', $user->id)->first();
        if ($details && $details->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Profile is already submitted and cannot be changed.'], 403);
        }

        if ($details && in_array($type, ['ktp_image', 'selfie_image'])) {
            if ($details->$type) {
                Storage::disk('public')->delete($details->$type);
            }
            $details->update([$type => null]);
            return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }
}
