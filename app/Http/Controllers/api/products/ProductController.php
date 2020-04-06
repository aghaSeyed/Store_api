<?php

namespace App\Http\Controllers\api\products;

use App\Http\Requests\api\User\UserRequest;
use App\Http\Resources\api\ProductResource;
use App\Http\Resources\api\CategoryResource;
use App\Shop\Categories\Category;
use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\Products\Exceptions\ProductNotFoundException;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Transformations\ProductTransformable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function GuzzleHttp\Promise\queue;

class ProductController extends Controller
{
    use ProductTransformable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;
    private $categoryRepo;

    /**
     * ProductController constructor.
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(ProductRepository $productRepository ,CategoryRepository $categoryRepository)
    {
        $this->productRepo = $productRepository;
        $this->categoryRepo = $categoryRepository;

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
    public function getCategory(string $slug)
    {
        $category = $this->categoryRepo->findCategoryBySlug(['slug' => $slug]);

        $repo = new CategoryRepository($category);

        $products = $repo->findProducts()->where('status', 1)->all();

        return response()->json([
//            'category' => CategoryResource::collection($category->get()),
            'products' => ProductResource::collection($repo->paginateArrayResults($products, 10))
        ]);

    }

    public function show(string $slug)
    {
        try{
            $products = $this->productRepo->findProductBySlug(['slug' => $slug])->get();
        }catch (ProductNotFoundException $e){
            return response()->json(['status' => false , 'message' =>'The Product you\'re trying to add does not exist.'],200);
        }
            $images =[];
            $categories=[];
            $productAttributes=[];
            foreach ($products as $product){
                $images['Product_id_'.$product->id] = $product->images()->get();
                $categories['Product_id_'.$product->id] = CategoryResource::collection($product->categories()->get());
                $productAttributes['Product_id_'.$product->id] = $product->attributes()->get();
                }

            return response()->json([
                'status' => true,
                'product' => ProductResource::collection($products),
                'images' => $images,
                'productAttributes' => $productAttributes,
                'category' => $categories
            ], 200);

    }

}
