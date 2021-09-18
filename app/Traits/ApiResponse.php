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

    public function error($data, $code = 404, $message = 'Something Went Wrong' )
    {
        return response()->json([
            'success'   => false,
            'errors'      => $data,
            'message'   => $message,
        ], $code);
    }
}
