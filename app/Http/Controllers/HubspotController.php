<?php

namespace App\Http\Controllers;

use App\Services\HubSpotService;

class HubSpotController extends Controller
{
    protected $hubSpot;

    public function __construct(HubSpotService $hubSpot)
    {
        $this->hubSpot = $hubSpot;
    }

    public function contacts()
    {
        $contacts = $this->hubSpot->getContacts();

        return view('panel.hubspot.contacts', [
            'contacts' => $contacts['results'] ?? []
        ]);
    }
}
