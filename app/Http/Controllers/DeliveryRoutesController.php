<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRoutes;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;

class DeliveryRoutesController extends Controller
{
    public function getAllData()
    {
        try {
            $deliveryRoutes = DeliveryRoutes::orderBy('created_at', 'DESC')->get();
            return response([
                'response' => 200,
                'status' => true,
                'message' => 'Delivery Routes retrieved successfully.',
                'data' => $deliveryRoutes,
            ], 200);
        } catch (Exception $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'Failed to retrieve delivery routes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addData(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'route_name' => 'required|string|max:255',
                'pincode' => 'required|numeric|min:100000|max:9999999999',  // Pincode must be a number with at least 6 digits
                'city_name' => 'required|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'locations' => 'nullable|array',
                'locations.*' => 'string',
                'is_active' => 'nullable|boolean',
            ]);

            // Provide default value for 'is_active' if not provided
            $validatedData['is_active'] = $validatedData['is_active'] ?? true;

            // Create the new delivery route
            $deliveryRoute = DeliveryRoutes::create($validatedData);

            return response([
                'response' => 200,
                'status' => true,
                'message' => 'Delivery Route created successfully.',
                'data' => $deliveryRoute,
            ], 200);
        } catch (QueryException $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'Database error occurred while creating the delivery route.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'An error occurred while creating the delivery route.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getData($id)
    {
        try {
            $deliveryRoute = DeliveryRoutes::find($id);

            if (!$deliveryRoute) {
                return response([
                    'response' => 404,
                    'status' => false,
                    'message' => 'Delivery Route not found.',
                ], 404);
            }

            return response([
                'response' => 200,
                'status' => true,
                'message' => 'Delivery Route retrieved successfully.',
                'data' => $deliveryRoute,
            ], 200);
        } catch (Exception $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'Failed to retrieve the delivery route.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $deliveryRoute = DeliveryRoutes::find($request->id);

            if (!$deliveryRoute) {
                return response([
                    'response' => 404,
                    'status' => false,
                    'message' => 'Delivery Route not found.',
                ], 404);
            }

            $validatedData = $request->validate([
                'route_name' => 'nullable|string|max:255',
                'pincode' => 'nullable|numeric|min:100000|max:9999999999', // Pincode is optional, but must be a number with at least 6 digits
                'city_name' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'locations' => 'nullable|array',
                'locations.*' => 'string',
                'is_active' => 'nullable|boolean',
            ]);

            // Provide default value for 'is_active' if not provided
            $validatedData['is_active'] = $validatedData['is_active'] ?? $deliveryRoute->is_active;

            // Update the delivery route
            $deliveryRoute->update($validatedData);

            return response([
                'response' => 200,
                'status' => true,
                'message' => 'Delivery Route updated successfully.',
                'data' => $deliveryRoute,
            ], 200);
        } catch (QueryException $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'Database error occurred while updating the delivery route.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'An error occurred while updating the delivery route.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeData(Request $request)
    {
        try {
            $deliveryRoute = DeliveryRoutes::find($request->id);

            if (!$deliveryRoute) {
                return response([
                    'response' => 404,
                    'status' => false,
                    'message' => 'Delivery Route not found.',
                ], 404);
            }

            $deliveryRoute->delete();

            return response([
                'response' => 200,
                'status' => true,
                'message' => 'Delivery Route deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            return response([
                'response' => 500,
                'status' => false,
                'message' => 'An error occurred while deleting the delivery route.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
