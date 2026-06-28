<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LogActivite extends Model
{
    protected $table      = 'log_activites';
    protected $primaryKey = 'id_logactivite';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps    = false;

    protected $fillable = [
        'id_utilisateur', 'id_docum', 'description', 'date_action', 'adresse_ip',
    ];

    protected $casts = ['date_action' => 'datetime'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'id_docum', 'id_docum');
    }

    // Déduire l'action depuis la description (colonne action supprimée)
    public function getActionAttribute(): string
    {
        $d = strtoupper($this->description ?? '');
        if (str_contains($d, 'DECONNEXION') || str_contains($d, 'DÉCONNEXION')) return 'DECONNEXION';
        if (str_contains($d, 'CONNEXION'))   return 'CONNEXION';
        if (str_contains($d, 'SUPPRESSION')) return 'SUPPRESSION';
        if (str_contains($d, 'UPLOAD') || str_contains($d, 'IMPORT'))           return 'UPLOAD';
        if (str_contains($d, 'RECHERCHE'))   return 'RECHERCHE';
        if (str_contains($d, 'CREATION') || str_contains($d, 'CRÉATION'))       return 'CREATION';
        if (str_contains($d, 'MODIFICATION'))return 'MODIFICATION';
        return 'CONSULTATION';
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'CONNEXION'    => 'Connexion',
            'DECONNEXION'  => 'Déconnexion',
            'UPLOAD'       => 'Import document',
            'CONSULTATION' => 'Consultation',
            'RECHERCHE'    => 'Recherche',
            'CREATION'     => 'Création',
            'MODIFICATION' => 'Modification',
            'SUPPRESSION'  => 'Suppression',
            default        => $this->description ?? 'Action',
        };
    }

    public function getActionCouleurAttribute(): string
    {
        return match ($this->action) {
            'CONNEXION', 'CREATION' => 'green',
            'UPLOAD'                => 'blue',
            'DECONNEXION'           => 'gray',
            'MODIFICATION', 'SUPPRESSION' => 'red',
            default                 => 'blue',
        };
    }

    public function getIdDocumentAttribute(): ?int { return $this->id_docum; }

    public static function enregistrer(int $idUtilisateur, string $description, ?int $idDocum = null, ?string $ip = null): void
    {
        static::create([
            'id_utilisateur' => $idUtilisateur,
            'id_docum'       => $idDocum,
            'description'    => $description,
            'date_action'    => now(),
            'adresse_ip'     => $ip ?? request()->ip(),
        ]);
    }
}
