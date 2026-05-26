<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    /**
     * Lista todas as oficinas
     */
    public function index()
    {
        $workshops = Workshop::with('productions')->get();
        return view('workshops.index', compact('workshops'));
    }

    /**
     * Form de criação
     */
    public function create()
    {
        return view('workshops.create');
    }

    /**
     * Salvar nova oficina
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Workshop::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('workshops.index')
            ->with('success', 'Oficina criada com sucesso!');
    }

    /**
     * Mostrar (opcional)
     */
    public function show(string $id)
    {
        $workshop = Workshop::with('productions')->findOrFail($id);
        return view('workshops.show', compact('workshop'));
    }

    /**
     * Form de edição
     */
    public function edit(string $id)
    {
        $workshop = Workshop::findOrFail($id);
        return view('workshops.edit', compact('workshop'));
    }

    /**
     * Atualizar oficina
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $workshop = Workshop::findOrFail($id);

        $workshop->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('workshops.index')
            ->with('success', 'Oficina atualizada!');
    }

    /**
     * Deletar oficina
     */
    public function destroy(string $id)
    {
        Workshop::destroy($id);

        return redirect()->route('workshops.index')
            ->with('success', 'Oficina removida!');
    }
}