<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Resources\Api\v1\CategoryResourceCollection;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * @tags v1 Categorias
 */
class CategoryController extends Controller
{
    /**
     * Lista as categorias.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new CategoryResourceCollection(Category::all());
    }
}
