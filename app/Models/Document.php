<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Document extends Model
{
    protected $table      = 'documents';
    protected $primaryKey = 'id_docum';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps    = false;

    protected $fillable = [
        'ipp',
        'id_utilisateur',
        'titre',
        'chemin_fichier',
        'service',
        'typedocument',
        'date_import',
        'mots_cles',
    ];

    protected $casts = [
        'date_import' => 'date',
    ];

    // Relations
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'ipp', 'ipp');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function logActivites(): HasMany
    {
        return $this->hasMany(LogActivite::class, 'id_docum', 'id_docum');
    }

    // Accesseurs
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->chemin_fichier ?? '', PATHINFO_EXTENSION));
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->typedocument) {
            'rapport_consultation'    => 'Rapport consultation',
            'compte_rendu_operatoire' => 'CR opératoire',
            'resultat_laboratoire'    => 'Résultat labo',
            'resultat_radiologie'     => 'Résultat radio',
            'ordonnance'              => 'Ordonnance',
            'courrier_medical'        => 'Courrier médical',
            default                   => 'Autre',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->typedocument) {
            'rapport_consultation'    => '📋',
            'compte_rendu_operatoire' => '🔬',
            'resultat_laboratoire'    => '🧪',
            'resultat_radiologie'     => '🩻',
            'ordonnance'              => '💊',
            'courrier_medical'        => '📧',
            default                   => '📄',
        };
    }

    public function getTypeCouleurAttribute(): string
    {
        return match ($this->typedocument) {
            'rapport_consultation'    => 'green',
            'compte_rendu_operatoire' => 'blue',
            'resultat_laboratoire'    => 'yellow',
            'resultat_radiologie'     => 'blue',
            'ordonnance'              => 'green',
            'courrier_medical'        => 'red',
            default                   => 'gray',
        };
    }

    // Compatibilité : les vues utilisent parfois $doc->id_document
    public function getIdDocumentAttribute(): int
    {
        return $this->id_docum;
    }

    // mots_cles maintenant disponible dans la BD
    public function getMotsClesAttribute(): ?string
    {
        return $this->attributes['mots_cles'] ?? null;
    }

    // Compatibilité : deleted_at n'existe plus → null (jamais supprimé)
    public function getDeletedAtAttribute(): ?Carbon
    {
        return null;
    }

    public function isPdf(): bool
    {
        return strtolower($this->extension) === 'pdf';
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    // Accesseur formaté pour la date d'import
    public function getDateImportFormatteeAttribute(): string
    {
        return $this->date_import ? $this->date_import->format('d/m/Y') : '—';
    }
}
