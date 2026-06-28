<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapport extends Model
{
    protected $table      = 'rapports';
    protected $primaryKey = 'id_rapport';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps    = false;

    protected $fillable = [
        'id_utilisateur', 'titre', 'type_rapport',
        'periode_debut', 'periode_fin', 'date_generation',
    ];

    protected $casts = [
        'periode_debut'   => 'date',
        'periode_fin'     => 'date',
        'date_generation' => 'date',
        'type_rapport'    => 'integer',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
}
