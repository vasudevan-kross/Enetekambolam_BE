<?php

namespace App\Http\Controllers;

use App\Models\DeliveryExecutiveOrderModal;
use App\Models\ProductModel;
use Carbon\Carbon;
use App\Models\DeliveryExecutive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryExecutiveOrderController extends Controller
{
    public function getAllData()
    {
        try {
            // Fetching data from the database
            $data = DB::table("delivery_executive_orders")
                ->select(
                    'delivery_executive_orders.*',
                    'orders.id as orderId',
                    'orders.product_detail',
                    'orders.subscription_type',
                    'orders.order_amount',
                    'orders.product_id',
                    'orders.pause_dates', // Include pause_dates for filtering
                    'delivery_executive.name as delivery_boy_name'
                )
                ->join('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
                ->join('orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
                ->where('orders.status', '=', 1)
                ->where('is_admin_reassigned', false)
                ->orderBy('delivery_executive_orders.created_at', 'DESC')
                ->get();

            // Filter data based on pause_dates using Carbon
            $filteredData = $data->filter(function ($item) {
                if (!empty($item->pause_dates)) {
                    // Parse pause_dates using explode and trim
                    $pauseDates = array_map(function ($date) {
                        return Carbon::parse(trim($date));
                    }, explode(',', trim($item->pause_dates, '[]')));

                    // Check if assigned_date exists in pause_dates
                    $assignedDate = Carbon::parse($item->assigned_date);
                    foreach ($pauseDates as $pauseDate) {
                        if ($assignedDate->equalTo($pauseDate)) {
                            return false; // Exclude the item
                        }
                    }
                }

                return true; // Include the item if no match or pause_dates is null
            });

            // Add product title if subscription type exists
            foreach ($filteredData as $item) {
                if ($item->subscription_type) {
                    $productData = ProductModel::where('id', '=', $item->product_id)->first();
                    $item->prodcut_title = $productData->title ?? null;
                }
            }

            // Prepare response
            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Routes retrieved successfully",
                "data" => $filteredData->values() // Reset indices
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            // Handle errors
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while fetching the routes: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }

    private function parseDate($date)
    {
        $formats = ['Y-m-d', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                // Try next format
            }
        }

        // If all formats fail, throw an exception
        throw new \InvalidArgumentException("Unsupported date format: $date");
    }
    public function getAllDataWithDateRange($startDate, $endDate)
    {
        try {
            // Format start and end dates
            if ($startDate && $endDate) {
                $formattedStartDate = $this->parseDate($startDate);
                $formattedEndDate = $this->parseDate($endDate);
            }

            // Build query
            $query = DB::table("delivery_executive_orders")
                ->select(
                    'delivery_executive_orders.*',
                    'orders.id as orderId',
                    'orders.product_detail',
                    'orders.subscription_type',
                    'orders.order_amount',
                    'orders.product_id',
                    'orders.pause_dates', // Include pause_dates for filtering
                    'delivery_executive.name as delivery_boy_name'
                )
                ->join('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
                ->join('orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
                ->where('is_admin_reassigned', false)
                ->where('orders.status', '=', 1)
                ->where('delivery_executive_orders.is_reassign_requested', false);

            // Apply date range filter
            if ($startDate && $endDate) {
                $query->whereBetween('delivery_executive_orders.assigned_date', [$formattedStartDate, $formattedEndDate]);
            }

            // Fetch data
            $data = $query->orderBy('delivery_executive_orders.created_at', 'DESC')->get();

            // Filter data based on pause_dates
            $filteredData = $data->filter(function ($item) {
                if (!empty($item->pause_dates)) {
                    // Parse pause_dates using explode and trim
                    $pauseDates = array_map(function ($date) {
                        return Carbon::parse(trim($date));
                    }, explode(',', trim($item->pause_dates, '[]')));

                    // Check if assigned_date exists in pause_dates
                    $assignedDate = Carbon::parse($item->assigned_date);
                    foreach ($pauseDates as $pauseDate) {
                        if ($assignedDate->equalTo($pauseDate)) {
                            return false; // Exclude the item
                        }
                    }
                }

                return true; // Include the item if no match or pause_dates is null
            });

            // Add product title if subscription type exists
            foreach ($filteredData as $item) {
                if ($item->subscription_type) {
                    $productData = ProductModel::where('id', '=', $item->product_id)->first();
                    $item->prodcut_title = $productData ? $productData->title : null;
                }
            }

            // Prepare response
            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Orders retrieved successfully",
                "data" => $filteredData->values() // Reset indices
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            // Handle errors
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while fetching the Orders: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }

    public function getDeliveryOrdersByDate($selectedDate)
    {
        try {
            // Normalize the date first
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $selectedDate)) {
                $selectedDate = Carbon::createFromFormat('d-m-Y', $selectedDate)->format('Y-m-d');
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
                $selectedDate = Carbon::createFromFormat('Y-m-d', $selectedDate)->format('Y-m-d');
            }

            $selectedDateCarbon = Carbon::parse($selectedDate);

            // Fetch all active orders
            $orders = DB::table('orders')
                ->join('users', 'users.id', '=', 'orders.user_id')
                ->join('user_address', 'orders.address_id', '=', 'user_address.id')
                ->leftJoin('delivery_executive_orders', 'orders.id', '=', 'delivery_executive_orders.order_id')
                ->where('orders.order_status', false)
                ->where('orders.status', '=', 1)
                ->select(
                    'orders.*',
                    'users.name as customerName',
                    'users.phone',
                    'users.email',
                    'user_address.name',
                    'user_address.s_phone',
                    'user_address.flat_no',
                    'user_address.apartment_name',
                    'user_address.area',
                    'user_address.landmark',
                    'user_address.city',
                    'user_address.pincode',
                )
                ->orderBy('orders.created_at', 'desc')
                ->distinct()
                ->get()
                ->filter(function ($order) use ($selectedDateCarbon) {
                    $startDate = Carbon::parse($order->start_date);
                    $created_at = Carbon::parse($order->created_at);
                    $selectedDate = Carbon::parse($selectedDateCarbon);
                    $isBeforeGivenDay = $selectedDateCarbon->isAfter($startDate) || $startDate->isSameDay($selectedDateCarbon);

                    if (is_null($order->subscription_type)) {
                        return is_null($order->start_date)
                            ? $created_at->addDay()->isSameDay($selectedDateCarbon)
                            : $startDate->isSameDay($selectedDateCarbon);
                    }

                    $deliveryCompletedDays = $this->noOfDeliveryCompleted($order->id);
                    $pauseDates = [];
                    if ($order->pause_dates) {
                        $pauseDates = array_map('trim', explode(',', trim($order->pause_dates, '[]')));
                    }
                    $isDateInPausePeriod = function ($selectedDate) use ($pauseDates) {
                        foreach ($pauseDates as $pauseDate) {
                            if (Carbon::parse($selectedDate)->isSameDay(Carbon::parse($pauseDate))) {
                                return true;
                            }
                        }
                        return false;
                    };

                    $productData = ProductModel::find($order->product_id);
                    $order->title = $productData->title ?? '';

                    $newEndDate = $this->calculateEndDate($startDate, $order, $pauseDates);
                    $order->end_date = $newEndDate;    
                    $daysDifference = $startDate->diffInDays($selectedDateCarbon);

                    switch ($order->subscription_type) {
                        case '1': // One-Time
                            return (
                                // $deliveryCompletedDays === 0 &&
                                $daysDifference === 0 &&
                                $isBeforeGivenDay &&
                                $selectedDate->lte($newEndDate)
                            );

                        case '2': // Weekly
                            if (
                                // $deliveryCompletedDays < 7 &&
                                $this->isDeliveryDayForWeekly($selectedDate, $order->selected_days_for_weekly) &&
                                !$isDateInPausePeriod($selectedDate) &&
                                $isBeforeGivenDay &&
                                $selectedDate->lte($newEndDate)
                            ) {
                                return true;
                            }
                            return false;

                        case '3': // Monthly
                            return (
                                // $deliveryCompletedDays < 30 &&
                                $daysDifference < (30 + count($pauseDates)) &&
                                !$isDateInPausePeriod($selectedDate) &&
                                $isBeforeGivenDay &&
                                $selectedDate->lte($newEndDate)
                            );

                        case '4': // Alternate Days
                            return (
                                // $deliveryCompletedDays < 15 &&
                                $daysDifference < (30 + count($pauseDates)) &&
                                !$isDateInPausePeriod($selectedDate) &&
                                $isBeforeGivenDay &&
                                $daysDifference % 2 === 0 &&
                                $selectedDate->lte($newEndDate)
                            );

                        default:
                            return false;
                    }
                });
            $orders = $orders->values(); // just in case keys were reset

            $totalOrders = count($orders);
            $assignedOrders = 0;
            $deliveredOrders = 0;

            $assignedData = [];
            $unassignedData = [];
            $deliveredData = [];
            $undeliveredData = [];

            foreach ($orders as $order) {
                // Get delivery executive and assigned date if exists
                $deliveryExecutiveOrder = DB::table('delivery_executive_orders')
                    ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
                    ->where('delivery_executive_orders.order_id', $order->id)
                    ->whereDate('delivery_executive_orders.assigned_date', $selectedDateCarbon->toDateString())
                    ->orderBy('delivery_executive_orders.is_reassign_requested', 'asc') // prioritize 0 over 1
                    ->select(
                        'delivery_executive.executive_id',
                        'delivery_executive.name as delivery_boy_name',
                        'delivery_executive_orders.assigned_date',
                        'delivery_executive.phn_no1'
                    )
                    ->first();


                $order->delivery_boy_name = $deliveryExecutiveOrder->delivery_boy_name ?? null;
                $order->executive_number = $deliveryExecutiveOrder->executive_id ?? null;
                $order->executive_mobile_number = $deliveryExecutiveOrder->phn_no1 ?? null;
                $order->assigned_date = $deliveryExecutiveOrder->assigned_date ?? null;

                // Assigned or not
                if ($order->assigned_date) {
                    $assignedOrders++;
                    $assignedData[] = $order;
                } else {
                    $unassignedData[] = $order;
                }

                // Delivered or not
                $delivery = DB::table('subscribed_order_delivery')
                    ->where('order_id', $order->id)
                    ->whereDate('date', $selectedDateCarbon->toDateString())
                    ->first();

                $order->isDelivered = $delivery ? true : false;
                $order->deliveredDate = $delivery ? $delivery->updated_at : null;


                if ($delivery) {
                    $deliveredOrders++;
                    $deliveredData[] = $order;
                } else {
                    $undeliveredData[] = $order;
                }
            }

            $unassignedOrders = $totalOrders - $assignedOrders;
            $undeliveredOrders = $totalOrders - $deliveredOrders;

            return response([
                "response" => 200,
                "status" => true,
                "message" => "Orders fetched for selected date",
                "counts" => [
                    "totalOrders" => $totalOrders,
                    "assignedOrders" => $assignedOrders,
                    "unassignedOrders" => $unassignedOrders,
                    "deliveredOrders" => $deliveredOrders,
                    "undeliveredOrders" => $undeliveredOrders,
                ],
                "data" => [
                    "all" => array_values($orders->toArray()),
                    "assigned" => $assignedData,
                    "unassigned" => $unassignedData,
                    "delivered" => $deliveredData,
                    "undelivered" => $undeliveredData
                ]
            ], 200);
        } catch (\Exception $e) {
            return response([
                "response" => 500,
                "status" => false,
                "message" => "Error: " . $e->getMessage()
            ], 500);
        }
    }

    public function getDeliveryOrdersExecutivesId($executiveId, $selectedDate)
    {
        try {
            $executive = DeliveryExecutive::with([
                'routes' => function ($query) {
                    $query->whereHas('deliveryRoute', function ($subQuery) {
                        $subQuery->where('is_active', true);
                    })->with('deliveryRoute');
                }
            ])->where('id', $executiveId)->first();


            if (!$executive) {
                return response([
                    "response" => 404,
                    "status" => false,
                    "message" => "Executive not found",
                ], 404);
            }
            $givenDate = $selectedDate;


            $orderData = $executive->routes->flatMap(function ($route) use ($givenDate) {
                $pincode = $route->deliveryRoute->pincode;
                $orders = DB::table('orders')
                    ->Join('users', 'users.id', '=', 'orders.user_id')
                    ->join('user_address', 'orders.address_id', '=', 'user_address.id')
                    ->leftJoin('subscribed_order_delivery', 'orders.id', '=', 'subscribed_order_delivery.order_id')
                    ->where(function ($query) use ($givenDate) {
                        $query->whereDate('subscribed_order_delivery.date', '!=', $givenDate)
                            ->orWhereNull('subscribed_order_delivery.date');
                    })
                    ->leftJoin('delivery_executive_orders', 'orders.id', '=', 'delivery_executive_orders.order_id')

                    ->where('user_address.pincode', $pincode)
                    ->where('orders.order_status', false)
                    ->where('orders.status', '=', 1)
                    ->select(
                        'orders.*',
                        'users.name as customerName',
                        'users.phone',
                        'users.email',
                        'user_address.name',
                        'user_address.s_phone',
                        'user_address.flat_no',
                        'user_address.apartment_name',
                        'user_address.area',
                        'user_address.landmark',
                        'user_address.city',
                        'user_address.pincode',
                    )
                    ->distinct()
                    ->get()
                    ->filter(function ($order) use ($givenDate) {
                        $startDate = Carbon::parse($order->start_date);
                        $created_at = Carbon::parse($order->created_at);
                        $selectedDate = Carbon::parse($givenDate);
                        if (is_null($order->subscription_type)) {
                            return is_null($order->start_date)
                                ? $created_at->addDay()->isSameDay($selectedDate)
                                : Carbon::parse($order->start_date)->isSameDay($selectedDate);
                        } else {
                            $daysDifference = $startDate->diffInDays($selectedDate);
                            $isABeforeGivenDay = $selectedDate->isAfter($startDate) || $startDate->isSameDay($selectedDate);
                            $pauseDiffDays = 0;
                            $pauseDates = [];
                            // if ($order->pause_dates && $order->resume_dates) {
                            //     $pauseDiffDays = $this->calculateDayDifference($order->pause_dates, $order->resume_dates, $isWeeklyDelivery = $order->subscription_type === 2, $order->selected_days_for_weekly);
                            // }
                            if ($order->pause_dates) {
                                $pauseDates = array_map('trim', explode(',', trim($order->pause_dates, '[]')));
                            }
                            $isDateInPausePeriod = function ($selectedDate) use ($pauseDates) {
                                foreach ($pauseDates as $pauseDate) {
                                    if (Carbon::parse($selectedDate)->isSameDay(Carbon::parse($pauseDate))) {
                                        return true;
                                    }
                                }
                                return false;
                            };
                            $deliveryCompletedDays = $this->noOfDeliveryCompleted($order->id);
                            $productData = ProductModel::where('id', '=', $order->product_id)->first();
                            $order->title = $productData->title;

                            $newEndDate = $this->calculateEndDate($startDate, $order, $pauseDates); // Calculate the end date

                            switch ($order->subscription_type) {
                                case '1': // One-Time
                                    return (
                                        $deliveryCompletedDays === 0 &&
                                        $daysDifference === 0 &&
                                        $isABeforeGivenDay &&
                                        $selectedDate->lte($newEndDate)
                                    );

                                case '2': // Weekly
                                    if (
                                        $deliveryCompletedDays < 7 &&
                                        $this->isDeliveryDayForWeekly($selectedDate, $order->selected_days_for_weekly) &&
                                        !$isDateInPausePeriod($selectedDate) &&
                                        $isABeforeGivenDay &&
                                        $selectedDate->lte($newEndDate)
                                    ) {
                                        return true;
                                    }
                                    return false;

                                case '3': // Monthly
                                    return (
                                        $deliveryCompletedDays < 30 &&
                                        $daysDifference < (30 + count($pauseDates)) &&
                                        !$isDateInPausePeriod($selectedDate) &&
                                        $isABeforeGivenDay &&
                                        $selectedDate->lte($newEndDate)
                                    );

                                case '4': // Alternate Days
                                    return (
                                        $deliveryCompletedDays < 15 &&
                                        $daysDifference < (30 + count($pauseDates)) &&
                                        !$isDateInPausePeriod($selectedDate) &&
                                        $isABeforeGivenDay &&
                                        $daysDifference % 2 === 0 &&
                                        $selectedDate->lte($newEndDate)
                                    );

                                default:
                                    return false;
                            }
                        }
                    });

                return $orders;
            });

            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $givenDate)) {
                // Format is d-m-Y
                $givenDate = Carbon::createFromFormat('d-m-Y', $givenDate)->format('Y-m-d');
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $givenDate)) {
                // Format is Y-m-d
                $givenDate = Carbon::createFromFormat('Y-m-d', $givenDate)->format('Y-m-d');
            }

            $currentExecutiveOrders = DB::table("delivery_executive_orders")
                ->where('delivery_executive_orders.delivery_executive_id', '=', $executiveId)
                ->whereDate('delivery_executive_orders.assigned_date', '=', $givenDate)
                ->where('delivery_executive_orders.is_reassign_requested', '=', false)  // Exclude reassigned orders
                ->orderBy('updated_at', 'desc')
                ->pluck('order_id')
                ->toArray();

            $otherAssignedOrders = DB::table("delivery_executive_orders")
                ->whereDate('delivery_executive_orders.assigned_date', '=', $givenDate)
                ->where('delivery_executive_orders.delivery_executive_id', '!=', $executiveId)
                ->where('delivery_executive_orders.is_reassign_requested', '=', false)  // Exclude reassigned orders
                ->orderBy('updated_at', 'desc')
                ->pluck('order_id')
                ->toArray();


            $orderDataArray = json_decode(json_encode($orderData), true);

            $orderDataArray = array_map(function ($order) use ($currentExecutiveOrders, $otherAssignedOrders) {
                if (in_array($order['id'], $otherAssignedOrders)) {
                    return null;
                }
                $order['isAssigned'] = in_array($order['id'], $currentExecutiveOrders) ? 1 : 0;
                return $order;
            }, $orderDataArray);

            $orderDataArray = array_filter($orderDataArray, function ($order) {
                return $order !== null;
            });

            $orderDataArray = array_values(array_reduce($orderDataArray, function ($carry, $order) {
                if (!isset($carry[$order['id']])) {
                    $carry[$order['id']] = $order;
                }
                return $carry;
            }, []));

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Delivery executive routes fetched successfully",
                "data" => array_values($orderDataArray),
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

    private static function calculateEndDate($startDate, $order, $pauseDates = [])
    {
        $startDate = Carbon::parse($startDate);
        $pausedaysDifference = count($pauseDates);

        switch ($order->subscription_type) {
            case 2: // Weekly
                $weekdayCount = 0;
                $tempStartDate = $startDate->copy();

                $selectedDaysJson = $order->selected_days_for_weekly;
                $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDaysJson);
                $selectedDays = is_string($selectedDaysJson) ? json_decode($selectedDaysJson, true) : $selectedDaysJson;

                $selectedDayCodes = array_map(function ($item) {
                    return (string) ($item['dayCode'] === 0 ? 7 : $item['dayCode']);
                }, $selectedDays);


                // Add 6 valid delivery days
                while ($weekdayCount < 6) {
                    $tempStartDate->addDay();
                    $dayCode = $tempStartDate->dayOfWeekIso;

                    if (in_array($dayCode, $selectedDayCodes)) {
                        $weekdayCount++;
                    }
                }

                // Add paused days to the end date
                if ($pausedaysDifference > 0) {
                    $additionalDaysAdded = 0;
                    while ($additionalDaysAdded < $pausedaysDifference) {
                        $tempStartDate->addDay();
                        $dayCode = $tempStartDate->dayOfWeekIso;

                        if (in_array($dayCode, $selectedDayCodes)) {
                            $additionalDaysAdded++;
                        }
                    }
                }

                return $tempStartDate;

            case 3: // Monthly
                return $startDate->addDays(29 + $pausedaysDifference);

            case 4: // Alternate Days
                return $startDate->addDays(28 + ($pausedaysDifference * 2));

            default: // Default (One-Time or others)
                return $startDate;
        }
    }

    private function noOfDeliveryCompleted($orderId)
    {
        $deliveredCount = DB::table('subscribed_order_delivery')
            ->where('order_id', $orderId)
            ->count();

        return $deliveredCount;
    }

    private function isDeliveryDayForWeekly($givenDate, $selectedDays)
    {
        $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDays);
        $selectedDays = is_string($selectedDays) ? json_decode($selectedDaysJson, true) : $selectedDaysJson;
        $selectedDayCodes = array_map(function ($item) {
            return (string)$item['dayCode'];  // Convert dayCode to string for comparison
        }, $selectedDays);

        $currentDay = Carbon::parse($givenDate)->format('N');
        $currentDayCode = $currentDay % 7;
        return in_array($currentDayCode, $selectedDayCodes);
    }

    private function isWithinWeeklyDeliveryWindow($startDate, $selectedDate, $selectedDaysForWeekly, $pauseDiffDays)
    {
        // Parse the start and selected dates
        $currentDate = clone $startDate;
        $validDeliveryDays = 0;

        // Iterate until we accumulate 7 valid delivery days (including pause days)
        while ($validDeliveryDays < (7 + $pauseDiffDays)) {
            // If selectedDate is before the currentDate, it's out of range
            if ($selectedDate < $currentDate) {
                return false; // No need to check further, we are out of range
            }

            // Check if the currentDate is a valid delivery day according to selectedDaysForWeekly
            if ($this->isDeliveryDayForWeekly($currentDate, $selectedDaysForWeekly)) {
                $validDeliveryDays++;
            }

            // If we reach the selectedDate and it matches a valid delivery day, return true
            if ($currentDate->format('Y-m-d') === $selectedDate->format('Y-m-d')) {
                // Ensure the selectedDate is a valid delivery day
                if ($this->isDeliveryDayForWeekly($selectedDate, $selectedDaysForWeekly)) {
                    return true; // It's a valid delivery day
                } else {
                    return false; // It's not a valid delivery day
                }
            }

            // Increment currentDate by 1 day
            $currentDate->modify('+1 day');
        }

        // If we complete the loop without encountering the selectedDate, return false
        return false;
    }

    function calculateDayDifference($pauseDates, $resumeDates, $isWeeklyDelivery = false, $selectedDays = null)
    {
        // Remove the brackets and split by commas to get individual date strings
        $pauseDateStrings = $pauseDates ? explode(', ', trim($pauseDates, '[]')) : [];
        $resumeDateStrings = $resumeDates ? explode(', ', trim($resumeDates, '[]')) : [];

        if (empty($pauseDateStrings) || empty($resumeDateStrings)) {
            throw new \Exception("Invalid input: Both pauseDates and resumeDates must contain values.");
        }

        if (count($pauseDateStrings) !== count($resumeDateStrings)) {
            throw new \Exception("Mismatch in the number of pauseDates and resumeDates.");
        }

        $differences = [];
        $totalDifference = 0;
        // Iterate through each pair of pause and resume dates
        foreach ($pauseDateStrings as $index => $pauseDateString) {
            $pauseDate = \DateTime::createFromFormat('Y-m-d', trim($pauseDateString));
            $resumeDate = \DateTime::createFromFormat('Y-m-d', trim($resumeDateStrings[$index]));

            if (!$pauseDate || !$resumeDate) {
                continue;
            }

            $currentPauseDate = clone $pauseDate;
            $dayCount = 0;

            while ($currentPauseDate <= $resumeDate) {
                $isDeliveryDay = !$isWeeklyDelivery || $this->isDeliveryDayForWeekly($currentPauseDate->format('Y-m-d'), $selectedDays);

                if ($isDeliveryDay) {
                    $dayCount++;
                }

                $currentPauseDate->modify('+1 day');
            }
            $dayDifference = $dayCount - 1;   // Subtract 1 as difference excludes start day
            if ($dayDifference >= 0) {
                $totalDifference += $dayDifference;
            }
        }

        return $totalDifference;
    }

    // public function addData(Request $request)
    // {
    //     try {
    //         $validator = Validator::make(request()->all(), [
    //             'executive_id' => 'required',
    //             'executive_number' => 'required',
    //             'assigned_orders' => 'nullable|array',
    //             'assigned_date' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 401);
    //         } else {
    //             $assignedOrders = $request->has('assigned_orders') ? $request->input('assigned_orders') : [$request->all()];
    //             $unassignedOrders = $request->input('unassigned_orders', []);
    //             $givenDate = $request->assigned_date;

    //             if (!empty($unassignedOrders)) {
    //                 foreach ($unassignedOrders as $orders) {
    //                     DeliveryExecutiveOrderModal::where('order_id', $orders['id'])
    //                         ->whereDate('assigned_date', '=', $givenDate)
    //                         ->delete();
    //                 }
    //             }

    //             $exsitingOrders = DeliveryExecutiveOrderModal::whereIn(
    //                 'order_id',
    //                 array_column($assignedOrders, 'id')
    //             )->whereDate('assigned_date', '=', $givenDate)
    //                 ->get(['delivery_executive_id', 'order_id', 'assigned_date'])
    //                 ->toArray();

    //             $newOrders = array_filter($assignedOrders, function ($order) use ($exsitingOrders, $request) {
    //                 return !in_array(
    //                     [
    //                         'delivery_executive_id' => $request->executive_id,
    //                         'order_id' => $order['id'],
    //                         'assigned_date' => $request->assigned_date,
    //                     ],
    //                     $exsitingOrders
    //                 );
    //             });

    //             $orderData = array_map(function ($order) use ($request) {
    //                 $timeStamp = date("Y-m-d H:i:s");
    //                 return [
    //                     'delivery_executive_id' => $request->executive_id,
    //                     'order_id' => $order['id'],
    //                     'assigned_date' => $request->assigned_date,
    //                     'order_number' => $order['order_number'],
    //                     'executive_number' => $request->executive_number,
    //                     'created_at' => $timeStamp,
    //                     'updated_at' => $timeStamp,
    //                 ];
    //             }, $newOrders);

    //             // Insert new routes (if any)
    //             if (!empty($orderData)) {
    //                 DeliveryExecutiveOrderModal::insert($orderData);
    //             }

    //             $response = [
    //                 "response" => 200,
    //                 "status" => true,
    //                 "message" => "Delivery executive order(s) assigned successfully",
    //             ];
    //             return response($response, 200);
    //         }
    //     } catch (\Exception $e) {
    //         $response = [
    //             "response" => 500,
    //             "status" => false,
    //             "message" => "An error occurred while assigning the order(s): " . $e->getMessage(),
    //         ];
    //         return response($response, 500);
    //     }
    // }


    public function addData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'executive_id' => 'required',
                'executive_number' => 'required',
                'assigned_orders' => 'nullable|array',
                'unassigned_orders' => 'nullable|array',
                'assigned_date' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 401);
            }

            $assignedOrders = $request->input('assigned_orders', []);
            $unassignedOrders = $request->input('unassigned_orders', []);
            $givenDate = $request->assigned_date;

            // If both assigned and unassigned are empty, stop
            if (empty($assignedOrders) && empty($unassignedOrders)) {
                return response()->json([
                    'response' => 400,
                    'status' => false,
                    'message' => 'No assigned or unassigned orders provided.',
                ], 400);
            }

            // Handle unassigning orders
            if (!empty($unassignedOrders)) {
                foreach ($unassignedOrders as $order) {
                    DeliveryExecutiveOrderModal::where('order_id', $order['id'])
                        ->whereDate('assigned_date', $givenDate)
                        ->delete();
                }
            }

            // Handle assigning orders
            if (!empty($assignedOrders)) {
                $existingOrders = DeliveryExecutiveOrderModal::whereIn('order_id', array_column($assignedOrders, 'id'))
                    ->whereDate('assigned_date', $givenDate)
                    ->pluck('order_id')
                    ->toArray();

                $newOrders = array_filter($assignedOrders, function ($order) use ($existingOrders) {
                    return !in_array($order['id'], $existingOrders);
                });

                if (!empty($newOrders)) {
                    $timeStamp = now();
                    $orderData = array_map(function ($order) use ($request, $timeStamp) {
                        return [
                            'delivery_executive_id' => $request->executive_id,
                            'order_id' => $order['id'],
                            'assigned_date' => $request->assigned_date,
                            'order_number' => $order['order_number'],
                            'executive_number' => $request->executive_number,
                            'created_at' => $timeStamp,
                            'updated_at' => $timeStamp,
                        ];
                    }, $newOrders);

                    DeliveryExecutiveOrderModal::insert($orderData);
                }
            }

            return response()->json([
                'response' => 200,
                'status' => true,
                'message' => 'Delivery executive order(s) updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'response' => 500,
                'status' => false,
                'message' => 'An error occurred while updating orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function removeData(Request $request)
    {
        try {
            $id = $request->input('id');

            if (is_null($id)) {
                return response([
                    "response" => 400,
                    "status" => false,
                    "message" => "ID is required to delete a Order"
                ], 400);
            }

            $order = DeliveryExecutiveOrderModal::findOrFail($id);
            $order->delete();

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Order deleted successfully"
            ];

            return response($response, 200);
        } catch (\Exception $e) {
            $response = [
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while deleting the order: " . $e->getMessage()
            ];
            return response($response, 500);
        }
    }

    // public function getAllReAssigneRequestData()
    // {
    //     try {

    //         $data = DB::table("delivery_executive_orders")
    //             ->select(
    //                 'delivery_executive_orders.*',
    //                 'delivery_executive.name as delivery_boy_name'
    //             )
    //             ->Join('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
    //             ->where('is_reassign_requested', true)
    //             ->where('is_admin_reassigned', false)
    //             ->orderBy('created_at', 'DESC')
    //             ->get();

    //         foreach ($data as $item) {
    //             if ($item->reassigned_executive_id) {
    //                 $executiveData = DeliveryExecutive::where('id', '=', $item->reassigned_executive_id)->first();
    //                 $item->request_executive_number = $executiveData->executive_id;
    //                 $item->request_executive_name = $executiveData->name;
    //             }
    //         }

    //         $response = [
    //             "response" => 200,
    //             "status" => true,
    //             "message" => "Routes retrieved successfully",
    //             "data" => $data
    //         ];

    //         return response($response, 200);
    //     } catch (\Exception $e) {
    //         $response = [
    //             "response" => 500,
    //             "status" => false,
    //             "message" => "An error occurred while fetching the routes: " . $e->getMessage()
    //         ];
    //         return response($response, 500);
    //     }
    // }


    public function getAllReAssigneRequestData()
    {
        try {
            $data = DB::table("delivery_executive_orders")
                ->select(
                    'delivery_executive_orders.*',
                    'delivery_executive.name as delivery_boy_name',
                    'user_address.name as customer_name',
                    'user_address.s_phone as customer_phone',
                    'user_address.pincode as customer_pincode'
                )
                ->join('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
                ->join('orders', 'orders.id', '=', 'delivery_executive_orders.order_id') //  Join with orders
                ->join('user_address', 'user_address.id', '=', 'orders.address_id')
                ->where('delivery_executive_orders.is_reassign_requested', true)
                ->where('delivery_executive_orders.is_admin_reassigned', false)
                ->where('orders.status', '=', 1) //  Filter only orders with status = 1
                ->orderBy('delivery_executive_orders.created_at', 'DESC')
                ->get();

            // Enrich with reassigned executive info
            foreach ($data as $item) {
                if ($item->reassigned_executive_id) {
                    $executiveData = DeliveryExecutive::where('id', $item->reassigned_executive_id)->first();
                    if ($executiveData) {
                        $item->request_executive_number = $executiveData->executive_id;
                        $item->request_executive_name = $executiveData->name;
                    }
                }
            }

            return response([
                "response" => 200,
                "status" => true,
                "message" => "Routes retrieved successfully",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return response([
                "response" => 500,
                "status" => false,
                "message" => "An error occurred while fetching the routes: " . $e->getMessage()
            ], 500);
        }
    }

    public function getDeliveryExectiveByOrder($id)
    {
        try {
            $orderPincode = DB::table("orders")
                ->select(
                    'orders.id as order_id',
                    'user_address.pincode as pincode'
                )
                ->join('user_address', 'user_address.id', '=', 'orders.address_id')
                ->where('orders.id', "=", $id)
                ->first();

            if (!$orderPincode) {
                return response([
                    "response" => 404,
                    "status" => false,
                    "message" => "Order not found"
                ], 404);
            }

            $pincode = $orderPincode->pincode;

            // Relationship Query method with sorting by 'created_at' in descending order
            $executive = DeliveryExecutive::whereHas('routes.deliveryRoute', function ($query) use ($pincode) {
                $query->where('pincode', $pincode);
            })
                ->select('id', 'executive_id', 'name', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $response = [
                "response" => 200,
                "status" => true,
                "message" => "Routes retrieved successfully",
                "data" => $executive
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


    // public function getDeliveryExectiveByOrder($id)
    // {
    //     try {
    //         $orderPincode = DB::table("orders")
    //             ->select(
    //                 'orders.id as order_id',
    //                 'user_address.pincode as pincode'
    //             )
    //             ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
    //             ->where('orders.id', "=", $id)
    //             ->first();
    //         $pincode = $orderPincode->pincode;

    //         // Relationship Query method
    //         $executive = DeliveryExecutive::whereHas('routes.deliveryRoute', function ($query) use ($pincode) {
    //             $query->where('pincode', $pincode);
    //         })
    //             ->select('id', 'executive_id', 'name')
    //             ->get();

    //         // Bulk Query method
    //         // $routeIds = DB::table('delivery_routes')
    //         //   ->where('pincode', $pincode)
    //         //   ->pluck('id')
    //         //   ->toArray();

    //         // $executiveIds = DB::table('delivery_executive_route')
    //         // ->whereIn('delivery_route_id', $routeIds)
    //         // ->pluck('delivery_executive_id')
    //         // ->toArray();

    //         // $executive = DB::table('delivery_executive')
    //         // ->whereIn('id', $executiveIds)
    //         // ->select('id', 'executive_id', 'name')
    //         // ->get();


    //         $response = [
    //             "response" => 200,
    //             "status" => true,
    //             "message" => "Routes retrieved successfully",
    //             "data" => $executive
    //         ];

    //         return response($response, 200);
    //     } catch (\Exception $e) {
    //         $response = [
    //             "response" => 500,
    //             "status" => false,
    //             "message" => "An error occurred while fetching the routes: " . $e->getMessage()
    //         ];
    //         return response($response, 500);
    //     }
    // }

    public function reAssignOrder(Request $request)
    {
        try {
            $validator = Validator::make(request()->all(), [
                'id' => 'required',
                'exsisting_executive' => 'required|array',
                'selected_executive' => 'required|array',
            ]);

            if ($validator->fails())
                return response(["response" => 400], 400);
            else {
                $exsitingExecutive = $request->input('exsisting_executive', []);
                $newExecutive = $request->has('selected_executive') ? $request->input('selected_executive') : [$request->all()];
                $timeStamp = date("Y-m-d H:i:s");
                $dataModel = new DeliveryExecutiveOrderModal();
                $dataModel->executive_number  = $newExecutive['executive_id'];
                $dataModel->delivery_executive_id  = $newExecutive['id'];
                $dataModel->order_id  = $exsitingExecutive['order_id'];
                $dataModel->order_number  = $exsitingExecutive['order_number'];
                $dataModel->assigned_date  = $exsitingExecutive['assigned_date'];
                $dataModel->created_at = $timeStamp;
                $dataModel->updated_at = $timeStamp;
                $qResponce = $dataModel->save();

                if ($qResponce) {
                    $order = DeliveryExecutiveOrderModal::findOrFail($request->id);
                    $order->is_admin_reassigned = true;
                    $order->save();
                    $response = [
                        "response" => 200,
                        'status' => true,
                        'message' => "successfully"
                    ];
                }
            }
        } catch (\Throwable $th) {
            $response = [
                "response" => 201,
                'status' => false,
                'message' => "error",

            ];
        }
        return response($response, 200);
    }
}
