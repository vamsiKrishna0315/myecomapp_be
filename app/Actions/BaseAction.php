<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseAction
{
    /**
     * Execute the action and return the result.
     *
     * @param array $data
     * @return mixed
     */
    abstract public function execute(array $data);

    /**
     * Execute the action within a database transaction.
     *
     * @param array $data
     * @return mixed
     */
    public function executeInTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->execute($data);
        });
    }

    /**
     * Validate the data before executing the action.
     *
     * @param array $data
     * @return bool
     */
    protected function validate(array $data): bool
    {
        // Override in child classes for custom validation
        return true;
    }

    /**
     * Prepare data before executing the action.
     *
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data): array
    {
        // Override in child classes for data preparation
        return $data;
    }

    /**
     * Handle after action execution.
     *
     * @param mixed $result
     * @param array $data
     * @return mixed
     */
    protected function afterExecution($result, array $data)
    {
        // Override in child classes for post-execution logic
        return $result;
    }
}