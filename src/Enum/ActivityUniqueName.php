<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Identifiants programmatiques pour les ActivityType système.
 * Permet de retrouver un type par code sans dépendre de son ID ou slug.
 */
enum ActivityUniqueName: string
{
    case SENIORITY = 'seniority';
}
