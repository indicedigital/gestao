<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Services\TutorialPersonaService;
use App\Support\TutorialCatalog;

class TutorialController extends Controller
{
    use InteractsWithCompany;

    public function index(TutorialPersonaService $personas)
    {
        $authz = $this->authz();
        $persona = $personas->resolve($authz);
        $guide = TutorialCatalog::build($persona, $authz);
        $personaLabel = $personas->labels()[$persona] ?? ucfirst($persona);
        $isPortal = $authz->isClient();

        return view('company.tutorial.index', compact('guide', 'persona', 'personaLabel', 'isPortal', 'authz'));
    }
}
