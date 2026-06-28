<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    protected $table      = 'patients';
    protected $primaryKey = 'ipp';
    public $incrementing  = false;
    protected $keyType    = 'int';
    public $timestamps    = false;

    protected $fillable = [
        'ipp',
        'cin',
        'numero_dossier',
        'nom',
        'prenom',
        'date_naissance',
        'service',
        'date_creation',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_creation'  => 'date',
    ];

    // Relations
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'ipp', 'ipp');
    }

    public function documentsActifs(): HasMany
    {
        // Pas de deleted_at dans la nouvelle BD — tous les documents sont actifs
        return $this->hasMany(Document::class, 'ipp', 'ipp');
    }

    // Accesseurs
    public function getNomCompletAttribute(): string
    {
        return strtoupper($this->nom ?? '') . ' ' . ($this->prenom ?? '');
    }

    public function getInitialesAttribute(): string
    {
        $p = mb_substr($this->prenom ?? '', 0, 1);
        $n = mb_substr($this->nom ?? '', 0, 1);
        return strtoupper($n . $p);
    }

    // IPP formaté sur 4 chiffres : 11 → "0011"
    public function getIppFormateAttribute(): string
    {
        return str_pad((string)$this->ipp, 4, '0', STR_PAD_LEFT);
    }

    public function getAgeAttribute(): int
    {
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }
}
