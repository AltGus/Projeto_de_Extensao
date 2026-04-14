<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Workshop;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    /**
     * Lista todas as produções
     */
    public function index()
    {
        $productions = Production::with('workshop')->get();
        return view('productions.index', compact('productions'));
    }

    /**
     * Form de criação
     */
    public function create()
    {
        $workshops = Workshop::all();
        return view('productions.create', compact('workshops'));
    }

    /**
     * Salvar nova produção
     */
    public function store(Request $request)
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'product' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        Production::create([
            'workshop_id' => $request->workshop_id,
            'product' => $request->product,
            'quantity' => $request->quantity
        ]);

        return redirect()->route('productions.index')
            ->with('success', 'Produção criada com sucesso!');
    }

    /**
     * Mostrar uma produção específica (opcional)
     */
    public function show(string $id)
    {
        $production = Production::with('workshop')->findOrFail($id);
        return view('productions.show', compact('production'));
    }

    /**
     * Form de edição
     */
    public function edit(string $id)
    {
        $production = Production::findOrFail($id);
        $workshops = Workshop::all();

        return view('productions.edit', compact('production', 'workshops'));
    }

    /**
     * Atualizar produção
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'product' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $production = Production::findOrFail($id);

        $production->update([
            'workshop_id' => $request->workshop_id,
            'product' => $request->product,
            'quantity' => $request->quantity
        ]);

        return redirect()->route('productions.index')
            ->with('success', 'Produção atualizada!');
    }

    /**
     * Deletar produção
     */
    public function destroy(string $id)
    {
        Production::destroy($id);

        return redirect()->route('productions.index')
            ->with('success', 'Produção removida!');
    }
}