<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Utilisateur extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table      = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps    = false;   // pas de created_at/updated_at dans la nouvelle BD

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'role', 'actif',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relations
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function logActivites(): HasMany
    {
        return $this->hasMany(LogActivite::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function rapports(): HasMany
    {
        return $this->hasMany(Rapport::class, 'id_utilisateur', 'id_utilisateur');
    }

    // Accesseurs
    public function getNomCompletAttribute(): string
    {
        $prefix = $this->isMedecin() ? 'Dr. ' : '';
        return $prefix . $this->prenom . ' ' . $this->nom;
    }

    public function getInitialesAttribute(): string
    {
        $p = mb_substr($this->prenom ?? '', 0, 1);
        $n = mb_substr($this->nom ?? '', 0, 1);
        return strtoupper($p . $n);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'medecin'       => 'Médecin',
            'infirmier'     => 'Infirmier(e)',
            'administratif' => 'Administratif',
            default         => ucfirst($this->role ?? ''),
        };
    }

    // Pas de created_at — on retourne une date vide
    public function getCreatedAtAttribute()
    {
        return null;
    }

    // Méthodes rôle
    public function isMedecin(): bool       { return $this->role === 'medecin'; }
    public function isInfirmier(): bool     { return $this->role === 'infirmier'; }
    public function isAdministratif(): bool { return $this->role === 'administratif'; }

    // Compatibilité Auth Laravel
    public function getAuthIdentifierName(): string  { return 'id_utilisateur'; }
    public function getAuthIdentifier()              { return $this->id_utilisateur; }
    public function getAuthPassword(): string        { return $this->password ?? ''; }
    public function getRememberToken(): ?string      { return null; }
    public function setRememberToken($value): void   {}
    public function getRememberTokenName(): string   { return ''; }
}
