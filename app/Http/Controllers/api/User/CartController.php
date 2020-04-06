<?php

namespace App\Http\Controllers\api\User;

use App\Shop\Carts\Exceptions\ProductInCartNotFoundException;
use App\Shop\Carts\Repositories\CartRepository;
use App\Shop\Carts\Repositories\Interfaces\CartRepositoryInterface;
use App\Shop\Carts\Requests\AddToCartRequest;
use App\Shop\Carts\Requests\UpdateCartRequest;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\ProductAttributes\Repositories\ProductAttributeRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Transformations\ProductTransformable;
use Gloudemans\Shoppingcart\Exceptions\CartAlreadyStoredException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ProductTransformable;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepo;

    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var CourierRepository
     */
    private $courierRepo;

    /**
     * @var ProductAttributeRepository
     */
    private $productAttributeRepo;

    /**
     * CartController constructor.
     * @param CartRepository $cartRepository
     * @param ProductRepository $productRepository
     * @param CourierRepository $courierRepository
     * @param ProductAttributeRepository $productAttributeRepository
     */
    public function __construct(
        CartRepository $cartRepository,
        ProductRepository $productRepository,
        CourierRepository $courierRepository,
        ProductAttributeRepository $productAttributeRepository
    ) {
        $this->cartRepo = $cartRepository;
        $this->productRepo = $productRepository;
        $this->courierRepo = $courierRepository;
        $this->productAttributeRepo = $productAttributeRepository;
    }

    public function index()
    {
        $customer = auth('api')->user();
        $courier = $this->courierRepo->findCourierById(request()->input('courierId',1));
        $shippingFee = $this->cartRepo->getShippingFee($courier);
        $cart = new CartRepository($this->cartRepo->openCart($customer));
        $this->cartRepo->saveCart($customer);
        return response()->json([
            'status' => true ,
            'cartItems' => $cart->getCartItems(),
            'subtotal' => $cart->getSubTotal(),
            'tax' => $cart->getTax(),
            'shippingFee' => $shippingFee,
            'total' => $cart->getTotal(2, $shippingFee)
        ],200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AddToCartRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Shop\ProductAttributes\Exceptions\ProductAttributeNotFoundException
     * @throws \App\Shop\Products\Exceptions\ProductNotFoundException
     */
    public function store(AddToCartRequest $request)
    {
        $customer = auth('api')->user();
        $this->cartRepo->openCart($customer);
        $product = $this->productRepo->findProductById($request->input('product'));
        if ($product->attributes()->count() > 0) {
            $productAttr = $product->attributes()->where('default', 1)->first();

            if (isset($productAttr->sale_price)) {
                $product->price = $productAttr->price;

                if (!is_null($productAttr->sale_price)) {
                    $product->price = $productAttr->sale_price;
                }
            }
        }

        $options = [];
        if ($request->has('productAttribute')) {

            $attr = $this->productAttributeRepo->findProductAttributeById($request->input('productAttribute'));
            $product->price = $attr->price;

            $options['product_attribute_id'] = $request->input('productAttribute');
            $options['combination'] = $attr->attributesValues->toArray();
        }

        $this->cartRepo->addToCart($product, $request->input('quantity'), $options);
        $this->cartRepo->saveCart($customer);
            return redirect()->route('cart.index');

        }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCartRequest $request, $id)
    {
        $customer = auth('api')->user();
        $this->cartRepo->openCart($customer);
        $this->cartRepo->updateQuantityInCart($id, $request->input('quantity'));
        $this->cartRepo->saveCart($customer);
        return redirect()->route('cart.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Shop\Carts\Exceptions\ProductInCartNotFoundException
     */
    public function destroy($id)
    {
        $customer = auth('api')->user();
        $this->cartRepo->openCart($customer);
        try{
        $this->cartRepo->removeToCart($id);}
        catch (ProductInCartNotFoundException $e){
            return response()->json([
                'status' => false,
                'message' => 'Product in cart not found'
            ],200);
        }
        $this->cartRepo->saveCart($customer);
        return redirect()->route('cart.index');
    }
}
