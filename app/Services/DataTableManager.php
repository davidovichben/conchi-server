<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DataTableManager {

    private $resultsLimit;
    private $query;
    private $params;
    private $columns;

    private function __construct($query, $params, $columns, $resultsLimit = 15)
    {
        $this->query = $query;
        $this->params = collect($params);
        $this->columns = collect($columns);
        $this->resultsLimit = 15;
    }

    public static function getInstance($query, $params, $columns, $resultsLimit = 15)
    {
        return new self($query, $params, collect($columns), $resultsLimit);
    }

    public function getQuery()
    {
        if ($this->params) {
            $this->filterQuery();
        }

        return $this->resultsLimit && $this->params ? $this->query->paginate($this->resultsLimit) : $this->query->get();
    }

    private function filterQuery()
    {
        $filters = $this->getFilterColumns();
        if ($this->params->get('checkedItems')) {
            if ($this->params->get('isCheckAll') && $this->params->get('checkedItems')) {
                $this->excludeFromQuery();
            }

            if (!$this->params->get('isCheckAll') && $this->params->get('checkedItems')) {
                $this->includeFromQuery();
            }
        }

        if (!(!$this->params->get('isCheckAll') && $this->params->get('checkedItems'))) {
            if (count($filters) > 0) {
                $this->searchByColumn($filters);
            }

            if ($this->params->get('keyword')) {
                $this->searchAll();
            }
        }

        if ($this->params->get('sortBy')) {
            $this->orderBy();
        }
    }

    private function excludeFromQuery()
    {
        $ids = $this->params->get('checkedItems')->pluck('id')->toArray();
        return $this->query->whereNotIn($this->columns->get('id'), $ids);
    }

    private function includeFromQuery()
    {
        $ids = $this->params->get('checkedItems')->pluck('id')->toArray();
        return $this->query->whereIn($this->columns->get('id'), $ids);
    }

    private function getFilterColumns()
    {
//        $filtered = collect($this->params->get('filters'))->filter(function($param) {
        $filtered = collect($this->params)->filter(function($param) {
            return $param || $param == '0';
        });

        return $filtered->intersectByKeys($this->columns);
    }

    private function searchByColumn($filter)
    {
        foreach ($filter as $key => $value) {
            $column = $this->columns[$key];
            if (isset($column['type'])) {
                switch ($column['type']) {
                    case 'date':
                        $values = collect($value);
                        if ($values->get('from') || $values->get('to')) {
                            $from = $values->get('from') ? $values->get('from') :'01/01/1900';
                            $values->put('from', Carbon::createFromFormat('d/m/Y', $from)->startOfDay());

                            $to = $values->get('to') ? $values->get('to') : Carbon::now()->format('d/m/Y');
                            $values->put('to', Carbon::createFromFormat('d/m/Y', $to)->endOfDay());

                            $this->query->whereBetween($column['column'], $values->only('to', 'from')->toArray());
                        }
                        if ($values->has('year') && $values->has('month')) {
                            $start = Carbon::createFromFormat('m/Y', $values->get('month') . '/' . $values->get('year'))->startOfMonth();
                            $end = Carbon::createFromFormat('m/Y', $values->get('month') . '/' . $values->get('year'))->endOfMonth();
                            $this->query->whereBetween($column['column'], [$start, $end]);
                        }
                        break;
                    case 'raw':
                        $this->query->whereRaw($column['column'] . ' like ?', '%' . $value . '%');
                        break;
                    case 'exact':
                        $this->query->where($column['column'], $value);
                        break;
                    case 'having':
                        $this->query->havingRaw($column['column'] . ' = ?', [$value]);
                        break;
                }
            } else {
                if (isset($column['filter'])) {
                    $column = $column['filter'];
                }
                if ($value instanceof Collection || is_array($value)){
                    $this->query->whereIn($column, $value);
                } else {
                    $this->query->where($column, 'like', '%' . $value . '%');
                }
            }
        }
    }

    private function searchAll()
    {
        $this->query->where(function($q) {
            $keyword = $this->params->get('keyword');
			foreach ($this->columns as $key => $value) {
                $column = isset($value['column']) ? $value['column'] : $value;
                if (isset($value['type'])) {
                    switch ($value['type']) {
                        case 'raw':
                            $q->orWhereRaw($column . ' like ?', '%' . $keyword . '%');
                            break;
                        case 'date':
                            $date = Helpers::reformatDate($keyword);
                            if ($date) {
                                $nextDayDate = Carbon::parse($date)->addDay();
                                $q->orWhereBetween($column, [$date, $nextDayDate]);
                            }

                            break;
                        case 'having':
                            $q->having($column . ' like ?', '%' . $keyword . '%');
                            break;
                    }
                } else {
                    if (isset($column['filter'])) {
                        $column = $column['filter'];
                    }

                    $q->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        });
    }

    private function orderBy()
    {
        $column = $this->params->get('sortBy');
        if ($this->columns->contains($column)) {
            $direction = $this->params->get('sortDir') === 'asc' ? 'asc' : 'desc';

            $columnName = isset($column['column']) ? $column['column'] : $column;
            if (isset($column['sortBy'])) {
                $columnName = $column['sortBy'];
            }
            if (isset($column['type']) && (in_array($column['type'], ['raw', 'having']))) {
                $this->query->orderByRaw($columnName . ' ' . $direction);
            } else {
                $this->query->orderBy($columnName, $direction);
            }
        }
    }
}
