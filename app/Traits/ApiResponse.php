<?php
namespace App\Traits;

/**
 * Api Response Trait
 */
trait ApiResponse
{
    public function success($data, $message = 'Ok' , $code=200)
    {
        return response()->json([
            'success'   => true,
            'data'      => $data,
            'message'   => $message,
        ], $code);
    }

    public function error($data, $message = 'Something Went Wrong' , $code=404)
    {
        return response()->json([
            'success'   => false,
            'data'      => $data,
            'message'   => $message,
        ], $code);
    }
}
