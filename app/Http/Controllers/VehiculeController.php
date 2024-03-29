<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use App\Models\Enums\AccountType;

class VehiculeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin'], ['except' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicules = Vehicule::all()
        ->map(function ($vehicule) {
            return [
                'uuid' => $vehicule->uuid,
                'type' => $vehicule->type,
                'permis' => $vehicule->permis,
                'marque' => $vehicule->marque,
                'created_at' => $vehicule->created_at,
                // 'image_url' => str_replace('http://localhost', 'http://127.0.0.1:8000', $vehicule->getFirstMediaUrl('annonces_images')),
            ];
        })
        // ->whereNotNull('published_at')
        ->sortBy('created_at');
        return view('vehicule.index', [ 'vehicules' => $vehicules ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vehicule.create-vehicule');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'permis' => 'required|string|max:255',
            'prix' => 'required|string|max:255',
            'marque' => 'required|string|max:255',
        ]);

        $team = Team::where('type', AccountType::LOUEUR)->first();
        
        $vehicule = $team->vehicules()->create($validated);

        if ($request->hasFile('image')) {
            $vehicule->addMediaFromRequest('image')->toMediaCollection('vehicules_images');
        }

        if( $vehicule ){
            return back()->withSuccess('Enregistré !');
        }

        return back()->with('error', 'Une erreur s\'est produite !!');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicule  $vehicule
     * @return \Illuminate\Http\Response
     */
    public function show( $uuid )
    {
        return view('vehicule.vehicule-details', [ 'vehicule' => Vehicule::where('uuid', $uuid)->first() ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        return view('vehicule.edit-vehicule', [ 'vehicule' => Vehicule::where('uuid', $uuid)->first() ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicule  $vehicule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicule $vehicule)
    {
        if( auth()->user()->is( $vehicule->user ) ){
            $vehicule->update();
            return back()->withSuccess('Mise à jour !');
        }
        return back()->with('error', 'Une erreur s\'est produite !!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicule  $vehicule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicule $vehicule)
    {
        if( auth()->user()->is( $vehicule->user ) ){
            $vehicule->delete();
            return back()->withSuccess('Supprimmée !');
        }
        return back()->with('error', 'Une erreur s\'est produite !!');
    }
}
