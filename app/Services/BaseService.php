<?php

namespace App\Services;

use Closure;
use DB;

class BaseService
{
    public function __construct()
    {
    }

    protected function atomic(Closure $callback)
    {
        return DB::transaction($callback);
    }

    protected function dataWrapper($data)
    {
        $results = [];

        if (isset($data['data'])) {
            $results['data'] = $data['data'];

            unset($data['data']);

            $results['meta'] = $data;
        } else {
            $results['data'] = $data;
        }

        return $results;
    }

    protected function queryBuilder($baseQuery, $attributes, $includes = [])
    {
        if (is_string($baseQuery)) {
            $baseQuery = ($baseQuery)::query();
        }

        $name = (@$attributes['name']) ? $attributes['name'] : null;
        $orderName = (@$attributes['order_name']) ? $attributes['order_name'] : null;
        $orderValue = (@$attributes['order_value']) ? $attributes['order_value'] : null;
        $status = (@$attributes['status']) ? $attributes['status'] : null;
        $agencyPlan = (@$attributes['agency_plan']) ? $attributes['agency_plan'] : null;

        $baseQuery = $baseQuery->with($includes);

        if ($name != null) {
            $names = explode(',', $name);

            $baseQuery = $baseQuery->where(function ($q) use ($names) {
                foreach ($names as $key => $name) {
                    if ($key == 0) {
                        $q->where('name', 'like', '%' . $name . '%');
                    } else {
                        $q->orWhere('name', 'like', '%' . $name . '%');
                    }
                }
            });
        }

        if ($status != null) {
            if ($status == 'active')
                $baseQuery = $baseQuery->where('status', 1);
            else
                $baseQuery = $baseQuery->where('status', 0);
        }

        if ($orderName != null && $orderValue != null) {
            $baseQuery = $baseQuery->orderBy($orderName, $orderValue);
        }

        if ($agencyPlan != null) {
            if ($agencyPlan == 'active') {
                $baseQuery = $baseQuery->where(function($query){
                    $query->where('agency_plan', 1)
                          ->orWhere('type', 'free');
                });
            }
            else {
                $baseQuery = $baseQuery->where(function($query){
                    $query->where('agency_plan', 0)
                          ->orWhere('type', 'free');
                });
            }
        }

        return $baseQuery;
    }
}
