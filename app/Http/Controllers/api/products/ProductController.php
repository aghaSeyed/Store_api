<?php

namespace App\Http\Controllers\api\products;

use App\Http\Requests\api\User\UserRequest;
use App\Http\Requests\api\User\UserSearchRequest;
use App\Http\Resources\api\ProductResource;
use App\Shop\Categories\Category;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Transformations\ProductTransformable;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    use ProductTransformable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * ProductController constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepo = $productRepository;
    }


    public function latest(){
       $products = Product::where('status',1)->orderBy('id','desc')->take(5)->get();
        return ProductResource::collection($products);
    }


    public function search(UserRequest $request)
    {
        if (request()->has('find') && request()->input('find') != '') {
            $list = $this->productRepo->searchProduct(request()->input('find'));
        } else {
            $list = $this->productRepo->listProducts();
        }

        $products = $list->where('status', 1)->map(function (Product $item) {
            return $this->transformProduct($item);
        });

        return $this->productRepo->paginateArrayResults($products->all(), 10);

    }
    public function categories(){
        $categories = Category::all();
        $arr=array();
        foreach ($categories as $category){
            $arr['name'][] = $category->name;
            $arr['desc'][] = $category->description;
        }
        return $arr;
    }

}
