<?php

namespace App\Enums;

enum DocumentType: string
{
    case AnnualReport = 'annual_report';
    case FinancialReport = 'financial_report';
    case Policy = 'policy';
    case Other = 'other';
}
