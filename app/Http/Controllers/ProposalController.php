<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index()
    {
        return view('proposal.index');
    }


    public function create()
    {
        return view('proposal.create');
    }

    public function store(Request $request)
    {
        //
    }


    public function show(Proposal $proposal)
    {
        //
    }


    public function edit(Proposal $proposal)
    {
        //
    }


    public function update(Request $request, Proposal $proposal)
    {
        //
    }


    public function destroy(Proposal $proposal)
    {
        //
    }
}
