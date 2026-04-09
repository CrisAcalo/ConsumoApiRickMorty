<?php

namespace App\Livewire\RickAndMorty;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

#[\Livewire\Attributes\Layout('layouts.app', ['title' => 'Rick & Morty'])]
class Index extends Component
{
    public string $tab = 'character';
    public int $page = 1;

    public string $characterName = '';
    public string $characterStatus = '';
    public string $characterSpecies = '';
    public string $characterType = '';
    public string $characterGender = '';
    public string $episodeName = '';
    public string $episodeCode = '';

    public function setTab($tabName)
    {
        $this->tab = $tabName;
        $this->page = 1;
        
        // Reset all filters when switching tabs
        $this->characterName = '';
        $this->characterStatus = '';
        $this->characterSpecies = '';
        $this->characterType = '';
        $this->characterGender = '';
        $this->episodeName = '';
        $this->episodeCode = '';
    }

    public function updated($property)
    {
        // Debugging payload receiving in Laravel 13 / Livewire 4
        \Illuminate\Support\Facades\Log::info("Livewire 4 property updated", [
            'property' => $property,
            'currentValue' => $this->$property,
            'type' => gettype($this->$property)
        ]);

        if (in_array($property, ['characterName', 'characterStatus', 'characterSpecies', 'characterType', 'characterGender', 'episodeName', 'episodeCode'])) {
            $this->page = 1;
        }
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    #[Computed]
    public function apiData()
    {
        $queryParams = ['page' => $this->page];

        if ($this->tab === 'character') {
            if ($this->characterName !== '') $queryParams['name'] = $this->characterName;
            if ($this->characterStatus !== '') $queryParams['status'] = $this->characterStatus;
            if ($this->characterSpecies !== '') $queryParams['species'] = $this->characterSpecies;
            if ($this->characterType !== '') $queryParams['type'] = $this->characterType;
            if ($this->characterGender !== '') $queryParams['gender'] = $this->characterGender;
        } elseif ($this->tab === 'episode') {
            if ($this->episodeName !== '') $queryParams['name'] = $this->episodeName;
            if ($this->episodeCode !== '') $queryParams['episode'] = $this->episodeCode;
        }

        $cacheKey = "rick_morty_{$this->tab}_" . md5(json_encode($queryParams));

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($queryParams) {
            $response = Http::get("https://rickandmortyapi.com/api/{$this->tab}", $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }

    public function render()
    {
        return view('livewire.rick-and-morty.index');
    }
}
