<?php

namespace App\Enums;

/**
 * Whether a Program is an official Saba program or an independent partner —
 * left unresolved for entries like The Hunter Initiative pending stakeholder
 * verification (docs/audit/current-website-audit.md, stakeholder item 2).
 */
enum ProgramRelationshipType: string
{
    case OfficialProgram = 'official_program';
    case IndependentPartner = 'independent_partner';
    case Unconfirmed = 'unconfirmed';
}
