<?php

namespace App\Rules;

use App\Models\OrderItem;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class Stock implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     * @var OrderItem
     */
    protected $data ;


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        Log::info(json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
