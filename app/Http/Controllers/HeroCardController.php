<?php

namespace App\Http\Controllers;

use App\Models\HeroCard;

class HeroCardController extends Controller
{
    public function index()
    {
        // Fetch all hero cards
        $heroCards = HeroCard::all();

        return response()->json($heroCards, 200);
    }

    public function show($slug)
    {
        // Fetch a single hero card by slug
        $heroCard = HeroCard::where('slug', $slug)->firstOrFail();

        return response()->json($heroCard, 200);
    }
}
