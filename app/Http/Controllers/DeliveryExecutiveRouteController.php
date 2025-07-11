<?php

namespace App\Http\Controllers;

use App\Models\DeliveryExecutive;
use App\Models\DeliveryExecutiveRouteModal;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryExecutiveRouteController extends Controller
{
    public function getAllData()
    {
        try {
            $routes = DeliveryExecutiveRouteModal::with([
                'deliveryExecutive:id,name,phn_no1,is_active,executive_id',
                'deliveryRoute:id,route_name,pincode,city_name,locations,is_active'
            ])
                ->orderBy('created_at', 'DESC')
                ->get();


            $transformedRoutes = $routes->map(function ($route) {
                return [
                    'delivery_executive_route' => [
                        'id' => $route->id,
                        'delivery_executive_id' => $route->delivery_executive_id,
                        'delivery_route_id' => $route->delivery_route_id,
                        'max_customers' => $route->max_customers,
                        'max_orders' => $route->max_orders,
                        'priority' => $route->priority,
                        'is_active' => $route->is_active,
                        'created_at' => $route->created_at,
                        'updated_at' => $route->updated_at,
                    ],
                    'delivery_executive' => [
                        "id" => $route->deliveryExecutive->id,
                        'name' => $route->deliveryExecutive->name ?? null,
                        'phone' => $route->deliveryExecutive->phn_no1 ?? null,
                        'executive_id' => $route->deliveryExecutive->executive_id ?? null,
                        'is_active' => $route->deliveryExecutive->is_active ?? null,
                    ],
                    'delivery_route' => [
                        'route_name' => $route->deliveryRoute->route_name ?? null,
                        'pincode' => $route->deliveryRoute->pincode ?? null,
                        'city_name' => $route->deliveryRoute->city_name ?? null,
                        'locations' => $route->deliveryRoute->locations ?? null,
                        'is_active' => $route->deliveryRoute->is_active ?? null,
                    ],
                ];
            });

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Routes retrieved successfully",
                "data" => $transformedRoutes
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while fetching the routes: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }

    public function getDeliveryExecutivesWithRouteswithId($executiveId)
    {
        try {
            // Fetch the delivery executive with assigned routes
            $executive = DeliveryExecutive::with([
                'routes' => function ($query) {
                    // Fetch only the necessary fields for the frontend
                    $query->with([
                        'deliveryRoute:id,route_name,pincode,city_name,locations,is_active',
                    ]);
                }
            ])
                ->where('id', $executiveId)
                ->first();

            if (!$executive) {
                return response([
                    "response" => 404,
                    "status" => false,
                    "message" => "Executive not found",
                ], 404);
            }

            // Fetch assigned route IDs for the delivery executive
            $assignedRouteIds = $executive->routes->pluck('delivery_route_id')->toArray();

            // Fetch all unassigned routes, ensuring no duplicates
            $unassignedRoutes = DB::table('delivery_routes')
                ->leftJoin('delivery_executive_route', 'delivery_routes.id', '=', 'delivery_executive_route.delivery_route_id')
                ->where(function ($query) use ($executiveId) {
                    // Check if the route is unassigned or assigned to another executive
                    $query->whereNull('delivery_executive_route.delivery_executive_id')
                        ->orWhere('delivery_executive_route.delivery_executive_id', '!=', $executiveId);
                })
                ->whereNotIn('delivery_routes.id', $assignedRouteIds) // Exclude assigned routes
                ->select(
                    'delivery_routes.id',
                    'delivery_routes.route_name',
                    'delivery_routes.pincode',
                    'delivery_routes.city_name',
                    'delivery_routes.locations',
                    'delivery_routes.is_active',
                    DB::raw('MAX(delivery_executive_route.max_customers) as max_customers'),
                    DB::raw('MAX(delivery_executive_route.max_orders) as max_orders'),
                    DB::raw('MAX(delivery_executive_route.priority) as priority')
                )
                ->groupBy('delivery_routes.id', 'delivery_routes.route_name', 'delivery_routes.pincode', 'delivery_routes.city_name', 'delivery_routes.locations', 'delivery_routes.is_active') // Group by all primary fields of delivery_routes
                ->havingRaw('delivery_routes.id IS NOT NULL') // Ensure the route id exists
                ->get();

            // Prepare the response for assigned routes
            $assignedRoutes = $executive->routes->map(function ($route) {
                return [
                    'id' => $route->deliveryRoute->id,
                    'route_name' => $route->deliveryRoute->route_name,
                    'pincode' => $route->deliveryRoute->pincode,
                    'city_name' => $route->deliveryRoute->city_name,
                    'locations' => $route->deliveryRoute->locations,
                    'is_active' => $route->deliveryRoute->is_active,
                    'max_customers' => $route->max_customers,
                    'max_orders' => $route->max_orders,
                    'priority' => $route->priority,
                    'delivery_executive_id' => $route->delivery_executive_id,
                    'delivery_route_id' => $route->deliveryRoute->id
                ];
            });

            // Map unassigned routes with additional fields
            $unassignedRoutesForExecutive = $unassignedRoutes->map(function ($route) use ($executiveId) {
                return [
                    'id' => $route->id,
                    'route_name' => $route->route_name,
                    'pincode' => $route->pincode,
                    'city_name' => $route->city_name,
                    'locations' => $route->locations,
                    'is_active' => $route->is_active,
                    'max_customers' => $route->max_customers,
                    'max_orders' => $route->max_orders,
                    'priority' => $route->priority,
                    'delivery_executive_id' => $executiveId,
                    'delivery_route_id' => $route->id
                ];
            });

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Delivery executive routes fetched successfully",
                "data" => [
                    'assigned_routes' => $assignedRoutes,
                    'unassigned_routes' => $unassignedRoutesForExecutive
                ]
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while fetching the routes: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }


    // Get a specific delivery executive route by ID
    public function getData($id)
    {
        try {
            // Fetch the route by ID with related delivery executive and delivery route
            $route = DeliveryExecutiveRouteModal::with(['deliveryExecutive', 'deliveryRoute'])->findOrFail($id);

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Route retrieved successfully",
                "data" => $route
            ];

            return response($response, 200);
        } catch (ModelNotFoundException $e) {
            $response = [
                "response" => 404,
                "status" => false,
                "message" => "Route not found"
            ];
            return response($response, 404);
        } catch (\Exception $e) {
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while fetching the route: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }

    public function addData(Request $request)
    {
        try {
            // Prepare routes and unassigned_routes from the request
            $routes = $request->has('routes') ? $request->input('routes') : [$request->all()];
            $unassignedRoutes = $request->input('unassigned_routes', []);

            // Validate the input routes
            $validated = $this->validateRoutes($routes);

            // Unassign (remove) routes
            if (!empty($unassignedRoutes)) {
                foreach ($unassignedRoutes as $route) {
                    DeliveryExecutiveRouteModal::where('delivery_executive_id', $route['delivery_executive_id'])
                        ->where('delivery_route_id', $route['delivery_route_id'])
                        ->delete();
                }
            }

            // Retrieve existing routes in bulk for better performance
            $existingRoutes = DeliveryExecutiveRouteModal::whereIn(
                'delivery_executive_id',
                array_column($validated, 'delivery_executive_id')
            )
                ->whereIn(
                    'delivery_route_id',
                    array_column($validated, 'delivery_route_id')
                )
                ->get(['delivery_executive_id', 'delivery_route_id'])
                ->toArray();

            // Filter out routes that already exist
            $newRoutes = array_filter($validated, function ($route) use ($existingRoutes) {
                return !in_array(
                    ['delivery_executive_id' => $route['delivery_executive_id'], 'delivery_route_id' => $route['delivery_route_id']],
                    $existingRoutes
                );
            });

            // Prepare data for insertion
            $routesData = array_map(function ($route) {
                return [
                    'delivery_executive_id' => $route['delivery_executive_id'],
                    'delivery_route_id' => $route['delivery_route_id'],
                    'max_customers' => $route['max_customers'],
                    'max_orders' => $route['max_orders'],
                    'priority' => $route['priority'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $newRoutes);

            // Insert new routes (if any)
            if (!empty($routesData)) {
                DeliveryExecutiveRouteModal::insert($routesData);
            }

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Delivery executive route(s) assigned successfully",
            ];
            return response($response, 200);
        } catch (\Exception $e) {
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while assigning the route(s): " . $e->getMessage(),
            ];
            return response($response, 500);
        }
    }

    public function updateOrAddRoutes(Request $request)
    {
        try {
            // Extract data from request
            $routes = $request->input('routes', []); // Routes to update/add
            $unassignedRoutes = $request->input('unassigned_routes', []); // Routes to remove

            // Validate routes
            $validatedRoutes = $this->validateRoutes($routes);

            // Step 1: Unassign routes (delete existing ones)
            if (!empty($unassignedRoutes)) {
                DeliveryExecutiveRouteModal::whereIn('delivery_executive_id', array_column($unassignedRoutes, 'delivery_executive_id'))
                    ->whereIn('delivery_route_id', array_column($unassignedRoutes, 'delivery_route_id'))
                    ->delete();
            }

            // Step 2: Retrieve existing routes for bulk operations
            $existingRoutes = DeliveryExecutiveRouteModal::whereIn(
                'delivery_executive_id',
                array_column($validatedRoutes, 'delivery_executive_id')
            )
                ->whereIn(
                    'delivery_route_id',
                    array_column($validatedRoutes, 'delivery_route_id')
                )
                ->get(['id', 'delivery_executive_id', 'delivery_route_id'])
                ->keyBy(function ($item) {
                    return $item->delivery_executive_id . '_' . $item->delivery_route_id;
                });

            // Step 3: Separate routes for update and insert
            $newRoutes = [];
            foreach ($validatedRoutes as $route) {
                $key = $route['delivery_executive_id'] . '_' . $route['delivery_route_id'];

                if (isset($existingRoutes[$key])) {
                    // Update existing route
                    DeliveryExecutiveRouteModal::where('id', $existingRoutes[$key]->id)
                        ->update([
                            'max_customers' => $route['max_customers'],
                            'max_orders' => $route['max_orders'],
                            'priority' => $route['priority'],
                            'is_active' => true,
                            'updated_at' => now(),
                        ]);
                } else {
                    // Prepare new route for bulk insertion
                    $newRoutes[] = [
                        'delivery_executive_id' => $route['delivery_executive_id'],
                        'delivery_route_id' => $route['delivery_route_id'],
                        'max_customers' => $route['max_customers'],
                        'max_orders' => $route['max_orders'],
                        'priority' => $route['priority'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Step 4: Bulk insert new routes (if any)
            if (!empty($newRoutes)) {
                DeliveryExecutiveRouteModal::insert($newRoutes);
            }

            return response([
                "response" => 200,
                "status" => true,
                "message" => "Routes updated/added successfully",
            ], 200);
        } catch (\Exception $e) {
            return response([
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while processing the routes: " . $e->getMessage(),
            ], 500);
        }
    }


    public function updateData(Request $request)
    {
        try {
            // Check if it's a single or bulk update
            $routes = $request->has('routes') ? $request->input('routes') : [$request->all()];

            // Validate for single or multiple routes
            $validated = $this->validateRoutes($routes, true);

            // Process each route update
            foreach ($validated as $routeData) {
                $route = DeliveryExecutiveRouteModal::findOrFail($routeData['id']);
                $route->update($routeData);
            }

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Delivery executive route(s) updated successfully"
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while updating the route(s): " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }

    // Delete a delivery executive route (single or bulk)
    public function removeData(Request $request)
    {
        try {
            // Get the single ID from the request
            $id = $request->input('id');

            // Check if ID is provided
            if (is_null($id)) {
                return response([
                    "response" => 400,
                    "status" => false,
                    "message" => "ID is required to delete a route"
                ], 400);
            }

            // Attempt to find and delete the route by ID
            $route = DeliveryExecutiveRouteModal::findOrFail($id);
            $route->delete();

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Route deleted successfully"
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            // Return an error response if the delete operation fails
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while deleting the route: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }


    private function validateRoutes(array $routes, $isUpdate = false)
    {
        $validationRules = [
            'delivery_executive_id' => 'required|exists:delivery_executive,id',
            'delivery_route_id' => 'required|exists:delivery_routes,id',
            'max_customers' => 'nullable|integer',
            'max_orders' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];

        // If it's an update, add validation for the 'id'
        if ($isUpdate) {
            $validationRules['id'] = 'required|exists:delivery_executive_route,id';
        }

        // Preprocess the routes to fix any issues before validation
        $processedRoutes = [];
        foreach ($routes as &$route) {
            // Handle locations as a JSON array (if it's a string, decode it)
            if (isset($route['locations']) && is_string($route['locations'])) {
                $route['locations'] = json_decode($route['locations'], true); // Convert to array
            }

            // Convert `is_active` to boolean if it is passed as "1" or "0"
            if (isset($route['is_active'])) {
                $route['is_active'] = (bool) $route['is_active'];
            }

            // If max_customers, max_orders, or priority are "null" strings, set them to null
            $route['max_customers'] = $route['max_customers'] === "null" ? null : $route['max_customers'];
            $route['max_orders'] = $route['max_orders'] === "null" ? null : $route['max_orders'];
            $route['priority'] = $route['priority'] === "null" ? null : $route['priority'];

            // Add the processed route to the new array
            $processedRoutes[] = $route;
        }

        // Validate each route entry with the new processed data
        $validatedRoutes = [];
        foreach ($processedRoutes as $route) {
            $validatedRoutes[] = Validator::make($route, $validationRules)->validate();
        }

        return $validatedRoutes;
    }
}
