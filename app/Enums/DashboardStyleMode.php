<?php

namespace App\Enums;

/**
 * How a tenant's dashboards pick their visual style.
 */
enum DashboardStyleMode: string
{
    /** The chosen style, else the one suggested by the event name, else the calendar's. */
    case Automatic = 'automatic';

    /** Only the styles chosen in the admin. */
    case Manual = 'manual';

    /** No style at all, whatever was chosen: the emergency switch. */
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Automatic => 'Automatique (calendrier et animations)',
            self::Manual => 'Manuel (styles choisis uniquement)',
            self::Disabled => 'Désactivé (aucun style)',
        };
    }
}
