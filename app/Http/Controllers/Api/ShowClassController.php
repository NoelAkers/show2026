<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShowClassResource;
use App\Models\ShowClass;
use Illuminate\Http\Request;

class ShowClassController extends Controller
{
    public function lookup(Request $request): ShowClassResource
    {
        $class = ShowClass::findOrFail($request->integer('number'));

        return new ShowClassResource($class);
    }
}
