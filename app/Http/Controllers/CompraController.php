<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        $vendas = Venda::with('produtos')
            ->where('cliente_id', Auth::id())
            ->latest()
            ->get();

        return view('compras.index', compact('vendas'));
    }

    public function create()
    {
        $produtos = Produto::orderBy('nome')->get();

        return view('compras.create', compact('produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'itens' => ['required', 'array'],
            'itens.*' => ['integer', 'min:0'],
        ]);

        // 1. ignora os itens com quantidade 0
        $itens = collect($request->input('itens'))
            ->map(fn ($quantidade) => (int) $quantidade)
            ->filter(fn ($quantidade) => $quantidade > 0);

        if ($itens->isEmpty()) {
            return back()->withInput()->withErrors(['itens' => 'Informe a quantidade de pelo menos um item.']);
        }

        $produtos = Produto::whereIn('id', $itens->keys())->get()->keyBy('id');

        // Bônus: recusa a compra se alguma quantidade passar do estoque
        foreach ($itens as $produtoId => $quantidade) {
            $produto = $produtos->get($produtoId);

            if (! $produto) {
                return back()->withInput()->withErrors(['itens' => 'Produto inválido na compra.']);
            }

            if ($quantidade > $produto->estoque) {
                return back()->withInput()->withErrors([
                    'itens' => "Estoque insuficiente para {$produto->nome} (disponível: {$produto->estoque}).",
                ]);
            }
        }

        DB::transaction(function () use ($itens, $produtos) {
            // 2. cria a venda com o cliente logado
            $venda = Venda::create(['cliente_id' => Auth::id()]);

            // 3. anexa cada item com a quantidade e o preço atual do produto
            foreach ($itens as $produtoId => $quantidade) {
                $produto = $produtos->get($produtoId);

                $venda->produtos()->attach($produto->id, [
                    'quantidade' => $quantidade,
                    'preco_unitario' => $produto->preco,
                ]);

                // Bônus: diminui o estoque
                $produto->decrement('estoque', $quantidade);
            }
        });

        // 4. redireciona para Minhas compras
        return redirect()->route('compras.index')->with('sucesso', 'Compra realizada!');
    }
}
