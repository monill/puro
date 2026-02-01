<?php

namespace App\Database\Models;

class Village extends Model {
    protected static $table = 'villages';

    public function owner() {
        return User::find($this->owner_id);
    }

    public function getOwner() {
        return User::find($this->owner_id);
    }

    public function getResources() {
        return [
            'wood' => $this->wood,
            'clay' => $this->clay,
            'iron' => $this->iron,
            'crop' => $this->crop
        ];
    }

    public function getProduction() {
        return [
            'wood' => $this->wood_production,
            'clay' => $this->clay_production,
            'iron' => $this->iron_production,
            'crop' => $this->crop_production
        ];
    }

    public function getStorage() {
        return [
            'max_store' => $this->max_store + $this->extra_max_store,
            'max_crop' => $this->max_crop + $this->extra_max_crop
        ];
    }

    public function canStoreResource($type, $amount) {
        $storage = $this->getStorage();
        $current = $this->$type;
        
        if ($type === 'crop') {
            return ($current + $amount) <= $storage['max_crop'];
        } else {
            return ($current + $amount) <= $storage['max_store'];
        }
    }

    public function getCoordinates() {
        return [
            'x' => $this->x,
            'y' => $this->y
        ];
    }

    public function getDistanceTo($otherVillage) {
        $dx = abs($this->x - $otherVillage->x);
        $dy = abs($this->y - $otherVillage->y);
        
        return sqrt(($dx * $dx) + ($dy * $dy));
    }

    public function isCapital() {
        return (bool) $this->is_capital;
    }

    public function getLoyaltyPercentage() {
        return min(100, max(0, $this->loyalty));
    }

    public function isUnderAttack() {
        // Verificar se há ataques em andamento
        // Implementar lógica quando tiver tabela de ataques
        return false;
    }
}
