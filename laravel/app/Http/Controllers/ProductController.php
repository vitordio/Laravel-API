<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use Flugg\Responder\Responder;
use App\Transformers\ProductTransformer;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Create a new ProductController instance.
     *
     * @return void
    */
    public function __construct(Responder $responder) {
        $this->middleware('auth:api');

        $this->responder = $responder;

        // Extensões permitidas
        $this->allowedExtensions = ['jpg', 'png'];
    }

    /**
     * Exibe a lista dos produtos
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return responder()->success(Product::all(), ProductTransformer::class)->respond();
    }

    /**
     * Exibe o produto pelo ID
     *
     * @param  \App\Model\Product  $produto
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $produto = Product::find($id);
        if($produto)
        {
            return $this->responder->success(
                [
                    "produto" => (new ProductTransformer)->transform($produto)
                ]
            )->respond();
        } else
        {
            return $this->responder->error('produto_nao_encontrado', 'O produto não foi encontrado.')->respond();
        }
    }

    /**
     * Salva o novo produto
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if($validator->fails()) {
            return $this->responder->error('campos_obrigatorios', 'Ocorreu um erro de validação')->data([
                'errors' => $validator->errors()
            ])->respond();
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $verifyExtension = in_array($extension, $this->allowedExtensions);

        if($verifyExtension)
        {
            $path = $request->file->store('public/images');
            $produto = new Product([
                'name' => $request->name,
                'price' => $request->price,
                'pathImage' => $path
            ]);

            $produto->save();

            return $this->responder->success(
                [
                    "produto" => (new ProductTransformer)->transform($produto)
                ]
            )->respond();
        } else
        {
            return $this->responder->error('extensao_nao_permitida', 'Extensão do arquivo não permitida.')->respond();
        }
    }

    /**
     * Atualiza o produto pelo ID
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $produto
     * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $produto) {
        $produto = Product::find($id);

        if($produto)
        {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            $verifyExtension = in_array($extension, $this->allowedExtensions);

            if($verifyExtension)
            {
                $path = $request->file->store('public/images');
                $produto = new Product([
                    'name' => $request->name,
                    'price' => $request->price,
                    'pathImage' => $path
                ]);

                $produto->update();

                return $this->responder->success(
                    [
                        "produto_atualizado" => (new ProductTransformer)->transform($produto)
                    ]
                )->respond();
            } else
            {
                return $this->responder->error('extensao_nao_permitida', 'Extensão do arquivo não permitida.')->respond();
            }
        } else
        {
            return $this->responder->error('produto_nao_encontrado', 'O produto não foi encontrado.')->respond();
        }
    }

    /**
     * Deleta o produto pelo ID
     *
     * @param  \App\Models\Product  $produto
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $produto = Product::find($id);

        if($produto)
        {
            $produto->delete();
            return $this->responder->success([ "Produto de ID $id deletado com sucesso." ])->respond();

        } else
        {
            return $this->responder->error('produto_nao_encontrado', 'O produto não foi encontrado.')->respond();
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome do produto é obrigatório',
            'price.required' => 'O preço do produto é obrigatório',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'price' => 'required',
        ];
    }

}
