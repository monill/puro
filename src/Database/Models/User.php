<?php

namespace App\Database\Models;

class User extends Model {
    protected static $table = 'users';

    public function villages() {
        return Village::where('owner_id', $this->id)->get();
    }

    public function getVillages() {
        return Village::where('owner_id', $this->id)->get();
    }

    public function getCapitalVillage() {
        return Village::where('owner_id', $this->id)->where('is_capital', 1)->first();
    }

    public function getTotalPopulation() {
        $villages = $this->getVillages();
        return array_reduce($villages, function($total, $village) {
            return $total + $village->population;
        }, 0);
    }

    public function isOnline() {
        // Considera online se acessou nos últimos 5 minutos
        return isset($this->last_login) && 
               (time() - strtotime($this->last_login)) < 300;
    }

    public function getDisplayName() {
        return $this->username ?? 'Jogador Anônimo';
    }

    public function getTribeName() {
        $tribes = [
            1 => 'Romanos',
            2 => 'Teutões', 
            3 => 'Gálias'
        ];
        
        return $tribes[$this->tribe] ?? 'Desconhecida';
    }
}
