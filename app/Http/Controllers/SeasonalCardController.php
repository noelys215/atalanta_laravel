<?php

namespace App\Http\Controllers;

use App\Models\SeasonalCard;
use Illuminate\Http\Request;

class SeasonalCardController extends Controller
{
    public function index()
    {
        // Fetch all seasonal cards
        $seasonalCards = SeasonalCard::all();

        return response()->json($seasonalCards, 200);
    }

    public function show($slug)
    {
        // Fetch a single seasonal card by slug
        $seasonalCard = SeasonalCard::where('slug', $slug)->firstOrFail();

        return response()->json($seasonalCard, 200);
    }
}
